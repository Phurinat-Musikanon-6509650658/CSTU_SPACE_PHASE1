<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GroupInvitation;
use App\Models\GroupMember;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GroupInvitationController extends Controller
{
    public function accept(GroupInvitation $invitation)
    {
        $student = Auth::guard('student')->user();

        // ตรวจสอบว่าเป็นผู้ที่ถูกเชิญ
        if ($invitation->invitee_username !== $student->username_std) {
            return redirect()->route('student.menu')->with('error', 'คุณไม่มีสิทธิ์ดำเนินการนี้');
        }

        // ตรวจสอบสถานะคำเชิญ
        if (!$invitation->isPending()) {
            return redirect()->route('student.menu')->with('error', 'คำเชิญนี้ได้ดำเนินการแล้ว');
        }

        // ตรวจสอบว่า student ยังไม่มีกลุ่ม
        if ($student->hasGroup()) {
            return redirect()->route('student.menu')->with('error', 'คุณมีกลุ่มแล้ว');
        }

        // ตรวจสอบว่ากลุ่มยังสามารถรับสมาชิกได้
        if (!$invitation->group->canAddMember()) {
            return redirect()->route('student.menu')->with('error', 'กลุ่มนี้เต็มแล้ว');
        }

        DB::beginTransaction();
        try {
            // ตอบรับคำเชิญ
            $invitation->accept();

            // เพิ่มเป็นสมาชิกกลุ่ม
            GroupMember::create([
                'group_id' => $invitation->group_id,
                'username_std' => $student->username_std
            ]);
            
            // อัพเดตสถานะกลุ่มเป็น member_added (ถ้ามีการเพิ่มสมาชิกใหม่)
            $invitation->group->update(['status_group' => 'member_added']);

            DB::commit();
            return redirect()->route('student.menu')->with('success', 'เข้าร่วมกลุ่มสำเร็จ');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('student.menu')->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    public function decline(GroupInvitation $invitation)
    {
        $student = Auth::guard('student')->user();

        // ตรวจสอบว่าเป็นผู้ที่ถูกเชิญ
        if ($invitation->invitee_username !== $student->username_std) {
            return redirect()->route('student.menu')->with('error', 'คุณไม่มีสิทธิ์ดำเนินการนี้');
        }

        // ตรวจสอบสถานะคำเชิญ
        if (!$invitation->isPending()) {
            return redirect()->route('student.menu')->with('error', 'คำเชิญนี้ได้ดำเนินการแล้ว');
        }

        // ปฏิเสธคำเชิญ
        $invitation->decline();

        return redirect()->route('student.menu')->with('success', 'ปฏิเสธคำเชิญแล้ว');
    }

    public function index()
    {
        $student = Auth::guard('student')->user();
        
        $invitations = $student->receivedInvitations()
            ->with(['group', 'inviter'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('student.invitations.index', compact('invitations'));
    }

    // ยกเลิกคำเชิญ (สำหรับผู้ส่งคำเชิญ)
    public function cancel(GroupInvitation $invitation)
    {
        $student = Auth::guard('student')->user();

        // ตรวจสอบว่าเป็นผู้ส่งคำเชิญ
        if ($invitation->inviter_username !== $student->username_std) {
            return redirect()->route('student.menu')->with('error', 'คุณไม่มีสิทธิ์ดำเนินการนี้');
        }

        // ตรวจสอบสถานะคำเชิญ
        if (!$invitation->isPending()) {
            return redirect()->route('student.menu')->with('error', 'คำเชิญนี้ได้ดำเนินการแล้ว');
        }

        // ลบคำเชิญ
        $invitation->delete();

        return redirect()->back()->with('success', 'ยกเลิกคำเชิญแล้ว');
    }
}
