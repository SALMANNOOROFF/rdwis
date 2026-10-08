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
        Schema::create('cen.user_case_draft_remarks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('acc_id')->index();
            $table->string('case_type', 50); // 'purchase' or 'contract'
            $table->unsignedBigInteger('case_id');
            $table->text('draft_remarks')->nullable();
            $table->timestamps();

            $table->unique(['acc_id', 'case_type', 'case_id'], 'user_case_draft_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cen.user_case_draft_remarks');
    }
};
