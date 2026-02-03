@extends('layouts.student')

@section('title', 'เสนอหัวข้อโครงงาน')

@section('content')
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <!-- Page Header -->
            <div class="page-header mb-4">
                <h1>
                    <i class="bi bi-lightbulb-fill me-2"></i>เสนอหัวข้อโครงงาน
                </h1>
                <p>กลุ่มที่ {{ $group->group_id }} - {{ $group->subject_code }}</p>
            </div>

            <!-- Alerts -->
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <h6 class="alert-heading">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>พบข้อผิดพลาด:
                    </h6>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Form Card -->
            <div class="card p-4">
                <form action="{{ route('proposals.store', $group->group_id) }}" method="POST">
                    @csrf

                    <!-- Group Info -->
                    <div class="mb-4 p-3" style="background: var(--gradient-primary); color: white; border-radius: 8px;">
                        <h6 class="mb-3"><i class="bi bi-info-circle me-2"></i>ข้อมูลกลุ่ม</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>กลุ่มที่:</strong> {{ $group->group_id }}</p>
                                <p class="mb-0"><strong>รหัสวิชา:</strong> {{ $group->subject_code }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>สมาชิก:</strong></p>
                                <ul class="mb-0 ps-3">
                                    @foreach($group->members as $member)
                                        <li>{{ $member->student->full_name }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Project Title -->
                    <div class="mb-4">
                        <label for="proposed_title" class="form-label">
                            ชื่อหัวข้อโครงงาน <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control @error('proposed_title') is-invalid @enderror" 
                               id="proposed_title" 
                               name="proposed_title" 
                               value="{{ old('proposed_title') }}"
                               placeholder="เช่น ระบบจัดการข้อมูลนักศึกษา"
                               required>
                        @error('proposed_title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">กรุณาระบุชื่อหัวข้อโครงงานที่ต้องการเสนอ</div>
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <label for="description" class="form-label">
                            รายละเอียดโครงงาน (ไม่บังคับ)
                        </label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" 
                                  name="description" 
                                  rows="5"
                                  placeholder="อธิบายแนวคิด วัตถุประสงค์ และขอบเขตของโครงงาน..."
                        >{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">อธิบายรายละเอียดของโครงงานให้อาจารย์เข้าใจ</div>
                    </div>

                    <!-- Lecturer Selection -->
                    <div class="mb-4">
                        <label for="proposed_to" class="form-label">
                            เสนอให้อาจารย์ <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @error('proposed_to') is-invalid @enderror" 
                                id="proposed_to" 
                                name="proposed_to" 
                                required>
                            <option value="">เลือกอาจารย์...</option>
                            @foreach($lecturers as $lecturer)
                                <option value="{{ $lecturer->username_user }}" 
                                        {{ old('proposed_to') == $lecturer->username_user ? 'selected' : '' }}>
                                    {{ $lecturer->full_name }} ({{ $lecturer->user_code }})
                                </option>
                            @endforeach
                        </select>
                        @error('proposed_to')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">เลือกอาจารย์ที่คุณต้องการให้เป็นที่ปรึกษาโครงงาน</div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex gap-2 justify-content-end mt-4">
                        <a href="{{ route('groups.show', $group->group_id) }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-1"></i>ยกเลิก
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-send-fill me-1"></i>ส่งข้อเสนอ
                        </button>
                    </div>
                </form>
            </div>

            <!-- Info Alert -->
            <div class="alert alert-info mt-3">
                <h6 class="alert-heading">
                    <i class="bi bi-info-circle-fill me-2"></i>ข้อมูลสำคัญ
                </h6>
                <ul class="mb-0">
                    <li>หัวหน้ากลุ่มเท่านั้นที่สามารถเสนอหัวข้อโครงงานได้</li>
                    <li>กรุณาตรวจสอบความถูกต้องของข้อมูลก่อนส่งข้อเสนอ</li>
                    <li>อาจารย์จะพิจารณาข้อเสนอและตอบกลับในภายหลัง</li>
                    <li>หากอาจารย์ปฏิเสธ คุณสามารถแก้ไขและเสนอใหม่ได้</li>
                </ul>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const title = document.getElementById('proposed_title').value.trim();
        const lecturer = document.getElementById('proposed_to').value;
        
        if (!title || !lecturer) {
            e.preventDefault();
            alert('กรุณากรอกข้อมูลให้ครบถ้วน');
            return false;
        }
        
        return confirm('ยืนยันการส่งข้อเสนอหัวข้อโครงงาน?');
    });
</script>
@endpush
