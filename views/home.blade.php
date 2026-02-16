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

  {{-- Main content (simplified for initial conversion) --}}
  <section class="d-flex container mb-20">
    <div class="row religion-row gx-5 gx-xl-4 px-0 px-sm-2">
      <div class="col-12 col-xl-4 religion-text d-flex flex-column gap-3 gap-xxl-3 justify-content-between">
        <h3 class="fs-xl grotesk-mono-bold letters-tight religion-head">RELIGION<span class="fs-4"> IN </span>INCARCERATION</h3>
        <p class="fs-body mb-0">Phasellus suscipit at ante a lobortis. Curabitur vehicula tristique enim in vestibulum. Suspendisse luctus finibus ligula, quis accumsan massa aliquam non.</p>
        <a href="#" class="btn py-3 px-4 bg-purple border-0 rounded-2 text-white fs-4 full-button">FULL EXHIBITION <i class="bi bi-arrow-right-short"></i></a>
        <span class="fs-6 text-grey-light">EXHIBITION OPEN TO THE PUBLIC UNTIL 02/22/2026</span>
      </div>

      <div class="col-12 col-xl-8 religion-people d-flex flex-md-row flex-column gap-2 mt-5 mt-xl-0">
        <div class="portrait active rounded-2"><img class="portrait-img" src="{{ \App\Support\Theme::asset('assets/portrait_1.jpg') }}" alt="portrait-1"/></div>
        <div class="portrait rounded-2"><img class="portrait-img" src="{{ \App\Support\Theme::asset('assets/portrait_2.jpg') }}" alt="portrait-2"/></div>
        <div class="portrait rounded-2"><img class="portrait-img" src="{{ \App\Support\Theme::asset('assets/portrait_3.jpg') }}" alt="portrait-3"/></div>
        <div class="portrait rounded-2"><img class="portrait-img" src="{{ \App\Support\Theme::asset('assets/portrait_4.jpg') }}" alt="portrait-4"/></div>
        <div class="portrait rounded-2"><img class="portrait-img" src="{{ \App\Support\Theme::asset('assets/portrait_5.jpg') }}" alt="portrait-5"/></div>
      </div>
    </div>
  </section>

  <section class="container my-10 ">
    <div class="contribute row py-5 px-3 px-lg-7 bg-grey-extralight rounded-2">
      <div class="col-12 col-lg-7 mb-5 mg-lg-auto  contribute-text d-flex flex-column justify-content-between">
        <h3 class="grotesk-mono-bold fs-xl">CONTRIBUTE TO THE ARCHIVE</h3>
        <p class="gotesk-reg fs-body mb-5">Phasellus suscipit at ante a lobortis. Curabitur vehicula tristique enim in vestibulum. Suspendisse luctus finibus ligula, quis accumsan massa aliquam non.</p>
        <a class="py-3 px-5 bg-purple border-0 rounded-2 mt-0 text-white fs-4 text-decoration-none full-button" href="#">GET IN TOUCH <i class="bi bi-arrow-right-short"></i></a>
      </div>

      <div class="col-12 col-lg-5 mb-auto mb-lg-0 mt-auto d-flex justify-content-center justify-content-lg-end">
        <img class="contribute-icons" alt="Contribute Icons" src="{{ \App\Support\Theme::asset('assets/contribute-icons.png') }}">
      </div>
    </div>
  </section>

@endsection
