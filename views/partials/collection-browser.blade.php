  {{-- Browse collection --}}
  <section class="container" id="collections">
    <div class="row gap-5 gap-md-0">
      <div class="col-12 col-md-4 d-flex flex-column gap-5">
        <h3 class="fs-xl grotesk-mono-bold letters-tight mb-0">BROWSE FULL COLLECTION</h3>

        <form id="collection-search-form" method="get" class="w-100 position-relative d-flex">
          <label for="collection-search" class="visually-hidden">Search the collection</label>
          <input type="search" id="collection-search" name="q" value="{{ request('q', request('search')) }}" placeholder="Search" class="search-bar rounded-2 w-100 bg-grey-extralight py-1">
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
