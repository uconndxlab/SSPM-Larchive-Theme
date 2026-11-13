@extends('layouts.app')

@php
    require_once public_path('themes/SSPM-Larchive-Theme/src/bootstrap.php');
    $presentation = (new \SSPM\Theme\ArchivePresentation)->item($item);
    $displayTitle = $presentation['title'];
    $recordedDate = $presentation['date'];
    $creator = $presentation['creator'];
    $segments = collect($presentation['segments']);
    $transcriptText = $presentation['transcriptText'];
    $subjects = collect($presentation['subjects']);
    $primary = $presentation['primary'];
    $active = $primary->first();
    $activeAvailable = $active && \SSPM\Theme\MediaAccess::exists($active);
@endphp

@section('content')
  <section class="hero single-story w-100 h-auto d-flex flex-column justify-content-center position-relative align-items-center mb-15">
    <img class="h-auto position-absolute hero-paper single-story z-4" src="{{ \App\Support\Theme::asset('assets/hero-paper.png') }}" alt="">
    <img class="w-100 h-auto position-relative hero-img single-story object-fit-cover z-3" src="{{ \App\Support\Theme::asset('assets/story-hero.png') }}" alt="">
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
        @if($presentation['image'])
          <img class="story-portrait" src="{{ $presentation['image'] }}" alt="{{ $item->title }}">
        @else
          <div class="story-portrait media-placeholder">No image available</div>
        @endif
        <div class="d-flex flex-column gap-1 gap-sm-5 gap-xl-1 justify-content-between">
          <h3 class="fs-lg-md text-center grotesk-mono-bold mb-0">{{ $displayTitle }}</h3>
          <span class="fs-md text-center grotesk-reg">{{ $item->collection?->title ?? ucfirst($item->item_type) . ' Story' }}</span>
        </div>
      </div>

      <div class="story-player d-flex flex-column px-2 px-sm-0 justify-content-center align-items-center w-100">
        @if(in_array($item->item_type, ['audio', 'video']))
          @if($primary->count() > 1)
            <label for="recording-select">Recording</label>
            <select id="recording-select" class="form-select mb-3">
              @foreach($primary as $recording)
                <option value="{{ \SSPM\Theme\MediaAccess::url($recording) }}" data-available="{{ \SSPM\Theme\MediaAccess::exists($recording) ? '1' : '0' }}">{{ $recording->metadata['label'] ?? $recording->filename }}</option>
              @endforeach
            </select>
          @endif
          <p id="playback-status" class="playback-status" role="status" aria-live="polite">{{ !$active ? 'No recording is available for this record.' : ($activeAvailable ? '' : 'The original recording is missing.') }}</p>
          @if($activeAvailable)
            @if($item->item_type === 'audio')
              <div class="story-audio d-flex flex-column position-relative z-1">
                <audio id="story-player" preload="metadata" data-available="1" src="{{ \SSPM\Theme\MediaAccess::url($active) }}"></audio>
                @include('partials.audio-waveform')
                <label for="audio-dur-slider" class="visually-hidden">Recording position</label>
                <input id="audio-dur-slider" type="range" class="audio-dur-slider z-4 mt-5 mb-2 position-relative" value="0" step=".25" min="0" max="100" disabled>
              </div>
              <div class="audio-settings d-flex position-relative gap-5 justify-content-center align-items-center pt-8">
                <span class="audio-time curr fs-md">0:00</span>
                <span class="audio-time full fs-md">—</span>
                <button type="button" class="audio-btn audio-reverse position-relative" aria-label="Back 30 seconds" disabled>
                  <span class="audio-skip-text fs-xs grotesk-mono-bold position-absolute">-30</span>
                  <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="currentColor" class="bi bi-arrow-clockwise back" viewBox="0 0 16 16" aria-hidden="true">
                    <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z"/>
                    <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466"/>
                  </svg>
                </button>
                <button type="button" class="audio-btn audio-toggle" aria-label="Play" aria-pressed="false"><span class="audio-toggle-icon"><i class="bi bi-play-circle fs-head"></i></span></button>
                <button type="button" class="audio-btn audio-forward position-relative" aria-label="Forward 30 seconds" disabled>
                  <span class="audio-skip-text fs-xs grotesk-mono-bold position-absolute">+30</span>
                  <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="currentColor" class="bi bi-arrow-clockwise forward" viewBox="0 0 16 16" aria-hidden="true">
                    <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z"/>
                    <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466"/>
                  </svg>
                </button>
              </div>
            @else
              <video id="story-player" controls class="w-100 rounded-2" preload="metadata" data-available="1" src="{{ \SSPM\Theme\MediaAccess::url($active) }}"></video>
            @endif
          @endif
          <div class="recording-downloads mt-3 d-flex flex-column gap-2">
            @foreach($primary as $recording)
              @if(\SSPM\Theme\MediaAccess::exists($recording))<a href="{{ \SSPM\Theme\MediaAccess::url($recording, true) }}" download="{{ $recording->filename }}">Download original recording: {{ $recording->filename }}</a>@endif
            @endforeach
          </div>
        @else
          @forelse($primary as $attachment)
            @if(\SSPM\Theme\MediaAccess::exists($attachment))
              @if(\SSPM\Theme\MediaAccess::kind($attachment) === 'image')
                <img class="w-100 rounded-2 mb-3" src="{{ \SSPM\Theme\MediaAccess::url($attachment) }}" alt="{{ $attachment->alt_text ?: $attachment->filename }}">
              @endif
              <a href="{{ \SSPM\Theme\MediaAccess::url($attachment, true) }}" download="{{ $attachment->filename }}">Download {{ $attachment->filename }}</a>
            @else
              <p>Original file missing: {{ $attachment->filename }}</p>
            @endif
          @empty
            <p>Media for this record is not available.</p>
          @endforelse
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
            <button type="button" class="overview-item py-3 border-0 text-start bg-transparent" data-start="{{ $segment['start'] ?? '' }}">
              <span class="item-timestamp">{{ $segment['start'] !== null ? '[' . gmdate('H:i:s', (int) $segment['start']) . ']' : '' }}</span>
              <h5 class="fs-lg grotesk-mono-bold mb-1">{{ $segment['title'] ?? 'Transcript segment' }}</h5>
              @if(!empty($segment['synopsis']))<span class="fs-body quote">{{ $segment['synopsis'] }}</span>@endif
            </button>
          @empty
            <div class="overview-item py-3">

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
          <p class="fs-body overview-text text-black pe-20 mt-3">{{ $presentation['description'] ?: 'No description is available for this record.' }}</p>
          @can('update', $item)
            <a href="{{ route('items.edit', $item) }}" class="d-inline-block mt-3 text-grey hover-underline">EDIT THIS RECORD</a>
          @endcan
        </div>
      </div>
    </div>

    <div class="row content transcript mt-7" role="tabpanel">
      <div class="col-12">
        <h4 class="fs-xl mb-4 grotesk-mono-bold">Transcript</h4>
        @if($transcriptText || $segments->isNotEmpty())
        <div class="transcript-panel">
          <div class="transcript-wrap d-flex gap-4 flex-column" tabindex="0" role="region" aria-label="Transcript text">
            @if($transcriptText)
              <article class="transcript-item">
                <p class="fs-body transcript-text">{{ $transcriptText }}</p>
              </article>
            @endif
            @foreach($segments as $segment)
              <article class="transcript-item">
                @if($segment['start'] !== null)<button type="button" class="segment-seek transcript-item-title fs-lg grotesk-mono-bold border-0 bg-transparent" data-start="{{ $segment['start'] }}">[{{ gmdate('H:i:s', (int) $segment['start']) }}]</button>@endif
                @if(!empty($segment['title']))<h5 class="transcript-item-title fs-lg grotesk-mono-bold">{{ $segment['title'] }}</h5>@endif
                <p class="fs-body transcript-text mt-3">{{ $segment['text'] ?? '' }}</p>
                @if(!empty($segment['synopsis']))<p>{{ $segment['synopsis'] }}</p>@endif
                @if(!empty($segment['keywords']))<p>{{ is_array($segment['keywords']) ? implode(', ', $segment['keywords']) : $segment['keywords'] }}</p>@endif
              </article>
            @endforeach
          </div>
        </div>
        @endif
        <div class="transcript-downloads mt-4 d-flex gap-2 flex-column">
          @foreach($presentation['transcripts'] as $transcript)
            @if(\SSPM\Theme\MediaAccess::exists($transcript))
              <a href="{{ \SSPM\Theme\MediaAccess::url($transcript, true) }}" download="{{ $transcript->filename }}">Download transcript: {{ $transcript->filename }}</a>
            @else
              <p>Transcript original missing: {{ $transcript->filename }}</p>
            @endif
          @endforeach
          @if(!$transcriptText && $segments->isEmpty() && $presentation['transcripts']->isEmpty())<p>No transcript is available for this record.</p>@endif
        </div>
      </div>
    </div>

    <div class="row content resources mt-7" role="tabpanel">
      <h4 class="fs-xl mb-4 grotesk-mono-bold">Resources</h4>
      <div class="col-12">
        @php
          $resourceImages = $presentation['resources']->filter(fn ($resource) => \SSPM\Theme\MediaAccess::kind($resource) === 'image' && \SSPM\Theme\MediaAccess::exists($resource))->take(5)->values();
        @endphp
        @if($resourceImages->isNotEmpty())
          <div class="row">
            <div class="col-12 d-grid resources-img-wrap">
              @foreach($resourceImages as $resource)
                <img class="resources-img {{ ['lg', 'sm-top', 'sm-bot', 'tall-1', 'tall-2'][$loop->index] }}" src="{{ \SSPM\Theme\MediaAccess::url($resource) }}" alt="{{ $resource->alt_text ?: $resource->filename }}">
              @endforeach
            </div>
          </div>
        @endif
        <div class="resource-downloads my-5">
          @forelse($presentation['resources'] as $resource)
            @php
              $resourceAvailable = \SSPM\Theme\MediaAccess::exists($resource);
              $resourceFormat = strtoupper(pathinfo($resource->filename, PATHINFO_EXTENSION)) ?: 'FILE';
              $resourceTitle = $resource->metadata['label'] ?? pathinfo($resource->filename, PATHINFO_FILENAME);
              $resourceDescription = $resource->metadata['description'] ?? $resource->filename;
            @endphp
            <{{ $resourceAvailable ? 'a' : 'div' }} class="resource-col d-flex align-items-center justify-content-between px-4 py-4 p-sm-5 rounded-2 bg-grey-extralight"
              @if($resourceAvailable) href="{{ \SSPM\Theme\MediaAccess::url($resource, true) }}" download="{{ $resource->filename }}" @endif>
              <div class="resource-text-wrap">
                <span class="fs-sm grotesk-reg download">{{ $resourceAvailable ? 'DOWNLOAD ' . $resourceFormat : 'FILE UNAVAILABLE' }}</span>
                <h5 class="resource-title fs-lg mt-3 mb-1 grotesk-mono-bold">{{ $resourceTitle }}</h5>
                <p class="resource-text fs-body">{{ $resourceAvailable ? $resourceDescription : 'Original file missing: ' . $resource->filename }}</p>
              </div>
              @if($resourceAvailable)
                <svg width="55" height="55" viewBox="0 0 34 34" class="resource-download-icon" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                  <path d="M17 25.5L6.375 14.875L9.35 11.7938L14.875 17.3188V0H19.125V17.3188L24.65 11.7938L27.625 14.875L17 25.5ZM4.25 34C3.08125 34 2.08073 33.5839 1.24844 32.7516C0.416146 31.9193 0 30.9188 0 29.75V23.375H4.25V29.75H29.75V23.375H34V29.75C34 30.9188 33.5839 31.9193 32.7516 32.7516C31.9193 33.5839 30.9188 34 29.75 34H4.25Z" fill="currentColor"/>
                </svg>
              @endif
            </{{ $resourceAvailable ? 'a' : 'div' }}>
          @empty
            <p>No supplemental resources are available for this record.</p>
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
        <a href="https://www.singsingprisonmuseum.org/contact.html" class="py-3 px-5 bg-purple border-0 rounded-2 text-white fs-4 text-decoration-none full-button">GET IN TOUCH <i class="bi bi-arrow-right-short"></i></a>
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
