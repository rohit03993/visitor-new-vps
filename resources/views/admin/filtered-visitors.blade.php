@extends('layouts.app')

@section('title', 'Filter Visitors by Tags - Task Book')
@section('page-title', 'Filter Visitors by Tags')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-filter me-2"></i>Filter Visitors by Tags
                </h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.filter-visitors') }}" id="filterForm">
                    <div class="row">
                        <!-- Tag Selection -->
                        <div class="col-12 mb-4">
                            <label class="form-label">Select Tags to Filter Visitors</label>
                            <div class="tag-filter-container">
                                @foreach($allTags as $tag)
                                    <div class="form-check form-check-inline mb-2">
                                        <input class="form-check-input" type="checkbox" 
                                               name="tags[]" value="{{ $tag->id }}" 
                                               id="filter_tag_{{ $tag->id }}"
                                               {{ in_array($tag->id, request('tags', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="filter_tag_{{ $tag->id }}">
                                            <span class="badge" style="background-color: {{ $tag->color }}; color: white;">
                                                {{ $tag->name }}
                                            </span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <div class="form-text">Select one or more tags to see visitors with those tags</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 text-center">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search me-1"></i>Show Visitors
                            </button>
                            <a href="{{ route('admin.filter-visitors') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Clear All
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Results -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-users me-2"></i>Filtered Results
                    <span class="badge bg-primary ms-2">{{ $visitors->total() }} visitors found</span>
                </h5>
            </div>
            <div class="card-body">
                @if($visitors->count() > 0)
                    <!-- Desktop Table View -->
                    <div class="table-responsive d-none d-lg-block">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Mobile Number</th>
                                    <th>Name</th>
                                    <th>Tags</th>
                                    <th>Total Interactions</th>
                                    <th>Last Updated By</th>
                                    <th>First Interaction</th>
                                    <th>Last Interaction</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($visitors as $visitor)
                                    <tr>
                                        <td>
                                            <strong>{{ $visitor->mobile_number }}</strong>
                                        </td>
                                        <td>
                                            <strong>{{ $visitor->name }}</strong>
                                        </td>
                                        <td>
                                            @if($visitor->tags->count() > 0)
                                                @foreach($visitor->tags as $tag)
                                                    <span class="badge me-1" style="background-color: {{ $tag->color }}; color: white;">
                                                        {{ $tag->name }}
                                                    </span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">No tags</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">{{ $visitor->interactions->count() }}</span>
                                        </td>
                                        <td>
                                            {{ $visitor->lastUpdatedBy->name ?? 'N/A' }}
                                        </td>
                                        <td>
                                            {{ \App\Helpers\DateTimeHelper::formatIndianDateTime($visitor->created_at) }}
                                        </td>
                                        <td>
                                            {{ \App\Helpers\DateTimeHelper::formatIndianDateTime($visitor->updated_at) }}
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.visitor-profile', $visitor->visitor_id) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye me-1"></i>View Profile
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="d-lg-none">
                        @foreach($visitors as $visitor)
                            <div class="card mb-3 border-left-primary">
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <strong>{{ $visitor->name }}</strong>
                                        </div>
                                        <div class="col-6 text-end">
                                            <span class="badge bg-primary">{{ $visitor->interactions->count() }} interactions</span>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-12">
                                            <small class="text-muted">Mobile: {{ $visitor->mobile_number }}</small>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-12">
                                            <small class="text-muted">Tags:</small>
                                            @if($visitor->tags->count() > 0)
                                                @foreach($visitor->tags as $tag)
                                                    <span class="badge me-1" style="background-color: {{ $tag->color }}; color: white;">
                                                        {{ $tag->name }}
                                                    </span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">No tags</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <small class="text-muted">First: {{ \App\Helpers\DateTimeHelper::formatIndianDate($visitor->created_at) }}</small>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Last: {{ \App\Helpers\DateTimeHelper::formatIndianDate($visitor->updated_at) }}</small>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <a href="{{ route('admin.visitor-profile', $visitor->visitor_id) }}" 
                                               class="btn btn-sm btn-primary w-100">
                                                <i class="fas fa-eye me-1"></i>View Profile
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        @include('components.pagination', ['paginator' => $visitors])
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No visitors found</h5>
                        <p class="text-muted">Try adjusting your filter criteria.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.border-left-primary {
    border-left: 4px solid #007bff !important;
}

.tag-filter-container {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 1rem;
    background-color: #f8f9fa;
    max-height: 200px;
    overflow-y: auto;
}

.tag-filter-container .form-check {
    margin-bottom: 0.5rem;
}

.tag-filter-container .badge {
    font-size: 0.875rem;
    padding: 0.5rem 0.75rem;
    border-radius: 0.375rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.tag-filter-container .badge:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
</style>
@endsection
