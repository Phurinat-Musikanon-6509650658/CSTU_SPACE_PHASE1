<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        // ปีการศึกษา 2568 เทอม 2 (ข้อมูลจริง 68-2_Projects1-2)
        // ขอบเขต: 1 ม.ค. 2569 → 31 ก.ค. 2569 (CE: 2026-01-01 → 2026-07-31)
        $open  = Carbon::parse('2026-01-01')->startOfDay();
        $close = Carbon::parse('2026-07-31')->endOfDay();

        $subjects = [
            ['code' => 'CS303', 'name' => 'โครงงานพิเศษ1'],
            ['code' => 'CS403', 'name' => 'โครงงานพิเศษ2'],
        ];

        foreach ($subjects as $s) {
            Subject::updateOrCreate(
                [
                    'subject_code' => $s['code'],
                    'year'         => 2568,
                    'semester'     => 2,
                ],
                [
                    'subject_name'          => $s['name'],
                    'is_enabled'            => true,
                    'open_date'             => $open,
                    'close_date'            => $close,
                    'access_open_date'      => $open,
                    'access_close_date'     => $close,
                    'evaluation_open_date'  => Carbon::parse('2026-06-01')->startOfDay(),
                    'evaluation_close_date' => $close,
                    'grade_edit_open_date'  => Carbon::parse('2026-08-01')->startOfDay(),
                    'grade_edit_close_date' => Carbon::parse('2026-08-31')->endOfDay(),
                ]
            );
        }
    }
}
