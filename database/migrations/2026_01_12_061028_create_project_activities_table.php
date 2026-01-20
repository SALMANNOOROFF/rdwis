<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('project_activities', function (Blueprint $table) {
        $table->id('pja_id');
        $table->unsignedBigInteger('pja_prj_id'); // Project ID
        $table->string('pja_action'); // e.g., 'File Upload', 'Milestone Update'
        $table->text('pja_details');  // e.g., 'Uploaded PPF', 'Changed date to 2026'
        $table->string('pja_user');   // Who did it?
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_activities');
    }
};
