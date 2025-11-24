<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\UserRole;
use App\Helpers\PermissionHelper;

class PermissionTestController extends Controller
{
    /**
     * Test permission system
     */
    public function testPermission()
    {
        // ดึง role_code ของ user ปัจจุบัน
        $roleCode = Session::get('role_code', 2048);
        $role = UserRole::where('role_code', $roleCode)->first();

        if (!$role) {
            return response()->json(['error' => 'Role not found'], 404);
        }

        $displayedNumber = $role->role_code;      // ตัวเลขที่แสดง (เช่น 32768)
        $binaryCode = $role->role_code_bin;       // รหัส binary จาก database

        // เช็ค binary integrity
        $integrityCheck = PermissionHelper::verifyBinaryIntegrity($displayedNumber, $binaryCode);
        
        if (!$integrityCheck) {
            return response()->json([
                'error' => 'Binary integrity check failed!',
                'displayed' => $displayedNumber,
                'binary_code' => $binaryCode,
                'displayed_binary' => PermissionHelper::toBinaryString($displayedNumber),
                'stored_binary' => PermissionHelper::toBinaryString($binaryCode)
            ], 403);
        }

        // ตัวอย่างการเช็ค permission
        $permissions = [
            'admin' => PermissionHelper::hasPermission($displayedNumber, PermissionHelper::ADMIN_PERMISSION),
            'coordinator' => PermissionHelper::hasPermission($displayedNumber, PermissionHelper::COORDINATOR_PERMISSION),
            'lecturer' => PermissionHelper::hasPermission($displayedNumber, PermissionHelper::LECTURER_PERMISSION),
            'staff' => PermissionHelper::hasPermission($displayedNumber, PermissionHelper::STAFF_PERMISSION),
            'student' => PermissionHelper::hasPermission($displayedNumber, PermissionHelper::STUDENT_PERMISSION)
        ];

        return response()->json([
            'user_role' => $role->role_name,
            'displayed_number' => $displayedNumber,
            'binary_representation' => PermissionHelper::toBinaryString($displayedNumber),
            'binary_integrity' => $integrityCheck,
            'permissions' => $permissions
        ]);
    }
}