@extends('layouts.app')

@section('title', 'All Visitors - Task Book')
@section('page-title', 'All Visitors')

@section('content')
<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3">
            <div>
                <small class="text-paytm-muted">Total {{ $visitors->total() }} visitors found</small>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-paytm-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card-paytm paytm-fade-in">
            <div class="card-paytm-header">
                <h5 class="mb-0">
                    <i class="fas fa-users me-2"></i>All Visitors
                </h5>
            </div>
            <div class="card-paytm-body">
                @if($visitors->count() > 0)
                    <!-- Desktop Table View -->
                    <div class="table-responsive d-none d-lg-block">
                        <table class="table table-paytm">
                            <thead>
                                <tr>
                                    <th>Mobile Number</th>
                                    <th>Name</th>
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
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No visitors found</h5>
                        <p class="text-muted">No visitors have been registered yet.</p>
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
</style>
@endsection
