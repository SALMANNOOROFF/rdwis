<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pur.pur_it_template', function (Blueprint $table) {
            $table->id();
            $table->string('subject', 255)->default('REQUEST FOR QUOTATION');
            $table->jsonb('paragraphs')->nullable();
            $table->string('signatory_name', 255)->default('MUHAMMAD MUDASSIR');
            $table->string('signatory_rank', 255)->default('Cdr (R) Pakistan Navy');
            $table->string('signatory_dept', 255)->default('Dir Procurement');
            $table->string('see_distribution', 255)->default('See distribution');
            $table->timestamps();
        });

        // Seed the default template row
        DB::table('pur.pur_it_template')->insert([
            'subject'          => 'REQUEST FOR QUOTATION',
            'paragraphs'       => json_encode([
                "1.\tR&D Wing NRDI at PNS JAUHAR is interested for the Procurement of {ITEM_TITLE}. In this regard, quotations are required to be submitted to MD R&D at NRDI by {DEADLINE_DATE}.",
                "2.\tQuotation will be opened on same day at 11:00 hrs in the presence of the participants or their representatives and will be accepted at lowest quotations rate basis. However, it is apprised that MD (R&D) reserves the right to accept/ reject any quotation without assigning any reason.",
                "a.\tThe envelope and the quote must bear the reference of tender number.",
                "b.\tThe validity period be clearly mentioned in quote. Atleast 30 days for locally available items and incase of imported items validity be either as per OEM or 60 days whichever falls early.",
                "c.\tQuote must be in conformance to the specifications given in the tender. Non-conforming or incomplete quotes will not be considered.",
                "d.\tItems available locally are to be delivered within 15 days after issuance of purchase order.",
                "e.\tPart Delivery / Partial payment or request for any advance payment shall not be entertained.",
                "f.\tWarrantee / Guarantee of one year is required.",
                "g.\tPayment will be processed / made after delivery and acceptance by user.",
                "3.\tIn case of any query; kindly contact well within time on dir-pandi@paknavy.gov.pk. Furthermore, it is requested to acknowledge receipt of tender/e-mail.",
            ]),
            'signatory_name'   => 'MUHAMMAD MUDASSIR',
            'signatory_rank'   => 'Cdr (R) Pakistan Navy',
            'signatory_dept'   => 'Dir Procurement',
            'see_distribution' => 'See distribution',
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('pur.pur_it_template');
    }
};
