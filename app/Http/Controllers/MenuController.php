<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Role;

class MenuController extends Controller
{
    /**
     * Display the menu page with role-based content
     */
    public function index()
    {
        // ตรวจสอบว่า user login หรือยัง
        if (!Session::has('displayname')) {
            return redirect()->route('login');
        }

        $displayname = Session::get('displayname');
        $department = Session::get('department', 'student');
        
        // ดึง role_code_bin จาก database
        $roleCodeBin = $this->getRoleCodeBinFromDatabase($department);
        
        // สร้างเมนูตาม binary permission
        $menuGroups = $this->getMenuByPermission($roleCodeBin);

        // ส่งข้อมูลไปยัง view
        return view('menu', [
            'displayname' => $displayname,
            'role' => $department,
            'menuGroups' => $menuGroups
        ]);
    }

    /**
     * ดึง role_code_bin จาก table roles ในฐานข้อมูล
     */
    private function getRoleCodeBinFromDatabase($department)
    {
        $role = Role::where('role', $department)->first();
        
        if ($role) {
            return $role->role_code_bin;
        }
        
        // ถ้าไม่เจอใน database ให้ default เป็น student
        $defaultRole = Role::where('role', 'student')->first();
        return $defaultRole ? $defaultRole->role_code_bin : 2048;
    }

    /**
     * Debug method สำหรับแสดง binary permission (สำหรับ development)
     */
    public function debugPermission($department = null)
    {
        if (!$department) {
            $department = Session::get('department', 'student');
        }
        
        $roleCodeBin = $this->getRoleCodeBinFromDatabase($department);
        
        // ดึง permission constants จาก database
        $roles = Role::all();
        $permissions = [];
        
        foreach ($roles as $role) {
            if (($roleCodeBin & $role->role_code_bin) !== 0) {
                $permissions[] = $role->role;
            }
        }
        
        // Check for guest (special case)
        if ($roleCodeBin === 1) {
            $permissions[] = 'Guest';
        }
        
        return [
            'department' => $department,
            'role_code_bin' => $roleCodeBin,
            'binary_string' => str_pad(decbin($roleCodeBin), 15, '0', STR_PAD_LEFT),
            'permissions' => $permissions,
            'menu_count' => count($this->getMenuByPermission($roleCodeBin)),
            'all_roles' => $roles->pluck('role', 'role_code_bin')->toArray()
        ];
    }

    /**
     * สร้างกลุ่มเมนูตาม binary permission จาก database
     */
    private function getMenuByPermission($roleCodeBin)
    {
        $menuGroups = [];

        // ดึง permission constants จาก database
        $adminRole = Role::where('role', 'admin')->first();
        $coordinatorRole = Role::where('role', 'coordinator')->first();
        $lecturerRole = Role::where('role', 'lecturer')->first();
        $staffRole = Role::where('role', 'staff')->first();
        $studentRole = Role::where('role', 'student')->first();

        $ADMIN_PERMISSION = $adminRole ? $adminRole->role_code_bin : 32768;
        $COORDINATOR_PERMISSION = $coordinatorRole ? $coordinatorRole->role_code_bin : 16384;
        $LECTURER_PERMISSION = $lecturerRole ? $lecturerRole->role_code_bin : 8192;
        $STAFF_PERMISSION = $staffRole ? $staffRole->role_code_bin : 4096;
        $STUDENT_PERMISSION = $studentRole ? $studentRole->role_code_bin : 2048;

        // Check permissions using bitwise operations
        $hasAdmin = ($roleCodeBin & $ADMIN_PERMISSION) !== 0;
        $hasCoordinator = ($roleCodeBin & $COORDINATOR_PERMISSION) !== 0;
        $hasLecturer = ($roleCodeBin & $LECTURER_PERMISSION) !== 0;
        $hasStaff = ($roleCodeBin & $STAFF_PERMISSION) !== 0;
        $hasStudent = ($roleCodeBin & $STUDENT_PERMISSION) !== 0;

        // Admin System Management
        if ($hasAdmin) {
            $menuGroups[] = $this->getAdminSystemMenu();
        }

        // Coordinator Project Management
        if ($hasCoordinator || $hasAdmin) {
            $menuGroups[] = $this->getProjectManagementMenu();
        }

        // Lecturer/Advisor Advisory Work
        if ($hasLecturer || $hasCoordinator || $hasAdmin) {
            $menuGroups[] = $this->getAdvisoryWorkMenu();
        }

        // Staff Management Tools
        if ($hasStaff || $hasAdmin) {
            $menuGroups[] = $this->getStaffManagementMenu();
        }

        // Student Work (for students or when viewing as student perspective)
        if ($hasStudent || $roleCodeBin === 1) { // Guest can see basic student view
            $menuGroups[] = $this->getStudentWorkMenu();
        }

        // If no specific permissions, show basic menu
        if (empty($menuGroups)) {
            $menuGroups[] = $this->getGuestMenu();
        }

        return $menuGroups;
    }

    /**
     * เมนู System Management สำหรับ Admin
     */
    private function getAdminSystemMenu()
    {
        return [
            'title' => 'System Management',
            'items' => [
                [
                    'title' => 'User Management',
                    'description' => 'Add/Edit/Delete users',
                    'icon' => 'bi-people-fill',
                    'url' => route('users.index'),
                    'class' => 'primary-card',
                    'btn_class' => 'primary-btn'
                ],
                [
                    'title' => 'Login Logs',
                    'description' => 'Track system access logs',
                    'icon' => 'bi-shield-lock',
                    'url' => route('admin.logs.index'),
                    'class' => 'warning-card',
                    'btn_class' => 'warning-btn'
                ],
                [
                    'title' => 'Statistics',
                    'description' => 'View usage statistics',
                    'icon' => 'bi-graph-up',
                    'url' => route('statistics.index'),
                    'class' => 'info-card',
                    'btn_class' => 'info-btn'
                ]
            ]
        ];
    }

    /**
     * เมนู Project Management สำหรับ Coordinator
     */
    private function getProjectManagementMenu()
    {
        return [
            'title' => 'Project Management',
            'items' => [
                [
                    'title' => 'Track All Projects',
                    'description' => 'View and track status of all projects',
                    'icon' => 'bi-clipboard-check',
                    'url' => '#',
                    'class' => 'primary-card',
                    'btn_class' => 'primary-btn'
                ],
                [
                    'title' => 'Manage Advisors',
                    'description' => 'View advisor list and their projects',
                    'icon' => 'bi-person-badge',
                    'url' => '#',
                    'class' => 'info-card',
                    'btn_class' => 'info-btn'
                ],
                [
                    'title' => 'Manage Students',
                    'description' => 'View all student records',
                    'icon' => 'bi-mortarboard-fill',
                    'url' => '#',
                    'class' => 'success-card',
                    'btn_class' => 'success-btn'
                ]
            ]
        ];
    }

    /**
     * เมนู Advisory Work สำหรับ Advisor
     */
    private function getAdvisoryWorkMenu()
    {
        return [
            'title' => 'Advisory Work',
            'items' => [
                [
                    'title' => 'My Projects',
                    'description' => 'View projects I\'m advising',
                    'icon' => 'bi-folder-fill',
                    'url' => '#',
                    'class' => 'primary-card',
                    'btn_class' => 'primary-btn'
                ],
                [
                    'title' => 'My Students',
                    'description' => 'View students under my guidance',
                    'icon' => 'bi-people',
                    'url' => '#',
                    'class' => 'success-card',
                    'btn_class' => 'success-btn'
                ],
                [
                    'title' => 'Project Reports',
                    'description' => 'Generate reports for my projects',
                    'icon' => 'bi-file-earmark-text',
                    'url' => '#',
                    'class' => 'info-card',
                    'btn_class' => 'info-btn'
                ]
            ]
        ];
    }

    /**
     * เมนู Staff Management สำหรับ Staff และ Admin
     */
    private function getStaffManagementMenu()
    {
        return [
            'title' => 'Staff Management',
            'items' => [
                [
                    'title' => 'Document Management',
                    'description' => 'Manage official documents',
                    'icon' => 'bi-files',
                    'url' => '#',
                    'class' => 'primary-card',
                    'btn_class' => 'primary-btn'
                ],
                [
                    'title' => 'Schedule Management',
                    'description' => 'Manage academic schedules',
                    'icon' => 'bi-calendar-event',
                    'url' => '#',
                    'class' => 'info-card',
                    'btn_class' => 'info-btn'
                ],
                [
                    'title' => 'Resource Booking',
                    'description' => 'Book rooms and resources',
                    'icon' => 'bi-geo-alt-fill',
                    'url' => '#',
                    'class' => 'success-card',
                    'btn_class' => 'success-btn'
                ]
            ]
        ];
    }

    /**
     * เมนูสำหรับ Guest
     */
    private function getGuestMenu()
    {
        return [
            'title' => 'Guest Access',
            'items' => [
                [
                    'title' => 'View Information',
                    'description' => 'View public information',
                    'icon' => 'bi-info-circle',
                    'url' => '#',
                    'class' => 'info-card',
                    'btn_class' => 'info-btn'
                ],
                [
                    'title' => 'Contact Support',
                    'description' => 'Get help and support',
                    'icon' => 'bi-headset',
                    'url' => '#',
                    'class' => 'warning-card',
                    'btn_class' => 'warning-btn'
                ]
            ]
        ];
    }

    /**
     * เมนู Student สำหรับ admin, coordinator, lecturer (ดูข้อมูล student ทั่วไป)
     */
    private function getStudentMenu()
    {
        return [
            'title' => 'Student Management',
            'items' => [
                [
                    'title' => 'Student List',
                    'description' => 'View all student records',
                    'icon' => 'bi-people-fill',
                    'url' => '#',
                    'class' => 'primary-card',
                    'btn_class' => 'primary-btn'
                ],
                [
                    'title' => 'Project Groups',
                    'description' => 'View student project groups',
                    'icon' => 'bi-diagram-3-fill',
                    'url' => '#',
                    'class' => 'success-card',
                    'btn_class' => 'success-btn'
                ],
                [
                    'title' => 'Student Reports',
                    'description' => 'Generate student progress reports',
                    'icon' => 'bi-bar-chart-line-fill',
                    'url' => '#',
                    'class' => 'info-card',
                    'btn_class' => 'info-btn'
                ]
            ]
        ];
    }

    /**
     * เมนูสำหรับ Student โดยเฉพาะ (งานของตัวเอง)
     */
    private function getStudentWorkMenu()
    {
        return [
            'title' => 'My Work',
            'items' => [
                [
                    'title' => 'Group Project',
                    'description' => 'View and manage my group project',
                    'icon' => 'bi-journal-text',
                    'url' => '#',
                    'class' => 'success-card',
                    'btn_class' => 'success-btn'
                ],
                [
                    'title' => 'Group Members',
                    'description' => 'View members in my group',
                    'icon' => 'bi-person-lines-fill',
                    'url' => '#',
                    'class' => 'warning-card',
                    'btn_class' => 'warning-btn'
                ],
                [
                    'title' => 'Submit Documents',
                    'description' => 'Submit project documents and reports',
                    'icon' => 'bi-cloud-upload-fill',
                    'url' => '#',
                    'class' => 'info-card',
                    'btn_class' => 'info-btn'
                ]
            ]
        ];
    }
}
