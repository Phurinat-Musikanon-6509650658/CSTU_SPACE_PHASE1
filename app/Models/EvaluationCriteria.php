<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluationCriteria extends Model
{
    protected $table = 'evaluation_criteria';

    protected $fillable = [
        'subject_code',
        'part1_label', 'part1_max',
        'part2_label', 'part2_max',
        'part3a_label', 'part3a_max',
        'part3b_label', 'part3b_max',
        'part3c_label', 'part3c_max',
    ];

    public function getAdvisorMax(): int
    {
        return $this->part1_max + $this->part2_max + $this->getPart3Max();
    }

    public function getCommitteeMax(): int
    {
        return $this->part2_max + $this->getPart3Max();
    }

    public function getPart3Max(): int
    {
        return $this->part3a_max + $this->part3b_max + $this->part3c_max;
    }

    public static function forSubject(string $subjectCode): self
    {
        return static::where('subject_code', $subjectCode)->first() ?? static::cs303Default();
    }

    public static function cs303Default(): self
    {
        return new self([
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
        ]);
    }
}
