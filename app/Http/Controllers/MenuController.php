<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\UserRole;

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
        $roleCode = Session::get('role_code', 2048); // ดึง role_code จาก session
        $department = Session::get('department', 'student'); // เก็บไว้สำหรับแสดงผล
        
        // สร้างเมนูตาม binary permission โดยใช้ role_code โดยตรง
        $menuGroups = $this->getMenuByPermission($roleCode);
        
        // ดึง roles ทั้งหมดที่ user มี
        $userRoles = $this->getUserRoles($roleCode);

        // ส่งข้อมูลไปยัง view
        return view('menu', [
            'displayname' => $displayname,
            'role' => $department,
            'userRoles' => $userRoles,
            'menuGroups' => $menuGroups
        ]);
    }
    
    /**
     * ดึงรายชื่อ roles ทั้งหมดที่ user มี
     */
    private function getUserRoles($roleCode)
    {
        $roles = [];
        
        // เช็คแต่ละ role ด้วย bitwise AND
        if (($roleCode & 32768) !== 0) $roles[] = ['name' => 'Admin', 'class' => 'admin'];
        if (($roleCode & 16384) !== 0) $roles[] = ['name' => 'Coordinator', 'class' => 'coordinator'];
        if (($roleCode & 8192) !== 0) $roles[] = ['name' => 'Lecturer', 'class' => 'lecturer'];
        if (($roleCode & 4096) !== 0) $roles[] = ['name' => 'Staff', 'class' => 'staff'];
        if (($roleCode & 2048) !== 0) $roles[] = ['name' => 'Student', 'class' => 'student'];
        
        return $roles;
    }

    /**
     * ดึง role_code_bin จาก table user_role ในฐานข้อมูล
     */
    private function getRoleCodeBinFromDatabase($department)
    {
        $role = UserRole::where('role_name', $department)->first();
        
        if ($role) {
            return $role->role_code_bin;
        }
        
        // ถ้าไม่เจอใน database ให้ default เป็น student
        $defaultRole = UserRole::where('role_name', 'student')->first();
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
        $roles = UserRole::all();
        $permissions = [];
        
        foreach ($roles as $role) {
            if (($roleCodeBin & $role->role_code_bin) !== 0) {
                $permissions[] = $role->role_name;
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
        $adminRole = UserRole::where('role_name', 'admin')->first();
        $coordinatorRole = UserRole::where('role_name', 'coordinator')->first();
        $lecturerRole = UserRole::where('role_name', 'lecturer')->first();
        $staffRole = UserRole::where('role_name', 'staff')->first();
        $studentRole = UserRole::where('role_name', 'student')->first();

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

        // Staff Menu (View exam schedules)
        if ($hasStaff && !$hasAdmin && !$hasCoordinator) {
            $menuGroups[] = $this->getStaffMenu();
        }

        // Lecturer/Advisor Advisory Work
        if ($hasLecturer || $hasCoordinator || $hasAdmin) {
            $menuGroups[] = $this->getAdvisoryWorkMenu();
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
     * เมนูสำหรับ Staff (ดูตารางสอบอย่างเดียว)
     */
    private function getStaffMenu()
    {
        return [
            'title' => 'แดชบอร์ดเจ้าหน้าที่',
            'items' => [
                [
                    'title' => 'ตารางสอบโครงงาน',
                    'description' => 'ดูตารางสอบของโครงงานทั้งหมด',
                    'icon' => 'bi-calendar-event-fill',
                    'url' => route('staff.exam-schedules'),
                    'class' => 'info-card',
                    'btn_class' => 'info-btn'
                ]
            ]
        ];
    }

    /**
     * เมนู System Management สำหรับ Admin
     */
    private function getAdminSystemMenu()
    {
        return [
            'title' => 'การจัดการระบบ',
            'items' => [
                [
                    'title' => 'จัดการผู้ใช้',
                    'description' => 'เพิ่ม/แก้ไข/ลบผู้ใช้ระบบ',
                    'icon' => 'bi-people-fill',
                    'url' => route('users.index'),
                    'class' => 'primary-card',
                    'btn_class' => 'primary-btn'
                ],
                [
                    'title' => 'จัดการตารางสอบ',
                    'description' => 'จัดการตารางสอบโครงงาน',
                    'icon' => 'bi-calendar-event-fill',
                    'url' => route('admin.exam-schedules.index'),
                    'class' => 'success-card',
                    'btn_class' => 'success-btn'
                ],
                [
                    'title' => 'สถิติการใช้งาน',
                    'description' => 'ดูสถิติการใช้งานระบบ',
                    'icon' => 'bi-graph-up',
                    'url' => route('statistics.index'),
                    'class' => 'info-card',
                    'btn_class' => 'info-btn'
                ],
                [
                    'title' => 'ประวัติการเข้าสู่ระบบ',
                    'description' => 'ติดตามประวัติการเข้าใช้งาน',
                    'icon' => 'bi-shield-lock',
                    'url' => route('admin.logs.index'),
                    'class' => 'warning-card',
                    'btn_class' => 'warning-btn'
                ],
                [
                    'title' => 'ตั้งค่าระบบ',
                    'description' => 'จัดการการตั้งค่าและสถานะระบบ',
                    'icon' => 'bi-gear-fill',
                    'url' => route('admin.system.index'),
                    'class' => 'danger-card',
                    'btn_class' => 'danger-btn'
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
            'title' => 'การจัดการโครงงาน',
            'items' => [
                [
                    'title' => 'แดชบอร์ดผู้ประสานงาน',
                    'description' => 'ภาพรวมโครงงานและกลุ่มทั้งหมด',
                    'icon' => 'bi-speedometer2',
                    'url' => route('coordinator.dashboard'),
                    'class' => 'primary-card',
                    'btn_class' => 'primary-btn'
                ],
                [
                    'title' => 'จัดการกลุ่มโครงงาน',
                    'description' => 'อนุมัติและจัดการกลุ่มโครงงาน',
                    'icon' => 'bi-people-fill',
                    'url' => route('coordinator.groups.index'),
                    'class' => 'info-card',
                    'btn_class' => 'info-btn'
                ],
                [
                    'title' => 'จัดตารางสอบและกรรมการ',
                    'description' => 'จัดตารางสอบและมอบหมายคณะกรรมการ',
                    'icon' => 'bi-calendar-check-fill',
                    'url' => route('coordinator.schedules.index'),
                    'class' => 'primary-card',
                    'btn_class' => 'primary-btn'
                ],
                [
                    'title' => 'ตารางสอบโครงงาน',
                    'description' => 'ดูและจัดการตารางสอบโครงงาน',
                    'icon' => 'bi-calendar-event-fill',
                    'url' => route('coordinator.exam-schedules.index'),
                    'class' => 'success-card',
                    'btn_class' => 'success-btn'
                ],
                [
                    'title' => 'ประเมินและให้คะแนน',
                    'description' => 'จัดการแบบฟอร์มประเมินและดูคะแนน',
                    'icon' => 'bi-clipboard-check-fill',
                    'url' => route('coordinator.evaluations.index'),
                    'class' => 'success-card',
                    'btn_class' => 'success-btn'
                ],
                [
                    'title' => 'จัดการผู้ใช้และนักศึกษา',
                    'description' => 'นำเข้า/ส่งออกข้อมูลผู้ใช้และนักศึกษา',
                    'icon' => 'bi-person-lines-fill',
                    'url' => route('coordinator.users.index'),
                    'class' => 'warning-card',
                    'btn_class' => 'warning-btn'
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
            'title' => 'งานอาจารย์ที่ปรึกษา',
            'items' => [
                [
                    'title' => 'แดชบอร์ดอาจารย์',
                    'description' => 'จัดการข้อเสนอและโครงงานนักศึกษา',
                    'icon' => 'bi-mortarboard-fill',
                    'url' => route('lecturer.dashboard'),
                    'class' => 'primary-card',
                    'btn_class' => 'primary-btn'
                ],
                [
                    'title' => 'ข้อเสนอโครงงาน',
                    'description' => 'รับและพิจารณาข้อเสนอโครงงาน',
                    'icon' => 'bi-file-earmark-text-fill',
                    'url' => route('lecturer.proposals.index'),
                    'class' => 'warning-card',
                    'btn_class' => 'warning-btn'
                ],
                [
                    'title' => 'ประเมินโครงงาน',
                    'description' => 'ประเมินและให้คะแนนโครงงาน',
                    'icon' => 'bi-clipboard-check-fill',
                    'url' => route('lecturer.evaluations.index'),
                    'class' => 'success-card',
                    'btn_class' => 'success-btn'
                ],
                [
                    'title' => 'รายงานโครงงาน',
                    'description' => 'สร้างรายงานโครงงานของฉัน',
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
                    'title' => 'User & Student Records',
                    'description' => 'ดูและ Export รายชื่อผู้ใช้และนักศึกษา',
                    'icon' => 'bi-person-lines-fill',
                    'url' => route('users.index'),
                    'class' => 'primary-card',
                    'btn_class' => 'primary-btn'
                ],
                [
                    'title' => 'Document Management',
                    'description' => 'Manage official documents',
                    'icon' => 'bi-files',
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
            'title' => 'ผู้เยี่ยมชม',
            'items' => [
                [
                    'title' => 'ดูข้อมูล',
                    'description' => 'ดูข้อมูลทั่วไป',
                    'icon' => 'bi-info-circle',
                    'url' => '#',
                    'class' => 'info-card',
                    'btn_class' => 'info-btn'
                ],
                [
                    'title' => 'ติดต่อสนับสนุน',
                    'description' => 'รับความช่วยเหลือและสนับสนุน',
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
            'title' => 'งานของฉัน',
            'items' => [
                [
                    'title' => 'โครงงานกลุ่ม',
                    'description' => 'ดูและจัดการโครงงานของกลุ่ม',
                    'icon' => 'bi-journal-text',
                    'url' => '#',
                    'class' => 'success-card',
                    'btn_class' => 'success-btn'
                ],
                [
                    'title' => 'สมาชิกกลุ่ม',
                    'description' => 'ดูสมาชิกในกลุ่มของฉัน',
                    'icon' => 'bi-person-lines-fill',
                    'url' => '#',
                    'class' => 'warning-card',
                    'btn_class' => 'warning-btn'
                ],
                [
                    'title' => 'ส่งเอกสาร',
                    'description' => 'ส่งเอกสารและรายงานโครงงาน',
                    'icon' => 'bi-cloud-upload-fill',
                    'url' => '#',
                    'class' => 'info-card',
                    'btn_class' => 'info-btn'
                ]
            ]
        ];
    }
}
