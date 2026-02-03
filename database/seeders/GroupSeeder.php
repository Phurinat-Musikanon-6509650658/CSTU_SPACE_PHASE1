<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\GroupInvitation;
use App\Models\Project;
use App\Models\Student;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        echo "🚀 เริ่ม seed กลุ่มและสถานการณ์ต่างๆ\n";
        echo "═══════════════════════════════════════════\n\n";

        // ล้างข้อมูลเดิม
        DB::table('project_proposals')->delete();
        DB::table('projects')->delete();
        DB::table('group_invitations')->delete();
        DB::table('group_members')->delete();
        DB::table('groups')->delete();

        $students = Student::all();
        if ($students->count() < 8) {
            echo "⚠️ ต้องมี student อย่างน้อย 8 คน (มีเพียง {$students->count()} คน)\n";
            return;
        }

        // กลุ่ม 1: กลุ่มเดี่ยว (1 คน) - สถานะ created
        echo "📝 สร้างกลุ่ม 1: กลุ่มเดี่ยว (1 คน)\n";
        $group1 = Group::create([
            'group_id' => 1,
            'year' => 2568,
            'semester' => 1,
            'subject_code' => 'CS303',
            'status_group' => 'created'
        ]);
        
        GroupMember::create([
            'group_id' => 1,
            'username_std' => $students[0]->username_std
        ]);
        
        Project::create([
            'group_id' => 1,
            'project_code' => '68-1-01',
            'status_project' => 'not_proposed',
            'student_type' => 'r'
        ]);
        
        echo "   ✅ กลุ่ม 1: {$students[0]->username_std}\n\n";

        // กลุ่ม 2: กลุ่ม 2 คน - สถานะ created
        echo "📝 สร้างกลุ่ม 2: กลุ่ม 2 คน (ไม่มี invitation รอ)\n";
        $group2 = Group::create([
            'group_id' => 2,
            'year' => 2568,
            'semester' => 1,
            'subject_code' => 'CS303',
            'status_group' => 'created'
        ]);
        
        GroupMember::create([
            'group_id' => 2,
            'username_std' => $students[1]->username_std
        ]);
        
        GroupMember::create([
            'group_id' => 2,
            'username_std' => $students[2]->username_std
        ]);
        
        Project::create([
            'group_id' => 2,
            'project_code' => '68-1-02',
            'status_project' => 'not_proposed',
            'student_type' => 'r'
        ]);
        
        echo "   ✅ กลุ่ม 2: {$students[1]->username_std}, {$students[2]->username_std}\n\n";

        // กลุ่ม 3: กลุ่มเดี่ยวที่มีคำเชิญรอ - สถานะ created
        echo "📝 สร้างกลุ่ม 3: กลุ่มเดี่ยวที่มีคำเชิญรอ\n";
        $group3 = Group::create([
            'group_id' => 3,
            'year' => 2568,
            'semester' => 1,
            'subject_code' => 'CS303',
            'status_group' => 'created'
        ]);
        
        GroupMember::create([
            'group_id' => 3,
            'username_std' => $students[3]->username_std
        ]);
        
        // สร้างคำเชิญที่รอตอบรับ
        if ($students->count() > 4) {
            GroupInvitation::create([
                'group_id' => 3,
                'inviter_username' => $students[3]->username_std,
                'invitee_username' => $students[4]->username_std,
                'message' => 'เชิญเข้าร่วมกลุ่มโครงงาน วิชา CS303',
                'status' => 'pending'
            ]);
            echo "   ✅ กลุ่ม 3: {$students[3]->username_std} (มีคำเชิญรอจาก {$students[4]->username_std})\n\n";
        }
        
        Project::create([
            'group_id' => 3,
            'project_code' => '68-1-03',
            'status_project' => 'not_proposed',
            'student_type' => 'r'
        ]);

        // กลุ่ม 4: กลุ่ม 2 คน ที่เพิ่งมีสมาชิกเข้ามา - สถานะ member_added
        if ($students->count() > 6) {
            echo "📝 สร้างกลุ่ม 4: กลุ่มที่เพิ่งมีสมาชิกเข้ามา (status: member_added)\n";
            $group4 = Group::create([
                'group_id' => 4,
                'year' => 2568,
                'semester' => 1,
                'subject_code' => 'CS403',
                'status_group' => 'member_added'
            ]);
            
            GroupMember::create([
                'group_id' => 4,
                'username_std' => $students[5]->username_std
            ]);
            
            GroupMember::create([
                'group_id' => 4,
                'username_std' => $students[6]->username_std
            ]);
            
            Project::create([
                'group_id' => 4,
                'project_code' => '68-1-04',
                'status_project' => 'not_proposed',
                'student_type' => 'r'
            ]);
            
            echo "   ✅ กลุ่ม 4: {$students[5]->username_std}, {$students[6]->username_std}\n\n";
        }

        // กลุ่ม 5: กลุ่มที่จะถูกลบ (เพื่อทดสอบการนำเลขกลับมาใช้)
        if ($students->count() > 7) {
            echo "📝 สร้างกลุ่ม 5: กลุ่มที่จะถูกลบ (สำหรับทดสอบ reuse number)\n";
            $group5 = Group::create([
                'group_id' => 5,
                'year' => 2568,
                'semester' => 2,
                'subject_code' => 'CS303',
                'status_group' => 'created'
            ]);
            
            GroupMember::create([
                'group_id' => 5,
                'username_std' => $students[7]->username_std
            ]);
            
            Project::create([
                'group_id' => 5,
                'project_code' => '68-2-05',
                'status_project' => 'not_proposed',
                'student_type' => 'r'
            ]);
            
            echo "   ✅ กลุ่ม 5: {$students[7]->username_std}\n";
            echo "   💡 ทดสอบ: หัวหน้าออกจากกลุ่ม → กลุ่มถูกลบ → เลข 5 ว่าง\n\n";
        }

        echo "═══════════════════════════════════════════\n";
        echo "📊 สรุป:\n";
        echo "   - กลุ่มทั้งหมด: " . Group::count() . " กลุ่ม\n";
        echo "   - กลุ่มเดี่ยว: กลุ่ม 1, 3, 5\n";
        echo "   - กลุ่ม 2 คน: กลุ่ม 2, 4\n";
        echo "   - มีคำเชิญรอ: กลุ่ม 3\n";
        echo "   - Status member_added: กลุ่ม 4\n";
        echo "   - พร้อมทดสอบลบ: กลุ่ม 5\n";
        echo "═══════════════════════════════════════════\n";
    }
}
