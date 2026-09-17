<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurItTemplate extends Model
{
    protected $table = 'pur.pur_it_template';

    protected $fillable = [
        'subject',
        'paragraphs',
        'signatory_name',
        'signatory_rank',
        'signatory_dept',
        'see_distribution',
    ];

    protected $casts = [
        'paragraphs' => 'array',
    ];

    /**
     * Get the single global template row, or create it with defaults if missing.
     */
    public static function getTemplate(): self
    {
        $tpl = static::first();

        if (!$tpl) {
            $tpl = static::create([
                'subject'          => 'REQUEST FOR QUOTATION',
                'paragraphs'       => [
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
                ],
                'signatory_name'   => 'MUHAMMAD MUDASSIR',
                'signatory_rank'   => 'Cdr (R) Pakistan Navy',
                'signatory_dept'   => 'Dir Procurement',
                'see_distribution' => 'See distribution',
            ]);
        }

        return $tpl;
    }

    /**
     * Apply per-case replacements to template paragraphs.
     * Replaces {ITEM_TITLE} and {DEADLINE_DATE} with actual values.
     */
    public function applyReplacements(string $itemTitle, string $deadlineDate): array
    {
        $paragraphs = $this->paragraphs ?? [];

        return array_map(function ($p) use ($itemTitle, $deadlineDate) {
            $p = str_replace('{ITEM_TITLE}', $itemTitle, $p);
            $p = str_replace('{DEADLINE_DATE}', $deadlineDate, $p);
            return $p;
        }, $paragraphs);
    }
}
