<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Group;
use App\Models\GroupInvitation;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    public function menu()
    {
        $student = Auth::guard('student')->user();
        
        if (!$student) {
            return redirect()->route('login')->with('error', 'กรุณาเข้าสู่ระบบ');
        }

        // ดึงข้อมูลกลุ่มของ student
        $myGroup = $student->groups()->with('members.student')->first();
        
        // ดึงคำเชิญที่ค้างอยู่
        $pendingInvitations = $student->pendingInvitations()
            ->with(['group', 'inviter'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('student.menu', compact('student', 'myGroup', 'pendingInvitations'));
    }

    public function dashboard()
    {
        return $this->menu(); // redirect to menu as requested
    }
}
