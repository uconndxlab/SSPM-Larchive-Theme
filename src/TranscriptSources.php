<?php

namespace SSPM\Theme;

use App\Models\Media;
use App\Transcripts\TranscriptSource;
use App\Transcripts\TranscriptSourceFactory;
use SimpleXMLElement;

class TranscriptSources
{
    public static function create(Media $media): ?TranscriptSource
    {
        $format = $media->format ?: strtolower(pathinfo($media->filename, PATHINFO_EXTENSION));

        return match ($format) {
            'ohms', 'xml' => new OhmsTranscriptSource($media),
            'srt' => new SrtTranscriptSource($media),
            default => TranscriptSourceFactory::create($media),
        };
    }
}

// Adapt existing parsers inside this theme; segments() never generates files.
class SrtTranscriptSource extends \App\Transcripts\SrtTranscriptSource
{
    protected function parseSrt(string $content): array
    {
        return parent::parseSrt(str_replace(["\r\n", "\r"], "\n", $content));
    }

    protected function parseTimestamp(string $timestamp): float
    {
        return ArchivePresentation::seconds(str_replace(',', '.', $timestamp)) ?? 0;
    }
}

class OhmsTranscriptSource extends \App\Transcripts\OhmsTranscriptSource
{
    protected function parseSegments(SimpleXMLElement $xml): array
    {
        $segments = parent::parseSegments($xml);
        $record = $xml->record ?? $xml;
        $index = $record->index ?? $xml->index ?? null;
        $position = 0;
        foreach ($index?->point ?? [] as $point) {
            $segments[$position++]['title'] = $this->getString($point->title);
        }

        return $segments;
    }

    protected function convertLegacySegments(array $legacySegments): array
    {
        return array_map(fn ($segment) => [
            'start' => ArchivePresentation::seconds($segment['start_time'] ?? $segment['start'] ?? $segment['time'] ?? 0) ?? 0,
            'end' => null,
            'title' => $segment['title'] ?? null,
            'text' => $segment['partial_transcript'] ?? $segment['title'] ?? '',
            'synopsis' => $segment['synopsis'] ?? null,
            'keywords' => $segment['keywords'] ?? [],
        ], $legacySegments);
    }
}
