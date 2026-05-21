<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Student') - CSTU SPACE</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --color-red: #DC143C;
            --color-yellow: #FFD700;
            --color-black: #1a1a1a;
            --color-blue: #0066CC;
            --color-dark-blue: #003d82;
            --gradient-primary: linear-gradient(135deg, var(--color-blue) 0%, var(--color-dark-blue) 100%);
            --gradient-accent: linear-gradient(135deg, var(--color-red) 0%, #FF6347 100%);
            --gradient-warning: linear-gradient(135deg, var(--color-yellow) 0%, #FFA500 100%);
            --gradient-dark: linear-gradient(135deg, #2c3e50 0%, var(--color-black) 100%);
            --shadow-light: 0 8px 25px rgba(0, 0, 0, 0.1);
            --shadow-hover: 0 15px 35px rgba(0, 0, 0, 0.2);
            --border-radius: 20px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%);
            min-height: 100vh;
            font-family: 'Kanit', sans-serif;
            color: #333;
            position: relative;
            overflow-x: hidden;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(220, 20, 60, 0.1) 0%, transparent 70%);
            animation: rotate 30s linear infinite;
            z-index: 0;
            pointer-events: none;
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .container-fluid,
        .container {
            position: relative;
            z-index: 1;
        }
        
        /* Cards */
        .card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-light);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }
        
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--color-red) 0%, var(--color-yellow) 50%, var(--color-blue) 100%);
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }
        
        /* Page Header */
        .page-header {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--shadow-light);
            position: relative;
            overflow: hidden;
            margin-bottom: 2rem;
        }
        
        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--color-red) 0%, var(--color-yellow) 50%, var(--color-blue) 100%);
        }
        
        .page-header h1 {
            font-size: 2rem;
            font-weight: 700;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }
        
        .page-header p {
            color: #666;
            margin-bottom: 0;
        }
        
        /* Buttons */
        .btn-primary {
            background: var(--gradient-primary);
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 50px;
            font-weight: 500;
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(0, 102, 204, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 102, 204, 0.4);
        }
        
        .btn-danger {
            background: var(--gradient-accent);
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 50px;
            font-weight: 500;
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(220, 20, 60, 0.3);
        }
        
        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(220, 20, 60, 0.4);
        }
        
        .btn-warning {
            background: var(--gradient-warning);
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 50px;
            font-weight: 500;
            color: var(--color-black);
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(255, 215, 0, 0.3);
        }
        
        .btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 215, 0, 0.4);
            color: var(--color-black);
        }
        
        .btn-secondary {
            background: var(--gradient-dark);
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 50px;
            font-weight: 500;
            transition: var(--transition);
        }
        
        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }
        
        /* Alerts */
        .alert {
            border-radius: 15px;
            border: none;
            padding: 1rem 1.5rem;
            box-shadow: var(--shadow-light);
        }
        
        .alert-success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }
        
        .alert-danger {
            background: var(--gradient-accent);
            color: white;
        }
        
        .alert-warning {
            background: var(--gradient-warning);
            color: var(--color-black);
        }
        
        .alert-info {
            background: var(--gradient-primary);
            color: white;
        }
        
        /* Form Controls */
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e0e0e0;
            padding: 0.75rem 1rem;
            transition: var(--transition);
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--color-blue);
            box-shadow: 0 0 0 0.2rem rgba(0, 102, 204, 0.25);
        }
        
        .form-label {
            font-weight: 600;
            color: var(--color-black);
            margin-bottom: 0.5rem;
        }
        
        /* Back Button */
        .btn-back {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-weight: 500;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-back:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
            transform: translateY(-2px);
        }
        
        /* Table */
        .table {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
        }
        
        .table thead {
            background: var(--gradient-primary);
            color: white;
        }
        
        .table thead th {
            border: none;
            padding: 1rem;
            font-weight: 600;
        }
        
        .table tbody tr {
            transition: var(--transition);
        }
        
        .table tbody tr:hover {
            background: rgba(0, 102, 204, 0.05);
        }
        
        /* Badge */
        .badge {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 500;
        }
        
        .badge.bg-primary {
            background: var(--gradient-primary) !important;
        }
        
        .badge.bg-danger {
            background: var(--gradient-accent) !important;
        }
        
        .badge.bg-warning {
            background: var(--gradient-warning) !important;
            color: var(--color-black) !important;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <div class="container py-4">
        <!-- Back Button -->
        <div class="row mb-3">
            <div class="col-12">
                <a href="{{ route('student.menu') }}" class="btn-back">
                    <i class="bi bi-arrow-left"></i>
                    <span>กลับหน้าหลัก</span>
                </a>
            </div>
        </div>

        @yield('content')
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')

    {{-- Session Timeout Warning Modal --}}
    <div class="modal fade" id="sessionTimeoutModal" tabindex="-1" aria-hidden="true"
         data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
            <div class="modal-content" style="border: none; border-radius: 20px; overflow: hidden;
                 box-shadow: 0 25px 60px rgba(0,0,0,0.3);">
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
                                   background: linear-gradient(135deg, #0066CC 0%, #003d82 100%);
                                   box-shadow: 0 4px 15px rgba(0,102,204,0.35);">
                        <i class="bi bi-arrow-repeat me-2"></i>ใช้งานต่อ
                    </button>
                    <button type="button" onclick="window.location.href='/logout'"
                            class="btn btn-outline-secondary fw-semibold"
                            style="border-radius: 50px; padding: 0.75rem 2rem;">
                        <i class="bi bi-box-arrow-right me-2"></i>ออกจากระบบ
                    </button>
                </div>
            </div>
        </div>
    </div>
    <script>
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
            if (left <= 0) { getModal().hide(); window.location.href = '/logout'; return; }
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
            idleTimer = setTimeout(function () { hideWarning(); window.location.href = '/logout'; }, IDLE_MS);
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
</body>
</html>
