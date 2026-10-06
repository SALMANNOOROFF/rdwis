<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hrforms.form_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_form_id')
                ->constrained('hrforms.case_forms')
                ->cascadeOnDelete();
            $table->integer('version')->default(1);
            $table->string('file_path', 500);
            $table->string('file_name', 255);
            $table->string('checksum', 64)->nullable();
            $table->bigInteger('byte_size')->nullable();
            $table->boolean('is_final')->default(false);
            $table->unsignedBigInteger('generated_by')->nullable();
            $table->timestamp('generated_at')->useCurrent();
            $table->timestamps();

            $table->unique(['case_form_id', 'version'], 'uq_form_files_case_form_version');
            $table->index('case_form_id');
            $table->index('is_final');
        });

        // Partial unique index: at most one final PDF per form
        DB::statement('CREATE UNIQUE INDEX uq_form_files_final_per_form ON hrforms.form_files (case_form_id) WHERE is_final = TRUE;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS hrforms.uq_form_files_final_per_form;');
        Schema::dropIfExists('hrforms.form_files');
    }
};
