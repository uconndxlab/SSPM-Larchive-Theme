@extends('layouts.app')

@php
    $storedFileExists = fn ($media) => $media && \Illuminate\Support\Facades\Storage::disk('public')->exists($media->path);
    $featuredMedia = $item->featuredImage;
    $audioMedia = $item->media->first(fn ($media) => str_starts_with($media->mime_type, 'audio/') && !$media->is_transcript && $storedFileExists($media));
    $videoMedia = $item->media->first(fn ($media) => str_starts_with($media->mime_type, 'video/') && !$media->is_transcript && $storedFileExists($media));
    $supplementalMedia = $item->media->filter(fn ($media) => !empty($media->metadata['role']) && $storedFileExists($media));
    $resourceImages = $supplementalMedia->filter(fn ($media) => str_starts_with($media->mime_type, 'image/'))->values();
    $downloadableMedia = $supplementalMedia->reject(fn ($media) => str_starts_with($media->mime_type, 'image/'))->values();
    $dcMetadata = $item->getDublinCore();
    $recordedDate = $dcMetadata['dc.date'] ?? optional($item->published_at ?? $item->created_at)->format('F j, Y');
    $creator = $dcMetadata['dc.creator'] ?? null;
    $displayTitle = $item->title;

    if (preg_match('/^OHLRP_\d+_([^_]+)_([^_]+)_(\d{8})/i', $item->title, $titleParts)) {
        $displayTitle = strtoupper($titleParts[2]) . '. ' . \Illuminate\Support\Str::title(strtolower($titleParts[1]));
        try {
            $recordedDate = \Carbon\Carbon::createFromFormat('Ymd', $titleParts[3])->format('F j, Y');
        } catch (\Throwable $exception) {
            // Keep the record's normal date when a legacy filename has an invalid date.
        }
    }
    $subjects = collect(explode(',', $dcMetadata['dc.subject'] ?? ''))->map(fn ($subject) => trim($subject))->filter();
    $segments = collect($item->ohms_json['segments'] ?? []);
    $transcriptText = $item->ohms_json['transcript'] ?? null;

    if (!$transcriptText && $item->transcript && \Illuminate\Support\Facades\Storage::disk('public')->exists($item->transcript->path)) {
        $transcriptText = \Illuminate\Support\Facades\Storage::disk('public')->get($item->transcript->path);
    }
@endphp

@section('content')
  <section class="hero single-story w-100 h-auto d-flex flex-column justify-content-center position-relative align-items-center mb-15">
    <img class="h-auto position-absolute hero-paper single-story z-4" src="{{ \App\Support\Theme::asset('assets/hero-paper.png') }}" alt="">
    <img class="w-100 h-auto position-relative hero-img single-story object-fit-cover z-3" src="{{ \App\Support\Theme::asset('assets/story-hero.png') }}" alt="Sing Sing oral history interview">
    <div class="position-absolute text-center hero-text-wrap">
      <h2 class="grotesk-mono-bold letters-tight text-center text-white mb-1 mb-sm-4 fs-head">{{ strtoupper($displayTitle) }}</h2>
      @if($recordedDate)
        <span class="grotesk-mono-reg text-center fs-md text-white">{{ strtoupper($recordedDate) }}</span>
      @endif
    </div>
  </section>

  <section class="container story-wrap d-flex flex-column">
    <a href="{{ route('home') }}#collections" class="fs-md-sm text-start story-back text-nowrap ps-4 mb-10 text-grey hover-underline pointer">FULL COLLECTION</a>

    <div class="story d-flex flex-column flex-xl-row justify-content-center align-items-center gap-2 gap-sm-5 px-10">
      <div class="d-flex flex-column flex-sm-row flex-xl-column story-info justify-content-center align-items-center me-0 me-xl-5 w-25 gap-3">
        @if($featuredMedia && str_starts_with($featuredMedia->mime_type, 'image/') && $storedFileExists($featuredMedia))
          <img class="story-portrait" src="{{ \Illuminate\Support\Facades\Storage::url($featuredMedia->path) }}" alt="{{ $featuredMedia->alt_text ?: $item->title }}">
        @else
          <img class="story-portrait" src="{{ \App\Support\Theme::asset('assets/story-portrait.png') }}" alt="{{ $item->title }}">
        @endif
        <div class="d-flex flex-column gap-1 gap-sm-5 gap-xl-1 justify-content-between">
          <h3 class="fs-lg-md text-center grotesk-mono-bold mb-0">{{ $displayTitle }}</h3>
          <span class="fs-md text-center grotesk-reg">{{ $item->collection?->title ?? ucfirst($item->item_type) . ' Story' }}</span>
        </div>
      </div>

      <div class="story-player d-flex flex-column px-2 px-sm-0 justify-content-center align-items-center w-100">
        @if($audioMedia || $item->item_type === 'audio')
          <div class="story-audio d-flex flex-column position-relative z-1">
            <audio id="story-audio" src="{{ $audioMedia ? \Illuminate\Support\Facades\Storage::url($audioMedia->path) : \App\Support\Theme::asset('assets/audio-temp.mp3') }}" data-fallback-src="{{ \App\Support\Theme::asset('assets/audio-temp.mp3') }}" preload="metadata"></audio>
            <img class="audio-timeline d-none d-sm-block" id="audio-timeline" src="{{ \App\Support\Theme::asset('assets/audio-waveform.svg') }}" alt="Audio waveform">
            <label for="audio-dur-slider" class="visually-hidden">Audio position</label>
            <input id="audio-dur-slider" type="range" class="audio-dur-slider z-4 mt-5 mb-2 position-relative" value="0" step=".25" min="0" max="100">
          </div>
          <div class="audio-settings d-flex position-relative gap-5 justify-content-center align-items-center pt-8">
            <span class="audio-time curr fs-md">0:00</span>
            <span class="audio-time full fs-md">{{ $audioMedia ? '0:00' : '10:00' }}</span>
            <button type="button" class="audio-btn audio-reverse position-relative" aria-label="Back 30 seconds">
              <span class="audio-skip-text fs-xs grotesk-mono-bold position-absolute">-30</span>
              <i class="bi bi-arrow-clockwise back fs-head" aria-hidden="true"></i>
            </button>
            <button type="button" class="audio-btn audio-toggle" aria-label="Play" aria-pressed="false">
              <span class="audio-toggle-icon" aria-hidden="true"></span>
            </button>
            <button type="button" class="audio-btn audio-forward position-relative" aria-label="Forward 30 seconds">
              <span class="audio-skip-text fs-xs grotesk-mono-bold position-absolute">+30</span>
              <i class="bi bi-arrow-clockwise forward fs-head" aria-hidden="true"></i>
            </button>
          </div>
        @elseif($videoMedia)
          <video controls class="w-100 rounded-2" preload="metadata">
            <source src="{{ \Illuminate\Support\Facades\Storage::url($videoMedia->path) }}" type="{{ $videoMedia->mime_type }}">
          </video>
        @else
          <div class="w-100 p-5 bg-grey-extralight rounded-2 text-center">
            <p class="fs-md mb-0">Media for this story is not available yet.</p>
          </div>
        @endif
      </div>
    </div>
  </section>

  <section class="container story-content my-8 d-flex flex-column w-100">
    <div class="selections d-flex text-black justify-content-start align-items-center gap-3 pb-2" role="tablist" aria-label="Story sections">
      <button type="button" class="overview grotesk-mono-bold selected" role="tab" aria-selected="true">Overview</button>
      <button type="button" class="transcript grotesk-mono-bold" role="tab" aria-selected="false">Transcript</button>
      <button type="button" class="resources grotesk-mono-bold" role="tab" aria-selected="false">Resources</button>
    </div>

    <div class="row content selected overview mt-7" role="tabpanel">
      <div class="col-12 col-lg-5 col-xl-4">
        <div class="overview-items mb-5 mb-lg-0 px-2 py-2 overflow-y-auto rounded-2 d-flex flex-column justify-content-start align-items-start bg-grey-extralight">
          @forelse($segments as $segment)
            <button type="button" class="overview-item py-3 border-0 text-start bg-transparent" data-start="{{ $segment['start_time'] ?? 0 }}">
              <span class="item-timestamp">[{{ gmdate('H:i:s', $segment['start_time'] ?? 0) }}]</span>
              <h5 class="fs-lg grotesk-mono-bold mb-1">{{ $segment['title'] ?? 'Untitled segment' }}</h5>
              @if(!empty($segment['synopsis']))<span class="fs-body quote">{{ $segment['synopsis'] }}</span>@endif
            </button>
          @empty
            <div class="overview-item py-3">
              <span class="item-timestamp">[{{ $item->created_at->format('Y') }}]</span>
              <h5 class="fs-lg grotesk-mono-bold mb-1">{{ $item->collection?->title ?? 'Oral History' }}</h5>
              <span class="fs-body quote">{{ \Illuminate\Support\Str::limit($item->description ?: 'Explore this interview and its accompanying archival materials.', 130) }}</span>
              @if($subjects->isNotEmpty())
                <div class="tags-wrap mt-3 d-flex flex-wrap gap-2">
                  @foreach($subjects->take(4) as $subject)<span class="tag fs-body text-white">{{ $subject }}</span>@endforeach
                </div>
              @endif
            </div>
          @endforelse
        </div>
      </div>
      <div class="col-12 col-lg-7 col-xl-8">
        <div class="position-relative text-content">
          <h4 class="fs-xl text-start grotesk-mono-bold mb-1">Overview</h4>
          @if($recordedDate || $creator)
            <span class="fs-md grotesk-mono-bold text-start">{{ $recordedDate ? 'Recorded ' . $recordedDate : '' }}{{ $creator ? ' by ' . $creator : '' }}</span>
          @endif
          <p class="fs-body overview-text text-black pe-20 mt-3">{{ $item->description ?: 'This oral history is part of the Sing Sing Prison Museum archive. Listen to the interview and explore the transcript and related resources available with this record.' }}</p>
          @can('update', $item)
            <a href="{{ route('items.edit', $item) }}" class="d-inline-block mt-3 text-grey hover-underline">EDIT THIS RECORD</a>
          @endcan
        </div>
      </div>
    </div>

    <div class="row content transcript mt-7" role="tabpanel">
      <div class="col-12">
        <h4 class="fs-xl mb-4 grotesk-mono-bold">Transcript</h4>
        <div class="transcript-wrap py-1 d-flex gap-4 flex-column">
          @if($transcriptText)
            <article class="transcript-item">
              <span class="fs-lg grotesk-mono-bold transcript-item-title">{{ $creator ?: $item->title }}</span>
              <p class="fs-body text-black mt-3 mb-0" style="white-space: pre-wrap">{{ $transcriptText }}</p>
            </article>
          @else
            <article class="transcript-item"><p class="fs-body mb-0">No transcript is available for this story.</p></article>
          @endif
        </div>
      </div>
    </div>

    <div class="row content resources mt-7" role="tabpanel">
      <h4 class="fs-xl mb-4 grotesk-mono-bold">Resources</h4>
      <div class="col-12">
        @if($resourceImages->isNotEmpty())
          <div class="resources-img-wrap d-grid">
            @foreach($resourceImages->take(5) as $index => $image)
              <img class="resources-img {{ ['lg', 'sm-top', 'sm-bot', 'tall-1', 'tall-2'][$index] }}" src="{{ \Illuminate\Support\Facades\Storage::url($image->path) }}" alt="{{ $image->alt_text ?: ($image->metadata['label'] ?? $image->filename) }}">
            @endforeach
          </div>
        @else
          <div class="resources-img-wrap d-grid">
            @foreach(['resource-img-1.png', 'resource-img-2.png', 'resource-img-3.png', 'resource-img-4.png', 'resource-img-5.png'] as $index => $image)
              <img class="resources-img {{ ['lg', 'sm-top', 'sm-bot', 'tall-1', 'tall-2'][$index] }}" src="{{ \App\Support\Theme::asset('assets/' . $image) }}" alt="Archival resource">
            @endforeach
          </div>
        @endif

        <div class="row px-2 justify-content-between my-5 g-3">
          @forelse($downloadableMedia as $resource)
            <a class="col-12 col-lg-6 resource-col d-flex flex-column flex-sm-row align-items-center justify-content-between px-2 py-4 p-sm-5 rounded-2 bg-grey-extralight text-decoration-none text-black" href="{{ \Illuminate\Support\Facades\Storage::url($resource->path) }}" target="_blank" rel="noopener">
              <div class="resource-text-wrap">
                <span class="fs-sm grotesk-reg download">DOWNLOAD {{ strtoupper(pathinfo($resource->filename, PATHINFO_EXTENSION)) }}</span>
                <h5 class="resource-title fs-lg mt-3 mb-1 grotesk-mono-bold">{{ $resource->metadata['label'] ?? $resource->filename }}</h5>
                <p class="resource-text fs-body mb-0">Supplemental material from this oral history record.</p>
              </div>
              <i class="bi bi-download fs-head text-purple" aria-hidden="true"></i>
            </a>
          @empty
            <div class="col-12 p-4 rounded-2 bg-grey-extralight">
              <p class="fs-body mb-0">Supplemental downloads for this record are currently unavailable.</p>
            </div>
          @endforelse
        </div>
      </div>
    </div>
  </section>

  <section class="container mt-4 mb-5">
    <div class="contribute row py-5 px-3 px-lg-7 bg-grey-extralight rounded-2">
      <div class="col-12 col-lg-7 mb-5 mb-lg-auto contribute-text d-flex flex-column justify-content-between">
        <h3 class="grotesk-mono-bold fs-xl">CONTRIBUTE TO THE ARCHIVE</h3>
        <p class="grotesk-reg fs-body mb-5">Help preserve the stories connected to Sing Sing by sharing oral histories, documents, photographs, and other archival materials.</p>
        <a class="py-3 px-5 bg-purple border-0 rounded-2 text-white fs-4 text-decoration-none full-button" href="#">GET IN TOUCH <i class="bi bi-arrow-right-short"></i></a>
      </div>
      <div class="col-12 col-lg-5 mb-auto mt-auto d-flex justify-content-center justify-content-lg-end">
        <img class="contribute-icons" alt="Contribute to the archive" src="{{ \App\Support\Theme::asset('assets/contribute-icons.png') }}">
      </div>
    </div>
  </section>
@endsection

@push('scripts')
  <script src="{{ \App\Support\Theme::asset('js/story.js') }}"></script>
@endpush
