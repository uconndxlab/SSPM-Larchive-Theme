@extends('layouts.app')

@php($items->getCollection()->loadMissing('creator'))

@section('content')
<section class="container mb-10 workspace-page">
  <nav class="selections admin mt-5 d-flex flex-wrap text-black justify-content-start align-items-center gap-3 pb-2" aria-label="Workspace sections">
    <a class="archive grotesk-mono-bold selected" href="{{ route('admin.items.workspace') }}" aria-current="page">Archive</a>
    @if(Auth::user()->isAdmin())
      <a class="exhibitions grotesk-mono-bold" href="{{ route('exhibits.index') }}">Exhibitions</a>
      <a class="pending-approval grotesk-mono-bold" href="{{ route('admin.items.workspace', ['status' => 'in_review']) }}">Pending Approval <span class="workspace-count">{{ $statusCounts['in_review'] }}</span></a>
      <a class="users grotesk-mono-bold" href="{{ route('admin.users.index') }}">Users</a>
    @endif
  </nav>

  <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-4 pb-2">
    <h1 class="grotesk-mono-bold fs-lg mb-0">Archive</h1>
    <div class="d-flex flex-wrap gap-2">
      @if(Auth::user()->isAdmin())<a href="{{ route('export.index') }}" class="bg-blue admin-btn letters-spaced text-white fs-sm grotesk-mono-reg px-4 py-3 rounded-2 text-decoration-none">DOWNLOAD</a>@endif
      @can('create', App\Models\Item::class)<a href="{{ route('items.create') }}" class="bg-purple admin-btn letters-spaced text-white fs-sm grotesk-mono-reg px-4 py-3 rounded-2 text-decoration-none">UPLOAD</a>@endcan
    </div>
  </div>

  <form method="GET" action="{{ route('admin.items.workspace') }}" class="row g-3 mt-1 align-items-end workspace-filters">
    <div class="col-12 col-md-4">
      <label for="search-archive" class="visually-hidden">Search archive</label>
      <input type="search" id="search-archive" name="search" value="{{ request('search') }}" placeholder="Search archive..." class="search-archive rounded-2 w-100 bg-grey-extralight py-2">
    </div>
    <div class="col-12 col-md-4">
      <label for="workspace-status" class="visually-hidden">Status</label>
      <select id="workspace-status" name="status" class="status-filter grotesk-mono-reg w-100 rounded-2 py-2">
        @foreach(['draft' => 'Draft', 'in_review' => 'Pending Approval', 'published' => 'Published', 'archived' => 'Archived'] as $value => $label)
          <option value="{{ $value }}" @selected($status === $value)>{{ $label }} ({{ $statusCounts[$value] }})</option>
        @endforeach
      </select>
    </div>
    <div class="col-12 col-md-3">
      <label for="workspace-collection" class="visually-hidden">Collection</label>
      <select id="workspace-collection" name="collection_id" class="status-filter grotesk-mono-reg w-100 rounded-2 py-2">
        <option value="">All Collections</option>
        @foreach($collections as $collection)<option value="{{ $collection->id }}" @selected(request('collection_id') == $collection->id)>{{ $collection->title }}</option>@endforeach
      </select>
    </div>
    <div class="col-12 col-md-1"><button type="submit" class="workspace-filter-submit bg-purple text-white border-0 rounded-2 w-100 py-2" aria-label="Apply filters"><i class="bi bi-search"></i></button></div>
  </form>

  <div class="table-responsive mt-4">
    <table class="archive-table">
      <thead><tr><th>Name</th><th>Status</th><th>Date</th><th>Author</th><th><span class="visually-hidden">Open</span></th></tr></thead>
      <tbody>
        @forelse($items as $item)
          <tr>
            <td>@can('update', $item)<a class="workspace-item-link" href="{{ route('items.edit', $item) }}">{{ $item->title }}</a>@else<a class="workspace-item-link" href="{{ route('items.show', $item) }}">{{ $item->title }}</a>@endcan</td>
            <td><span class="badge {{ $item->status === 'in_review' ? 'pending' : ($item->status === 'published' ? 'published' : 'draft') }}">{{ $item->status === 'in_review' ? 'Pending Approval' : ucfirst($item->status) }}</span></td>
            <td>{{ $item->updated_at?->format('m/d/y') }}</td>
            <td>{{ $item->creator?->name ?? '—' }}</td>
            <td class="arrow-col">@can('update', $item)<a href="{{ route('items.edit', $item) }}" class="arrow" aria-label="Edit {{ $item->title }}"></a>@else<a href="{{ route('items.show', $item) }}" class="arrow" aria-label="View {{ $item->title }}"></a>@endcan</td>
          </tr>
        @empty
          <tr><td colspan="5">No items found for this status.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($items->hasPages())<div class="mt-4">{{ $items->links() }}</div>@endif
</section>
@endsection
