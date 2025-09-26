@extends('layouts.app')

@section('title', 'Filter Interactions by Tags - Task Book')
@section('page-title', 'Filter Interactions by Tags')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-filter me-2"></i>Filter Interactions by Tags
                </h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.filter-interactions') }}" id="filterForm">
                    <div class="row">
                        <!-- Tag Selection -->
                        <div class="col-12 mb-4">
                            <label class="form-label">Select Tags to Filter Interactions</label>
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
                            <div class="form-text">Select one or more tags to see interactions for visitors with those tags</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 text-center">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search me-1"></i>Show Interactions
                            </button>
                            <a href="{{ route('admin.filter-interactions') }}" class="btn btn-secondary">
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
                    <i class="fas fa-handshake me-2"></i>Filtered Results
                    <span class="badge bg-primary ms-2">{{ $interactions->total() }} interactions found</span>
                </h5>
            </div>
            <div class="card-body">
                @if($interactions->count() > 0)
                    <!-- Desktop Table View -->
                    <div class="table-responsive d-none d-lg-block">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Visitor</th>
                                    <th>Tags</th>
                                    <th>Mode</th>
                                    <th>Purpose</th>
                                    <th>Meeting With</th>
                                    <th>Address</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($interactions as $interaction)
                                    <tr>
                                        <td>
                                            <div>
                                                <strong>{{ \App\Helpers\DateTimeHelper::formatIndianDate($interaction->created_at) }}</strong>
                                                <br>
                                                <small class="text-muted">{{ \App\Helpers\DateTimeHelper::formatIndianTime($interaction->created_at) }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $interaction->name_entered }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $interaction->visitor->mobile_number }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            @if($interaction->visitor->tags->count() > 0)
                                                @foreach($interaction->visitor->tags as $tag)
                                                    <span class="badge me-1" style="background-color: {{ $tag->color }}; color: white;">
                                                        {{ $tag->name }}
                                                    </span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">No tags</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $interaction->getModeBadgeColor() }}">
                                                {{ $interaction->mode }}
                                            </span>
                                        </td>
                                        <td>
                                            <small>{{ $interaction->purpose }}</small>
                                        </td>
                                        <td>
                                            <div>
                                                <small>{{ $interaction->meetingWith->name ?? 'No Data' }}</small>
                                                <br>
                                                <span class="badge bg-info">
                                                    {{ $interaction->meetingWith->branch->branch_name ?? 'No Data' }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <small>{{ $interaction->address->address_name ?? 'N/A' }}</small>
                                        </td>
                                        <td>
                                            @if($interaction->is_completed)
                                                <span class="badge bg-success">Completed</span>
                                            @else
                                                <span class="badge bg-warning">Pending</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.visitor-profile', $interaction->visitor->visitor_id) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye me-1"></i>View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="d-lg-none">
                        @foreach($interactions as $interaction)
                            <div class="card mb-3 border-left-primary">
                                <div class="card-body">
                                    <!-- Header with Date and Status -->
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h6 class="mb-1">
                                                <strong>{{ \App\Helpers\DateTimeHelper::formatIndianDate($interaction->created_at) }}</strong>
                                            </h6>
                                            <small class="text-muted">
                                                <i class="fas fa-clock me-1"></i>
                                                {{ \App\Helpers\DateTimeHelper::formatIndianTime($interaction->created_at) }}
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            @if($interaction->is_completed)
                                                <span class="badge bg-success">Completed</span>
                                            @else
                                                <span class="badge bg-warning">Pending</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Visitor Details -->
                                    <div class="row mb-2">
                                        <div class="col-12">
                                            <small class="text-muted">Visitor:</small>
                                            <div><strong>{{ $interaction->name_entered }}</strong></div>
                                            <small class="text-muted">{{ $interaction->visitor->mobile_number }}</small>
                                        </div>
                                    </div>

                                    <!-- Tags -->
                                    <div class="row mb-2">
                                        <div class="col-12">
                                            <small class="text-muted">Tags:</small>
                                            @if($interaction->visitor->tags->count() > 0)
                                                @foreach($interaction->visitor->tags as $tag)
                                                    <span class="badge me-1" style="background-color: {{ $tag->color }}; color: white;">
                                                        {{ $tag->name }}
                                                    </span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">No tags</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Meeting Details -->
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <small class="text-muted">Mode:</small>
                                            <div>
                                                <span class="badge bg-{{ $interaction->getModeBadgeColor() }}">
                                                    {{ $interaction->mode }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Purpose:</small>
                                            <div>{{ $interaction->purpose }}</div>
                                        </div>
                                    </div>

                                    <!-- Meeting With and Address -->
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <small class="text-muted">Meeting With:</small>
                                            <div>{{ $interaction->meetingWith->name ?? 'No Data' }}</div>
                                            <span class="badge bg-info">
                                                {{ $interaction->meetingWith->branch->branch_name ?? 'No Data' }}
                                            </span>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Address:</small>
                                            <div>{{ $interaction->address->address_name ?? 'N/A' }}</div>
                                        </div>
                                    </div>

                                    <!-- Action Button -->
                                    <div class="row">
                                        <div class="col-12">
                                            <a href="{{ route('admin.visitor-profile', $interaction->visitor->visitor_id) }}" 
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
                        @include('components.pagination', ['paginator' => $interactions])
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No interactions found</h5>
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
