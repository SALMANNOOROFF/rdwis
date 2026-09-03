<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Legacy-faithful pricing / tax engine for purchase cases.
 *
 * Ported from the Access application so old (Access-created) and new
 * (Laravel-created) cases carry identical figures:
 *
 *   Modules/Standard/PurchaseCase.bas       TaxRate(), GetTaxes()
 *   Queries/pur_purcase_taxes.sql           case-level SST / GST split
 *   Queries/pur_quote_taxes.sql             quote-level SST / GST split
 *   Queries/pur_quotesmin{,1}.sql           lowest tech-acceptable quote
 *   Queries/pur_quotes_recomm.sql           one recommended quote per case
 *   Queries/pur_purcase_updatetolowest.sql  copy quote prices onto case items
 *   Forms/Code/pur_purcases_detail.bas      CompareQuotesAndMarkLowest(),
 *                                           UpdatePricesAsPerLowestQuote(),
 *                                           UpdateFirmAndRecomm()
 *
 * The cascade is two-stage and the order matters:
 *
 *   pcs_intprice = Sum(pci_price * pci_qty)        base, tax free
 *   pcs_inttax   = SST 13% on services (pci_type = 3)
 *   pcs_midprice = pcs_intprice + pcs_inttax
 *   pcs_midtax   = GST 18% on everything else
 *   pcs_price    = pcs_midprice + pcs_midtax
 *
 * Quotes carry the same four fields (qte_*). With pcs_quotetype = 2 the firms
 * quoted tax-inclusive, so the quote's own taxes are copied onto the case; with
 * pcs_quotetype = 1 (the DB default) quotes are base-only and the case computes
 * the tax - and legacy leaves those two fields editable, so a caller may pass
 * explicit overrides which are then stored verbatim.
 */
class PurchasePricingService
{
    /** SST rate applied to services (pci_type = 3). */
    public const SST_RATE = 0.13;

    /** GST rate applied to every other item type. */
    public const GST_RATE = 0.18;

    /** Item type that legacy treats as a service. */
    public const TYPE_SERVICE = 3;

    /**
     * PurchaseCase.bas: TaxRate(ItemType) - Case 3: 0.13, Case Else: 0.18.
     */
    public static function taxRate(?int $itemType): float
    {
        return (int) $itemType === self::TYPE_SERVICE ? self::SST_RATE : self::GST_RATE;
    }

    /**
     * Queries/pur_purcase_taxes.sql - the case's own item lines.
     *
     * @return array{price: float, sst: float, gst: float}
     */
    public function caseTaxes(int $pcsId): array
    {
        $row = DB::table('pur.purcaseitems')
            ->where('pci_pcs_id', $pcsId)
            ->selectRaw('COALESCE(SUM(pci_price * COALESCE(pci_qty, 1)), 0) AS price')
            ->selectRaw('COALESCE(SUM(CASE WHEN pci_type = ? THEN pci_price * COALESCE(pci_qty, 1) * ? ELSE 0 END), 0) AS sst', [self::TYPE_SERVICE, self::SST_RATE])
            ->selectRaw('COALESCE(SUM(CASE WHEN pci_type IS DISTINCT FROM ? THEN pci_price * COALESCE(pci_qty, 1) * ? ELSE 0 END), 0) AS gst', [self::TYPE_SERVICE, self::GST_RATE])
            ->first();

        // GetTaxes() rounds both tax figures to whole rupees, the base price is not rounded.
        return [
            'price' => (float) ($row->price ?? 0),
            'sst'   => round((float) ($row->sst ?? 0)),
            'gst'   => round((float) ($row->gst ?? 0)),
        ];
    }

    /**
     * Queries/pur_quote_taxes.sql - a quote's own item lines, typed by the case item.
     *
     * @return array{price: float, sst: float, gst: float}
     */
    public function quoteTaxes(int $qteId): array
    {
        $row = DB::table('pur.quoteitems as qti')
            ->join('pur.purcaseitems as pci', 'pci.pci_id', '=', 'qti.qti_pci_id')
            ->where('qti.qti_qte_id', $qteId)
            ->selectRaw('COALESCE(SUM(qti.qti_price * COALESCE(qti.qti_qty, 1)), 0) AS price')
            ->selectRaw('COALESCE(SUM(CASE WHEN pci.pci_type = ? THEN qti.qti_price * COALESCE(qti.qti_qty, 1) * ? ELSE 0 END), 0) AS sst', [self::TYPE_SERVICE, self::SST_RATE])
            ->selectRaw('COALESCE(SUM(CASE WHEN pci.pci_type IS DISTINCT FROM ? THEN qti.qti_price * COALESCE(qti.qti_qty, 1) * ? ELSE 0 END), 0) AS gst', [self::TYPE_SERVICE, self::GST_RATE])
            ->first();

        return [
            'price' => (float) ($row->price ?? 0),
            'sst'   => round((float) ($row->sst ?? 0)),
            'gst'   => round((float) ($row->gst ?? 0)),
        ];
    }

    /**
     * Split a declared case-level tax into the SST / GST buckets.
     *
     * The Laravel case forms offer one tax type plus a percentage for the whole
     * case (legacy left the two fields free to type into when pcs_quotetype = 1),
     * so an explicit declaration puts everything in that one bucket.
     *
     * @param  array{type?: string, percent?: float|string}  $declared
     * @return array{0: float, 1: float}  [sst, gst]
     */
    public static function splitDeclaredTax(float $base, array $declared): array
    {
        $percent = (float) ($declared['percent'] ?? 0);
        $amount  = round($base * $percent / 100);

        return strtoupper(trim((string) ($declared['type'] ?? 'GST'))) === 'SST'
            ? [$amount, 0.0]
            : [0.0, $amount];
    }

    /**
     * Which tax a recalculation should store, in priority order:
     *
     *  1. an explicit declaration from the form;
     *  2. the rates the case already carries, rescaled to the new base - this is
     *     what keeps a 2019 case at its 17% GST and keeps a hand-typed rate from
     *     snapping back to 18% when an item is edited;
     *  3. the legacy default split by item type (SST 13% on services, GST 18%).
     *
     * @return array{0: float, 1: float}  [sst, gst]
     */
    public function resolveCaseTaxes(int $pcsId, float $newBase, ?array $declaredTax = null, $case = null): array
    {
        if ($declaredTax !== null) {
            return self::splitDeclaredTax($newBase, $declaredTax);
        }

        $case ??= DB::table('pur.purcases')->where('pcs_id', $pcsId)->first();
        $storedBase = (float) ($case->pcs_intprice ?? 0);
        $storedSst  = (float) ($case->pcs_inttax ?? 0);
        $storedGst  = (float) ($case->pcs_midtax ?? 0);

        if ($storedBase > 0 && ($storedSst + $storedGst) > 0) {
            return [
                round($newBase * $storedSst / $storedBase),
                round($newBase * $storedGst / $storedBase),
            ];
        }

        $computed = $this->caseTaxes($pcsId);

        return [$computed['sst'], $computed['gst']];
    }

    /**
     * Default pci_type for a new item. Real data has no pci_type = 1 at all:
     * Ps (store purchases) are goods, Pt and Rb (payments / repair bills) are
     * overwhelmingly services, and an SST declaration means services by definition.
     */
    public static function defaultItemType(?string $caseType, ?string $declaredTaxType = null): int
    {
        if (strtoupper(trim((string) $declaredTaxType)) === 'SST') {
            return self::TYPE_SERVICE;
        }

        $type = strtolower(trim((string) $caseType));

        $serviceTypes = [
            'pt', 'rb',                                     // legacy vocabulary
            'serv', 'services', 'cons', 'consultancy',       // Laravel case categories
            'trn', 'training', 'tran', 'transport', 'tada',
            'lic', 'license', 'net', 'internet', 'pub', 'publishing',
        ];

        return in_array($type, $serviceTypes, true) ? self::TYPE_SERVICE : 2;
    }

    /**
     * Recompute and store a case's four price fields from its own item lines.
     *
     * pur_purcases_detail.bas lines 112-122. $declaredTax carries the tax type and
     * percentage a form declared; pass null to keep the case's existing rates.
     *
     * @return array{base: float, sst: float, mid: float, gst: float, total: float}
     */
    public function recalcCaseFromItems(int $pcsId, ?array $declaredTax = null): array
    {
        $base = $this->caseTaxes($pcsId)['price'];
        [$sst, $gst] = $this->resolveCaseTaxes($pcsId, $base, $declaredTax);

        return $this->storeCasePricing($pcsId, $base, $sst, $gst);
    }

    /**
     * Apply the cascade and persist it. Single writer for the four pcs_* fields.
     *
     * @return array{base: float, sst: float, mid: float, gst: float, total: float}
     */
    public function storeCasePricing(int $pcsId, float $base, float $sst, float $gst): array
    {
        $base = round($base, 2);
        $mid   = round($base + $sst, 2);
        $total = round($mid + $gst, 2);

        DB::table('pur.purcases')->where('pcs_id', $pcsId)->update([
            'pcs_intprice' => $base,
            'pcs_inttax'   => $sst,
            'pcs_midprice' => $mid,
            'pcs_midtax'   => $gst,
            'pcs_price'    => $total,
        ]);

        return ['base' => $base, 'sst' => $sst, 'mid' => $mid, 'gst' => $gst, 'total' => $total];
    }

    /**
     * Quote-level equivalent (pur_quotes_detail.bas lines 37-95).
     *
     * With quotetype 1 the firm quoted base prices only, so both taxes stay 0 and
     * qte_price equals qte_intprice - the case, not the quote, carries the tax.
     * That identity is what makes the list column and the case page agree.
     *
     * @return array{base: float, sst: float, mid: float, gst: float, total: float}
     */
    public function recalcQuoteFromItems(int $qteId, ?int $quoteType = null, ?array $declaredTax = null): array
    {
        $quote = DB::table('pur.quotes')->where('qte_id', $qteId)->first();
        if (!$quote) {
            return ['base' => 0.0, 'sst' => 0.0, 'mid' => 0.0, 'gst' => 0.0, 'total' => 0.0];
        }

        $quoteType ??= (int) ($quote->qte_quotetype
            ?: DB::table('pur.purcases')->where('pcs_id', $quote->qte_pcs_id)->value('pcs_quotetype')
            ?: 1);

        $taxes = $this->quoteTaxes($qteId);
        $base  = round($taxes['price'], 2);
        $sst   = 0.0;
        $gst   = 0.0;

        if ((int) $quoteType === 2) {
            // The firm quoted tax-inclusive, so the tax belongs on the quote.
            [$sst, $gst] = $declaredTax !== null
                ? self::splitDeclaredTax($base, $declaredTax)
                : [$taxes['sst'], $taxes['gst']];
        }

        $mid   = round($base + $sst, 2);
        $total = round($mid + $gst, 2);

        DB::table('pur.quotes')->where('qte_id', $qteId)->update([
            'qte_intprice' => $base,
            'qte_inttax'   => $sst,
            'qte_midprice' => $mid,
            'qte_midtax'   => $gst,
            'qte_price'    => $total,
        ]);

        return ['base' => $base, 'sst' => $sst, 'mid' => $mid, 'gst' => $gst, 'total' => $total];
    }

    /**
     * Queries/pur_quotes_recomm.sql - exactly one recommended quote per case.
     *
     * $qteId null reproduces CompareQuotesAndMarkLowest(): the cheapest
     * technically acceptable offer wins (pur_quotesmin1.sql filters on
     * qte_techaccept). Returns the quote that ended up recommended, or null when
     * the case has no quotes at all.
     */
    public function markRecommended(int $pcsId, ?int $qteId = null): ?int
    {
        if ($qteId === null) {
            $qteId = DB::table('pur.quotes')
                ->where('qte_pcs_id', $pcsId)
                ->where('qte_techaccept', true)
                ->orderBy('qte_price')
                ->orderBy('qte_id')
                ->value('qte_id');

            // No acceptable offer: legacy exits and leaves the flags alone rather
            // than recommending a rejected firm.
            if (!$qteId) {
                return null;
            }
        }

        DB::table('pur.quotes')->where('qte_pcs_id', $pcsId)->update([
            'qte_recomm' => DB::raw('CASE WHEN qte_id = ' . (int) $qteId . ' THEN true ELSE false END'),
        ]);

        return (int) $qteId;
    }

    /**
     * pur_purcases_detail.bas: UpdatePricesAsPerLowestQuote().
     *
     * Copies the recommended quote's item prices onto the case items, rebuilds the
     * four pcs_* fields from them, then refreshes pcs_frm_id / pcs_recomm. Call
     * this after markRecommended(); with no recommended quote the case is zeroed
     * exactly like pur_purcase_updatetolowest_nothing.
     *
     * @return array{base: float, sst: float, mid: float, gst: float, total: float}
     */
    public function applyRecommendedQuote(int $pcsId, ?array $declaredTax = null): array
    {
        $case = DB::table('pur.purcases')->where('pcs_id', $pcsId)->first();
        if (!$case) {
            return ['base' => 0.0, 'sst' => 0.0, 'mid' => 0.0, 'gst' => 0.0, 'total' => 0.0];
        }

        $quote = DB::table('pur.quotes')
            ->where('qte_pcs_id', $pcsId)
            ->where('qte_recomm', true)
            ->first();

        if (!$quote) {
            DB::table('pur.purcaseitems')->where('pci_pcs_id', $pcsId)->update(['pci_price' => null]);
            DB::table('pur.purcases')->where('pcs_id', $pcsId)->update(['pcs_frm_id' => null, 'pcs_recomm' => '']);

            return $this->storeCasePricing($pcsId, 0, 0, 0);
        }

        DB::statement(
            'UPDATE pur.purcaseitems pci SET pci_price = qti.qti_price
               FROM pur.quoteitems qti
              WHERE qti.qti_pci_id = pci.pci_id
                AND qti.qti_qte_id = ?
                AND pci.pci_pcs_id = ?',
            [$quote->qte_id, $pcsId]
        );

        $itemTaxes = $this->caseTaxes($pcsId);

        // Legacy takes the base straight off the quote (pcs_intprice = qte_intprice).
        // Fall back to the item sum for quotes saved with a total but no base split.
        $base = (float) ($quote->qte_intprice ?: ($itemTaxes['price'] ?: (float) $quote->qte_price));

        if ((int) ($case->pcs_quotetype ?? 1) === 2) {
            // Firms quoted tax-inclusive: lift their taxes verbatim.
            $sst = (float) $quote->qte_inttax;
            $gst = (float) $quote->qte_midtax;
        } else {
            [$sst, $gst] = $this->resolveCaseTaxes($pcsId, $base, $declaredTax, $case);
        }

        $pricing = $this->storeCasePricing($pcsId, $base, $sst, $gst);
        $this->updateFirmAndRecomm($pcsId);

        return $pricing;
    }

    /**
     * pur_purcases_detail.bas: UpdateFirmAndRecomm().
     *
     * Picks the cheapest technically acceptable offer, stores it in pcs_frm_id and
     * writes the recommendation sentence legacy printed on the case.
     */
    public function updateFirmAndRecomm(int $pcsId): void
    {
        $quotes = DB::table('pur.quotes as q')
            ->leftJoin('frm.firmz as f', 'f.frm_id', '=', 'q.qte_frm_id')
            ->where('q.qte_pcs_id', $pcsId)
            ->orderBy('q.qte_id')
            ->select('q.qte_id', 'q.qte_price', 'q.qte_techaccept', 'q.qte_frm_id', 'q.qte_firmname', 'f.frm_name')
            ->get();

        if ($quotes->isEmpty()) {
            DB::table('pur.purcases')->where('pcs_id', $pcsId)->update(['pcs_recomm' => '']);

            return;
        }

        if ($quotes->contains(fn($q) => $q->qte_price === null)) {
            DB::table('pur.purcases')->where('pcs_id', $pcsId)->update(['pcs_recomm' => 'Cannot be calculated']);

            return;
        }

        $name = fn($q) => trim((string) ($q->frm_name ?: $q->qte_firmname ?: ('Firm #' . $q->qte_frm_id)));

        $acceptable   = $quotes->filter(fn($q) => (bool) $q->qte_techaccept)->values();
        $unacceptable = $quotes->reject(fn($q) => (bool) $q->qte_techaccept)->values();

        // No acceptable offer at all: fall back to the overall cheapest so the case
        // still names a firm, the way legacy's un-guarded first row did. The query is
        // ordered by qte_id and PHP's sort is stable, so ties keep entry order.
        $lowest = ($acceptable->isNotEmpty() ? $acceptable : $quotes)
            ->sortBy(fn($q) => (float) $q->qte_price)
            ->first();

        if ($acceptable->count() === 1) {
            $sentence = 'Offer of M/s ' . $name($acceptable->first())
                . ' is recommended as it is the only technically acceptable offer.';
        } else {
            $sentence = 'Offer of M/s ' . $name($lowest) . ' is recommended based on lowest cost.';
        }

        if ($unacceptable->isNotEmpty()) {
            $names = $unacceptable->map(fn($q) => 'M/s ' . $name($q))->implode(', ');
            $sentence = ($unacceptable->count() === 1
                ? 'Offer of ' . $names . ' is technically unacceptable.'
                : 'Offers of ' . $names . ' are technically unacceptable.')
                . "\r\n" . $sentence;
        }

        DB::table('pur.purcases')->where('pcs_id', $pcsId)->update([
            'pcs_frm_id'  => $lowest->qte_frm_id ?: null,
            'pcs_recomm'  => $sentence,
        ]);
    }

    /**
     * Taxable base split from the case items - the SST slice (services) and the GST
     * slice (everything else). Used only to label the stored tax with an effective
     * rate; the amounts themselves always come from the stored columns.
     *
     * @return array{service: float, goods: float}
     */
    public function caseTaxableBases(int $pcsId): array
    {
        $row = DB::table('pur.purcaseitems')
            ->where('pci_pcs_id', $pcsId)
            ->selectRaw('COALESCE(SUM(CASE WHEN pci_type = ? THEN pci_price * COALESCE(pci_qty, 1) ELSE 0 END), 0) AS svc', [self::TYPE_SERVICE])
            ->selectRaw('COALESCE(SUM(CASE WHEN pci_type IS DISTINCT FROM ? THEN pci_price * COALESCE(pci_qty, 1) ELSE 0 END), 0) AS gds', [self::TYPE_SERVICE])
            ->first();

        return ['service' => (float) ($row->svc ?? 0), 'goods' => (float) ($row->gds ?? 0)];
    }

    /**
     * Read-side breakdown for display, safe on every case ever created.
     *
     * Stored values win: GST moved 17% -> 18% and SST has been 10/13/15% over the
     * years, so recomputing an old case would show a figure the file never carried.
     * Only when a case has no split recorded at all do we derive one.
     *
     * @param  object|array  $case            anything exposing the pcs_* price fields
     * @param  array|null    $taxableBases    optional caseTaxableBases() result for the %
     * @return array{base: float, sst: float, mid: float, gst: float, total: float,
     *               tax: float, sst_pct: ?float, gst_pct: ?float, has_tax: bool,
     *               derived: bool, balanced: bool}
     */
    public static function breakdown($case, ?array $taxableBases = null): array
    {
        $get = static function ($key) use ($case) {
            $value = is_array($case) ? ($case[$key] ?? null) : ($case->{$key} ?? null);

            return (float) ($value ?? 0);
        };

        $base  = $get('pcs_intprice');
        $sst   = $get('pcs_inttax');
        $gst   = $get('pcs_midtax');
        $mid   = $get('pcs_midprice');
        $total = $get('pcs_price');

        $derived = false;

        // Old or half-entered rows: rebuild whatever is missing from what is there.
        if ($base <= 0 && $total > 0) {
            $base    = round($total - $sst - $gst, 2);
            $derived = true;
        }
        if ($mid <= 0) {
            $mid = round($base + $sst, 2);
        }
        if ($total <= 0) {
            $total = round($mid + $gst, 2);
        }

        $sstPct = null;
        $gstPct = null;
        if ($taxableBases) {
            if ($sst > 0 && ($taxableBases['service'] ?? 0) > 0) {
                $sstPct = round($sst / $taxableBases['service'] * 100, 2);
            }
            if ($gst > 0 && ($taxableBases['goods'] ?? 0) > 0) {
                $gstPct = round($gst / $taxableBases['goods'] * 100, 2);
            }
        }

        return [
            'base'     => $base,
            'sst'      => $sst,
            'mid'      => $mid,
            'gst'      => $gst,
            'total'    => $total,
            'tax'      => round($sst + $gst, 2),
            'sst_pct'  => $sstPct,
            'gst_pct'  => $gstPct,
            'has_tax'  => ($sst + $gst) > 0,
            'derived'  => $derived,
            // aud_chk_purcases_prices.sql flags pcs_price <> pcs_midprice + pcs_midtax
            'balanced' => abs($total - ($base + $sst + $gst)) < 1.0,
        ];
    }
}
