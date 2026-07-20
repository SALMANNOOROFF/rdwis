<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillCommitmentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'purchase:backfill-commitments {--dry-run : Print summary without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill missing fund commitments for approved purchase cases';

    /**
     * Map RDWIS purchase type code to legacy cmt_type code
     */
    private function mapToLegacyType(string $pcsType): string
    {
        return match(strtolower(trim($pcsType))) {
            'mat', 'civ', 'tran', 'book', 'lic', 'net', 'pub', 'stat' => 'Ps',
            'cons', 'serv' => 'Rb',
            'tada', 'trn'  => 'Pt',
            default => 'Ps',
        };
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        $missingCases = DB::table('pur.purcases')
            ->where('pcs_status', 'Approved')
            ->whereNotIn('pcs_id', function ($query) {
                $query->select('cmt_docid')
                    ->from('fin.commitments')
                    ->whereIn('cmt_type', ['Ps', 'Pt', 'Rb']);
            })
            ->orderBy('pcs_id', 'asc')
            ->get();

        $count = $missingCases->count();

        if ($count === 0) {
            $this->info("No uncommitted approved purchase cases found.");
            return 0;
        }

        $totalAmount = 0;
        $rowsToInsert = [];

        foreach ($missingCases as $case) {
            $amt = -1 * ($case->pcs_transtype == 1 ? ($case->pcs_midprice ?? 0) : ($case->pcs_price ?? 0));
            $totalAmount += abs($amt);

            $rowsToInsert[] = [
                'cmt_docid'     => $case->pcs_id,
                'cmt_type'      => $this->mapToLegacyType($case->pcs_type ?? 'mat'),
                'cmt_date'      => $case->pcs_date ?? now()->toDateString(),
                'cmt_amount'    => $amt,
                'cmt_status'    => 'Awaited',
                'cmt_effhed_id' => $case->pcs_effhed_id,
                'cmt_effunt_id' => $case->pcs_effunt_id,
                'cmt_hed_id'    => $case->pcs_hed_id,
                'cmt_unt_id'    => $case->pcs_unt_id,
                'cmt_sudohed'   => $case->pcs_sudohed,
            ];
        }

        $this->line("==========================================");
        $this->info("Missing Commitments Report");
        $this->line("==========================================");
        $this->line("Total Missing Approved Cases : {$count}");
        $this->line("Total Commitment Amount      : PKR " . number_format($totalAmount, 2));
        $this->line("First Case ID                : " . $missingCases->first()->pcs_id);
        $this->line("Last Case ID                 : " . $missingCases->last()->pcs_id);
        $this->line("==========================================");

        if ($isDryRun) {
            $this->warn("[DRY RUN] No records were inserted into fin.commitments.");
            return 0;
        }

        DB::transaction(function () use ($rowsToInsert) {
            foreach (array_chunk($rowsToInsert, 100) as $chunk) {
                DB::table('fin.commitments')->insert($chunk);
            }
        });

        $this->info("Successfully backfilled {$count} commitments into fin.commitments!");
        return 0;
    }
}
