<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get current year (in Buddhist calendar)
        $currentYear = intval(date('Y')) + 543; // 2026 + 543 = 2569

        // CS303 - Update or Create
        Subject::updateOrCreate(
            ['subject_code' => 'CS303'],
            [
                'subject_name' => 'โครงงานพิเศษ1',
                'semester' => 1,
                'year' => $currentYear,
                'is_enabled' => true,
                'open_date' => Carbon::now()->startOfDay(),
                'close_date' => Carbon::now()->addMonths(4)->endOfDay(),
                // Access period - เปิดการเข้าใช้ตั้งแต่วันแรก
                'access_open_date' => Carbon::now()->startOfDay(),
                'access_close_date' => Carbon::now()->addMonths(4)->endOfDay(),
                // Evaluation period - อาจารย์ประเมินได้ 2 เดือนสุดท้าย
                'evaluation_open_date' => Carbon::now()->addMonths(2)->startOfDay(),
                'evaluation_close_date' => Carbon::now()->addMonths(4)->endOfDay(),
                // Grade edit period - แก้ไขคะแนนได้ 1 เดือนหลังปิด
                'grade_edit_open_date' => Carbon::now()->addMonths(4)->addDays(1)->startOfDay(),
                'grade_edit_close_date' => Carbon::now()->addMonths(5)->endOfDay(),
            ]
        );

        // CS403 - Update or Create
        Subject::updateOrCreate(
            ['subject_code' => 'CS403'],
            [
                'subject_name' => 'โครงงานพิเศษ2',
                'semester' => 1,
                'year' => $currentYear,
                'is_enabled' => true,
                'open_date' => Carbon::now()->startOfDay(),
                'close_date' => Carbon::now()->addMonths(4)->endOfDay(),
                // Access period - เปิดการเข้าใช้ตั้งแต่วันแรก
                'access_open_date' => Carbon::now()->startOfDay(),
                'access_close_date' => Carbon::now()->addMonths(4)->endOfDay(),
                // Evaluation period - อาจารย์ประเมินได้ 2 เดือนสุดท้าย
                'evaluation_open_date' => Carbon::now()->addMonths(2)->startOfDay(),
                'evaluation_close_date' => Carbon::now()->addMonths(4)->endOfDay(),
                // Grade edit period - แก้ไขคะแนนได้ 1 เดือนหลังปิด
                'grade_edit_open_date' => Carbon::now()->addMonths(4)->addDays(1)->startOfDay(),
                'grade_edit_close_date' => Carbon::now()->addMonths(5)->endOfDay(),
            ]
        );
    }
}
