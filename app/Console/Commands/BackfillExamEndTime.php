<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\XlsxParser;
use App\Http\Controllers\ProjectImportController;
use App\Models\Project;

class BackfillExamEndTime extends Command
{
    protected $signature   = 'projects:backfill-end-time
                                {--file= : Path to xlsx (default: 68-2_Projects1-2_Info-for-CSTU-SPACE.xlsx)}
                                {--month= : Year-month base, e.g. 2568-01 (BE) or 2025-01 (CE)}';
    protected $description = 'Backfill exam_end_time for existing projects from the project Excel file (col AC / index 28)';

    public function handle(): int
    {
        $excelPath = $this->option('file') ?: base_path('68-2_Projects1-2_Info-for-CSTU-SPACE.xlsx');

        if (!file_exists($excelPath)) {
            $this->error("File not found: {$excelPath}");
            return 1;
        }

        // Detect exam_month from existing projects' exam_datetime
        $examMonth = $this->option('month');
        if (!$examMonth) {
            $sample = Project::whereNotNull('exam_datetime')->value('exam_datetime');
            if ($sample) {
                // Convert CE date to YYYY-MM string
                $examMonth = \Carbon\Carbon::parse($sample)->format('Y-m');
            } else {
                $this->error('Cannot detect exam month. Use --month=YYYY-MM (CE).');
                return 1;
            }
        }

        $this->info("Using exam base month: {$examMonth}");

        try {
            $sheetNames = XlsxParser::sheetNames($excelPath);
        } catch (\Exception $e) {
            $this->error('Cannot read xlsx: ' . $e->getMessage());
            return 1;
        }

        $projectSheets = array_filter(
            $sheetNames,
            fn($n) => str_contains(strtolower($n), '--project') && !str_contains(strtolower($n), 'student')
        );

        if (empty($projectSheets)) {
            $this->error('No "--Project" sheet found.');
            return 1;
        }

        $updated = 0;
        $skipped = 0;
        $noEnd   = 0;

        foreach ($projectSheets as $sName) {
            $this->line("Sheet: {$sName}");

            try {
                $rows = XlsxParser::parseSheet($excelPath, $sName, 0);
            } catch (\Exception $e) {
                $this->warn("Cannot read sheet {$sName}: " . $e->getMessage());
                continue;
            }

            foreach ($rows as $ri => $row) {
                if ($ri < 2) continue;

                $projCode    = trim($row[1] ?? '');
                $thaiDateRaw = trim($row[28] ?? '');

                if ($projCode === '' || $thaiDateRaw === '') {
                    continue;
                }

                $parsed = ProjectImportController::parseThaiExamDate($thaiDateRaw, $examMonth);

                if (!$parsed || empty($parsed['end'])) {
                    $noEnd++;
                    continue;
                }

                $rows_affected = Project::where('project_code', $projCode)
                    ->whereNull('exam_end_time')
                    ->update(['exam_end_time' => $parsed['end']]);

                if ($rows_affected > 0) {
                    $updated++;
                    $this->line("  ✓ {$projCode} → {$parsed['end']}");
                } else {
                    $skipped++;
                }
            }
        }

        $this->info("Done. Updated: {$updated}, skipped (already set or not found): {$skipped}, no end time in Excel: {$noEnd}");
        return 0;
    }
}
