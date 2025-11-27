@extends('layouts.app')

@section('title', 'Import Users | Coordinator')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-file-earmark-arrow-up me-2"></i>
                        Import Users from CSV
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>หมายเหตุ:</strong> Coordinator ไม่สามารถกำหนด Role ได้ 
                        ผู้ใช้ที่ Import เข้ามาจะถูกกำหนดเป็น <strong>Lecturer</strong> โดยอัตโนมัติ
                    </div>

                    <form action="{{ route('coordinator.users.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label fw-bold">Select CSV File</label>
                            <input type="file" name="file" class="form-control" accept=".csv" required>
                            <small class="text-muted">CSV format: username, firstname, lastname, email, user_code</small>
                        </div>

                        <div class="mb-4">
                            <h6 class="fw-bold">CSV Format Example:</h6>
                            <pre class="bg-light p-3 rounded"><code>username,firstname,lastname,email,user_code
teacher1,John,Doe,john@example.com,jd
teacher2,Jane,Smith,jane@example.com,js</code></pre>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-upload me-2"></i>Import Users
                            </button>
                            <a href="{{ route('coordinator.users.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
