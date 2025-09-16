@extends('layouts.app')

@section('title', 'Visitor Search - Log Book')
@section('page-title', 'Visitor Search')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-6">
        <div class="card-paytm paytm-fade-in">
            <div class="card-paytm-header">
                <h5 class="mb-0">
                    <i class="fas fa-search me-2"></i>Search Log by Mobile Number
                </h5>
            </div>
            <div class="card-paytm-body">
                <form method="POST" action="{{ route('staff.search-visitor') }}">
                    @csrf
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text" style="background: var(--paytm-primary); color: white; border-color: var(--paytm-primary);">+91</span>
                            <input type="tel" class="form-control-paytm" id="mobile_number" name="mobile_number" 
                                   required maxlength="10" placeholder="" 
                                   value="{{ old('mobile_number', $prefilledMobile ?? '') }}"
                                   inputmode="numeric" pattern="[0-9]{10}"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)">
                        </div>
                        <div class="form-text text-paytm-muted">Enter the mobile number to search for Log or Enter New Log</div>
                        @error('mobile_number')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-paytm-primary">
                            <i class="fas fa-search me-2"></i>Search Log
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<!-- Paytm styling applied via paytm-theme.css -->
@endsection
