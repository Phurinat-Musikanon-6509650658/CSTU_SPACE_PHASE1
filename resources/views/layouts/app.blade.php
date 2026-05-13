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

    @if(Session::has('displayname'))
    {{-- Session Timeout Warning Modal --}}
    <div class="modal fade" id="sessionTimeoutModal" tabindex="-1" aria-hidden="true"
         data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
            <div class="modal-content" style="border: none; border-radius: 20px; overflow: hidden;
                 box-shadow: 0 25px 60px rgba(0,0,0,0.25);">
                <div class="modal-header border-0"
                     style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); padding: 1.5rem 2rem;">
                    <h5 class="modal-title fw-bold text-white mb-0">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>เซสชันจะหมดอายุ
                    </h5>
                </div>
                <div class="modal-body text-center py-4 px-4">
                    <div style="font-size: 3rem; margin-bottom: 0.75rem;">⏱️</div>
                    <p class="mb-1" style="color: #666; font-size: 0.95rem;">
                        คุณไม่มีการใช้งาน ระบบจะออกจากระบบอัตโนมัติใน
                    </p>
                    <div id="sessionCountdown"
                         style="font-size: 3.5rem; font-weight: 700; color: #e74c3c;
                                font-family: 'Courier New', monospace; line-height: 1.1;
                                margin: 0.5rem 0 1rem; letter-spacing: 2px;">2:00</div>
                    <div class="progress mb-3" style="height: 10px; border-radius: 5px; background: #f0f0f0;">
                        <div id="sessionProgressBar" class="progress-bar" role="progressbar"
                             style="width: 100%; background: linear-gradient(90deg, #e74c3c, #c0392b);
                                    border-radius: 5px; transition: width 1s linear;"></div>
                    </div>
                    <p style="color: #999; font-size: 0.82rem; margin: 0;">
                        กด <strong>"ใช้งานต่อ"</strong> เพื่อยังคงอยู่ในระบบต่อไป
                    </p>
                </div>
                <div class="modal-footer border-0 justify-content-center pb-4 gap-2">
                    <button type="button" onclick="extendSession()" class="btn fw-semibold"
                            style="border-radius: 50px; padding: 0.75rem 2rem; border: none; color: white;
                                   background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                                   box-shadow: 0 4px 15px rgba(102,126,234,0.35);">
                        <i class="bi bi-arrow-repeat me-2"></i>ใช้งานต่อ
                    </button>
                    <button type="button" onclick="logout()" class="btn btn-outline-secondary fw-semibold"
                            style="border-radius: 50px; padding: 0.75rem 2rem;">
                        <i class="bi bi-box-arrow-right me-2"></i>ออกจากระบบ
                    </button>
                </div>
            </div>
        </div>
    </div>
    <script>
    function logout() { window.location.href = '/logout'; }

    (function () {
        var IDLE_MS     = 15 * 60 * 1000;
        var WARNING_MS  = 13 * 60 * 1000;
        var WARNING_SEC = 2 * 60;

        var idleTimer, warnTimer, tickTimer;
        var bsModal = null;

        function getModal() {
            if (!bsModal) bsModal = new bootstrap.Modal(document.getElementById('sessionTimeoutModal'));
            return bsModal;
        }

        function tick(left) {
            var m = Math.floor(left / 60);
            var s = left % 60;
            document.getElementById('sessionCountdown').textContent = m + ':' + (s < 10 ? '0' : '') + s;
            var bar = document.getElementById('sessionProgressBar');
            if (bar) bar.style.width = (left / WARNING_SEC * 100) + '%';
            if (left <= 0) { getModal().hide(); logout(); return; }
            tickTimer = setTimeout(function () { tick(left - 1); }, 1000);
        }

        function showWarning() {
            clearTimeout(tickTimer);
            tick(WARNING_SEC);
            getModal().show();
        }

        function hideWarning() {
            clearTimeout(tickTimer);
            if (bsModal) bsModal.hide();
        }

        window.extendSession = function () {
            hideWarning();
            reset();
            fetch('/refresh-session', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            }).catch(function () {});
        };

        function reset() {
            clearTimeout(idleTimer);
            clearTimeout(warnTimer);
            warnTimer = setTimeout(showWarning, WARNING_MS);
            idleTimer = setTimeout(function () { hideWarning(); logout(); }, IDLE_MS);
        }

        reset();

        ['mousedown', 'keydown', 'scroll', 'touchstart', 'click'].forEach(function (evt) {
            document.addEventListener(evt, function () {
                var el = document.getElementById('sessionTimeoutModal');
                if (el && !el.classList.contains('show')) reset();
            }, { passive: true });
        });

        window.addEventListener('beforeunload', function () {
            if (navigator.sendBeacon) {
                var fd = new FormData();
                var t = document.querySelector('meta[name="csrf-token"]');
                if (t) fd.append('_token', t.content);
                navigator.sendBeacon('/logout-beacon', fd);
            }
        });
    }());
    </script>
    @endif

    <!-- Additional Page Scripts -->
    @stack('scripts')
</body>
</html>

