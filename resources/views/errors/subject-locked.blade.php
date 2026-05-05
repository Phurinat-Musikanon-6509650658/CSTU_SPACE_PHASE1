@extends('layouts.app')

@section('title', 'ปิดใช้งาน | CSTU SPACE')

@push('styles')
<style>
    .locked-container {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .locked-card {
        background: white;
        border-radius: 12px;
        padding: 3rem;
        max-width: 600px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        text-align: center;
    }

    .lock-icon {
        font-size: 5rem;
        color: #764ba2;
        margin-bottom: 1.5rem;
    }

    .locked-title {
        font-size: 2rem;
        font-weight: 700;
        color: #212529;
        margin-bottom: 1rem;
    }

    .subject-info {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 8px;
        margin: 2rem 0;
        border-left: 4px solid #764ba2;
    }

    .subject-code {
        font-size: 0.85rem;
        color: #6c757d;
        margin-bottom: 0.5rem;
    }

    .subject-name {
        font-size: 1.5rem;
        font-weight: 700;
        color: #212529;
        margin-bottom: 1rem;
    }

    .message {
        font-size: 1.1rem;
        color: #495057;
        margin-bottom: 2rem;
        line-height: 1.6;
    }

    .action-buttons {
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
    }

    .action-buttons a,
    .action-buttons button {
        padding: 0.75rem 2rem;
        border-radius: 6px;
        font-weight: 600;
    }
</style>
@endpush

@section('content')
<div class="locked-container">
    <div class="locked-card">
        <div class="lock-icon">
            <i class="bi bi-lock-fill"></i>
        </div>

        <h1 class="locked-title">
            รายวิชาปิดใช้งาน
        </h1>

        @if($subject)
            <div class="subject-info">
                <div class="subject-code">{{ $subject->subject_code }}</div>
                <div class="subject-name">{{ $subject->subject_name }}</div>
            </div>
        @endif

        <p class="message">
            <i class="bi bi-exclamation-circle me-2"></i>
            {{ $message ?? 'ขออภัย รายวิชานี้ไม่สามารถเข้าใช้งานได้ในขณะนี้' }}
        </p>

        <div class="alert alert-info" role="alert">
            <i class="bi bi-info-circle me-2"></i>
            <strong>คำแนะนำ:</strong> หากคุณมีคำถามเกี่ยวกับเรื่องนี้ โปรดติดต่อแอดมินหรือเจ้าหน้าที่
        </div>

        <div class="action-buttons">
            <a href="{{ route('login') }}" class="btn btn-secondary">
                <i class="bi bi-house me-2"></i>กลับหน้าหลัก
            </a>
            @if(auth()->check())
                <a href="{{ route('student.dashboard') }}" class="btn btn-primary">
                    <i class="bi bi-arrow-left me-2"></i>กลับไปที่แดชบอร์ด
                </a>
            @endif
        </div>
    </div>
</div>
@endsection
