@extends('layouts.app')

@php
    require_once public_path('themes/SSPM-Larchive-Theme/src/bootstrap.php');
    $data = (new \SSPM\Theme\ArchivePresentation)->home();
    $archiveCards = $data['archiveCards'];
    $categories = $data['categories'];
    $languages = $data['languages'];
    $durationMax = $data['durationMax'];
@endphp

@section('content')
  <div class="container mt-5 mb-10"><h1 class="fs-xl grotesk-mono-bold letters-tight">FULL ARCHIVE</h1></div>
  @include('partials.collection-browser')
  <script type="application/json" id="archive-data">{!! json_encode($archiveCards, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) !!}</script>
@endsection
