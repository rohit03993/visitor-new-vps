@extends('layouts.app')

@section('title', 'Manage Locations - VMS')
@section('page-title', 'Manage Locations')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
            <h2 class="h4 mb-0">Location Management</h2>
            <button class="btn btn-paytm-primary" data-bs-toggle="modal" data-bs-target="#createLocationModal">
                <i class="fas fa-plus me-2"></i>Add New Location
            </button>
        </div>
    </div>
</div>

<!-- Locations Table -->
<div class="row">
    <div class="col-12">
        <div class="card-paytm paytm-fade-in">
            <div class="card-paytm-header">
                <h5 class="mb-0">
                    <i class="fas fa-map-marker-alt me-2"></i>All Locations
                </h5>
            </div>
            <div class="card-paytm-body">
                @if($addresses->count() > 0)
                    <!-- Desktop Table View -->
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-paytm">
                            <thead>
                                <tr>
                                    <th>Location Name</th>
                                    <th>Created By</th>
                                    <th>Created Date</th>
                                    <th>Total Interactions</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($addresses as $address)
                                    <tr>
                                        <td>
                                            <strong>{{ $address->address_name }}</strong>
                                            <br><small class="text-muted">{{ $address->full_address }}</small>
                                        </td>
                                        <td>{{ $address->createdBy?->name ?? 'Unknown' }}</td>
                                        <td>{{ $address->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <span class="badge bg-primary">{{ $address->interactions->count() }}</span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteLocation({{ $address->address_id }}, '{{ $address->address_name }}')">
                                                <i class="fas fa-trash me-1"></i>Delete
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="d-md-none">
                        @foreach($addresses as $address)
                            <div class="card mb-3 interaction-card">
                                <div class="card-body">
                                    <!-- Header with Location Name -->
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h6 class="card-title mb-1">{{ $address->address_name }}</h6>
                                            <small class="text-muted">
                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                {{ $address->full_address }}
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-primary">{{ $address->interactions->count() }} interactions</span>
                                        </div>
                                    </div>

                                    <!-- Location Details -->
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <small class="text-muted">Created By:</small><br>
                                            <strong>{{ $address->createdBy?->name ?? 'Unknown' }}</strong>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Created Date:</small><br>
                                            <strong>{{ $address->created_at->format('M d, Y') }}</strong>
                                        </div>
                                    </div>

                                    <!-- Action Button -->
                                    <div class="d-flex justify-content-end">
                                        <button class="btn btn-outline-danger btn-sm" 
                                                onclick="deleteLocation({{ $address->address_id }}, '{{ $address->address_name }}')">
                                            <i class="fas fa-trash me-1"></i>Delete Location
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-map-marker-alt fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No addresses found</h5>
                        <p class="text-muted">Add your first address to get started.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Create Location Modal -->
<div class="modal fade" id="createLocationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Location</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.create-location') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="address_name" class="form-label">Address Name *</label>
                        <input type="text" class="form-control" id="address_name" name="address_name" 
                               required placeholder="e.g., Madhunagar, Sanjay Place">
                        <div class="form-text">Enter a unique address name</div>
                    </div>
                    <div class="mb-3">
                        <label for="full_address" class="form-label">Full Address *</label>
                        <textarea class="form-control" id="full_address" name="full_address" 
                                  required placeholder="e.g., Madhunagar, Sanjay Place, Agra, Uttar Pradesh 282001"></textarea>
                        <div class="form-text">Enter the complete address</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Location</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Location Modal -->
<div class="modal fade" id="deleteLocationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Location</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the address <strong id="deleteLocationName"></strong>?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone. All interactions associated with this address will be permanently deleted.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteLocationForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Location</button>
                </form>
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
</style>
@endsection

@section('scripts')
<script>
function deleteLocation(locationId, locationName) {
    document.getElementById('deleteLocationName').textContent = locationName;
    document.getElementById('deleteLocationForm').action = '{{ route("admin.delete-location", ":id") }}'.replace(':id', locationId);
    
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteLocationModal'));
    deleteModal.show();
}
</script>
@endsection
