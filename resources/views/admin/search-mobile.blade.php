@extends('layouts.app')

@section('title', 'Search Visitor - VMS')
@section('page-title', 'Search Visitor by Mobile')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-search me-2"></i>Search Visitor by Mobile Number
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.search-mobile') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="mobile_number" class="form-label">Mobile Number *</label>
                        <div class="input-group">
                            <span class="input-group-text">+91</span>
                            <input type="tel" class="form-control" id="mobile_number" name="mobile_number" 
                                   required maxlength="10" placeholder="Enter 10-digit mobile number" 
                                   value="{{ old('mobile_number') }}"
                                   inputmode="numeric" pattern="[0-9]{10}"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)">
                        </div>
                        <div class="form-text">Enter the mobile number to search for visitor profile</div>
                    </div>
                    
                    <div class="d-flex flex-column flex-md-row justify-content-between gap-2">
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Search Visitor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
