@extends('layouts.app')

@php
    require_once public_path('themes/SSPM-Larchive-Theme/src/bootstrap.php');
    $data = (new \SSPM\Theme\ArchivePresentation)->home();
    $archiveCards = $data['archiveCards'];
    $featuredExhibit = $data['featuredExhibit'];
    $categories = $data['categories'];
    $languages = $data['languages'];
    $durationMax = $data['durationMax'];
@endphp

@section('content')
  {{-- Hero (converted from theme index.html) --}}
  <section class="hero w-100 h-auto d-flex flex-column justify-content-center position-relative align-items-center mb-15">
    <img class="h-auto position-absolute hero-paper" src="{{ \App\Support\Theme::asset('assets/hero-paper.png') }}" alt="" />
    <img class="w-100 h-auto position-relative hero-img w-full object-fit-cover" src="{{ \App\Support\Theme::asset('assets/hero-bg.png') }}" alt="historical prison"/>
    <div class="position-absolute text-center hero-text-wrap">
      <h2 class="grotesk-mono-bold letters-tight text-center text-white mb-1 mb-sm-4 fs-head ">ORAL HISTORIES PROJECT</h2>
      <span class="grotesk-mono-reg text-center fs-md text-white">THE STORIES OF SING SING PRISON</span>
    </div>
  </section>

  @include('partials.religion-exhibition')

  @if($featuredExhibit)
  <section class="d-flex container mb-20">
    <div class="row religion-row gx-5 gx-xl-4 px-0 px-sm-2">
      <div class="col-12 col-xl-4 religion-text d-flex flex-column gap-3 gap-xxl-3 justify-content-between">
        <h3 class="fs-xl grotesk-mono-bold letters-tight religion-head">{{ $featuredExhibit['title'] }}</h3>
        <p class="fs-body mb-0">{{ $featuredExhibit['description'] }}</p>
        <a href="{{ $featuredExhibit['url'] }}" class="py-3 px-4 bg-purple border-0 rounded-2 text-white fs-4 text-decoration-none full-button">FULL EXHIBITION <i class="bi bi-arrow-right-short"></i></a>
      </div>
      <div class="col-12 col-xl-8 religion-people d-flex flex-md-row flex-column gap-2 mt-5 mt-xl-0">
        @foreach($featuredExhibit['items']->take(5) as $card)
          <a class="portrait rounded-2 {{ $loop->first ? 'active' : '' }}" href="{{ $card['url'] }}" aria-label="{{ $card['title'] }}">
            @if($card['image'])<img class="portrait-img" src="{{ $card['image'] }}" alt="{{ $card['title'] }}">@else<div class="media-placeholder">No image available</div>@endif
          </a>
        @endforeach
        @if($featuredExhibit['items']->isEmpty() && $featuredExhibit['cover'])
          <a class="portrait rounded-2 active" href="{{ $featuredExhibit['url'] }}" aria-label="{{ $featuredExhibit['title'] }}"><img class="portrait-img" src="{{ $featuredExhibit['cover'] }}" alt=""></a>
        @endif
      </div>
    </div>
  </section>
  @endif

  @include('partials.collection-browser')

  <section class="container my-10 ">
    <div class="contribute row py-5 px-3 px-lg-7 bg-grey-extralight rounded-2">
      <div class="col-12 col-lg-7 mb-5 mg-lg-auto  contribute-text d-flex flex-column justify-content-between">
        <h3 class="grotesk-mono-bold fs-xl">CONTRIBUTE TO THE ARCHIVE</h3>
        <p class="grotesk-reg fs-body mb-5">Help preserve the stories connected to Sing Sing by sharing oral histories, documents, photographs, and other archival materials.</p>
        <a href="https://www.singsingprisonmuseum.org/contact.html" class="py-3 px-5 bg-purple border-0 rounded-2 text-white fs-4 text-decoration-none full-button">GET IN TOUCH <i class="bi bi-arrow-right-short"></i></a>
      </div>

      <div class="col-12 col-lg-5 mb-auto mb-lg-0 mt-auto d-flex justify-content-center justify-content-lg-end">
        <img class="contribute-icons" alt="Contribute Icons" src="{{ \App\Support\Theme::asset('assets/contribute-icons.png') }}">
      </div>
    </div>
  </section>

  <script type="application/json" id="archive-data">{!! json_encode($archiveCards, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) !!}</script>
@endsection
