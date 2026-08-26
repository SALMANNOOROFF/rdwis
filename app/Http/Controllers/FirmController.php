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

        $specialities = DB::table('frm.specs')
            ->whereNotNull('spc_spec')
            ->where('spc_spec', '!=', '')
            ->distinct()
            ->orderBy('spc_spec')
            ->pluck('spc_spec');

        $defaultFacilities = [
            'UAVs',
            'Augmented Reality',
            'Software',
            'General Order Supplier',
            'Importer & Distributer Service',
            'Manufacturing',
            'Provision of Transport Facility',
            'Defence supplier',
            'Engineers, Contractors, Suppliers',
            'Reconditioning of Mechanical',
            'PCBs & Electronics',
            'Electronics',
            'Engineering Solution Provider',
            'Deals in Furniture, Fixture Electric',
            'Communication Systems',
            'Embedded Systems',
            'Hardware & Networking',
            'Scientific & Lab Equipment',
            'Civil Works & Construction',
            'Security & Surveillance Systems'
        ];

        $dbFacils = DB::table('frm.facils')
            ->whereNotNull('fcl_facil')
            ->where('fcl_facil', '!=', '')
            ->distinct()
            ->pluck('fcl_facil')
            ->toArray();

        $facilities = collect(array_unique(array_merge($defaultFacilities, $dbFacils)))->sort()->values();

        $contactTypes = ['Mobile', 'Landline', 'Fax', 'Email', 'Website'];

        $totalFirms = $enrichedFirms->count();
        $activeFirms = $enrichedFirms->where('frm_black', false)->count();
        $blacklistedFirms = $enrichedFirms->where('frm_black', true)->count();
        $awardedFirmsCount = $enrichedFirms->where('approved_cases_count', '>', 0)->count();

        return view('nrdi.firms.list', compact(
            'enrichedFirms',
            'entities',
            'types',
            'specialities',
            'facilities',
            'contactTypes',
            'totalFirms',
            'activeFirms',
            'blacklistedFirms',
            'awardedFirmsCount'
        ));
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

    /**
     * Store a newly created firm with General, Offices, Persons, and Projects.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $area = strtolower(trim((string) ($user->acc_untarea ?? '')));
        $isAuthorized = in_array($area, ['fin', 'proc', 'prc', 'rdw', 'nrdi']) 
            || session('impersonated_by_god') 
            || strtolower($user->acc_username ?? '') === 'superadminrdw';

        if (!$isAuthorized) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Only Finance and Procurement Directorate can register new firms.'
            ], 403);
        }

        $request->validate([
            'frm_name' => 'required|string|max:255',
            'frm_type' => 'nullable|string|max:100',
            'frm_ntn'  => 'nullable|string|max:100',
            'frm_gst'  => 'nullable|string|max:100',
        ]);

        return DB::transaction(function () use ($request) {
            $blacklisted = $request->input('frm_black') === 'true' || $request->input('frm_black') === '1' || $request->input('frm_black') === true;

            // 1. Insert into frm.firmz
            $newFrmId = DB::table('frm.firmz')->insertGetId([
                'frm_name'   => trim($request->frm_name),
                'frm_entity' => $request->frm_entity ?: 'Company',
                'frm_type'   => $request->frm_type ?: 'Private company',
                'frm_group'  => $request->frm_group ?: null,
                'frm_emp'    => is_numeric($request->frm_emp) ? (int)$request->frm_emp : null,
                'frm_points' => is_numeric($request->frm_points) ? (int)$request->frm_points : 5,
                'frm_black'  => $blacklisted,
                'frm_notes'  => $request->frm_notes ?: null,
                'frm_ntn'    => $request->frm_ntn ?: null,
                'frm_gst'    => $request->frm_gst ?: null,
            ], 'frm_id');

            // 2. Specialities (frm.specs)
            if ($request->has('specialities') && is_array($request->specialities)) {
                foreach ($request->specialities as $spec) {
                    $specTrim = trim((string)$spec);
                    if (!empty($specTrim)) {
                        DB::table('frm.specs')->insert([
                            'spc_xfrm_id' => $newFrmId,
                            'spc_spec'    => $specTrim,
                        ]);
                    }
                }
            }

            // 3. Facilities (frm.facils)
            if ($request->has('facilities') && is_array($request->facilities)) {
                foreach ($request->facilities as $facil) {
                    $facilTrim = trim((string)$facil);
                    if (!empty($facilTrim)) {
                        DB::table('frm.facils')->insert([
                            'fcl_xfrm_id' => $newFrmId,
                            'fcl_facil'   => $facilTrim,
                        ]);
                    }
                }
            }

            // 4. General Contacts (frm.info)
            if ($request->has('contacts') && is_array($request->contacts)) {
                foreach ($request->contacts as $c) {
                    if (!empty($c['type']) && !empty($c['value'])) {
                        DB::table('frm.info')->insert([
                            'inf_xmsc_id'     => $newFrmId,
                            'inf_xmsc_entity' => 'Firm',
                            'inf_type'        => trim($c['type']),
                            'inf_value'       => trim($c['value']),
                        ]);
                    }
                }
            }

            // 5. Offices (frm.offices)
            if ($request->has('offices') && is_array($request->offices)) {
                foreach ($request->offices as $off) {
                    if (!empty($off['off_city']) || !empty($off['off_address']) || !empty($off['off_type'])) {
                        $newOffId = DB::table('frm.offices')->insertGetId([
                            'off_entity'  => 'Office',
                            'off_xfrm_id' => $newFrmId,
                            'off_name'    => $off['off_name'] ?? ($off['off_type'] ?? 'Office'),
                            'off_type'    => $off['off_type'] ?? 'Head Office',
                            'off_address' => $off['off_address'] ?? null,
                            'off_city'    => $off['off_city'] ?? null,
                        ], 'off_id');

                        if (isset($off['contacts']) && is_array($off['contacts'])) {
                            foreach ($off['contacts'] as $oc) {
                                if (!empty($oc['type']) && !empty($oc['value'])) {
                                    DB::table('frm.info')->insert([
                                        'inf_xmsc_id'     => $newOffId,
                                        'inf_xmsc_entity' => 'Office',
                                        'inf_type'        => trim($oc['type']),
                                        'inf_value'       => trim($oc['value']),
                                    ]);
                                }
                            }
                        }
                    }
                }
            }

            // 6. Persons (frm.persons)
            if ($request->has('persons') && is_array($request->persons)) {
                foreach ($request->persons as $per) {
                    if (!empty($per['per_name'])) {
                        $newPerId = DB::table('frm.persons')->insertGetId([
                            'per_entity'  => 'Person',
                            'per_xfrm_id' => $newFrmId,
                            'per_title'   => $per['per_title'] ?? 'Mr.',
                            'per_name'    => trim($per['per_name']),
                            'per_desig'   => $per['per_desig'] ?? null,
                            'per_dept'    => $per['per_dept'] ?? null,
                            'per_exprt'   => $per['per_exprt'] ?? null,
                        ], 'per_id');

                        if (isset($per['contacts']) && is_array($per['contacts'])) {
                            foreach ($per['contacts'] as $pc) {
                                if (!empty($pc['type']) && !empty($pc['value'])) {
                                    DB::table('frm.info')->insert([
                                        'inf_xmsc_id'     => $newPerId,
                                        'inf_xmsc_entity' => 'Person',
                                        'inf_type'        => trim($pc['type']),
                                        'inf_value'       => trim($pc['value']),
                                    ]);
                                }
                            }
                        }
                    }
                }
            }

            // 7. Projects (frm.projects)
            if ($request->has('projects') && is_array($request->projects)) {
                foreach ($request->projects as $prj) {
                    if (!empty($prj['prj_name'])) {
                        DB::table('frm.projects')->insert([
                            'prj_xfrm_id' => $newFrmId,
                            'prj_name'    => trim($prj['prj_name']),
                            'prj_scope'   => $prj['prj_scope'] ?? null,
                            'prj_awarddt' => !empty($prj['prj_awarddt']) ? $prj['prj_awarddt'] : null,
                            'prj_status'  => $prj['prj_status'] ?? 'Completed',
                            'prj_compdt'  => !empty($prj['prj_compdt']) ? $prj['prj_compdt'] : null,
                            'prj_tech'    => $prj['prj_tech'] ?? null,
                            'prj_cost'    => is_numeric($prj['prj_cost'] ?? null) ? (int)$prj['prj_cost'] : null,
                        ]);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Firm "' . $request->frm_name . '" registered successfully!',
                'firm_id' => $newFrmId,
                'firm' => [
                    'frm_id'               => $newFrmId,
                    'frm_name'             => trim($request->frm_name),
                    'frm_entity'           => $request->frm_entity ?: 'Company',
                    'frm_type'             => $request->frm_type ?: 'Private company',
                    'frm_ntn'              => $request->frm_ntn ?: 'N/A',
                    'frm_gst'              => $request->frm_gst ?: 'N/A',
                    'frm_black'            => $blacklisted,
                    'frm_notes'            => $request->frm_notes ?: '',
                    'offices_count'        => isset($request->offices) ? count($request->offices) : 0,
                    'main_city'            => isset($request->offices[0]['off_city']) ? $request->offices[0]['off_city'] : 'N/A',
                    'persons_count'        => isset($request->persons) ? count($request->persons) : 0,
                    'cases_count'          => 0,
                    'approved_cases_count' => 0,
                    'total_awarded'        => 0,
                    'quotes_count'         => 0
                ]
            ]);
        });
    }
}
