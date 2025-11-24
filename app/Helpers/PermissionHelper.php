<?php

namespace App\Helpers;

class PermissionHelper
{
    // Define permission constants
    const ADMIN_PERMISSION = 32768;         // 1000000000000000
    const COORDINATOR_PERMISSION = 16384;   // 0100000000000000  
    const LECTURER_PERMISSION = 8192;       // 0010000000000000
    const STAFF_PERMISSION = 4096;          // 0001000000000000
    const STUDENT_PERMISSION = 2048;        // 0000100000000000
    const GUEST_PERMISSION = 1;             // 0000000000000001
    
    // Combined permissions
    const COORDINATOR_LECTURER = 24576;     // 0110000000000000 (16384 + 8192)
    const COORDINATOR_STAFF = 20480;        // 0101000000000000 (16384 + 4096)

    /**
     * Check if user has specific permission
     * @param int $userPermission - User's role_code
     * @param int $requiredPermission - Required permission
     * @return bool
     */
    public static function hasPermission(int $userPermission, int $requiredPermission): bool
    {
        return ($userPermission & $requiredPermission) !== 0;
    }

    /**
     * Verify binary integrity
     * @param int $displayedNumber - The role_code shown
     * @param int $binaryCode - The role_code_bin from database
     * @return bool
     */
    public static function verifyBinaryIntegrity(int $displayedNumber, int $binaryCode): bool
    {
        return $displayedNumber === $binaryCode;
    }

    /**
     * Get permission name
     * @param int $permission
     * @return string
     */
    public static function getPermissionName(int $permission): string
    {
        $permissions = [
            self::ADMIN_PERMISSION => 'Admin',
            self::COORDINATOR_PERMISSION => 'Coordinator',
            self::LECTURER_PERMISSION => 'Lecturer', 
            self::STAFF_PERMISSION => 'Staff',
            self::STUDENT_PERMISSION => 'Student',
            self::GUEST_PERMISSION => 'Guest',
            self::COORDINATOR_LECTURER => 'Coordinator - Lecturer',
            self::COORDINATOR_STAFF => 'Coordinator - Staff'
        ];

        return $permissions[$permission] ?? 'Unknown';
    }

    /**
     * Convert decimal to binary string (16 bits)
     * @param int $decimal
     * @return string
     */
    public static function toBinaryString(int $decimal): string
    {
        return str_pad(decbin($decimal), 16, '0', STR_PAD_LEFT);
    }

    /**
     * Check if current session user is admin
     * @return bool
     */
    public static function isAdmin(): bool
    {
        $roleCode = session('role_code', 0);
        return self::hasPermission($roleCode, self::ADMIN_PERMISSION);
    }

    /**
     * Check if current session user is coordinator
     * @return bool
     */
    public static function isCoordinator(): bool
    {
        $roleCode = session('role_code', 0);
        return self::hasPermission($roleCode, self::COORDINATOR_PERMISSION);
    }

    /**
     * Check if current session user is lecturer
     * @return bool
     */
    public static function isLecturer(): bool
    {
        $roleCode = session('role_code', 0);
        return self::hasPermission($roleCode, self::LECTURER_PERMISSION);
    }

    /**
     * Check if current session user is student
     * @return bool
     */
    public static function isStudent(): bool
    {
        $roleCode = session('role_code', 0);
        return self::hasPermission($roleCode, self::STUDENT_PERMISSION);
    }
}