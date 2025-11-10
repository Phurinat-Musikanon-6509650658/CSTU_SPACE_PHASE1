<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('images/logo_cstuspace.png') }}" type="image/png">
    <title>@yield('title', 'CSTU-SPACE-app-layout')</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
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
    </script>
    @endif

    <!-- Additional Page Scripts -->
    @stack('scripts')
</body>
</html>

