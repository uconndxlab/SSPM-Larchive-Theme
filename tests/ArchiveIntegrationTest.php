<?php

namespace SSPM\Theme\Tests;

use App\Models\Collection;
use App\Models\Exhibit;
use App\Models\Item;
use App\Models\Media;
use App\Models\SiteSetting;
use App\Models\Taxonomy;
use App\Models\Term;
use App\Models\User;
use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use SSPM\Theme\ArchivePresentation;
use SSPM\Theme\MediaAccess;
use Tests\TestCase;

class ArchiveIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        require_once public_path('themes/SSPM-Larchive-Theme/src/bootstrap.php');
        Storage::fake('public');
        SiteSetting::set('active_theme', 'sspm');
        (new AppServiceProvider($this->app))->boot();
    }

    private function item(array $attributes = []): Item
    {
        return Item::create($attributes + ['title' => 'Real interview', 'slug' => 'record-'.uniqid(), 'item_type' => 'audio', 'status' => 'published', 'visibility' => 'public']);
    }

    private function media(Item $item, string $name, array $attributes = [], ?string $contents = '0123456789'): Media
    {
        $media = $item->media()->create($attributes + ['filename' => $name, 'path' => 'items/'.$item->id.'/'.$name, 'mime_type' => 'application/octet-stream', 'size' => strlen($contents ?? ''), 'processing_status' => 'uploaded']);
        if ($contents !== null) {
            Storage::disk('public')->put(MediaAccess::path($media->path), $contents);
        }

        return $media;
    }

    private function cards(): array
    {
        $response = $this->get('/')->assertOk();
        preg_match('#<script type="application/json" id="archive-data">(.*?)</script>#s', $response->getContent(), $matches);

        return json_decode($matches[1], true, 512, JSON_THROW_ON_ERROR);
    }

    public function test_home_uses_real_records_metadata_terms_and_safe_serialization(): void
    {
        $collection = Collection::create(['title' => 'Museum voices', 'slug' => 'voices']);
        $item = $this->item(['title' => 'OHLRP_001_Actual_title_20000101 </script>', 'description' => 'Personal memories', 'collection_id' => $collection->id]);
        $item->setDC('dc.language', 'eng; pl');
        $item->setDC('dc.date', '1984-07-02');
        $item->setDC('dc.subject', 'Work, family');
        $taxonomy = Taxonomy::create(['key' => 'topics', 'name' => 'Topics']);
        $term = Term::create(['taxonomy_id' => $taxonomy->id, 'name' => 'Community', 'slug' => 'community']);
        $item->terms()->attach($term);
        $this->media($item, 'session.wav', ['metadata' => ['duration' => 4500]]);
        $image = $this->media($item, 'portrait.jpg');
        $item->update(['featured_image_id' => $image->id]);
        $this->item(['title' => 'Draft secret', 'status' => 'draft']);
        $this->item(['title' => 'Hidden secret', 'visibility' => 'hidden']);
        $this->item(['title' => 'Login secret', 'visibility' => 'authenticated']);
        $cards = $this->cards();
        $this->assertCount(1, $cards);
        $this->assertSame($item->title, $cards[0]['title']);
        $this->assertSame('1984-07-02', $cards[0]['date']);
        $this->assertSame(['English', 'Polish'], $cards[0]['languages']);
        $this->assertSame(['Community'], $cards[0]['categories']);
        $this->assertSame(75, $cards[0]['duration']);
        $this->assertSame('Museum voices', $cards[0]['collection']);
        $this->assertSame(MediaAccess::url($image), $cards[0]['image']);
        $this->get('/')->assertDontSee('collections-items.json')->assertDontSee('Phasellus')->assertDontSee('02/22/2026')->assertSee('max="75"', false);
    }

    public function test_absent_metadata_and_missing_files_use_neutral_states(): void
    {
        $item = $this->item();
        $this->media($item, 'missing.wav', [], null);
        $image = $this->media($item, 'missing.jpg', [], null);
        $item->update(['featured_image_id' => $image->id]);
        $card = $this->cards()[0];
        $this->assertNull($card['date']);
        $this->assertNull($card['duration']);
        $this->assertNull($card['image']);
        $this->get(route('items.show', $item))->assertOk()->assertSee('The original recording is missing.')
            ->assertDontSee('audio-temp')->assertDontSee('audio-waveform')->assertDontSee('story-portrait.png')->assertDontSee('resource-img-');
    }

    public function test_featured_exhibit_selection_and_visible_linked_items(): void
    {
        $this->get('/')->assertOk()->assertDontSee('FULL EXHIBITION');
        Exhibit::create(['title' => 'Hidden exhibit', 'status' => 'published', 'visibility' => 'hidden', 'featured' => true, 'sort_order' => -10]);
        Exhibit::create(['title' => 'Draft exhibit', 'status' => 'draft', 'visibility' => 'public', 'featured' => true, 'sort_order' => -5]);
        $first = Exhibit::create(['title' => 'Selected exhibition', 'description' => 'Real exhibition description', 'status' => 'published', 'visibility' => 'public', 'featured' => true, 'sort_order' => 0, 'cover_image' => 'public/exhibits/cover.jpg']);
        Exhibit::create(['title' => 'Second exhibition', 'status' => 'published', 'visibility' => 'public', 'featured' => true, 'sort_order' => 0]);
        Storage::disk('public')->put('exhibits/cover.jpg', 'image');
        $visible = $this->item(['title' => 'Visible exhibit story']);
        $hidden = $this->item(['title' => 'Hidden exhibit story', 'visibility' => 'hidden']);
        $first->items()->attach([$visible->id, $hidden->id]);
        $this->get('/')->assertOk()->assertSee('Selected exhibition')->assertSee('Real exhibition description')->assertSee(route('exhibits.show', $first))->assertDontSee('Second exhibition');
        $data = app(ArchivePresentation::class)->home()['featuredExhibit'];
        $this->assertCount(1, $data['items']);
        $this->assertSame($visible->id, $data['items'][0]['id']);
        $this->assertStringEndsWith('/storage/exhibits/cover.jpg', $data['cover']);
    }

    public function test_primary_recordings_order_wav_variants_and_all_resources(): void
    {
        $item = $this->item();
        $one = $this->media($item, 'one.wav', ['sort_order' => 2, 'mime_type' => 'audio/x-wav']);
        $two = $this->media($item, 'two.WAV', ['sort_order' => 2]);
        $supplement = $this->media($item, 'supplement.wav', ['sort_order' => 0, 'metadata' => ['role' => 'supplemental']]);
        for ($i = 0; $i < 7; $i++) {
            $this->media($item, "resource-$i.jpg", ['metadata' => ['role' => 'supplemental']]);
        }
        $data = app(ArchivePresentation::class)->item($item);
        $this->assertSame([$one->id, $two->id], $data['primary']->pluck('id')->all());
        $this->assertCount(8, $data['resources']);
        $this->get(route('items.show', $item))->assertOk()->assertSee('recording-select')->assertSee(MediaAccess::url($one))->assertSee(MediaAccess::url($two))->assertSee('resource-6.jpg');
        $this->get(route('items.show', $item))->assertSee('download="supplement.wav"', false);
    }

    public function test_legacy_paths_and_duration_fallback(): void
    {
        $item = $this->item();
        $item->setDC('oh.duration', '01:02:03');
        $media = $this->media($item, 'legacy.wav', ['path' => 'public/items/legacy.wav']);
        $this->assertEquals(62.05, app(ArchivePresentation::class)->item($item)['duration']);
        $this->get(MediaAccess::url($media))->assertOk();
    }

    public function test_transcripts_support_txt_vtt_srt_ohms_and_binary_downloads_without_writes(): void
    {
        $fixtures = [
            'txt' => ['Narrator (01:02): Actual words', 62],
            'vtt' => ["WEBVTT\n\n00:01:02.500 --> 00:01:05.000\nActual words\n", 62.5],
            'srt' => ["1\r\n00:01:02,500 --> 00:01:05,000\r\nActual words\r\n\r\n", 62.5],
            'xml' => ['<record><index><point><time>01:02</time><title>Segment title</title><synopsis>Summary</synopsis><keywords>work,family</keywords><partial_transcript>Actual words</partial_transcript></point></index></record>', 62],
        ];
        foreach ($fixtures as $ext => [$contents, $seconds]) {
            $item = $this->item();
            $this->media($item, 'record.wav');
            $transcript = $this->media($item, 'transcript.'.$ext, ['is_transcript' => true], $contents);
            $before = Storage::disk('public')->allFiles();
            $data = app(ArchivePresentation::class)->item($item);
            $this->assertEquals($seconds, $data['segments'][0]['start'], $ext);
            $this->assertStringContainsString('Actual words', $data['segments'][0]['text']);
            $this->get(route('items.show', $item))->assertOk()->assertSee('Actual words')->assertSee(MediaAccess::url($transcript, true));
            $this->assertSame($before, Storage::disk('public')->allFiles());
        }
        foreach (['docx', 'pdf'] as $ext) {
            $item = $this->item();
            $transcript = $this->media($item, 'transcript.'.$ext, [], 'binary-contents');
            $item->update(['transcript_id' => $transcript->id]);
            $this->get(route('items.show', $item))->assertOk()->assertSee('Download transcript:')->assertDontSee('binary-contents');
            $this->get(route('items.show', $item))->assertSee('download="'.$transcript->filename.'"', false);
            $this->assertCount(0, app(ArchivePresentation::class)->item($item)['primary']);
        }
    }

    public function test_legacy_ohms_normalizes_time_and_preserves_segment_fields(): void
    {
        $item = $this->item(['ohms_json' => ['segments' => [['time' => '01:02:03', 'title' => 'Real title', 'synopsis' => 'Real synopsis', 'keywords' => ['Work'], 'partial_transcript' => 'Partial words']]]]);
        $segment = app(ArchivePresentation::class)->item($item)['segments'][0];
        $this->assertEquals(3723, $segment['start']);
        $this->assertSame('Real title', $segment['title']);
        $this->assertSame('Partial words', $segment['text']);
        $this->get(route('items.show', $item))->assertOk()->assertSee('data-start="3723"', false)->assertSee('Real synopsis')->assertSee('Work');
    }

    public function test_media_authorization_follows_parent_and_attachment_visibility(): void
    {
        $public = $this->item();
        $open = $this->media($public, 'open.wav');
        $login = $this->media($public, 'login.pdf', ['metadata' => ['role' => 'supplemental', 'visibility' => 'authenticated']]);
        $hidden = $this->media($public, 'hidden.pdf', ['metadata' => ['role' => 'supplemental', 'visibility' => 'hidden']]);
        $private = $this->media($this->item(['visibility' => 'hidden']), 'private.wav');
        $draft = $this->media($this->item(['status' => 'draft']), 'draft.wav');
        $missing = $this->media($public, 'missing.wav', [], null);
        $this->assertTrue(MediaAccess::allowed($open));
        foreach ([$login, $hidden, $private, $draft] as $media) {
            $this->assertFalse(MediaAccess::allowed($media));
        }
        $this->get(MediaAccess::url($missing))->assertNotFound();
        $this->get(route('items.show', $public))->assertDontSee('login.pdf')->assertDontSee('hidden.pdf');
        $this->actingAs(User::factory()->create(['role' => 'contributor']));
        $this->assertTrue(MediaAccess::allowed($login));
        $this->assertFalse(MediaAccess::allowed($hidden));
        $this->actingAs(User::factory()->create(['role' => 'curator']));
        $this->assertTrue(MediaAccess::allowed($hidden));
        $this->assertTrue(MediaAccess::allowed($private));
        $this->assertTrue(MediaAccess::allowed($draft));
    }

    public function test_authenticated_home_and_hidden_attachments_are_filtered(): void
    {
        $item = $this->item(['title' => 'Member interview', 'visibility' => 'authenticated']);
        $image = $this->media($item, 'hidden.jpg', ['metadata' => ['visibility' => 'hidden']]);
        $transcript = $this->media($item, 'hidden.txt', ['is_transcript' => true, 'metadata' => ['visibility' => 'hidden']], 'Private transcript contents');
        $item->update(['featured_image_id' => $image->id, 'transcript_id' => $transcript->id]);
        $this->assertCount(0, $this->cards());
        $this->actingAs(User::factory()->create(['role' => 'contributor']));
        $cards = $this->cards();
        $this->assertCount(1, $cards);
        $this->assertNull($cards[0]['image']);
        $this->get(route('items.show', $item))->assertOk()->assertDontSee('Private transcript contents')->assertDontSee('hidden.txt')->assertDontSee('hidden.jpg');
        $this->actingAs(User::factory()->create(['role' => 'curator']));
        $this->get(route('items.show', $item))->assertOk()->assertSee('Private transcript contents')->assertSee('hidden.jpg');
    }

    public function test_image_document_and_video_items_use_their_real_attachments(): void
    {
        foreach (['image' => 'scan.jpg', 'document' => 'letter.pdf', 'video' => 'interview.mp4'] as $type => $filename) {
            $item = $this->item(['item_type' => $type]);
            $primary = $this->media($item, $filename);
            $unrelated = $this->media($item, 'supplement.wav', ['metadata' => ['role' => 'supplemental']]);
            $data = app(ArchivePresentation::class)->item($item);
            $this->assertSame([$primary->id], $data['primary']->pluck('id')->all());
            $this->assertSame([$unrelated->id], $data['resources']->pluck('id')->all());
            $this->get(route('items.show', $item))->assertOk()->assertSee(MediaAccess::url($primary, true));
            if ($type !== 'document') {
                $this->get(route('items.show', $item))->assertSee('src="'.MediaAccess::url($primary).'"', false);
            }
        }
    }
}
