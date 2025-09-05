@extends('layouts.app')

@section('title', 'Search Visitors - VMS')
@section('page-title', 'Search Visitors')

@section('content')
<div class="row">
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-search me-2"></i>Search Your Visitors
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('frontdesk.search-visitors') }}">
                    @csrf
                    <div class="row">
                        <div class="col-12 col-md-8">
                            <label for="mobile_number" class="form-label">Mobile Number *</label>
                            <div class="input-group">
                                <span class="input-group-text">+91</span>
                                <input type="tel" class="form-control" id="mobile_number" name="mobile_number" 
                                       required maxlength="10" placeholder="Enter 10-digit mobile number" 
                                       value="{{ old('mobile_number') }}"
                                       inputmode="numeric" pattern="[0-9]{10}"
                                       oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>Search
                                </button>
                            </div>
                            <div class="form-text">Search for visitors you created or are meeting with</div>
                            @error('mobile_number')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>Search Help
                </h6>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>You can search for:</strong></p>
                <ul class="list-unstyled mb-3">
                    <li><i class="fas fa-check text-success me-2"></i>Visitors you created</li>
                    <li><i class="fas fa-check text-success me-2"></i>Visitors you are meeting with</li>
                </ul>
                <p class="mb-2"><strong>You cannot see:</strong></p>
                <ul class="list-unstyled">
                    <li><i class="fas fa-times text-danger me-2"></i>Visitors created by other staff</li>
                    <li><i class="fas fa-times text-danger me-2"></i>Visitors meeting with other employees</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="d-flex justify-content-between">
            <a href="{{ route('frontdesk.dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
            <a href="{{ route('frontdesk.visitor-form') }}" class="btn btn-outline-primary">
                <i class="fas fa-plus me-2"></i>Add New Visitor
            </a>
        </div>
    </div>
</div>
@endsection
