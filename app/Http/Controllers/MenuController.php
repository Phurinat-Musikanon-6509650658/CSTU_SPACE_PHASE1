<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

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
        $role = Session::get('department', 'student'); // ใช้ department เป็น role

        // ส่งข้อมูลไปยัง view
        return view('menu', [
            'displayname' => $displayname,
            'role' => $role,
        ]);
    }
}
