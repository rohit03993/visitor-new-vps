@extends('layouts.app')

@section('title', 'Search Results - VMS')
@section('page-title', 'Search Results')

@section('content')
<!-- Mobile-Optimized Header -->
<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
            <div>
                <h2 class="h4 mb-1">Search Results</h2>
                @if(isset($mobileNumber))
                    <small class="text-muted">Searching for: +91{{ $mobileNumber }} ({{ $interactions->count() }} found)</small>
                @else
                    <small class="text-muted">Showing {{ $interactions->count() }} of {{ $interactions->total() }} interactions</small>
                @endif
            </div>
            <div class="d-flex gap-2">
                @if(isset($mobileNumber) && $interactions->count() > 0)
                    <a href="{{ route('frontdesk.visitor-form', ['mobile' => $mobileNumber, 'name' => $interactions->first()->name_entered]) }}" 
                       class="btn btn-success btn-sm">
                        <i class="fas fa-plus me-1"></i><span class="d-none d-sm-inline">Add Revisit</span>
                    </a>
                @endif
                <form method="POST" action="{{ route('frontdesk.search-visitors') }}" class="d-flex gap-2">
                    @csrf
                    <div class="input-group" style="width: 200px;">
                        <span class="input-group-text">+91</span>
                        <input type="tel" class="form-control form-control-sm" name="mobile_number" 
                               required maxlength="10" placeholder="Mobile number" 
                               value="{{ $mobileNumber ?? old('mobile_number') }}"
                               inputmode="numeric" pattern="[0-9]{10}"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
                @if(auth()->user()->getAllowedBranchIds('can_download_excel'))
                    <button class="btn btn-outline-primary btn-sm" onclick="showPrintOptions()">
                        <i class="fas fa-print me-1"></i><span class="d-none d-sm-inline">Print</span>
                    </button>
                @endif
                <a href="{{ route('frontdesk.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i><span class="d-none d-sm-inline">Back</span>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Search Results Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body p-0">
                @if($interactions->count() > 0)
                    <!-- Desktop Table View -->
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-hover table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width: 80px;">Date</th>
                                    <th style="min-width: 70px;">Time</th>
                                    <th style="min-width: 120px;">Visitor</th>
                                    <th style="min-width: 100px;">Mobile</th>
                                    <th style="min-width: 80px;">Mode</th>
                                    <th style="min-width: 100px;">Purpose</th>
                                    <th style="min-width: 100px;">Meeting With</th>
                                    <th style="min-width: 100px;">Branch</th>
                                    <th style="min-width: 100px;">Address</th>
                                    <th style="min-width: 80px;">Status</th>
                                    <th style="min-width: 80px;">Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($interactions as $interaction)
                                    <tr>
                                        <td>
                                            <small class="text-muted">{{ \App\Helpers\DateTimeHelper::formatIndianDate($interaction->created_at) }}</small>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ \App\Helpers\DateTimeHelper::formatIndianTime($interaction->created_at) }}</small>
                                        </td>
                                        <td>
                                            <div class="fw-medium">{{ $interaction->name_entered }}</div>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $interaction->visitor->mobile_number }}</small>
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
                                            <small>{{ $interaction->meetingWith->name ?? 'No Data' }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                {{ $interaction->meetingWith->branch->branch_name ?? 'No Data' }}
                                            </span>
                                        </td>
                                        <td>
                                            <small>{{ $interaction->address->address_name ?? 'N/A' }}</small>
                                        </td>
                                        <td>
                                            @if($interaction->hasPendingRemarks())
                                                <span class="badge bg-warning">Pending</span>
                                            @else
                                                <span class="badge bg-success">Done</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                @if(auth()->user()->canViewRemarksForInteraction($interaction))
                                                    <button class="btn btn-sm btn-outline-primary" 
                                                            onclick="viewRemarks({{ $interaction->interaction_id }})"
                                                            title="View Remarks">
                                                        <i class="fas fa-comments me-1"></i>View
                                                    </button>
                                                @else
                                                    <span class="text-muted small">No Access</span>
                                                @endif
                                                <a href="{{ route('frontdesk.visitor-form', ['mobile' => $interaction->visitor->mobile_number, 'name' => $interaction->name_entered]) }}" 
                                                   class="btn btn-sm btn-success" title="Add Revisit">
                                                    <i class="fas fa-plus"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="d-md-none">
                        @foreach($interactions as $interaction)
                            <div class="card mb-3 interaction-card">
                                <div class="card-body">
                                    <!-- Header with Date, Time, and Status -->
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h6 class="card-title mb-1">{{ $interaction->name_entered }}</h6>
                                            <small class="text-muted">
                                                <i class="fas fa-calendar me-1"></i>
                                                {{ \App\Helpers\DateTimeHelper::formatIndianDate($interaction->created_at) }}
                                                <i class="fas fa-clock ms-2 me-1"></i>
                                                {{ \App\Helpers\DateTimeHelper::formatIndianTime($interaction->created_at) }}
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            @if($interaction->hasPendingRemarks())
                                                <span class="badge bg-warning">Pending</span>
                                            @else
                                                <span class="badge bg-success">Completed</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Visitor Details -->
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <small class="text-muted">Mobile:</small><br>
                                            <strong>{{ $interaction->visitor->mobile_number }}</strong>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Mode:</small><br>
                                            <span class="badge bg-{{ $interaction->getModeBadgeColor() }}">
                                                {{ $interaction->mode }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Meeting Details -->
                                    <div class="row mb-2">
                                        <div class="col-12">
                                            <small class="text-muted">Purpose:</small><br>
                                            <strong>{{ $interaction->purpose }}</strong>
                                        </div>
                                    </div>

                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <small class="text-muted">Meeting With:</small><br>
                                            <strong>{{ $interaction->meetingWith->name ?? 'No Data' }}</strong>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Branch:</small><br>
                                            <span class="badge bg-info">
                                                {{ $interaction->meetingWith->branch->branch_name ?? 'No Data' }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Address -->
                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <small class="text-muted">Address:</small><br>
                                            <strong>{{ $interaction->address->address_name ?? 'N/A' }}</strong>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="d-flex justify-content-end gap-2">
                                        @if(auth()->user()->canViewRemarksForInteraction($interaction))
                                            <button class="btn btn-outline-primary btn-sm" 
                                                    onclick="viewRemarks({{ $interaction->interaction_id }})">
                                                <i class="fas fa-comments me-1"></i>View Remarks
                                            </button>
                                        @else
                                            <span class="text-muted small">
                                                <i class="fas fa-lock me-1"></i>No Access to Remarks
                                            </span>
                                        @endif
                                        <a href="{{ route('frontdesk.visitor-form', ['mobile' => $interaction->visitor->mobile_number, 'name' => $interaction->name_entered]) }}" 
                                           class="btn btn-success btn-sm">
                                            <i class="fas fa-plus me-1"></i>Add Revisit
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <!-- Pagination -->
                    <div class="mt-3">
                        @include('components.pagination', ['paginator' => $interactions])
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No results found</h5>
                        <p class="text-muted">Try searching with a different mobile number.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Print Options Modal -->
<div class="modal fade" id="printOptionsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Print Options</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Select what to print:</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="printOption" id="printCurrentPage" value="current" checked>
                        <label class="form-check-label" for="printCurrentPage">
                            Print Current Page (Page {{ $interactions->currentPage() }})
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="printOption" id="printAllPages" value="all">
                        <label class="form-check-label" for="printAllPages">
                            Print All Pages ({{ $interactions->total() }} interactions)
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="printOption" id="printToday" value="today">
                        <label class="form-check-label" for="printToday">
                            Print Today's Data Only
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="printOption" id="printCustomRange" value="custom">
                        <label class="form-check-label" for="printCustomRange">
                            Print Custom Date Range
                        </label>
                    </div>
                </div>
                
                <div id="customDateRange" class="mb-3" style="display: none;">
                    <div class="row">
                        <div class="col-6">
                            <label for="startDate" class="form-label">From Date:</label>
                            <input type="date" class="form-control" id="startDate">
                        </div>
                        <div class="col-6">
                            <label for="endDate" class="form-label">To Date:</label>
                            <input type="date" class="form-control" id="endDate">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="executePrint()">
                    <i class="fas fa-print me-1"></i>Print
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Remarks Modal -->
<div class="modal fade" id="remarksModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Interaction Remarks</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="remarksContent">
                <!-- Remarks will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
/* Mobile Card Styles */
.interaction-card {
    border: 1px solid #e9ecef;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.interaction-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
}

.interaction-card .card-body {
    padding: 1rem;
}

.interaction-card .card-title {
    color: #495057;
    font-weight: 600;
    font-size: 1rem;
}

.interaction-card .badge {
    font-size: 0.75rem;
    padding: 0.4em 0.6em;
}

.interaction-card .btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    border-radius: 6px;
}

/* Mobile-optimized table styles (Desktop only) */
@media (max-width: 768px) {
    .table-responsive {
        border: none;
        box-shadow: none;
    }
    
    .table {
        font-size: 0.8rem;
        margin-bottom: 0;
    }
    
    .table th,
    .table td {
        padding: 0.4rem 0.3rem;
        vertical-align: middle;
    }
    
    .badge-sm {
        font-size: 0.65rem;
        padding: 0.25rem 0.4rem;
    }
    
    .btn-sm {
        padding: 0.2rem 0.4rem;
        font-size: 0.7rem;
    }
    
    .card-body {
        padding: 0.5rem;
    }
}

/* Better spacing for mobile */
@media (max-width: 576px) {
    .d-flex.gap-2 {
        gap: 0.5rem !important;
    }
    
    .btn {
        font-size: 0.8rem;
    }
    
    h2.h4 {
        font-size: 1.2rem;
    }
}

/* Remove horizontal scroll indicator for mobile cards */
@media (max-width: 767px) {
    .table-responsive::after {
        display: none !important;
    }
}
</style>
@endsection

@section('scripts')
<script>
function viewRemarks(interactionId) {
    // Fetch remarks for this interaction
    fetch(`/frontdesk/interactions/${interactionId}/remarks`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayRemarks(data.remarks, data.interaction);
            } else {
                alert('Error loading remarks: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading remarks');
        });
}

function displayRemarks(remarks, interaction) {
    const content = document.getElementById('remarksContent');
    
    let html = `
        <div class="mb-3">
            <h6>Interaction Details:</h6>
            <p><strong>Visitor:</strong> ${interaction.visitor_name}</p>
            <p><strong>Purpose:</strong> ${interaction.purpose}</p>
            <p><strong>Meeting With:</strong> ${interaction.meeting_with}</p>
            <p><strong>Date:</strong> ${interaction.date}</p>
        </div>
        <hr>
        <h6>Remarks:</h6>
    `;
    
    if (remarks.length > 0) {
        remarks.forEach(remark => {
            html += `
                <div class="card mb-2">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="mb-1">${remark.remark_text}</p>
                                <small class="text-muted">Added by: ${remark.added_by_name}</small>
                            </div>
                            <small class="text-muted">${remark.created_at}</small>
                        </div>
                    </div>
                </div>
            `;
        });
    } else {
        html += '<p class="text-muted">No remarks available.</p>';
    }
    
    content.innerHTML = html;
    new bootstrap.Modal(document.getElementById('remarksModal')).show();
}

function showPrintOptions() {
    const modal = new bootstrap.Modal(document.getElementById('printOptionsModal'));
    modal.show();
}

function executePrint() {
    const printOption = document.querySelector('input[name="printOption"]:checked').value;
    const modal = bootstrap.Modal.getInstance(document.getElementById('printOptionsModal'));
    
    // Close modal first
    modal.hide();
    
    // Create print content based on selection
    let printContent = '';
    
    switch(printOption) {
        case 'current':
            printContent = createPrintContent('current');
            break;
        case 'all':
            printContent = createPrintContent('all');
            break;
        case 'today':
            printContent = createPrintContent('today');
            break;
        case 'custom':
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            if (!startDate || !endDate) {
                alert('Please select both start and end dates.');
                return;
            }
            printContent = createPrintContent('custom', startDate, endDate);
            break;
    }
    
    // Create print window
    const printWindow = window.open('', '_blank');
    printWindow.document.write(printContent);
    printWindow.document.close();
    printWindow.print();
}

function createPrintContent(type, startDate = null, endDate = null) {
    const currentDate = new Date().toLocaleDateString();
    const currentTime = new Date().toLocaleTimeString();
    
    let title = '';
    let tableContent = '';
    
    switch(type) {
        case 'current':
            title = 'Current Page Interactions';
            tableContent = getCurrentPageTable();
            break;
        case 'all':
            title = 'All Interactions';
            tableContent = getAllPagesTable();
            break;
        case 'today':
            title = 'Today\'s Interactions';
            tableContent = getTodayTable();
            break;
        case 'custom':
            title = `Interactions from ${startDate} to ${endDate}`;
            tableContent = getCustomRangeTable(startDate, endDate);
            break;
    }
    
    return `
        <!DOCTYPE html>
        <html>
        <head>
            <title>VMS - ${title}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .print-header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
                .print-footer { margin-top: 20px; text-align: center; font-size: 10px; border-top: 1px solid #000; padding-top: 10px; }
                table { width: 100%; border-collapse: collapse; font-size: 12px; }
                th, td { border: 1px solid #000; padding: 4px; text-align: left; }
                th { background-color: #f5f5f5; font-weight: bold; }
                .badge { border: 1px solid #000; background: none; color: #000; padding: 2px 4px; }
            </style>
        </head>
        <body>
            <div class="print-header">
                <h2>Visitor Management System</h2>
                <h3>${title}</h3>
                <p>Generated on: ${currentDate} at ${currentTime}</p>
            </div>
            
            ${tableContent}
            
            <div class="print-footer">
                <p>This report was generated by VMS - Visitor Management System</p>
            </div>
        </body>
        </html>
    `;
}

function getCurrentPageTable() {
    const table = document.querySelector('.table');
    if (!table) return '<p>No data available</p>';
    
    return `
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Visitor Name</th>
                    <th>Mobile</th>
                    <th>Mode</th>
                    <th>Purpose</th>
                    <th>Meeting With</th>
                    <th>Branch</th>
                    <th>Location</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                ${Array.from(table.querySelectorAll('tbody tr')).map(row => {
                    const cells = row.querySelectorAll('td');
                    if (cells.length < 10) return '';
                    
                    return `
                        <tr>
                            <td>${cells[0].textContent}</td>
                            <td>${cells[1].textContent}</td>
                            <td>${cells[2].textContent}</td>
                            <td>${cells[3].textContent}</td>
                            <td>${cells[4].textContent}</td>
                            <td>${cells[5].textContent}</td>
                            <td>${cells[6].textContent}</td>
                            <td>${cells[7].textContent}</td>
                            <td>${cells[8].textContent}</td>
                            <td>${cells[9].textContent}</td>
                        </tr>
                    `;
                }).join('')}
            </tbody>
        </table>
    `;
}

function getAllPagesTable() {
    // For now, return current page. In a real implementation, you'd fetch all data via AJAX
    return getCurrentPageTable() + '<p><em>Note: This shows current page only. Full implementation would fetch all pages.</em></p>';
}

function getTodayTable() {
    // For now, return current page. In a real implementation, you'd filter by today's date
    return getCurrentPageTable() + '<p><em>Note: This shows current page only. Full implementation would filter by today\'s date.</em></p>';
}

function getCustomRangeTable(startDate, endDate) {
    // For now, return current page. In a real implementation, you'd filter by date range
    return getCurrentPageTable() + `<p><em>Note: This shows current page only. Full implementation would filter from ${startDate} to ${endDate}.</em></p>`;
}

// Show/hide custom date range
document.addEventListener('DOMContentLoaded', function() {
    const customRangeRadio = document.getElementById('printCustomRange');
    const customDateRange = document.getElementById('customDateRange');
    
    if (customRangeRadio && customDateRange) {
        customRangeRadio.addEventListener('change', function() {
            if (this.checked) {
                customDateRange.style.display = 'block';
            } else {
                customDateRange.style.display = 'none';
            }
        });
    }
});
</script>
@endsection