@extends('layouts.app')

@section('title', 'Visitor History - VMS')
@section('page-title', 'Visitor History')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
            <h2 class="h4 mb-0">Visitor History</h2>
            <a href="{{ route('employee.dashboard') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>
</div>

<!-- Visitor Info -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-user me-2"></i>Visitor Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Mobile Number:</strong></td>
                                <td>{{ $visitor->mobile_number }}</td>
                            </tr>
                            <tr>
                                <td><strong>Latest Name:</strong></td>
                                <td>{{ $visitor->name }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Total Interactions (with you):</strong></td>
                                <td><span class="badge bg-primary">{{ $interactions->count() }}</span></td>
                            </tr>
                            <tr>
                                <td><strong>Last Interaction:</strong></td>
                                <td>{{ $interactions->first() ? $interactions->first()->created_at->format('M d, Y') : 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Interaction History -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-history me-2"></i>Interaction History (Your Assignments)
                </h5>
            </div>
            <div class="card-body">
                @if($interactions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Mode</th>
                                    <th>Purpose</th>
                                    <th>Address</th>
                                    <th>Name Entered</th>
                                    <th>Created By</th>
                                    <th>Remark History</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($interactions as $interaction)
                                    <tr>
                                        <td>{{ $interaction->created_at->format('M d, Y') }}</td>
                                        <td>{{ $interaction->created_at->format('H:i') }}</td>
                                        <td>
                                            <span class="badge bg-{{ $interaction->getModeBadgeColor() }}">
                                                {{ $interaction->mode }}
                                            </span>
                                        </td>
                                        <td>{{ $interaction->purpose }}</td>
                                        <td>{{ $interaction->address->address_name ?? 'N/A' }}</td>
                                        <td>{{ $interaction->name_entered }}</td>
                                        <td>{{ $interaction->createdBy->name }}</td>
                                        <td>
                                            @if($interaction->remarks->count() > 0)
                                                <div class="remark-timeline">
                                                                                                         @foreach($interaction->remarks as $remark)
                                                        <div class="remark-item mb-1">
                                                            <small class="text-muted">{{ $remark->created_at->format('M d, H:i') }}</small>
                                                            <div class="badge bg-{{ $remark->remark_text == 'NA' ? 'warning' : 'success' }}">
                                                                {{ Str::limit($remark->remark_text, 30) }}
                                                            </div>
                                                            <small class="text-muted d-block">
                                                                by {{ $remark->addedBy->name }} <strong>({{ $remark->addedBy->branch->branch_name ?? 'No Branch' }})</strong>
                                                            </small>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-muted">No remarks</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-history fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No interaction history</h5>
                        <p class="text-muted">This visitor has no interactions assigned to you.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
