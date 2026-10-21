<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('fin.fund_shifts')) {
            Schema::create('fin.fund_shifts', function (Blueprint $table) {
                $table->bigIncrements('fsh_id');
                $table->bigInteger('fsh_rev_id')->nullable()->index(); // 1:1 linked to aud.revs.rev_id
                $table->integer('fsh_prj_id')->index();
                $table->integer('fsh_hed_id')->index();
                $table->integer('fsh_unt_id')->nullable()->index();
                
                // Subhead details
                $table->integer('fsh_from_sbh_id')->index();
                $table->string('fsh_from_sbh_name', 100);
                $table->integer('fsh_to_sbh_id')->index();
                $table->string('fsh_to_sbh_name', 100);
                
                // Amounts
                $table->decimal('fsh_proposed_amount', 18, 2)->default(0);
                $table->decimal('fsh_approved_amount', 18, 2)->nullable();
                $table->decimal('fsh_from_alloc_before', 18, 2)->default(0);
                $table->decimal('fsh_from_alloc_after', 18, 2)->default(0);
                $table->decimal('fsh_to_alloc_before', 18, 2)->default(0);
                $table->decimal('fsh_to_alloc_after', 18, 2)->default(0);

                // Workflow & Stage
                $table->string('fsh_current_stage', 50)->default('Division'); // Division, Finance, MD, DDG, DG, Completed, Cancelled
                $table->string('fsh_status', 50)->default('Draft'); // Draft, Under Review, Under Approval, Approved, Implemented, Cancelled, Returned
                $table->text('fsh_purpose')->nullable();
                
                // Creator audit
                $table->integer('fsh_created_by')->nullable();
                $table->string('fsh_created_name', 150)->nullable();
                $table->string('fsh_created_rank', 50)->nullable();
                $table->string('fsh_created_desig', 100)->nullable();
                
                // Implementation audit
                $table->integer('fsh_implemented_by')->nullable();
                $table->timestamp('fsh_implemented_at')->nullable();
                $table->text('fsh_implementation_notes')->nullable();

                $table->timestamps();
            });
        }

        if (!Schema::hasTable('fin.fund_shift_decisions')) {
            Schema::create('fin.fund_shift_decisions', function (Blueprint $table) {
                $table->bigIncrements('fsd_id');
                $table->bigInteger('fsd_fsh_id')->index();
                $table->integer('fsd_acc_id')->nullable();
                $table->string('fsd_user_name', 150)->nullable();
                $table->string('fsd_user_rank', 50)->nullable();
                $table->string('fsd_user_desig', 100)->nullable();
                $table->string('fsd_role', 50)->nullable();
                $table->string('fsd_action', 50); // Draft Created, Forwarded, Approved, Returned, Implemented, Cancelled
                $table->string('fsd_from_stage', 50)->nullable();
                $table->string('fsd_to_stage', 50)->nullable();
                $table->text('fsd_remarks')->nullable();
                $table->timestamp('fsd_dtg')->useCurrent();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (app()->environment('testing')) {
            Schema::dropIfExists('fin.fund_shift_decisions');
            Schema::dropIfExists('fin.fund_shifts');
        }
    }
};
