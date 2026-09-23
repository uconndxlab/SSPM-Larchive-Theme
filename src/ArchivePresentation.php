<?php

namespace SSPM\Theme;

use App\Models\Exhibit;
use App\Models\Item;
use Illuminate\Support\Facades\Storage;

class ArchivePresentation
{
    public const RELATIONS = ['media', 'featuredImage', 'transcript', 'metadata', 'collection', 'terms.taxonomy'];

    public static function seconds(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_numeric($value)) {
            return (float) $value >= 0 ? (float) $value : null;
        }
        if (! is_string($value) || ! preg_match('/^\d+:\d{2}(?::\d{2})?(?:\.\d+)?$/', $value)) {
            return null;
        }
        $seconds = 0;
        foreach (explode(':', $value) as $part) {
            $seconds = $seconds * 60 + (float) $part;
        }

        return $seconds;
    }

    public static function language(string $value): string
    {
        $value = trim($value);

        return match (strtolower(str_replace('_', '-', $value))) {
            'en', 'eng', 'english' => 'English',
            'en-us' => 'English (United States)',
            'en-gb' => 'English (United Kingdom)',
            'de', 'deu', 'ger', 'german' => 'German',
            'pl', 'pol', 'polish' => 'Polish',
            'it', 'ita', 'italian' => 'Italian',
            'es', 'spa', 'spanish' => 'Spanish',
            'fr', 'fra', 'fre', 'french' => 'French',
            'pt', 'por', 'portuguese' => 'Portuguese',
            'zh', 'zho', 'chi', 'chinese' => 'Chinese',
            default => $value,
        };
    }

    public function item(Item $item, bool $withTranscripts = true): array
    {
        $item->loadMissing(self::RELATIONS);
        // Reuse the loaded parent instead of querying it for each attachment.
        $attachments = $item->media->sortBy([['sort_order', 'asc'], ['id', 'asc']]);
        $attachments->each(fn ($m) => $m->setRelation('item', $item));
        $attachments = $attachments->filter(fn ($m) => MediaAccess::allowed($m));
        $transcripts = $attachments->filter(fn ($m) => $m->is_transcript || $m->id === $item->transcript_id);
        if ($item->transcript && ! $transcripts->contains('id', $item->transcript_id) && MediaAccess::allowed($item->transcript)) {
            $transcripts = $transcripts->push($item->transcript);
        }
        $primary = $attachments->filter(fn ($m) => ! $m->is_transcript && $m->id !== $item->transcript_id
            && empty($m->metadata['role']) && MediaAccess::kind($m) === $item->item_type)->values();
        $resources = $attachments->reject(fn ($m) => $primary->contains('id', $m->id) || $transcripts->contains('id', $m->id))->values();
        $metadata = $item->metadata->pluck('value', 'key');
        $languages = $item->metadata->where('key', 'dc.language')->pluck('value')->flatMap(fn ($value) => preg_split('/[,;]+/', $value))->map(fn ($s) => self::language($s))->filter()->unique()->values();
        $featured = $item->featuredImage;
        $image = $featured && MediaAccess::allowed($featured) && MediaAccess::kind($featured) === 'image' && MediaAccess::exists($featured)
            ? MediaAccess::url($featured) : null;
        $duration = self::seconds($primary->first()?->metadata['duration'] ?? $primary->first()?->meta['duration'] ?? null)
            ?? self::seconds($metadata['oh.duration'] ?? null);
        $segments = [];
        foreach ($withTranscripts ? $transcripts : [] as $transcript) {
            if (! MediaAccess::exists($transcript)) {
                continue;
            }
            $normalized = clone $transcript;
            $normalized->path = MediaAccess::path($transcript->path);
            $source = TranscriptSources::create($normalized);
            foreach ($source?->segments() ?? [] as $segment) {
                $segments[] = $segment + ['title' => null, 'synopsis' => null, 'keywords' => [], 'start' => null];
            }
        }
        if ($withTranscripts && ! $segments && ! empty($item->ohms_json['segments'])) {
            $segments = (new OhmsTranscriptSource($item))->segments();
        }

        return [
            'id' => $item->id, 'title' => $item->title, 'description' => $item->description ?: ($metadata['dc.description'] ?? ''),
            'collection' => $item->collection?->title ?? '', 'url' => route('items.show', $item), 'image' => $image,
            'date' => $metadata['dc.date'] ?? null, 'creator' => $metadata['dc.creator'] ?? null,
            'subjects' => $item->metadata->where('key', 'dc.subject')->pluck('value')->values()->all(),
            'categories' => $item->terms->pluck('name')->unique()->values()->all(), 'languages' => $languages->all(),
            'duration' => $duration === null ? null : $duration / 60,
            'mediaAvailable' => $primary->contains(fn ($m) => MediaAccess::exists($m)),
            'primary' => $primary, 'resources' => $resources, 'transcripts' => $transcripts,
            'segments' => $segments, 'transcriptText' => $item->ohms_json['transcript'] ?? null,
        ];
    }

    public function home(): array
    {
        $user = auth()->user();
        $items = Item::visibleTo($user);

        // Public browsing stays limited to published records, while admins can
        // review draft collection items from the themed archive homepage.
        if (! $user || ! $user->isAdmin()) {
            $items->published();
        }

        $cards = $items->with(self::RELATIONS)->orderBy('id')->get()
            ->map(fn ($item) => collect($this->item($item, false))->except(['primary', 'resources', 'transcripts', 'segments', 'transcriptText'])->all());
        $exhibit = Exhibit::published()->visibleTo(auth()->user())->where('featured', true)->orderBy('sort_order')->orderBy('id')->first();
        $featuredExhibit = null;
        if ($exhibit) {
            $path = MediaAccess::path($exhibit->cover_image ?? '');
            $featuredExhibit = [
                'title' => $exhibit->title, 'description' => $exhibit->description, 'url' => route('exhibits.show', $exhibit),
                'cover' => $path && Storage::disk('public')->exists($path) ? Storage::disk('public')->url($path) : null,
                'items' => $exhibit->items()->published()->visibleTo(auth()->user())->with(self::RELATIONS)->get()->map(fn ($i) => $this->item($i, false)),
            ];
        }

        return ['archiveCards' => $cards->all(), 'categories' => $cards->pluck('categories')->flatten()->unique()->sort()->values(),
            'languages' => $cards->pluck('languages')->flatten()->unique()->sort()->values(),
            'durationMax' => max(1, ceil($cards->max('duration') ?? 0)), 'featuredExhibit' => $featuredExhibit];
    }
}
