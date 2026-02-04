@extends('layouts.app')

@section('title', 'System Closed | CSTU SPACE')

@section('content')
<div class="container d-flex align-items-center justify-content-center" style="min-height: 100vh;">
    <div class="text-center">
        <div class="display-1 mb-4">
            <i class="bi bi-lock-fill text-danger"></i>
        </div>
        <h1 class="mb-3">ระบบปิดอยู่</h1>
        <p class="lead text-muted mb-4">
            ขออภัย ระบบกำลังอยู่ในระหว่างการบำรุงรักษา หรือชั่วคราวปิดให้บริการ<br>
            กรุณากลับมาใหม่ในภายหลัง
        </p>
        
        <div class="alert alert-info" role="alert">
            <i class="bi bi-info-circle me-2"></i>
            หากคุณมีคำถามใดๆ โปรดติดต่อผู้ดูแลระบบ
        </div>
        
        <a href="{{ route('logout') }}" class="btn btn-secondary mt-4">
            <i class="bi bi-box-arrow-left me-2"></i>ออกจากระบบ
        </a>
    </div>
</div>

<style>
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
    }
    .container {
        background: white;
        border-radius: 10px;
        padding: 40px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    }
</style>
@endsection
