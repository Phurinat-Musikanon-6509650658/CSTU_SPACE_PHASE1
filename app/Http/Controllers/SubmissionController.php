<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class SubmissionController extends Controller
{
    /**
     * แสดงหน้าส่งเล่มรายงาน
     */
    public function showUploadForm()
    {
        $student = Auth::guard('student')->user();
        
        // หากลุ่มของ student
        $group = $student->groups()->first();
        
        if (!$group) {
            return redirect()->route('student.menu')
                ->with('error', 'คุณยังไม่ได้อยู่ในกลุ่มใดๆ');
        }
        
        // ตรวจสอบว่ามี project หรือยัง
        if (!$group->project) {
            return redirect()->route('student.menu')
                ->with('error', 'กลุ่มของคุณยังไม่มีโครงงาน');
        }
        
        $project = $group->project;
        
        // ตรวจสอบสถานะ project
        if ($project->status_project !== 'approved' && $project->status_project !== 'in_progress') {
            return redirect()->route('student.menu')
                ->with('error', 'โครงงานยังไม่ได้รับการอนุมัติ ไม่สามารถส่งเล่มรายงานได้');
        }
        
        return view('student.submission.upload', compact('project'));
    }
    
    /**
     * อัพโหลดไฟล์เล่มรายงาน
     */
    public function upload(Request $request, $projectId)
    {
        $request->validate([
            'report_file' => 'required|file|mimes:pdf|max:51200', // 50 MB
        ], [
            'report_file.required' => 'กรุณาเลือกไฟล์เล่มรายงาน',
            'report_file.mimes' => 'ไฟล์ต้องเป็น PDF เท่านั้น',
            'report_file.max' => 'ไฟล์มีขนาดใหญ่เกิน 50 MB',
        ]);
        
        $student = Auth::guard('student')->user();
        $project = Project::findOrFail($projectId);
        
        // ตรวจสอบว่า student อยู่ในกลุ่มนี้หรือไม่
        $group = $project->group;
        if (!$group->members->contains('username_std', $student->username_std)) {
            return back()->with('error', 'คุณไม่มีสิทธิ์ส่งเล่มรายงานโครงงานนี้');
        }
        
        DB::beginTransaction();
        try {
            $file = $request->file('report_file');
            
            // สร้างชื่อไฟล์ตามรูปแบบ: project_code_YYYYMMDD.pdf
            $filename = $project->project_code . '_' . date('Ymd') . '.pdf';
            
            // ลบไฟล์เดิม (ถ้ามี)
            if ($project->submission_file && Storage::disk('public')->exists($project->submission_file)) {
                Storage::disk('public')->delete($project->submission_file);
            }
            
            // บันทึกไฟล์ใหม่
            $path = $file->storeAs('submissions', $filename, 'public');
            
            // อัพเดทข้อมูลใน database
            $project->update([
                'submission_file' => $path,
                'submission_original_name' => $file->getClientOriginalName(),
                'submitted_at' => now(),
                'submitted_by' => $student->username_std,
                'status_project' => 'submitted' // เปลี่ยนสถานะเป็นส่งแล้ว
            ]);
            
            DB::commit();
            
            return redirect()->route('student.submission.form')
                ->with('success', 'ส่งเล่มรายงานเรียบร้อยแล้ว');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'เกิดข้อผิดพลาดในการอัพโหลดไฟล์: ' . $e->getMessage());
        }
    }
    
    /**
     * ดาวน์โหลดไฟล์เล่มรายงาน
     */
    public function download($projectId)
    {
        $project = Project::findOrFail($projectId);
        
        if (!$project->submission_file) {
            return back()->with('error', 'ไม่พบไฟล์เล่มรายงาน');
        }
        
        // ตรวจสอบสิทธิ์
        $user = Auth::guard('web')->user();
        $student = Auth::guard('student')->user();
        
        if ($student) {
            // Student ดาวน์โหลดได้เฉพาะกลุ่มของตัวเอง
            $group = $project->group;
            if (!$group->members->contains('username_std', $student->username_std)) {
                return back()->with('error', 'คุณไม่มีสิทธิ์ดาวน์โหลดไฟล์นี้');
            }
        } elseif ($user) {
            // Lecturer ดาวน์โหลดได้เฉพาะโครงงานที่ตัวเองเกี่ยวข้อง (อาจารย์ที่ปรึกษา, กรรมการ)
            if ($user->isLecturer() && !$user->isCoordinator() && !$user->isAdmin()) {
                $isRelated = (
                    $project->advisor_code === $user->user_code ||
                    $project->committee1_code === $user->user_code ||
                    $project->committee2_code === $user->user_code ||
                    $project->committee3_code === $user->user_code
                );
                
                if (!$isRelated) {
                    return back()->with('error', 'คุณไม่มีสิทธิ์ดาวน์โหลดไฟล์นี้');
                }
            }
            // Coordinator, Admin, Staff สามารถดาวน์โหลดได้ทั้งหมด
        } else {
            return redirect()->route('login')->with('error', 'กรุณาเข้าสู่ระบบ');
        }
        
        if (!Storage::disk('public')->exists($project->submission_file)) {
            return back()->with('error', 'ไม่พบไฟล์ในระบบ');
        }
        
        return Storage::disk('public')->download(
            $project->submission_file,
            $project->submission_original_name
        );
    }
}
