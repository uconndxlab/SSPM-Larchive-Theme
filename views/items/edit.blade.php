@extends('layouts.app')

@section('body_class', 'draft-page')

@section('content')
<section class="container draft mb-10">
  <nav class="selections admin mt-5" aria-label="Workspace"><a class="archive grotesk-mono-bold selected" href="{{ route('admin.items.workspace') }}">Archive</a></nav>
  <a class="d-inline-flex align-items-center text-black text-decoration-none mt-4" href="{{ route('admin.items.workspace') }}"><i class="bi bi-arrow-left-short fs-md draft-back-arrow"></i><span class="letters-spaced fs-sm grotesk-mono-reg">BACK TO FULL ARCHIVE</span></a>
  <h1 class="fs-lg grotesk-mono-bold text-start draft-title position-relative mt-3" data-status="{{ $item->status === 'in_review' ? 'Pending Approval' : ucfirst($item->status) }}">{{ $item->title }}</h1>

  <div class="row justify-content-between mt-4 g-5">
    <div class="col-12 col-lg-7">
      <h2 class="text-start fs-md grotesk-mono-bold">Details</h2>
      <ul class="nav nav-tabs my-4" id="itemEditTabs" role="tablist">
        <li class="nav-item" role="presentation"><button class="nav-link active" id="details-tab" data-bs-toggle="tab" data-bs-target="#details" type="button" role="tab" aria-controls="details" aria-selected="true">Item Details</button></li>
        <li class="nav-item" role="presentation"><button class="nav-link" id="media-tab" data-bs-toggle="tab" data-bs-target="#media" type="button" role="tab" aria-controls="media" aria-selected="false">Media &amp; Transcripts</button></li>
      </ul>
      <div class="tab-content" id="itemEditTabsContent">
        <div class="tab-pane fade show active" id="details" role="tabpanel" aria-labelledby="details-tab">
          <form id="item-edit-form" action="{{ route('items.update', $item) }}" method="POST" enctype="multipart/form-data" class="details-form">
            @csrf
            @method('PUT')
            @include('items._form')
            @include('items._ohms_upload_main')
          </form>
        </div>
        <div class="tab-pane fade" id="media" role="tabpanel" aria-labelledby="media-tab">
          @include('items._unattached_uploads')
          @include('items._media_and_transcript')
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-5 draft-sidebar d-flex flex-column gap-3">
      @include('items._tags_taxonomies')
      @include('items._featured_image')
      @include('items._workflow')
      <div class="p-3 bg-off-white rounded-2 d-flex flex-column gap-2">
        <button type="submit" form="item-edit-form" class="bg-blue admin-btn letters-spaced text-white fs-sm grotesk-mono-reg px-4 py-3 rounded-2">UPDATE</button>
        <a href="{{ route('items.show', $item) }}" class="text-center text-grey">View item</a>
      </div>
      @include('partials._user_tracking', ['model' => $item])
      @can('delete', $item)
        <form action="{{ route('items.destroy', $item) }}" method="POST" onsubmit="return confirm('Delete this item and all its media and metadata?')" class="mt-4">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-outline-danger">Delete item</button>
        </form>
      @endcan
    </div>
  </div>
</section>
@endsection
