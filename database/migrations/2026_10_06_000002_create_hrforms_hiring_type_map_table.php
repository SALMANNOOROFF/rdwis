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
        Schema::create('hrforms.hiring_type_map', function (Blueprint $table) {
            $table->id();
            $table->string('ctc_type', 50)->unique();
            $table->string('hiring_type', 50)->nullable();
            $table->string('note', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed approved rows based on DB reality + codebase definitions
        DB::table('hrforms.hiring_type_map')->insert([
            ['ctc_type' => 'Cr', 'hiring_type' => 'Renewal',   'note' => 'Contract Renewal (show.blade.php:353)',                                'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['ctc_type' => 'Hg', 'hiring_type' => 'Fresh',     'note' => 'Fresh Hiring (show.blade.php:351)',                                    'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['ctc_type' => 'HG', 'hiring_type' => 'Fresh',     'note' => 'Fresh Hiring uppercase (show.blade.php:484)',                          'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['ctc_type' => 'Ce', 'hiring_type' => 'Extension', 'note' => 'Contract Extension (show.blade.php:352, ContractCaseController:292)', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['ctc_type' => 'CR', 'hiring_type' => 'Renewal',   'note' => 'Contract Renewal uppercase (FulfillmentService:58)',                  'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['ctc_type' => 'Rh', 'hiring_type' => 'Rehiring',  'note' => 'Defined in RDWIS show.blade.php:354, 0 cases currently',              'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hrforms.hiring_type_map');
    }
};
