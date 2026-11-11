<?php

namespace App\Console\Commands;

use App\Models\HrCtrCase;
use App\Models\HrForms\CaseForm;
use App\Services\HrForms\FormGenerationService;
use App\Services\HrForms\HrFormsSchema;
use Illuminate\Console\Command;
use Throwable;

class HrFormsSyncCommand extends Command
{
    protected $signature = 'hrforms:sync
                            {--case= : Sync a single contract case by ctc_id}
                            {--force : Re-sync cases that already have forms}';

    protected $description = 'Generate / refresh HR Policy forms for editable contract cases (Draft, Under Revision)';

    public function handle(FormGenerationService $service): int
    {
        if (! HrFormsSchema::available()) {
            $this->error('HR Forms is unavailable: either HRFORMS_ENABLED is off or the hrforms schema is not migrated.');

            return self::FAILURE;
        }

        $query = HrCtrCase::query()->whereIn('ctc_status', ['Draft', 'Under Revision']);

        if ($caseId = $this->option('case')) {
            $query->where('ctc_id', (int) $caseId);
        }

        $cases = $query->orderBy('ctc_id')->get();

        if ($cases->isEmpty()) {
            $this->warn('No Draft / Under Revision contract cases matched.');

            return self::SUCCESS;
        }

        $force = (bool) $this->option('force');
        $synced = $skipped = $failed = 0;

        foreach ($cases as $case) {
            $hasForms = CaseForm::where('case_id', $case->ctc_id)->withTrashed()->exists();

            if ($hasForms && ! $force) {
                $skipped++;
                continue;
            }

            try {
                $result = $service->syncForms($case);
                $count = $result['synced'] ?? 0;
                $this->line("  CC-{$case->ctc_id}: {$count} form(s) — {$result['message']}");
                $synced++;
            } catch (Throwable $e) {
                $this->error("  CC-{$case->ctc_id}: {$e->getMessage()}");
                $failed++;
            }
        }

        $this->newLine();
        $this->info("Done. Cases synced: {$synced}, skipped (already had forms): {$skipped}, failed: {$failed}.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
