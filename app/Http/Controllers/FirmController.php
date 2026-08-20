<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Firm;

class FirmController extends Controller
{
    /**
     * Display the Firms Directory and Advanced Search page.
     */
    public function index(Request $request)
    {
        $entities = DB::table('frm.firmz')
            ->where('frm_id', '>', 0)
            ->where('frm_name', 'not like', '%< Select%')
            ->where('frm_name', 'not like', '%<Select%')
            ->whereNotNull('frm_entity')
            ->where('frm_entity', '!=', '')
            ->where('frm_entity', '!=', 'special')
            ->distinct()
            ->pluck('frm_entity');

        $types = DB::table('frm.firmz')
            ->where('frm_id', '>', 0)
            ->where('frm_name', 'not like', '%< Select%')
            ->where('frm_name', 'not like', '%<Select%')
            ->whereNotNull('frm_type')
            ->where('frm_type', '!=', '')
            ->where('frm_type', '!=', 'special')
            ->distinct()
            ->pluck('frm_type');

        $specialities = DB::table('frm.specs')
            ->distinct()
            ->orderBy('spc_spec')
            ->pluck('spc_spec');

        $facilities = DB::table('frm.facils')
            ->distinct()
            ->orderBy('fcl_facil')
            ->pluck('fcl_facil');

        $cities = DB::table('frm.offices')
            ->whereNotNull('off_city')
            ->where('off_city', '!=', '')
            ->distinct()
            ->orderBy('off_city')
            ->pluck('off_city');

        return view('nrdi.firms.index', compact('entities', 'types', 'specialities', 'facilities', 'cities'));
    }

    /**
     * Display comprehensive list of all registered firms with performance metrics.
     */
    public function list(Request $request)
    {
        $entities = DB::table('frm.firmz')
            ->where('frm_id', '>', 0)
            ->where('frm_name', 'not like', '%< Select%')
            ->where('frm_name', 'not like', '%<Select%')
            ->whereNotNull('frm_entity')
            ->where('frm_entity', '!=', '')
            ->where('frm_entity', '!=', 'special')
            ->distinct()
            ->pluck('frm_entity');

        $types = DB::table('frm.firmz')
            ->where('frm_id', '>', 0)
            ->where('frm_name', 'not like', '%< Select%')
            ->where('frm_name', 'not like', '%<Select%')
            ->whereNotNull('frm_type')
            ->where('frm_type', '!=', '')
            ->where('frm_type', '!=', 'special')
            ->distinct()
            ->pluck('frm_type');

        $firms = DB::table('frm.firmz as f')
            ->where('f.frm_id', '>', 0)
            ->where('f.frm_name', 'not like', '%< Select%')
            ->where('f.frm_name', 'not like', '%<Select%')
            ->orderBy('f.frm_name', 'asc')
            ->get();

        $enrichedFirms = $firms->map(function($f) {
            $officesCount = DB::table('frm.offices')->where('off_xfrm_id', $f->frm_id)->count();
            $mainCity = DB::table('frm.offices')->where('off_xfrm_id', $f->frm_id)->value('off_city') ?: 'N/A';
            $personsCount = DB::table('frm.persons')->where('per_xfrm_id', $f->frm_id)->count();
            $casesCount = DB::table('pur.purcases')->where('pcs_frm_id', $f->frm_id)->count();
            $approvedCasesCount = DB::table('pur.purcases')
                ->where('pcs_frm_id', $f->frm_id)
                ->where('pcs_status', 'Approved')
                ->count();
            $totalAwarded = DB::table('pur.purcases')
                ->where('pcs_frm_id', $f->frm_id)
                ->where('pcs_status', 'Approved')
                ->sum('pcs_price');
            $quotesCount = DB::table('pur.quotes')->where('qte_frm_id', $f->frm_id)->count();

            return (object)[
                'frm_id'               => $f->frm_id,
                'frm_name'             => $f->frm_name,
                'frm_entity'           => $f->frm_entity ?: 'N/A',
                'frm_type'             => $f->frm_type ?: 'General Supplier',
                'frm_ntn'              => $f->frm_ntn ?: 'N/A',
                'frm_gst'              => $f->frm_gst ?: 'N/A',
                'frm_black'            => (bool)$f->frm_black,
                'frm_notes'            => $f->frm_notes ?: '',
                'offices_count'        => $officesCount,
                'main_city'            => $mainCity,
                'persons_count'        => $personsCount,
                'cases_count'          => $casesCount,
                'approved_cases_count' => $approvedCasesCount,
                'total_awarded'        => (float)$totalAwarded,
                'quotes_count'         => $quotesCount
            ];
        });

        $totalFirms = $enrichedFirms->count();
        $activeFirms = $enrichedFirms->where('frm_black', false)->count();
        $blacklistedFirms = $enrichedFirms->where('frm_black', true)->count();
        $awardedFirmsCount = $enrichedFirms->where('approved_cases_count', '>', 0)->count();

        return view('nrdi.firms.list', compact('enrichedFirms', 'entities', 'types', 'totalFirms', 'activeFirms', 'blacklistedFirms', 'awardedFirmsCount'));
    }

    /**
     * Handle AJAX search for firms with basic and advanced search criteria.
     */
    public function searchData(Request $request)
    {
        try {
            $query = DB::table('frm.firmz as f')
                ->where('f.frm_id', '>', 0)
                ->where('f.frm_name', 'not like', '%< Select%')
                ->where('f.frm_name', 'not like', '%<Select%');

            $anyPart = $request->boolean('any_part', true);
            $mainSearch = trim((string)$request->input('main_search', ''));
            $statusFilter = $request->input('status_filter', 'All');

            // Left Panel Filter (All, Active, Blacklisted, or Entity/Type)
            if ($statusFilter === 'Active') {
                $query->where(function($q) {
                    $q->whereNull('f.frm_black')->orWhere('f.frm_black', false);
                });
            } elseif ($statusFilter === 'Blacklisted') {
                $query->where('f.frm_black', true);
            } elseif ($statusFilter !== 'All' && !empty($statusFilter)) {
                $query->where(function($q) use ($statusFilter) {
                    $q->where('f.frm_entity', $statusFilter)
                      ->orWhere('f.frm_type', $statusFilter);
                });
            }

            // Right Panel Main Search
            if (!empty($mainSearch)) {
                $comp = $anyPart ? 'ILIKE' : '=';
                $term = $anyPart ? "%{$mainSearch}%" : $mainSearch;

                $query->where(function($q) use ($comp, $term) {
                    $q->where('f.frm_name', $comp, $term)
                      ->orWhere('f.frm_ntn', $comp, $term)
                      ->orWhere('f.frm_gst', $comp, $term)
                      ->orWhere('f.frm_type', $comp, $term)
                      ->orWhere('f.frm_entity', $comp, $term)
                      ->orWhere('f.frm_notes', $comp, $term);
                });
            }

            // Advanced Search Fields
            // 1. General Data
            $genField = $request->input('gen_field', 'Name');
            $genVal = trim((string)$request->input('gen_val', ''));
            if (!empty($genVal)) {
                $comp = $anyPart ? 'ILIKE' : '=';
                $term = $anyPart ? "%{$genVal}%" : $genVal;
                
                if ($genField === 'NTN') $query->where('f.frm_ntn', $comp, $term);
                elseif ($genField === 'GST') $query->where('f.frm_gst', $comp, $term);
                elseif ($genField === 'Entity') $query->where('f.frm_entity', $comp, $term);
                elseif ($genField === 'Type') $query->where('f.frm_type', $comp, $term);
                else $query->where('f.frm_name', $comp, $term);
            }

            // 2. Office
            $offField = $request->input('off_field', 'Name');
            $offVal = trim((string)$request->input('off_val', ''));
            if (!empty($offVal)) {
                $comp = $anyPart ? 'ILIKE' : '=';
                $term = $anyPart ? "%{$offVal}%" : $offVal;

                $query->whereExists(function ($sub) use ($offField, $comp, $term) {
                    $sub->select(DB::raw(1))
                        ->from('frm.offices as o')
                        ->whereColumn('o.off_xfrm_id', 'f.frm_id');
                    
                    if ($offField === 'City') $sub->where('o.off_city', $comp, $term);
                    elseif ($offField === 'Address') $sub->where('o.off_address', $comp, $term);
                    elseif ($offField === 'Type') $sub->where('o.off_type', $comp, $term);
                    else $sub->where('o.off_name', $comp, $term);
                });
            }

            // 3. Contacts
            $contactVal = trim((string)$request->input('contact_val', ''));
            if (!empty($contactVal)) {
                $comp = $anyPart ? 'ILIKE' : '=';
                $term = $anyPart ? "%{$contactVal}%" : $contactVal;

                $query->whereExists(function ($sub) use ($comp, $term) {
                    $sub->select(DB::raw(1))
                        ->from('frm.info as inf')
                        ->whereColumn('inf.inf_xmsc_id', 'f.frm_id')
                        ->where('inf.inf_value', $comp, $term);
                });
            }

            // 4. Speciality
            $specVal = trim((string)$request->input('spec_val', ''));
            if (!empty($specVal)) {
                $comp = $anyPart ? 'ILIKE' : '=';
                $term = $anyPart ? "%{$specVal}%" : $specVal;

                $query->whereExists(function ($sub) use ($comp, $term) {
                    $sub->select(DB::raw(1))
                        ->from('frm.specs as spc')
                        ->whereColumn('spc.spc_xfrm_id', 'f.frm_id')
                        ->where('spc.spc_spec', $comp, $term);
                });
            }

            // 5. Person
            $perField = $request->input('per_field', 'Name');
            $perVal = trim((string)$request->input('per_val', ''));
            if (!empty($perVal)) {
                $comp = $anyPart ? 'ILIKE' : '=';
                $term = $anyPart ? "%{$perVal}%" : $perVal;

                $query->whereExists(function ($sub) use ($perField, $comp, $term) {
                    $sub->select(DB::raw(1))
                        ->from('frm.persons as per')
                        ->whereColumn('per.per_xfrm_id', 'f.frm_id');

                    if ($perField === 'Designation') $sub->where('per.per_desig', $comp, $term);
                    elseif ($perField === 'Department') $sub->where('per.per_dept', $comp, $term);
                    elseif ($perField === 'Expertise') $sub->where('per.per_exprt', $comp, $term);
                    elseif ($perField === 'Title') $sub->where('per.per_title', $comp, $term);
                    else $sub->where('per.per_name', $comp, $term);
                });
            }

            // 6. Facility
            $facilVal = trim((string)$request->input('facil_val', ''));
            if (!empty($facilVal)) {
                $comp = $anyPart ? 'ILIKE' : '=';
                $term = $anyPart ? "%{$facilVal}%" : $facilVal;

                $query->whereExists(function ($sub) use ($comp, $term) {
                    $sub->select(DB::raw(1))
                        ->from('frm.facils as fcl')
                        ->whereColumn('fcl.fcl_xfrm_id', 'f.frm_id')
                        ->where('fcl.fcl_facil', $comp, $term);
                });
            }

            // 7. Project
            $prjField = $request->input('prj_field', 'Name');
            $prjVal = trim((string)$request->input('prj_val', ''));
            if (!empty($prjVal)) {
                $comp = $anyPart ? 'ILIKE' : '=';
                $term = $anyPart ? "%{$prjVal}%" : $prjVal;

                $query->whereExists(function ($sub) use ($prjField, $comp, $term) {
                    $sub->select(DB::raw(1))
                        ->from('frm.projects as prj')
                        ->whereColumn('prj.prj_xfrm_id', 'f.frm_id');

                    if ($prjField === 'Scope') $sub->where('prj.prj_scope', $comp, $term);
                    elseif ($prjField === 'Tech') $sub->where('prj.prj_tech', $comp, $term);
                    elseif ($prjField === 'Status') $sub->where('prj.prj_status', $comp, $term);
                    else $sub->where('prj.prj_name', $comp, $term);
                });
            }

            $firms = $query->orderBy('f.frm_name', 'asc')
                ->limit(200)
                ->get();

            // Enrich firms with associated counts
            $enrichedFirms = $firms->map(function($f) {
                $officesCount = DB::table('frm.offices')->where('off_xfrm_id', $f->frm_id)->count();
                $personsCount = DB::table('frm.persons')->where('per_xfrm_id', $f->frm_id)->count();
                $casesCount = DB::table('pur.purcases')->where('pcs_frm_id', $f->frm_id)->count();
                $totalAwarded = DB::table('pur.purcases')
                    ->where('pcs_frm_id', $f->frm_id)
                    ->where('pcs_status', 'Approved')
                    ->sum('pcs_price');

                return [
                    'frm_id'        => $f->frm_id,
                    'frm_name'      => $f->frm_name,
                    'frm_entity'    => $f->frm_entity ?: 'N/A',
                    'frm_type'      => $f->frm_type ?: 'General Supplier',
                    'frm_ntn'       => $f->frm_ntn ?: 'N/A',
                    'frm_gst'       => $f->frm_gst ?: 'N/A',
                    'frm_black'     => (bool)$f->frm_black,
                    'frm_notes'     => $f->frm_notes ?: '',
                    'offices_count' => $officesCount,
                    'persons_count' => $personsCount,
                    'cases_count'   => $casesCount,
                    'total_awarded' => (float)$totalAwarded
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $enrichedFirms,
                'count' => count($enrichedFirms)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show full profile / dossier of a single firm.
     */
    public function show($id)
    {
        $firm = DB::table('frm.firmz')->where('frm_id', $id)->firstOrFail();

        $offices = DB::table('frm.offices')->where('off_xfrm_id', $id)->get();
        $persons = DB::table('frm.persons')->where('per_xfrm_id', $id)->get();
        $contacts = DB::table('frm.info')->where('inf_xmsc_id', $id)->get();
        $specialities = DB::table('frm.specs')->where('spc_xfrm_id', $id)->get();
        $facilities = DB::table('frm.facils')->where('fcl_xfrm_id', $id)->get();
        $projects = DB::table('frm.projects')->where('prj_xfrm_id', $id)->get();
        
        $cases = DB::table('pur.purcases as pc')
            ->leftJoin('cen.units as u', 'pc.pcs_unt_id', '=', 'u.unt_id')
            ->where('pc.pcs_frm_id', $id)
            ->select('pc.*', 'u.unt_namesh')
            ->orderBy('pc.pcs_id', 'desc')
            ->get();

        return response()->json([
            'success'      => true,
            'firm'         => $firm,
            'offices'      => $offices,
            'persons'      => $persons,
            'contacts'     => $contacts,
            'specialities' => $specialities,
            'facilities'   => $facilities,
            'projects'     => $projects,
            'cases'        => $cases
        ]);
    }
}
