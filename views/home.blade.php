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

  @if($featuredExhibit)
  <section class="d-flex container mb-20">
    <div class="row religion-row gx-5 gx-xl-4 px-0 px-sm-2">
      <div class="col-12 col-xl-4 religion-text d-flex flex-column gap-3">
        <h3 class="fs-xl grotesk-mono-bold letters-tight">{{ $featuredExhibit['title'] }}</h3>
        <p class="fs-body">{{ $featuredExhibit['description'] }}</p>
        <a href="{{ $featuredExhibit['url'] }}" class="btn py-3 px-4 bg-purple rounded-2 text-white fs-4">FULL EXHIBITION <i class="bi bi-arrow-right-short"></i></a>
      </div>
      <div class="col-12 col-xl-8 religion-people d-flex flex-md-row flex-column gap-2">
        @if($featuredExhibit['cover'])<img class="exhibit-cover" src="{{ $featuredExhibit['cover'] }}" alt="{{ $featuredExhibit['title'] }}">@endif
        @foreach($featuredExhibit['items'] as $card)
          <a class="portrait rounded-2 {{ $loop->first ? 'active' : '' }}" href="{{ $card['url'] }}">
            @if($card['image'])<img class="portrait-img" src="{{ $card['image'] }}" alt="{{ $card['title'] }}">@else<div class="media-placeholder">No image available</div>@endif
            <span>{{ $card['title'] }}</span>
          </a>
        @endforeach
      </div>
    </div>
  </section>
  @endif

  {{-- Browse collection --}}
  <section class="container" id="collections">
    <div class="row gap-5 gap-md-0">
      <div class="col-12 col-md-4 d-flex flex-column gap-5">
        <h3 class="fs-xl grotesk-mono-bold letters-tight mb-0">BROWSE FULL COLLECTION</h3>

        <form id="collection-search-form" method="get" class="w-100 position-relative d-flex">
          <label for="collection-search" class="visually-hidden">Search the collection</label>
          <input type="search" id="collection-search" name="q" placeholder="Search" class="search-bar rounded-2 w-100 bg-grey-extralight py-1">
          <button type="submit" class="search-btn bg-transparent" aria-label="Search"></button>
        </form>

        <div class="duration-wrap position-relative">
          <h4 class="fs-lg grotesk-mono-bold mb-0 filter-title">Duration<button type="button" class="filter-clear bg-transparent border-0 p-0" id="duration-clear">CLEAR</button></h4>
          <div class="slider-container">
            <div class="slider-track"></div>
            <label for="dur-slider-min" class="dur-slider-label min text-white bg-purple px-1 rounded-2 position-absolute text-nowrap">0 MIN</label>
            <input type="range" class="dur-range min" min="0" max="{{ $durationMax }}" step="1" value="0" id="dur-slider-min">
            <label for="dur-slider-max" class="dur-slider-label max text-white bg-purple px-1 rounded-2 position-absolute text-nowrap">{{ $durationMax }} MIN</label>
            <input type="range" class="dur-range max" min="0" max="{{ $durationMax }}" step="1" value="{{ $durationMax }}" id="dur-slider-max">
          </div>
        </div>

        <div class="categories-wrap">
          <h4 class="fs-lg grotesk-mono-bold mb-3 filter-title">Category<button type="button" class="filter-clear bg-transparent border-0 p-0" id="category-clear">CLEAR</button></h4>
          <div class="category-wrap ps-1 d-flex flex-column justify-content-between align-items-start gap-4">
            @foreach($categories as $label)
              <div class="category d-flex align-items-center gap-3">
                <input type="checkbox" id="cat-{{ $loop->index }}" class="category-check rounded-2" value="{{ $label }}">
                <label for="cat-{{ $loop->index }}" class="category-label fs-body">{{ $label }}</label>
              </div>
            @endforeach
          </div>
        </div>

        <div class="categories-wrap">
          <h4 class="fs-lg grotesk-mono-bold mb-3 filter-title">Native Language<button type="button" class="filter-clear bg-transparent border-0 p-0" id="language-clear">CLEAR</button></h4>
          <div class="category-wrap ps-1 d-flex flex-column justify-content-between align-items-start gap-4">
            @foreach($languages as $label)
              <div class="category languages d-flex align-items-center gap-3">
                <input type="checkbox" id="lang-{{ $loop->index }}" class="category-check rounded-2" value="{{ $label }}">
                <label for="lang-{{ $loop->index }}" class="category-label fs-body">{{ $label }}</label>
              </div>
            @endforeach
          </div>
        </div>

        <button type="button" class="clear-all px-3 py-1 bg-grey-extralight border-0 user-select-none">CLEAR ALL FILTERS</button>
      </div>

      <div class="col-12 col-md-8 d-flex flex-column">
        <div class="d-flex flex-column flex-lg-row align-items-end justify-content-between mb-2 ps-0 ps-md-4 ps-lg-0 items-head">
          <span class="fs-xs mb-4 mb-lg-0 ms-0 me-auto"><span id="collection-count" aria-live="polite">{{ count($archiveCards) }} OUT OF {{ count($archiveCards) }} STORIES</span></span>
          <div class="d-flex align-items-center gap-md-5 justify-content-between w-100">
            <div tabindex="0" role="group" aria-label="Collection view" class="view-select select-grid z-0 d-flex align-items-center bg-grey-extralight px-2 rounded-3 position-relative grotesk-mono-bold">
              <div class="selection-wrap z-2 position-relative active d-flex align-items-center" id="grid"><span class="px-4 position-relative user-select-none select-span">GRID VIEW</span></div>
              <div class="selection-wrap z-2 position-relative d-flex align-items-center" id="list"><span class="px-4 position-relative user-select-none select-span">LIST VIEW</span></div>
            </div>
            <button type="button" class="refresh bg-purple border-0" aria-label="Refresh collection"></button>
          </div>
        </div>
        <div class="collection-items">
          <ul class="collection-ul ps-0 ps-md-4 ps-lg-0 grid"></ul>
        </div>
      </div>
    </div>
  </section>

  <section class="container my-10 ">
    <div class="contribute row py-5 px-3 px-lg-7 bg-grey-extralight rounded-2">
      <div class="col-12 col-lg-7 mb-5 mg-lg-auto  contribute-text d-flex flex-column justify-content-between">
        <h3 class="grotesk-mono-bold fs-xl">CONTRIBUTE TO THE ARCHIVE</h3>
        <p class="grotesk-reg fs-body mb-5">Help preserve the stories connected to Sing Sing by sharing oral histories, documents, photographs, and other archival materials.</p>
        
      </div>

      <div class="col-12 col-lg-5 mb-auto mb-lg-0 mt-auto d-flex justify-content-center justify-content-lg-end">
        <img class="contribute-icons" alt="Contribute Icons" src="{{ \App\Support\Theme::asset('assets/contribute-icons.png') }}">
      </div>
    </div>
  </section>

  <script type="application/json" id="archive-data">{!! json_encode($archiveCards, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) !!}</script>
@endsection
