@extends('layouts.app')

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

  {{-- Featured exhibition --}}
  <section class="d-flex container mb-20">
    <div class="row religion-row gx-5 gx-xl-4 px-0 px-sm-2">
      <div class="col-12 col-xl-4 religion-text d-flex flex-column gap-3 gap-xxl-3 justify-content-between">
        <h3 class="fs-xl grotesk-mono-bold letters-tight religion-head">RELIGION<span class="fs-4"> IN </span>INCARCERATION</h3>
        <p class="fs-body mb-0">Phasellus suscipit at ante a lobortis. Curabitur vehicula tristique enim in vestibulum. Suspendisse luctus finibus ligula, quis accumsan massa aliquam non. Sed at vehicula mi. Aenean et felis non dui vestibulum ullamcorper id semper quam.</p>
        <a href="#" class="btn py-3 px-4 bg-purple border-0 rounded-2 text-white fs-4 full-button">FULL EXHIBITION <i class="bi bi-arrow-right-short"></i></a>
        <span class="fs-6 text-grey-light">EXHIBITION OPEN TO THE PUBLIC UNTIL 02/22/2026</span>
      </div>

      <div class="col-12 col-xl-8 religion-people d-flex flex-md-row flex-column gap-2 mt-5 mt-xl-0">
        <div class="portrait active rounded-2" tabindex="0" id="portrait-1"><img class="portrait-img" src="{{ \App\Support\Theme::asset('assets/portrait_1.jpg') }}" alt="Portrait from the oral histories collection"/></div>
        <div class="portrait rounded-2" tabindex="0" id="portrait-2"><img class="portrait-img" src="{{ \App\Support\Theme::asset('assets/portrait_2.jpg') }}" alt="Portrait from the oral histories collection"/></div>
        <div class="portrait rounded-2" tabindex="0" id="portrait-3"><img class="portrait-img" src="{{ \App\Support\Theme::asset('assets/portrait_3.jpg') }}" alt="Portrait from the oral histories collection"/></div>
        <div class="portrait rounded-2" tabindex="0" id="portrait-4"><img class="portrait-img" src="{{ \App\Support\Theme::asset('assets/portrait_4.jpg') }}" alt="Portrait from the oral histories collection"/></div>
        <div class="portrait rounded-2" tabindex="0" id="portrait-5"><img class="portrait-img" src="{{ \App\Support\Theme::asset('assets/portrait_5.jpg') }}" alt="Portrait from the oral histories collection"/></div>
      </div>
    </div>
  </section>

  {{-- Browse collection --}}
  <section class="container" id="collections">
    <div class="row gap-5 gap-md-0">
      <div class="col-12 col-md-4 d-flex flex-column gap-5">
        <h3 class="fs-xl grotesk-mono-bold letters-tight mb-0">BROWSE FULL COLLECTION</h3>

        <form onsubmit="return searchCollection()" method="get" class="w-100 position-relative d-flex">
          <label for="collection-search" class="visually-hidden">Search the collection</label>
          <input type="search" id="collection-search" name="q" placeholder="Search" class="search-bar rounded-2 w-100 bg-grey-extralight py-1">
          <button type="submit" class="search-btn bg-transparent" aria-label="Search"></button>
        </form>

        <div class="duration-wrap position-relative">
          <h4 class="fs-lg grotesk-mono-bold mb-0 filter-title">Duration<span class="filter-clear" id="duration-clear">CLEAR</span></h4>
          <div class="slider-container">
            <div class="slider-track"></div>
            <label for="dur-slider-min" class="dur-slider-label min text-white bg-purple px-1 rounded-2 position-absolute text-nowrap">10 MIN</label>
            <input type="range" class="dur-range min" min="0" max="100" step="8.33" value="0" id="dur-slider-min" oninput="handleDurMin()">
            <label for="dur-slider-max" class="dur-slider-label max text-white bg-purple px-1 rounded-2 position-absolute text-nowrap">60 MIN</label>
            <input type="range" class="dur-range max" min="0" max="100" step="8.33" value="99.96" id="dur-slider-max" oninput="handleDurMax()">
          </div>
        </div>

        <div class="categories-wrap">
          <h4 class="fs-lg grotesk-mono-bold mb-3 filter-title">Category<span class="filter-clear" id="category-clear">CLEAR</span></h4>
          <div class="category-wrap ps-1 d-flex flex-column justify-content-between align-items-start gap-4">
            @foreach(['afterlife' => 'Afterlife', 'forgiveness' => 'Forgiveness', 'spirituality' => 'Spirituality', 'transformation' => 'Transformation'] as $value => $label)
              <div class="category d-flex align-items-center gap-3">
                <input type="checkbox" id="cat-{{ $value }}" class="category-check rounded-2">
                <label for="cat-{{ $value }}" class="category-label fs-body">{{ $label }}</label>
              </div>
            @endforeach
          </div>
        </div>

        <div class="categories-wrap">
          <h4 class="fs-lg grotesk-mono-bold mb-3 filter-title">Native Language<span class="filter-clear" id="language-clear">CLEAR</span></h4>
          <div class="category-wrap ps-1 d-flex flex-column justify-content-between align-items-start gap-4">
            @foreach(['english' => 'English (United States)', 'german' => 'German', 'polish' => 'Polish', 'italian' => 'Italian'] as $value => $label)
              <div class="category languages d-flex align-items-center gap-3">
                <input type="checkbox" id="lang-{{ $value }}" class="category-check rounded-2">
                <label for="lang-{{ $value }}" class="category-label fs-body {{ $value }}">{{ $label }}</label>
              </div>
            @endforeach
          </div>
        </div>

        <button type="button" class="clear-all px-3 py-1 bg-grey-extralight border-0 user-select-none">CLEAR ALL FILTERS</button>
      </div>

      <div class="col-12 col-md-8 d-flex flex-column">
        <div class="d-flex flex-column flex-lg-row align-items-end justify-content-between mb-2 ps-0 ps-md-4 ps-lg-0 items-head">
          <span class="fs-xs mb-4 mb-lg-0 ms-0 me-auto">12 OUT OF 25 STORIES</span>
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
        <p class="grotesk-reg fs-body mb-5">Phasellus suscipit at ante a lobortis. Curabitur vehicula tristique enim in vestibulum. Suspendisse luctus finibus ligula, quis accumsan massa aliquam non. Sed at vehicula mi. Aenean et felis non dui vestibulum ullamcorper id semper quam.</p>
        <a class="py-3 px-5 bg-purple border-0 rounded-2 mt-0 text-white fs-4 text-decoration-none full-button" href="#">GET IN TOUCH <i class="bi bi-arrow-right-short"></i></a>
      </div>

      <div class="col-12 col-lg-5 mb-auto mb-lg-0 mt-auto d-flex justify-content-center justify-content-lg-end">
        <img class="contribute-icons" alt="Contribute Icons" src="{{ \App\Support\Theme::asset('assets/contribute-icons.png') }}">
      </div>
    </div>
  </section>

@endsection
