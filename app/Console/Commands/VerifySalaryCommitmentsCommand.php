<?php

namespace App\Console\Commands;

use App\Models\FinCommitment;
use App\Models\FinSalOrder;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class VerifySalaryCommitmentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'salary:verify-commitments 
                            {--dry-run : Run in dry-run mode (default, no DB changes)} 
                            {--fix : Actually insert missing commitments}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit and backfill missing fin.commitments for Approved salary orders';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isFix = (bool) $this->option('fix');
        $isDryRun = (bool) $this->option('dry-run') || !$isFix;

        $this->info("Scanning Approved salary orders for missing commitments...");
        if ($isDryRun) {
            $this->warn("MODE: Dry-Run (No database changes will be committed. Use --fix to backfill)");
        } else {
            $this->warn("MODE: Live Backfill (Missing commitments will be inserted into fin.commitments)");
        }

        $missing = DB::table('fin.salorders as s')
            ->leftJoin('fin.commitments as c', function ($join) {
                $join->on('s.sor_id', '=', 'c.cmt_docid')
                     ->where('c.cmt_type', '=', 'Sa');
            })
            ->where('s.sor_status', 'Approved')
            ->whereNull('c.cmt_id')
            ->select('s.*')
            ->orderBy('s.sor_id')
            ->get();

        $count = $missing->count();

        if ($count === 0) {
            $this->info("All Approved salary orders have corresponding commitments. No orphans detected.");
            return Command::SUCCESS;
        }

        $this->warn("Found {$count} Approved salary order(s) without commitment:");

        $tableRows = [];
        $fixedCount = 0;

        foreach ($missing as $sor) {
            $tableRows[] = [
                $sor->sor_id,
                $sor->sor_emp_id,
                $sor->sor_month,
                number_format($sor->sor_salary),
                $sor->sor_status,
                $sor->sor_releasedtg ?: 'N/A',
            ];

            if (!$isDryRun) {
                $releaseDate = $sor->sor_releasedtg
                    ? Carbon::parse($sor->sor_releasedtg)->toDateString()
                    : Carbon::now()->toDateString();

                FinCommitment::create([
                    'cmt_docid'     => $sor->sor_id,
                    'cmt_type'      => 'Sa',
                    'cmt_date'      => $releaseDate,
                    'cmt_amount'    => -1 * abs((float)$sor->sor_salary),
                    'cmt_status'    => 'Awaited',
                    'cmt_effhed_id' => $sor->sor_effhed_id,
                    'cmt_effunt_id' => $sor->sor_effunt_id,
                    'cmt_hed_id'    => $sor->sor_hed_id,
                    'cmt_unt_id'    => $sor->sor_unt_id,
                    'cmt_sudohed'   => $sor->sor_sudohed,
                    'cmt_remarks'   => 'Backfilled via salary:verify-commitments',
                ]);
                $fixedCount++;
            }
        }

        $this->table(['SOR ID', 'Emp ID', 'Month', 'Salary', 'Status', 'Released TG'], $tableRows);

        if (!$isDryRun) {
            $this->info("Successfully backfilled {$fixedCount} missing commitment(s).");
        } else {
            $this->info("Dry-run complete. Re-run with --fix to apply changes.");
        }

        return Command::SUCCESS;
    }
}
