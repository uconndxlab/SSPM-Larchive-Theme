<section class="religion-exhibition d-flex container mb-20" aria-labelledby="religion-heading">
  <div class="row religion-row gx-5 gx-xl-4 px-0 px-sm-2 w-100">
    <div class="col-12 col-xl-4 religion-text d-flex flex-column gap-3 gap-xxl-3 justify-content-between">
      <h3 id="religion-heading" class="fs-xl grotesk-mono-bold letters-tight religion-head">RELIGION<span class="fs-4"> IN </span>INCARCERATION</h3>
      <p class="fs-body mb-0">Explore oral histories about religion, spirituality, and the experience of incarceration.</p>
      <a href="{{ route('exhibits.index') }}" class="py-3 px-4 bg-purple border-0 rounded-2 text-white fs-4 text-decoration-none full-button">FULL EXHIBITION <i class="bi bi-arrow-right-short" aria-hidden="true"></i></a>
    </div>
    <div class="col-12 col-xl-8 religion-people d-flex flex-md-row flex-column gap-2 mt-5 mt-xl-0" role="group" aria-label="Exhibition portraits">
      @for($portrait = 1; $portrait <= 5; $portrait++)
        <button type="button" class="portrait rounded-2 border-0 p-0 {{ $portrait === 1 ? 'active' : '' }}" aria-label="Expand exhibition portrait {{ $portrait }}" aria-pressed="{{ $portrait === 1 ? 'true' : 'false' }}">
          <img class="portrait-img" src="{{ \App\Support\Theme::asset('assets/portrait_' . $portrait . '.jpg') }}" alt="" loading="lazy">
        </button>
      @endfor
    </div>
  </div>
</section>
