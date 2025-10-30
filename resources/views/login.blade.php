@extends('layouts.app')

@section('title', 'Login')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            @if(session('login_error_message'))
                <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                    <strong>{{ session('login_error_message') }}</strong>
                    @if(session('login_error_description'))
                        <div class="mt-1">{{ session('login_error_description') }}</div>
                    @endif
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <div class="card">
                <div class="card-header bg-primary text-white text-center">
                    <h4>CSTU-SPACE</h4>
                </div>
                <div class="card-body">
                    @if ($errors->has('login_error'))
                        <div class="alert alert-danger">
                            {{ $errors->first('login_error') }}
                        </div>
                    @endif

                    <form action="{{ url('login') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" id="username" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" id="password" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
