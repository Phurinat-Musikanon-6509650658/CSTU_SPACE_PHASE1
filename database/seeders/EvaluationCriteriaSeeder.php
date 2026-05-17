<?php

namespace Database\Seeders;

use App\Models\EvaluationCriteria;
use Illuminate\Database\Seeder;

class EvaluationCriteriaSeeder extends Seeder
{
    public function run(): void
    {
        $criteria = [
            [
                'subject_code' => 'CS303',
                'part1_label'  => 'ความก้าวหน้าโครงงาน',
                'part1_max'    => 10,
                'part2_label'  => 'คุณภาพของรายงาน',
                'part2_max'    => 30,
                'part3a_label' => 'ความเข้าใจในงานที่ทำ',
                'part3a_max'   => 20,
                'part3b_label' => 'คุณภาพการนำเสนอและการตอบคำถาม',
                'part3b_max'   => 20,
                'part3c_label' => 'การประยุกต์ใช้ความรู้ทางวิทยาการคอมพิวเตอร์อย่างเหมาะสมในการนำเสนอโครงงาน',
                'part3c_max'   => 20,
            ],
            [
                'subject_code' => 'CS403',
                'part1_label'  => 'ความก้าวหน้าโครงงาน',
                'part1_max'    => 10,
                'part2_label'  => 'คุณภาพของรายงาน',
                'part2_max'    => 20,
                'part3a_label' => 'คุณภาพของผลงานและความสมบูรณ์ของงาน',
                'part3a_max'   => 30,
                'part3b_label' => 'คุณภาพการนำเสนอและการตอบคำถาม',
                'part3b_max'   => 20,
                'part3c_label' => 'การประยุกต์ใช้ความรู้ทางวิทยาการคอมพิวเตอร์อย่างเหมาะสมในการนำเสนอโครงงาน',
                'part3c_max'   => 20,
            ],
        ];

        foreach ($criteria as $c) {
            EvaluationCriteria::updateOrCreate(['subject_code' => $c['subject_code']], $c);
        }
    }
}
