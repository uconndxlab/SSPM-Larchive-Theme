@extends('layouts.app')

@section('body_class', 'draft-page')

@section('content')
<section class="container draft mb-10">
  <nav class="selections admin mt-5" aria-label="Workspace"><a class="archive grotesk-mono-bold selected" href="{{ route('admin.items.workspace') }}">Archive</a></nav>
  <a class="d-inline-flex align-items-center text-black text-decoration-none mt-4" href="{{ route('admin.items.workspace') }}"><i class="bi bi-arrow-left-short fs-md draft-back-arrow"></i><span class="letters-spaced fs-sm grotesk-mono-reg">BACK TO FULL ARCHIVE</span></a>
  <h1 class="fs-lg grotesk-mono-bold text-start draft-title position-relative mt-3" data-status="New">Create New Item</h1>
  <form action="{{ route('items.store') }}" method="POST" enctype="multipart/form-data" class="row justify-content-between mt-4 g-5">
    @csrf
    <div class="col-12 col-lg-7 details-form">
      <h2 class="text-start fs-md grotesk-mono-bold mb-4">Details</h2>
      @include('items._form')
    </div>
    <div class="col-12 col-lg-5 draft-sidebar d-flex flex-column gap-3">
      @include('items._tags_taxonomies')
      @include('items._workflow')
      <div class="p-3 bg-off-white rounded-2"><button type="submit" class="bg-purple admin-btn letters-spaced text-white fs-sm grotesk-mono-reg px-4 py-3 rounded-2 w-100">CREATE ITEM</button></div>
    </div>
  </form>
</section>
@endsection
