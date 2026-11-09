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
        if (!Schema::hasTable('fin.sal_order_cases')) {
            Schema::create('fin.sal_order_cases', function (Blueprint $table) {
                $table->bigIncrements('soc_id');
                $table->string('soc_month', 10)->index(); // e.g. 2026-09
                $table->string('soc_minute_no', 50)->default('Minute 1');
                $table->string('soc_title', 255);
                $table->string('soc_encl_ref', 100)->default('Encl 40-A/F');
                $table->text('soc_para1_text')->nullable();
                $table->text('soc_para2_text')->nullable(); // Editable Held salaries / Remarks in draft
                $table->text('soc_para3_text')->nullable(); // Formal Approval Recommendation
                $table->string('soc_payment_channel', 150)->default('M/s MTSS');
                
                // Aggregated Metrics
                $table->integer('soc_total_due_emps')->default(0);
                $table->integer('soc_total_paid_emps')->default(0);
                $table->integer('soc_split_salaries_count')->default(0);
                $table->integer('soc_split_payments_count')->default(0);
                $table->integer('soc_total_payments')->default(0);
                $table->decimal('soc_total_amount', 18, 2)->default(0);
                
                // Workflow Stages: Finance, MD, DDG, DG, DLOG, Completed
                $table->string('soc_current_stage', 50)->default('Finance');
                $table->integer('soc_current_acc_id')->nullable()->index();
                $table->string('soc_current_holder', 255)->nullable();
                $table->string('soc_status', 50)->default('Draft'); // Draft, Under Review, Under Approval, Approved, Cancelled
                
                // Audit
                $table->integer('soc_created_by')->nullable()->index();
                $table->string('soc_created_name', 150)->nullable();
                $table->string('soc_created_desig', 150)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('fin.sal_order_case_decisions')) {
            Schema::create('fin.sal_order_case_decisions', function (Blueprint $table) {
                $table->bigIncrements('scd_id');
                $table->bigInteger('scd_soc_id')->index();
                $table->integer('scd_acc_id')->nullable()->index();
                $table->string('scd_user_name', 150)->nullable();
                $table->string('scd_user_rank', 100)->nullable();
                $table->string('scd_user_desig', 150)->nullable();
                $table->string('scd_role', 150)->nullable();
                $table->string('scd_action', 255); // Forwarded to ..., Approved, Returned to ..., Cancelled
                $table->string('scd_from_stage', 100)->nullable();
                $table->string('scd_to_stage', 100)->nullable();
                $table->text('scd_remarks')->nullable();
                $table->timestamp('scd_dtg')->useCurrent();
            });
        }

        if (Schema::hasTable('hr.sal_orders') && !Schema::hasColumn('hr.sal_orders', 'sor_soc_id')) {
            Schema::table('hr.sal_orders', function (Blueprint $table) {
                $table->bigInteger('sor_soc_id')->nullable()->index();
            });
        }

        if (Schema::hasTable('fin.salorders') && !Schema::hasColumn('fin.salorders', 'sor_soc_id')) {
            Schema::table('fin.salorders', function (Blueprint $table) {
                $table->bigInteger('sor_soc_id')->nullable()->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('fin.salorders') && Schema::hasColumn('fin.salorders', 'sor_soc_id')) {
            Schema::table('fin.salorders', function (Blueprint $table) {
                $table->dropColumn('sor_soc_id');
            });
        }
        if (Schema::hasTable('hr.sal_orders') && Schema::hasColumn('hr.sal_orders', 'sor_soc_id')) {
            Schema::table('hr.sal_orders', function (Blueprint $table) {
                $table->dropColumn('sor_soc_id');
            });
        }
        Schema::dropIfExists('fin.sal_order_case_decisions');
        Schema::dropIfExists('fin.sal_order_cases');
    }
};
