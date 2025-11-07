@extends('layouts.app')

@section('title', 'Welcome')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white text-center">
                    <h4>Welcome, {{ $displayname }}</h4>
                </div>
                <div class="card-body">
                    <p><strong>Department:</strong> {{ $department }}</p>
                    <a href="{{ route('logout') }}" class="btn btn-secondary w-100">Logout</a>
                </div>
            </div>
        </div>
    </div>
@endsection
