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
        'page_setup',
    ];

    protected $casts = [
        'paragraphs' => 'array',
        'page_setup' => 'array',
    ];

    public static function defaultPageSetup(): array
    {
        return [
            'page_size'           => 'A4',
            'orientation'         => 'portrait',
            'margin_top'          => '18',
            'margin_bottom'       => '18',
            'margin_left'         => '20',
            'margin_right'        => '18',
            'header_space'        => '10',
            'footer_space'        => '10',
            'row_spacing'         => '5pt',
            'col_spacing'         => '6px',

            'header_show'         => false,
            'header_left_text'    => "Tele: 021-9924000\nFax: 021-9924001",
            'header_center_text'  => "GOVERNMENT OF PAKISTAN - MINISTRY OF DEFENCE\nR&D WING NRDI AT PNS JAUHAR",
            'header_right_text'   => "Habib Ibrahim Rahimtoola Road\nKarachi-75350",

            'footer_show'         => false,
            'footer_left_text'    => "CONFIDENTIAL / FOR OFFICIAL USE ONLY",
            'footer_right_text'   => "Page 1 of 1",

            'header_org_name'     => 'Naval Research & Development Institute',
            'header_wing'         => 'R&D Wing',
            'header_base'         => 'at PNS JAUHAR',
            'header_address'      => 'Habib Rehmatullah Road',
            'header_city'         => 'KARACHI',
            'header_phone'        => 'Ph (off): 48504781',
            'see_distribution'    => 'See distribution:',
            'ref_prefix'          => 'R&D/Projects/Proc/',
            'annex_label'         => 'ANNEX A',
            'dated_label'         => 'Dated :',
            'annex_title'         => 'LIST OF REQUIRED ITEMS',
            'th_sno'              => 'S No',
            'th_spec'             => 'Item / specification',
            'th_qty'              => 'Qty',

            'editable_ref_no'     => true,
            'editable_date'       => true,
            'editable_subject'    => true,
            'editable_paragraphs' => true,
            'editable_signatory'  => true,
            'editable_firms'      => true,
            'editable_items'      => true,
        ];
    }

    public function getMergedPageSetup(): array
    {
        $defaults = static::defaultPageSetup();
        $saved = $this->page_setup ?? [];

        return array_merge($defaults, is_array($saved) ? $saved : []);
    }

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
                'page_setup'       => static::defaultPageSetup(),
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
