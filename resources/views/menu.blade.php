@extends('layouts.app')

@section('title', 'Dashboard | CSTU SPACE')

@section('content')
<div class="menu-container">
    <div class="row">
        <div class="col-12">
            <!-- Welcome Header -->
            <div class="welcome-header mb-5">
                <div class="welcome-content">
                    <div class="welcome-avatar">
                        <i class="bi bi-person-circle"></i>
                    </div>
                    <div class="welcome-text">
                        <h2 class="welcome-title">Welcome</h2>
                        <h4 class="welcome-name">{{ $displayname }}</h4>
                        <div class="role-badge">
                            <span class="badge role-{{ $role }}">
                                <i class="bi bi-shield-check"></i>
                                {{ ucfirst($role) }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="welcome-decoration">
                    <div class="decoration-circle circle-1"></div>
                    <div class="decoration-circle circle-2"></div>
                    <div class="decoration-circle circle-3"></div>
                </div>
            </div>

        <!-- Menu Content based on Role -->
        <div class="menu-grid">
            {{-- Admin Menu - ทำได้ทุกอย่าง --}}
            @if($role === 'admin')
                <div class="menu-section">
                    <h5 class="section-title">System Management</h5>
                    <div class="row g-4">
                        <div class="col-lg-3 col-md-6">
                            <div class="menu-card admin-card">
                                <div class="card-icon">
                                    <i class="bi bi-gear-fill"></i>
                                </div>
                                <div class="card-content">
                                    <h6 class="card-title">System Settings</h6>
                                    <p class="card-description">Manage users and system configuration</p>
                                    <a href="#" class="menu-btn admin-btn">
                                        <span>Access System</span>
                                        <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                                <div class="card-overlay"></div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="menu-card primary-card">
                                <div class="card-icon">
                                    <i class="bi bi-people-fill"></i>
                                </div>
                                <div class="card-content">
                                    <h6 class="card-title">User Management</h6>
                                    <p class="card-description">Add/Edit/Delete users</p>
                                    <a href="{{ route('users.index') }}" class="menu-btn primary-btn">
                                        <span>Manage</span>
                                        <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                                <div class="card-overlay"></div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="menu-card success-card">
                                <div class="card-icon">
                                    <i class="bi bi-file-earmark-text-fill"></i>
                                </div>
                                <div class="card-content">
                                    <h6 class="card-title">All Reports</h6>
                                    <p class="card-description">View system-wide reports</p>
                                    <a href="#" class="menu-btn success-btn">
                                        <span>View Reports</span>
                                        <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                                <div class="card-overlay"></div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="menu-card warning-card">
                                <div class="card-icon">
                                    <i class="bi bi-shield-lock"></i>
                                </div>
                                <div class="card-content">
                                    <h6 class="card-title">Login Logs</h6>
                                    <p class="card-description">Track system access logs</p>
                                    <a href="{{ route('admin.logs.index') }}" class="menu-btn warning-btn">
                                        <span>View Logs</span>
                                        <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                                <div class="card-overlay"></div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="menu-card info-card">
                                <div class="card-icon">
                                    <i class="bi bi-graph-up"></i>
                                </div>
                                <div class="card-content">
                                    <h6 class="card-title">Statistics</h6>
                                    <p class="card-description">View usage statistics</p>
                                    <a href="{{ route('statistics.index') }}" class="menu-btn info-btn">
                                        <span>View Stats</span>
                                        <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                                <div class="card-overlay"></div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Coordinator Menu - ดูและติดตามสถานะโครงงานทั้งหมด --}}
            @if($role === 'coordinator' || $role === 'admin')
                <div class="menu-section">
                    <h5 class="section-title">Project Management</h5>
                    <div class="row g-4">
                        <div class="col-lg-4 col-md-6">
                            <div class="menu-card primary-card">
                                <div class="card-icon">
                                    <i class="bi bi-clipboard-check"></i>
                                </div>
                                <div class="card-content">
                                    <h6 class="card-title">Track All Projects</h6>
                                    <p class="card-description">View and track status of all projects</p>
                                    <a href="#" class="menu-btn primary-btn">
                                        <span>View Projects</span>
                                        <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                                <div class="card-overlay"></div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="menu-card info-card">
                                <div class="card-icon">
                                    <i class="bi bi-person-badge"></i>
                                </div>
                                <div class="card-content">
                                    <h6 class="card-title">Manage Advisors</h6>
                                    <p class="card-description">View advisor list and their projects</p>
                                    <a href="#" class="menu-btn info-btn">
                                        <span>Manage</span>
                                        <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                                <div class="card-overlay"></div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="menu-card success-card">
                                <div class="card-icon">
                                    <i class="bi bi-mortarboard-fill"></i>
                                </div>
                                <div class="card-content">
                                    <h6 class="card-title">Manage Students</h6>
                                    <p class="card-description">View all student records</p>
                                    <a href="#" class="menu-btn success-btn">
                                        <span>Manage</span>
                                        <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                                <div class="card-overlay"></div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Advisor Menu - ดูโครงงานของตัวเองและนักศึกษา --}}
            @if($role === 'advisor' || $role === 'coordinator' || $role === 'admin')
                <div class="menu-section">
                    <h5 class="section-title">Advisory Work</h5>
                    <div class="row g-4">
                        <div class="col-lg-6 col-md-6">
                            <div class="menu-card primary-card">
                                <div class="card-icon">
                                    <i class="bi bi-folder-fill"></i>
                                </div>
                                <div class="card-content">
                                    <h6 class="card-title">My Projects</h6>
                                    <p class="card-description">View projects I'm advising</p>
                                    <a href="#" class="menu-btn primary-btn">
                                        <span>View Projects</span>
                                        <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                                <div class="card-overlay"></div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <div class="menu-card info-card">
                                <div class="card-icon">
                                    <i class="bi bi-people"></i>
                                </div>
                                <div class="card-content">
                                    <h6 class="card-title">My Students</h6>
                                    <p class="card-description">View students in my projects</p>
                                    <a href="#" class="menu-btn info-btn">
                                        <span>View Students</span>
                                        <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                                <div class="card-overlay"></div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Student Menu - ดูกลุ่มของตัวเอง --}}
            @if($role === 'student' || $role === 'advisor' || $role === 'coordinator' || $role === 'admin')
                <div class="menu-section">
                    <h5 class="section-title">My Work</h5>
                    <div class="row g-4">
                        <div class="col-lg-6 col-md-6">
                            <div class="menu-card success-card">
                                <div class="card-icon">
                                    <i class="bi bi-journal-text"></i>
                                </div>
                                <div class="card-content">
                                    <h6 class="card-title">Group Project</h6>
                                    <p class="card-description">View and manage my group project</p>
                                    <a href="#" class="menu-btn success-btn">
                                        <span>View Project</span>
                                        <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                                <div class="card-overlay"></div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <div class="menu-card warning-card">
                                <div class="card-icon">
                                    <i class="bi bi-person-lines-fill"></i>
                                </div>
                                <div class="card-content">
                                    <h6 class="card-title">Group Members</h6>
                                    <p class="card-description">View members in my group</p>
                                    <a href="#" class="menu-btn warning-btn">
                                        <span>View Members</span>
                                        <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                                <div class="card-overlay"></div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Logout Section -->
        <div class="logout-section">
            <div class="logout-container">
                <a href="{{ route('logout') }}" class="logout-btn">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<style>
    :root {
        --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --gradient-success: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        --gradient-warning: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        --gradient-info: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
        --gradient-admin: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
        --shadow-light: 0 8px 25px rgba(0, 0, 0, 0.1);
        --shadow-hover: 0 15px 35px rgba(0, 0, 0, 0.15);
        --border-radius: 20px;
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .menu-container {
        padding: 2rem 0;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
    }

    /* Welcome Header Styles */
    .welcome-header {
        background: var(--gradient-primary);
        border-radius: var(--border-radius);
        padding: 3rem 2rem;
        position: relative;
        overflow: hidden;
        box-shadow: var(--shadow-light);
        margin-bottom: 3rem;
    }

    .welcome-content {
        position: relative;
        z-index: 2;
        display: flex;
        align-items: center;
        gap: 2rem;
        color: white;
    }

    .welcome-avatar {
        font-size: 4rem;
        opacity: 0.9;
    }

    .welcome-title {
        font-size: 2rem;
        font-weight: 300;
        margin-bottom: 0.5rem;
        opacity: 0.9;
    }

    .welcome-name {
        font-size: 2.5rem;
        font-weight: 600;
        margin-bottom: 1rem;
    }

    .role-badge .badge {
        font-size: 1rem;
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-weight: 500;
    }

    .role-admin { background: linear-gradient(45deg, #ff6b6b, #ee5a24); }
    .role-coordinator { background: linear-gradient(45deg, #4834d4, #686de0); }
    .role-advisor { background: linear-gradient(45deg, #0abde3, #006ba6); }
    .role-student { background: linear-gradient(45deg, #55a3ff, #003d82); }

    .welcome-decoration {
        position: absolute;
        top: 0;
        right: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
    }

    .decoration-circle {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
        animation: float 6s ease-in-out infinite;
    }

    .circle-1 {
        width: 150px;
        height: 150px;
        top: -50px;
        right: -50px;
        animation-delay: 0s;
    }

    .circle-2 {
        width: 100px;
        height: 100px;
        top: 30%;
        right: 10%;
        animation-delay: 2s;
    }

    .circle-3 {
        width: 80px;
        height: 80px;
        bottom: 20%;
        right: 30%;
        animation-delay: 4s;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(180deg); }
    }

    /* Menu Grid Styles */
    .menu-grid {
        padding: 0 1rem;
    }

    .menu-section {
        margin-bottom: 3rem;
    }

    .section-title {
        color: #2c3e50;
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 2rem;
        padding-left: 1rem;
        position: relative;
    }

    .section-title::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 4px;
        height: 30px;
        background: var(--gradient-primary);
        border-radius: 2px;
    }

    /* Menu Card Styles */
    .menu-card {
        background: white;
        border-radius: var(--border-radius);
        padding: 2rem 1.5rem;
        height: 100%;
        position: relative;
        overflow: hidden;
        box-shadow: var(--shadow-light);
        transition: var(--transition);
        border: none;
        cursor: pointer;
    }

    .menu-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--shadow-hover);
    }

    .menu-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--gradient-primary);
        transition: var(--transition);
    }

    .primary-card::before { background: var(--gradient-primary); }
    .success-card::before { background: var(--gradient-success); }
    .warning-card::before { background: var(--gradient-warning); }
    .info-card::before { background: var(--gradient-info); }
    .admin-card::before { background: var(--gradient-admin); }

    .card-icon {
        font-size: 3rem;
        margin-bottom: 1.5rem;
        background: var(--gradient-primary);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        display: flex;
        justify-content: center;
    }

    .primary-card .card-icon { background: var(--gradient-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    .success-card .card-icon { background: var(--gradient-success); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    .warning-card .card-icon { background: var(--gradient-warning); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    .info-card .card-icon { background: var(--gradient-info); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    .admin-card .card-icon { background: var(--gradient-admin); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

    .card-content {
        text-align: center;
        position: relative;
        z-index: 2;
    }

    .card-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 0.75rem;
    }

    .card-description {
        color: #7f8c8d;
        font-size: 0.95rem;
        line-height: 1.5;
        margin-bottom: 1.5rem;
    }

    /* Menu Button Styles */
    .menu-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 500;
        transition: var(--transition);
        color: white;
        position: relative;
        overflow: hidden;
    }

    .menu-btn:hover {
        transform: translateX(5px);
        text-decoration: none;
        color: white;
    }

    .menu-btn i {
        transition: var(--transition);
    }

    .menu-btn:hover i {
        transform: translateX(5px);
    }

    .primary-btn { background: var(--gradient-primary); }
    .success-btn { background: var(--gradient-success); }
    .warning-btn { background: var(--gradient-warning); }
    .info-btn { background: var(--gradient-info); }
    .admin-btn { background: var(--gradient-admin); }

    .card-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.1) 100%);
        opacity: 0;
        transition: var(--transition);
    }

    .menu-card:hover .card-overlay {
        opacity: 1;
    }

    /* Logout Section */
    .logout-section {
        margin-top: 4rem;
        padding: 2rem 1rem;
    }

    .logout-container {
        display: flex;
        justify-content: center;
    }

    .logout-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1rem 2rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        text-decoration: none;
        border-radius: 50px;
        font-weight: 500;
        font-size: 1.1rem;
        transition: var(--transition);
        box-shadow: var(--shadow-light);
    }

    .logout-btn:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-hover);
        text-decoration: none;
        color: white;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .welcome-content {
            flex-direction: column;
            text-align: center;
            gap: 1rem;
        }

        .welcome-avatar {
            font-size: 3rem;
        }

        .welcome-title {
            font-size: 1.5rem;
        }

        .welcome-name {
            font-size: 2rem;
        }

        .menu-container {
            padding: 1rem 0;
        }

        .welcome-header {
            padding: 2rem 1rem;
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
        }

        .menu-card {
            margin-bottom: 1rem;
        }
    }

    @media (max-width: 576px) {
        .card-icon {
            font-size: 2.5rem;
        }

        .card-title {
            font-size: 1.1rem;
        }

        .card-description {
            font-size: 0.9rem;
        }

        .menu-btn {
            padding: 0.6rem 1.2rem;
            font-size: 0.9rem;
        }
    }

    /* Animation on page load */
    .menu-card {
        opacity: 0;
        transform: translateY(30px);
        animation: fadeInUp 0.6s ease forwards;
    }

    .menu-card:nth-child(1) { animation-delay: 0.1s; }
    .menu-card:nth-child(2) { animation-delay: 0.2s; }
    .menu-card:nth-child(3) { animation-delay: 0.3s; }
    .menu-card:nth-child(4) { animation-delay: 0.4s; }
    .menu-card:nth-child(5) { animation-delay: 0.5s; }

    @keyframes fadeInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
@endpush
