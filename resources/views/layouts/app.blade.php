<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('images/logo_cstuspace.png') }}" type="image/png">
    <title>@yield('title', 'CSTU SPACE - Modern Project Management')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Global Styles -->
    <style>
        :root {
            --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-success: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --gradient-warning: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            --gradient-info: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            --gradient-danger: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
            --shadow-light: 0 8px 25px rgba(0, 0, 0, 0.1);
            --shadow-hover: 0 15px 35px rgba(0, 0, 0, 0.15);
            --border-radius: 20px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .modern-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-light);
            border: none;
            overflow: hidden;
            transition: var(--transition);
        }

        .modern-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        .modern-btn {
            border-radius: 50px;
            padding: 0.75rem 2rem;
            font-weight: 500;
            border: none;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .modern-btn:hover {
            transform: translateY(-2px);
            text-decoration: none;
        }

        .btn-primary-modern {
            background: var(--gradient-primary);
            color: white;
        }

        .btn-success-modern {
            background: var(--gradient-success);
            color: white;
        }

        .btn-warning-modern {
            background: var(--gradient-warning);
            color: white;
        }

        .btn-info-modern {
            background: var(--gradient-info);
            color: white;
        }

        .btn-danger-modern {
            background: var(--gradient-danger);
            color: white;
        }

        .alert-modern {
            border-radius: 15px;
            border: none;
            box-shadow: var(--shadow-light);
        }

        .form-control {
            border-radius: 15px;
            border: 2px solid #e9ecef;
            padding: 0.75rem 1rem;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .table-modern {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow-light);
        }

        .navbar-modern {
            background: var(--gradient-primary);
            border-radius: 0 0 var(--border-radius) var(--border-radius);
            box-shadow: var(--shadow-light);
        }

        .page-header {
            background: var(--gradient-primary);
            color: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-light);
        }

        .container {
            margin-top: 2rem;
        }
    </style>
    
    <!-- Additional Page Styles -->
    @stack('styles')
</head>
<body>
    <div class="container mt-5">
        @yield('content')  <!-- เนื้อหาของแต่ละหน้า (login, welcome) จะมาที่นี่ -->
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

    <!-- Auto Logout Script (เฉพาะเมื่อ login แล้ว) -->
    @if(Session::has('displayname'))
    <script>
        let idleTimer;
        let isWarningShown = false;
        const IDLE_TIME = 15 * 60 * 1000; // 15 นาที
        const WARNING_TIME = 13 * 60 * 1000; // 13 นาที (แจ้งเตือนก่อน 2 นาที)

        function resetIdleTimer() {
            clearTimeout(idleTimer);
            if (isWarningShown) {
                hideWarning();
            }
            
            // ตั้งค่าเตือนหลัง 13 นาที
            setTimeout(() => {
                if (!isWarningShown) {
                    showWarning();
                }
            }, WARNING_TIME);
            
            // Auto logout หลัง 15 นาที
            idleTimer = setTimeout(() => {
                logout();
            }, IDLE_TIME);
        }

        function showWarning() {
            isWarningShown = true;
            
            // สร้าง modal แจ้งเตือน
            const modal = document.createElement('div');
            modal.id = 'logoutWarningModal';
            modal.innerHTML = `
                <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5);">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-warning text-dark">
                                <h5 class="modal-title">⚠️ แจ้งเตือน</h5>
                            </div>
                            <div class="modal-body">
                                <p>คุณจะถูก logout อัตโนมัติใน <span id="countdown">2:00</span> นาที</p>
                                <p>หากต้องการใช้งานต่อ กรุณาคลิก "ใช้งานต่อ"</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary" onclick="extendSession()">ใช้งานต่อ</button>
                                <button type="button" class="btn btn-secondary" onclick="logout()">Logout ทันที</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            
            // นับถอยหลัง 2 นาที
            let timeLeft = 120; // 2 นาที = 120 วินาที
            const countdownEl = document.getElementById('countdown');
            
            const countdownInterval = setInterval(() => {
                timeLeft--;
                const minutes = Math.floor(timeLeft / 60);
                const seconds = timeLeft % 60;
                countdownEl.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
                
                if (timeLeft <= 0) {
                    clearInterval(countdownInterval);
                    logout();
                }
            }, 1000);
        }

        function hideWarning() {
            const modal = document.getElementById('logoutWarningModal');
            if (modal) {
                modal.remove();
            }
            isWarningShown = false;
        }

        function extendSession() {
            hideWarning();
            
            // ส่ง request ไป server เพื่อ refresh session
            fetch('/refresh-session', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                }
            }).then(() => {
                resetIdleTimer();
            });
        }

        function logout() {
            window.location.href = '/logout';
        }

        // เริ่มต้น timer
        resetIdleTimer();

        // Reset timer เมื่อมี activity
        ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'].forEach(event => {
            document.addEventListener(event, resetIdleTimer, true);
        });

        // จัดการการปิดเบราว์เซอร์หรือปิดแท็บ
        window.addEventListener('beforeunload', function(event) {
            // ส่ง request เพื่ออัปเดต logout time
            // ใช้ sendBeacon เพื่อให้แน่ใจว่า request จะถูกส่งแม้เบราว์เซอร์จะปิด
            if (navigator.sendBeacon) {
                const formData = new FormData();
                formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || '');
                navigator.sendBeacon('/logout-beacon', formData);
            }
        });

        // จัดการ visibility change (เมื่อเปลี่ยนแท็บหรือปิดเบราว์เซอร์)
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                // บันทึกเวลาที่ออกจากหน้า
                sessionStorage.setItem('page_hidden_time', Date.now());
            } else {
                // เมื่อกลับมาใช้หน้า ตรวจสอบว่านานแค่ไหน
                const hiddenTime = sessionStorage.getItem('page_hidden_time');
                if (hiddenTime) {
                    const timeDiff = Date.now() - parseInt(hiddenTime);
                    // ถ้าหายไปนานกว่า 5 นาที ให้ logout
                    if (timeDiff > 5 * 60 * 1000) {
                        logout();
                    }
                    sessionStorage.removeItem('page_hidden_time');
                }
            }
        });
    </script>
    @endif

    <!-- Additional Page Scripts -->
    @stack('scripts')
</body>
</html>

