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
        Schema::create('cen.user_quick_remarks', function (Blueprint $table) {
            $table->bigIncrements('uqr_id');
            $table->unsignedBigInteger('uqr_acc_id')->index();
            $table->string('uqr_label', 150);
            $table->text('uqr_description');
            $table->integer('uqr_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cen.user_quick_remarks');
    }
};
