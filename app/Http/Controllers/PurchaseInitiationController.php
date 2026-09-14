<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseInitiationController extends Controller
{
    /**
     * Display the Division Initiation Dashboard
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $userArea = strtolower(trim((string) ($user->acc_untarea ?? '')));
        $isHqOrProc = in_array($userArea, ['rdw', 'hqs', 'nrdi', 'rdwprj', 'prjrdw', 'fin', 'proc', 'prc'], true);

        if ($isHqOrProc) {
            $lower = 0;
            $upper = 99999999;
        } else {
            [$lower, $upper] = $user->acc_lowers == 0
                ? [$user->acc_lowerm, $user->acc_upperm]
                : [$user->acc_lowers, $user->acc_uppers];
        }

        $unitId = $user->acc_unt_id;

        // Fetch all cases initiated by this unit/division with rich context
        $purchases = Purchase::with(['project', 'latestDecision.account', 'items', 'quotes.firm', 'decisions', 'currentSubstatus'])
            ->where(function($q) use ($user, $lower, $upper) {
                if ($user->acc_unt_id) {
                    $q->where('pcs_unt_id', $user->acc_unt_id);
                }
                $q->orWhereBetween('pcs_unt_id', [$lower, $upper]);
            })
            ->orderBy('pcs_id', 'desc')
            ->get();

        $actionReqCases = $purchases->filter(function($p) use ($user, $unitId) {
            $status = strtolower(trim($p->pcs_status));
            if (in_array($status, ['fulfilled', 'completed', 'cancelled', 'rejected', 'not approved', 'approved'])) {
                return false;
            }

            $latest = $p->latestDecision;

            // 1. Initial Draft:
            if ($status === 'draft') {
                $hasFloatedOrForwarded = $p->decisions->contains(fn($d) => in_array($d->pdec_action, ['float_to_proc', 'reshare_to_proc', 'forward', 'forward_negative']));
                if ($hasFloatedOrForwarded) {
                    return false; // Floated / forwarded into pipeline
                }
                return true; // Unfloated initial draft
            }

            // 2. Active cases (Returned or Under Approval):
            $currentStage = $p->currentSubstatus?->pss_stage;
            $isWithDivision = ($currentStage === 'Division');

            $isLatestByMyUnit = ($latest && (int)($latest->account?->acc_unt_id ?? 0) === (int)$unitId);
            $isLatestByMe = ($latest && (int)$latest->pdec_acc_id === (int)$user->acc_id);
            $isDivisionAction = ($isLatestByMyUnit || $isLatestByMe) && in_array($latest->pdec_action, ['forward', 'forward_negative', 'float_to_proc', 'reshare_to_proc']);

            if ($isDivisionAction) {
                return false; // Division forwarded it out -> in Open pipeline
            }

            if ($isWithDivision || $status === 'returned') {
                return true; // In Division's hands -> Action Required!
            }

            return false;
        });

        $initiatedCases = $purchases->filter(function($p) use ($actionReqCases) {
            $status = strtolower(trim($p->pcs_status));
            if (in_array($status, ['fulfilled', 'partially fulfilled', 'completed', 'cancelled', 'rejected', 'not approved'])) return false;
            return !$actionReqCases->contains('pcs_id', $p->pcs_id);
        });

        $completedCases = $purchases->filter(function($p) {
            $status = strtolower(trim($p->pcs_status));
            return in_array($status, ['fulfilled', 'partially fulfilled', 'completed', 'cancelled', 'rejected', 'not approved']);
        });

        $pageTitle = "PC Initiation Hub";
        $detailsRouteName = 'purchase.initiation.show'; // New dedicated route

        // Financial Intelligence Summary
        $finService = app(\App\Services\FinancialIntelligenceService::class);
        $head = DB::table('cen.heads')->where('hed_unt_id', $unitId)->orWhereBetween('hed_unt_id', [$lower, $upper])->first();
        $finSummary = null;
        if ($head) {
            $s = $finService->getHeadStatus($head->hed_id);
            $finSummary = [
                'received' => $s->received,
                'expenditure' => $s->expenditure,
                'commitments' => $s->commitments,
                'in_process' => $s->in_process,
                'balance' => $s->balance,
                'available' => $s->available
            ];
        }

        return view('purchase.initiation.index', compact(
            'purchases', 'pageTitle', 'detailsRouteName', 'unitId',
            'initiatedCases', 'actionReqCases', 'completedCases', 'finSummary'
        ));
    }

    /**
     * Show the detailed view for Division Initiation (Editable if Draft)
     */
    public function show($id)
    {
        $user = Auth::user();
        $isDProc = str_contains(strtolower(trim($user->acc_untarea)), 'proc') || str_contains(strtolower(trim($user->acc_untarea)), 'prc');
        
        $query = Purchase::with(['items', 'quotes.firm', 'noQuotes', 'project', 'attachments', 'decisions.account']);
        
        $userArea = strtolower(trim((string) ($user->acc_untarea ?? '')));
        $isHqOrProc = in_array($userArea, ['rdw', 'hqs', 'nrdi', 'rdwprj', 'prjrdw', 'fin', 'proc', 'prc'], true);

        if ($isHqOrProc) {
            $lower = 0;
            $upper = 99999999;
        } else {
            [$lower, $upper] = $user->acc_lowers == 0
                ? [$user->acc_lowerm, $user->acc_upperm]
                : [$user->acc_lowers, $user->acc_uppers];
        }

        $query->where(function($q) use ($user, $lower, $upper) {
            $q->where('pcs_unt_id', $user->acc_unt_id)
              ->orWhereBetween('pcs_unt_id', [$lower, $upper]);
        });


        $purchase = $query->findOrFail($id);


        $service = app(\App\Services\PurchaseApprovalService::class);
        $currentAuthority = $service->getStatusDisplayName($purchase->pcs_status);
        $nextAuthority = $service->getNextAuthorityName($purchase, 'prj'); // prj is Division

        // Financial Intelligence (Legacy Logic)
        $finService = app(\App\Services\FinancialIntelligenceService::class);
        $fin = $finService->getHeadStatus($purchase->pcs_hed_id);
        $subheads = $finService->getSubheadBreakdown($purchase->pcs_hed_id);
        $head = $fin;


        // Recent Approved Cases for the same project/head
        $recentApproved = Purchase::withCount('items')
            ->where('pcs_hed_id', $purchase->pcs_hed_id)
            ->where(function($q) {
                $q->whereRaw('LOWER(pcs_status) = ?', ['approved'])
                  ->orWhere('pcs_status', 'Approved');
            })
            ->where('pcs_id', '!=', $purchase->pcs_id)
            ->orderBy('pcs_date', 'desc')
            ->limit(10)
            ->get();

        // Fallback: If no approved cases exist for this specific project head, show recent approved cases of any project
        if ($recentApproved->isEmpty()) {
            $recentApproved = Purchase::withCount('items')
                ->where(function($q) {
                    $q->whereRaw('LOWER(pcs_status) = ?', ['approved'])
                      ->orWhere('pcs_status', 'Approved');
                })
                ->where('pcs_id', '!=', $purchase->pcs_id)
                ->orderBy('pcs_date', 'desc')
                ->limit(10)
                ->get();
        }

        $firms = \App\Models\Firm::orderBy('frm_name')->get();
        $canEdit = in_array(strtolower($purchase->pcs_status), ['draft', 'returned']);
        $pageTitle = "Initiation Details: " . $purchase->pcs_title;
        $area = 'prj';

        $employees = collect();
        if ($purchase->pcs_type === 'Rb') {
            $userArea = strtolower(trim((string) ($user->acc_untarea ?? '')));
            $isHqOrProc = in_array($userArea, ['rdw', 'hqs', 'nrdi', 'rdwprj', 'prjrdw', 'fin', 'proc', 'prc'], true);

            if ($isHqOrProc || empty($user->acc_unt_id)) {
                $lower = 0;
                $upper = 99999999;
            } else {
                $lower = (int) ($user->acc_lowers == 0 ? $user->acc_lowerm : $user->acc_lowers);
                $upper = (int) ($user->acc_lowers == 0 ? $user->acc_upperm : $user->acc_uppers);
            }

            $employees = DB::table('hr.emps')
                ->where(function($q) use ($user, $lower, $upper, $isHqOrProc) {
                    if (!$isHqOrProc && $user->acc_unt_id) {
                        $q->where('emp_unt_id', $user->acc_unt_id);
                        if ($lower > 0 && $upper > 0) {
                            $q->orWhereBetween('emp_unt_id', [$lower, $upper]);
                        }
                    }
                })
                ->where('emp_status', 'ILIKE', 'Active%')
                ->whereExists(function($q) {
                    $q->select(DB::raw(1))
                      ->from('hr.contracts')
                      ->whereColumn('hr.contracts.ctr_num', 'hr.emps.emp_id');
                })
                ->select('emp_id', 'emp_name', 'emp_rank', 'emp_title', 'emp_status', 'emp_unt_id')
                ->orderBy('emp_name')
                ->get();
        }

        $projectSubheads = DB::table('fin.subheads')->where('sbh_hed_id', $purchase->pcs_hed_id)->pluck('sbh_name')->all();

        return view('nrdi.purchase_cases_new.show', compact('purchase', 'head', 'firms', 'pageTitle', 'canEdit', 'currentAuthority', 'nextAuthority', 'area', 'subheads', 'projectSubheads', 'recentApproved', 'employees'));
    }

    /**
     * Pull back a case from HQ to Division (Hold/Revert)
     */
    public function holdCase($id)
    {
        $purchase = Purchase::with('currentSubstatus')->findOrFail($id);
        $this->authorize('update', $purchase);
        
        // Security check
        if ($purchase->pcs_unt_id != Auth::user()->acc_unt_id) {
            return back()->with('error', 'Unauthorized access.');
        }

        // Fix #3: Gate on substatus stage, not pcs_status.
        // Only allow hold if case is at DFinance (first post-Division stage)
        $currentStage = $purchase->currentSubstatus?->pss_stage;
        if ($currentStage !== 'DFinance') {
            return back()->with('error', 'Case cannot be held — it has already been processed beyond Finance.');
        }

        return DB::transaction(function () use ($purchase, $currentStage) {
            $purchase->pcs_status = 'Draft';
            $purchase->save();

            // Transition substatus back to Division
            \App\Models\PurCaseSubstatus::where('pss_pcs_id', $purchase->pcs_id)
                ->where('pss_is_current', true)
                ->update(['pss_is_current' => false, 'pss_until' => now()]);

            \App\Models\PurCaseSubstatus::create([
                'pss_pcs_id'    => $purchase->pcs_id,
                'pss_stage'     => 'Division',
                'pss_is_current'=> true,
                'pss_since'     => now(),
            ]);

            // Record the hold action in the decision trail
            DB::table('pur.purdecisions')->insert([
                'pdec_pcs_id' => $purchase->pcs_id,
                'pdec_role' => 'Initiator',
                'pdec_acc_id' => Auth::id(),
                'pdec_action' => 'hold',
                'pdec_remarks' => 'Case held by Division for internal review/corrections.',
                'pdec_from_status' => $currentStage,
                'pdec_to_status' => 'Division',
                'created_at' => now()
            ]);

            return back()->with('success', 'Case has been pulled back to Draft and is now editable.');
        });
    }

    public function save(Request $request, $id)
    {
        $op = (string) $request->input('op', '');
        $rules = [
            'op' => 'required|in:save_title,save_remarks,add_files,delete_file,add_item,edit_item,delete_item,add_quote,delete_quote,upload_quote_file,add_noquote,delete_noquote',
        ];

        if ($op === 'save_title') {
            $rules['pcs_title'] = 'required|string|max:500';
        } elseif ($op === 'save_remarks') {
            $rules['pcs_remarks'] = 'nullable|string';
        } elseif ($op === 'add_files') {
            $rules['attachments'] = 'required|array';
            $rules['attachments.*'] = 'file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240';
        } elseif ($op === 'delete_file') {
            $rules['pat_id'] = 'required|integer';
        } elseif ($op === 'add_item') {
            $rules['item_desc'] = 'required|string|max:2000';
            $rules['item_qty'] = 'required|numeric|min:0.0001';
        } elseif ($op === 'edit_item') {
            $rules['pci_id'] = 'required|integer';
            $rules['item_desc'] = 'required|string|max:2000';
            $rules['item_qty'] = 'required|numeric|min:0.0001';
        } elseif ($op === 'delete_item') {
            $rules['pci_id'] = 'required|integer';
        } elseif ($op === 'add_quote') {
            $rules['firm_name'] = 'required|string|max:255';
            $rules['item_prices'] = 'array';
            $rules['item_prices.*'] = 'numeric|min:0';
            $rules['quote_file'] = 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:15360';
        } elseif ($op === 'delete_quote') {
            $rules['qte_id'] = 'required|integer';
        } elseif ($op === 'upload_quote_file') {
            $rules['qte_id'] = 'required|integer';
            $rules['quote_file'] = 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:15360';
        } elseif ($op === 'add_noquote') {
            $rules['nqt_frm_id'] = 'required|integer';
        } elseif ($op === 'delete_noquote') {
            $rules['nqt_id'] = 'required|integer';
        }

        $request->validate($rules);

        $user = Auth::user();
        $isDProc = str_contains(strtolower(trim($user->acc_untarea ?? '')), 'proc') || str_contains(strtolower(trim($user->acc_untarea ?? '')), 'prc');

        if ($op === 'add_files' || $op === 'delete_file') {
            // Any authenticated user can upload or remove case attachments across all stages
            $purchase = Purchase::findOrFail($id);
            $this->authorize('update', $purchase);
        } else {
            $query = Purchase::query();
            if ($isDProc) {
                $lower = $user->acc_lowerm;
                $upper = $user->acc_upperm;
                $query->whereBetween('pcs_unt_id', [$lower, $upper]);
            } else {
                $unitId = $user->acc_unt_id;
                [$lower, $upper] = $user->acc_lowers == 0
                    ? [$user->acc_lowerm, $user->acc_upperm]
                    : [$user->acc_lowers, $user->acc_uppers];

                $query->where(function($q) use ($unitId, $lower, $upper) {
                    $q->where('pcs_unt_id', $unitId)
                      ->orWhereBetween('pcs_unt_id', [$lower, $upper]);
                });
            }
            $purchase = $query->findOrFail($id);
            $this->authorize('update', $purchase);
        }

        return DB::transaction(function () use ($request, $purchase, $op) {
            if ($op === 'save_title') {
                $purchase->pcs_title = trim((string) $request->input('pcs_title'));
                $purchase->save();
                return ['ok' => true, 'message' => 'Title updated.', 'pcsId' => (int) $purchase->pcs_id];
            }

            if ($op === 'save_remarks') {
                $purchase->pcs_remarks = (string) $request->input('pcs_remarks', '');
                $purchase->save();
                return ['ok' => true, 'message' => 'Remarks updated.', 'pcsId' => (int) $purchase->pcs_id];
            }

            if ($op === 'add_files') {
                $files = $request->file('attachments', []);
                $count = 0;
                $storage = app(\App\Services\FileStorageService::class);
                foreach ($files as $file) {
                    if (!$file || !$file->isValid()) {
                        continue;
                    }
                    $storedPath = $storage->store($file, 'pur', 'pcs-', (string) $purchase->pcs_id);

                    DB::table('pur.purattachments')->insert([
                        'pat_objtype' => 'pcs',
                        'pat_objid' => $purchase->pcs_id,
                        'pat_type' => 'Supporting Document',
                        'pat_path' => $storedPath,
                    ]);
                    $count++;
                }
                return ['ok' => true, 'message' => "{$count} file(s) uploaded.", 'pcsId' => (int) $purchase->pcs_id];
            }

            if ($op === 'delete_file') {
                $patId = (int) $request->input('pat_id');
                $att = DB::table('pur.purattachments')
                    ->where('pat_objtype', 'pcs')
                    ->where('pat_objid', $purchase->pcs_id)
                    ->where('pat_id', $patId)
                    ->first();
                if ($att) {
                    if (!empty($att->pat_path)) {
                        app(\App\Services\FileStorageService::class)->delete($att->pat_path);
                    }
                    DB::table('pur.purattachments')->where('pat_id', $patId)->delete();
                }
                return ['ok' => true, 'message' => 'Attachment deleted.', 'pcsId' => (int) $purchase->pcs_id];
            }

            if ($op === 'add_item') {
                $maxSerial = (int) (DB::table('pur.purcaseitems')->where('pci_pcs_id', $purchase->pcs_id)->max('pci_serial') ?? 0);
                $nextSerial = $maxSerial + 1;
                $desc = trim((string) $request->input('item_desc'));
                $qty = (float) $request->input('item_qty');
                $unit = trim((string) $request->input('item_qtyunit', $request->input('item_unit', 'num'))) ?: 'num';
                $empId = trim((string) $request->input('emp_id', ''));
                $itemPrice = (float) $request->input('item_price', 0);

                if ($purchase->pcs_type === 'Rb' && !empty($empId)) {
                    try {
                        $pricingService = app(\App\Services\PurchasePricingService::class);
                        $tadaData = $pricingService->getEmployeeTadaDetails($empId);
                        $itemPrice = $tadaData['tada_amount'];
                        $desc = $tadaData['description'];
                        $qty = 1;
                        $unit = 'num';
                    } catch (\Throwable $e) {
                        return ['ok' => false, 'message' => $e->getMessage(), 'pcsId' => (int) $purchase->pcs_id];
                    }
                }

                // Subhead resolution
                $caseSubhead = DB::table('pur.purcases_shd')
                    ->where('pcd_pcs_id', $purchase->pcs_id)
                    ->value('pcd_subhead');

                if (empty($caseSubhead)) {
                    $caseSubhead = ($purchase->pcs_type === 'Ps') ? 'Equipment' : 'Misc';
                    DB::table('pur.purcases_shd')->updateOrInsert(
                        ['pcd_pcs_id' => $purchase->pcs_id, 'pcd_subhead' => $caseSubhead],
                        ['pcd_type' => $purchase->pcs_type ?: 'Ps', 'pcd_ratio' => 1.0]
                    );
                }

                $itemSubhead = trim((string)$request->input('item_subhead', '')) ?: $caseSubhead;
                $itemType = (int)($request->input('item_type') ?? ($purchase->pcs_type === 'Rb' ? 3 : 7));
                $itemSubtype = trim((string)($request->input('item_subtype') ?? ($purchase->pcs_type === 'Rb' ? 'Travelling/Boarding/Lodging' : ($itemSubhead === 'Equipment' ? 'Parts' : $itemSubhead))));
                $itemType2 = null;
                if ($itemType != 3) {
                    $itemType2 = (int)($request->input('item_type2') ?? ($itemType == 2 ? 5 : 6));
                }

                $pciId = DB::table('pur.purcaseitems')->insertGetId([
                    'pci_pcs_id' => $purchase->pcs_id,
                    'pci_emp_id' => !empty($empId) ? $empId : null,
                    'pci_serial' => $nextSerial,
                    'pci_desc' => $desc,
                    'pci_qty' => $qty ?: 1,
                    'pci_qtyunit' => $unit,
                    'pci_price' => $itemPrice,
                    'pci_type' => $itemType,
                    'pci_subtype' => $itemSubtype ?: ($itemSubhead === 'Equipment' ? 'Parts' : 'Misc'),
                    'pci_type2' => $itemType2,
                    'pci_subhead' => $itemSubhead,
                ], 'pci_id');

                $quotes = DB::table('pur.quotes')->where('qte_pcs_id', $purchase->pcs_id)->get(['qte_id', 'qte_num']);
                foreach ($quotes as $q) {
                    DB::table('pur.quoteitems')->insert([
                        'qti_qte_id' => $q->qte_id,
                        'qti_pci_id' => $pciId,
                        'qti_price' => 0,
                        'qti_qty' => $qty,
                        'qti_serial' => $nextSerial,
                        'qti_desc' => $desc,
                        'qti_qtyunit' => $unit,
                        'qti_pcsdesc' => $desc,
                    ]);
                }

                $this->recalcCasePricing($purchase->pcs_id);
                return ['ok' => true, 'message' => 'Item added.', 'pcsId' => (int) $purchase->pcs_id];
            }

            if ($op === 'edit_item') {
                $pciId = (int) $request->input('pci_id');
                $desc = trim((string) $request->input('item_desc'));
                $qty = (float) $request->input('item_qty');
                $unit = trim((string) $request->input('item_qtyunit', $request->input('item_unit', 'num'))) ?: 'num';

                $item = DB::table('pur.purcaseitems')
                    ->where('pci_pcs_id', $purchase->pcs_id)
                    ->where('pci_id', $pciId)
                    ->first();

                if (!$item) {
                    return ['ok' => false, 'message' => 'Item not found.', 'pcsId' => (int) $purchase->pcs_id];
                }

                $updateData = [
                    'pci_desc' => $desc,
                    'pci_qty' => $qty,
                    'pci_qtyunit' => $unit
                ];

                if ($request->has('item_type')) {
                    $updateData['pci_type'] = (int) $request->input('item_type');
                }
                if ($request->has('item_subtype')) {
                    $updateData['pci_subtype'] = trim((string) $request->input('item_subtype'));
                }
                if ($request->has('item_type2')) {
                    $updateData['pci_type2'] = (int) $request->input('item_type2');
                }
                if ($request->has('item_subhead')) {
                    $updateData['pci_subhead'] = trim((string) $request->input('item_subhead'));
                }

                DB::table('pur.purcaseitems')
                    ->where('pci_pcs_id', $purchase->pcs_id)
                    ->where('pci_id', $pciId)
                    ->update($updateData);

                DB::table('pur.quoteitems')
                    ->where('qti_pci_id', $pciId)
                    ->update([
                        'qti_desc' => $desc,
                        'qti_qty' => $qty,
                        'qti_qtyunit' => $unit,
                        'qti_pcsdesc' => $desc
                    ]);

                $this->recalcCasePricing($purchase->pcs_id);
                return ['ok' => true, 'message' => 'Item updated successfully.', 'pcsId' => (int) $purchase->pcs_id];
            }

            if ($op === 'add_noquote') {
                $frmId = (int) $request->input('nqt_frm_id');
                if ($frmId > 0) {
                    DB::table('pur.noquotes')->updateOrInsert(
                        ['nqt_pcs_id' => $purchase->pcs_id, 'nqt_frm_id' => $frmId]
                    );
                }
                return ['ok' => true, 'message' => 'Firm recorded under Quotes Not Received.', 'pcsId' => (int) $purchase->pcs_id];
            }

            if ($op === 'delete_noquote') {
                $nqtId = (int) $request->input('nqt_id');
                DB::table('pur.noquotes')->where('nqt_id', $nqtId)->where('nqt_pcs_id', $purchase->pcs_id)->delete();
                return ['ok' => true, 'message' => 'Record removed from Quotes Not Received.', 'pcsId' => (int) $purchase->pcs_id];
            }

            if ($op === 'delete_item') {
                $pciId = (int) $request->input('pci_id');
                $item = DB::table('pur.purcaseitems')->where('pci_pcs_id', $purchase->pcs_id)->where('pci_id', $pciId)->first();
                if (!$item) {
                    return ['ok' => false, 'message' => 'Item not found.', 'pcsId' => (int) $purchase->pcs_id];
                }
                DB::table('pur.quoteitems')->where('qti_pci_id', $pciId)->delete();
                DB::table('pur.purcaseitems')->where('pci_pcs_id', $purchase->pcs_id)->where('pci_id', $pciId)->delete();
                $this->recalcCasePricing($purchase->pcs_id);
                return ['ok' => true, 'message' => 'Item deleted.', 'pcsId' => (int) $purchase->pcs_id];
            }

            if ($op === 'add_quote') {
                $qteId = $request->input('qte_id');
                $firmId = $request->input('qte_frm_id'); 
                $firmName = trim((string) $request->input('firm_name'));
                $qteNum = $request->input('qte_num');
                if ($qteNum === null || $qteNum === '') {
                    if ($qteId) {
                        $qteNum = DB::table('pur.quotes')->where('qte_id', $qteId)->value('qte_num');
                    }
                    if ($qteNum === null || $qteNum === '') {
                        $qteNum = (int) (DB::table('pur.quotes')->where('qte_pcs_id', $purchase->pcs_id)->max('qte_num') ?? 0) + 1;
                    }
                }
                $qteDate = $request->input('qte_date') ?: $purchase->pcs_date;
                
                // Tax parameters
                $taxType = strtoupper(trim((string) $request->input('tax_type', 'GST')));
                $taxPercent = (float) $request->input('tax_percent', 18);

                // Nested items from the modal OR flat array
                $inputItems = (array) $request->input('items', []);
                $flatItemPrices = (array) $request->input('item_prices', []);
                
                // Fetch case items to ensure consistency
                $caseItems = DB::table('pur.purcaseitems')->where('pci_pcs_id', $purchase->pcs_id)->get();
                
                $subtotal = 0.0;
                foreach ($caseItems as $it) {
                    $price = 0.0;
                    if (isset($inputItems[$it->pci_id]['price'])) {
                        $price = (float) $inputItems[$it->pci_id]['price'];
                    } elseif (isset($flatItemPrices[$it->pci_id])) {
                        $price = (float) $flatItemPrices[$it->pci_id];
                    }
                    $subtotal += ($price * (float)$it->pci_qty);
                }

                $taxAmount = $subtotal * ($taxPercent / 100);
                $total = $subtotal + $taxAmount;

                // Identify Firm
                if (!$firmId && $firmName) {
                    $firm = DB::table('frm.firmz')->where('frm_name', $firmName)->first();
                    $firmId = $firm ? $firm->frm_id : null;
                } elseif ($firmId) {
                    $firm = DB::table('frm.firmz')->where('frm_id', $firmId)->first();
                    $firmName = $firm ? $firm->frm_name : $firmName;
                }

                $isSst = $taxType === 'SST';
                $sstAmount = $isSst ? $taxAmount : 0.0;
                $gstAmount = !$isSst ? $taxAmount : 0.0;
                $midPrice = $subtotal + $sstAmount;

                if ($qteId) {
                    // Update existing quote
                    DB::table('pur.quotes')->where('qte_id', $qteId)->update([
                        'qte_frm_id' => $firmId,
                        'qte_firmname' => $firmName,
                        'qte_price' => $total,
                        'qte_intprice' => $subtotal,
                        'qte_inttax' => $sstAmount,
                        'qte_midprice' => $midPrice,
                        'qte_midtax' => $gstAmount,
                        'qte_num' => $qteNum,
                        'qte_date' => $qteDate,
                    ]);
                    DB::table('pur.quoteitems')->where('qti_qte_id', $qteId)->delete();
                } else {
                    // Insert new quote
                    $qteId = DB::table('pur.quotes')->insertGetId([
                        'qte_pcs_id' => $purchase->pcs_id,
                        'qte_frm_id' => $firmId,
                        'qte_firmname' => $firmName,
                        'qte_price' => $total,
                        'qte_intprice' => $subtotal,
                        'qte_inttax' => $sstAmount,
                        'qte_midprice' => $midPrice,
                        'qte_midtax' => $gstAmount,
                        'qte_num' => $qteNum,
                        'qte_date' => $qteDate,
                        'qte_techaccept' => true,
                    ], 'qte_id');
                }

                // Handle Scanned Quote Document Upload
                if ($request->hasFile('quote_file')) {
                    $qFile = $request->file('quote_file');
                    if ($qFile && $qFile->isValid()) {
                        // Remove existing attachment for this quote
                        $existingAtt = DB::table('pur.purattachments')
                            ->where('pat_objtype', 'qte')
                            ->where('pat_objid', $qteId)
                            ->first();

                        if ($existingAtt && !empty($existingAtt->pat_path)) {
                            app(\App\Services\FileStorageService::class)->delete($existingAtt->pat_path);
                            DB::table('pur.purattachments')->where('pat_id', $existingAtt->pat_id)->delete();
                        }

                        $stored = app(\App\Services\FileStorageService::class)->store($qFile, 'pur', 'pcs-', (string) $purchase->pcs_id);

                        DB::table('pur.purattachments')->insert([
                            'pat_objtype' => 'qte',
                            'pat_objid' => $qteId,
                            'pat_type' => 'Quotation Document',
                            'pat_path' => $stored,
                        ]);
                    }
                }

                foreach ($caseItems as $it) {
                    $price = 0.0;
                    if (isset($inputItems[$it->pci_id]['price'])) {
                        $price = (float) $inputItems[$it->pci_id]['price'];
                    } elseif (isset($flatItemPrices[$it->pci_id])) {
                        $price = (float) $flatItemPrices[$it->pci_id];
                    }
                    
                    DB::table('pur.quoteitems')->insert([
                        'qti_qte_id' => $qteId,
                        'qti_pci_id' => $it->pci_id,
                        'qti_price' => $price,
                        'qti_qty' => (float) $it->pci_qty,
                        'qti_serial' => (int) $it->pci_serial,
                        'qti_desc' => (string) $it->pci_desc,
                        'qti_qtyunit' => (string) $it->pci_qtyunit,
                        'qti_pcsdesc' => (string) $it->pci_desc,
                    ]);
                }

                $declaredTax = $this->declaredTaxFrom($request);
                $this->recalcCasePricing($purchase->pcs_id, $declaredTax);

                return ['ok' => true, 'message' => 'Quotation saved successfully.', 'pcsId' => (int) $purchase->pcs_id];
            }

            if ($op === 'upload_quote_file') {
                $qteId = (int) $request->input('qte_id');
                $quote = DB::table('pur.quotes')->where('qte_pcs_id', $purchase->pcs_id)->where('qte_id', $qteId)->first();
                if (!$quote) {
                    return ['ok' => false, 'message' => 'Quotation not found.', 'pcsId' => (int) $purchase->pcs_id];
                }

                if ($request->hasFile('quote_file')) {
                    $qFile = $request->file('quote_file');
                    if ($qFile && $qFile->isValid()) {
                        $existingAtt = DB::table('pur.purattachments')
                            ->where('pat_objtype', 'qte')
                            ->where('pat_objid', $qteId)
                            ->first();

                        if ($existingAtt && !empty($existingAtt->pat_path)) {
                            app(\App\Services\FileStorageService::class)->delete($existingAtt->pat_path);
                            DB::table('pur.purattachments')->where('pat_id', $existingAtt->pat_id)->delete();
                        }

                        $stored = app(\App\Services\FileStorageService::class)->store($qFile, 'pur', 'pcs-', (string) $purchase->pcs_id);

                        DB::table('pur.purattachments')->insert([
                            'pat_objtype' => 'qte',
                            'pat_objid' => $qteId,
                            'pat_type' => 'Quotation Document',
                            'pat_path' => $stored,
                        ]);

                        return ['ok' => true, 'message' => 'Quotation document uploaded successfully.', 'pcsId' => (int) $purchase->pcs_id];
                    }
                }
                return ['ok' => false, 'message' => 'Invalid document file.', 'pcsId' => (int) $purchase->pcs_id];
            }

            if ($op === 'delete_quote') {
                $qteId = (int) $request->input('qte_id');
                $quote = DB::table('pur.quotes')->where('qte_pcs_id', $purchase->pcs_id)->where('qte_id', $qteId)->first();
                if (!$quote) {
                    return ['ok' => false, 'message' => 'Quotation not found.', 'pcsId' => (int) $purchase->pcs_id];
                }
                $existingAtt = DB::table('pur.purattachments')->where('pat_objtype', 'qte')->where('pat_objid', $qteId)->first();
                if ($existingAtt && !empty($existingAtt->pat_path)) {
                    app(\App\Services\FileStorageService::class)->delete($existingAtt->pat_path);
                }
                DB::table('pur.purattachments')->where('pat_objtype', 'qte')->where('pat_objid', $qteId)->delete();
                DB::table('pur.quoteitems')->where('qti_qte_id', $qteId)->delete();
                DB::table('pur.quotes')->where('qte_id', $qteId)->delete();
                $this->recalcCasePricing($purchase->pcs_id);
                return ['ok' => true, 'message' => 'Quotation deleted.', 'pcsId' => (int) $purchase->pcs_id];
            }

            return ['ok' => false, 'message' => 'Invalid operation.', 'pcsId' => (int) $purchase->pcs_id];
        });

        return $this->respond($request, (int) ($result['pcsId'] ?? $purchase->pcs_id), (bool) ($result['ok'] ?? false), (string) ($result['message'] ?? ''));
    }

    protected function respond(Request $request, int $pcsId, bool $ok, string $message)
    {
        if ($request->expectsJson() || $request->ajax()) {
            $status = $ok ? 200 : 422;
            return response()->json([
                'ok' => $ok,
                'message' => $message,
                'data' => $ok ? $this->snapshot($pcsId) : null,
            ], $status);
        }

        return back()->with($ok ? 'success' : 'error', $message);
    }

    protected function snapshot(int $pcsId): array
    {
        $purchase = Purchase::with(['items', 'quotes.firm', 'attachments'])->findOrFail($pcsId);

        $items = $purchase->items->sortBy('pci_serial')->values()->map(fn($i) => [
            'pci_id' => (int) $i->pci_id,
            'pci_serial' => (int) $i->pci_serial,
            'pci_desc' => (string) $i->pci_desc,
            'pci_qty' => (float) $i->pci_qty,
            'pci_qtyunit' => (string) $i->pci_qtyunit,
            'pci_price' => (float) ($i->pci_price ?? 0),
        ])->values();

        $quoteAttachments = DB::table('pur.purattachments')
            ->where('pat_objtype', 'qte')
            ->whereIn('pat_objid', $purchase->quotes->pluck('qte_id')->toArray())
            ->get()
            ->keyBy('pat_objid');

        $quotes = $purchase->quotes->values()->map(function($q) use ($quoteAttachments) {
            $att = $quoteAttachments->get($q->qte_id);
            $filePath = $att ? (string) $att->pat_path : null;
            $fileName = $filePath ? basename(str_replace('\\', '/', $filePath)) : null;
            $qBase = (float) ($q->qte_intprice ?: $q->qte_price);
            $qSst = (float) ($q->qte_inttax ?? 0);
            $qGst = (float) ($q->qte_midtax ?? 0);
            $qTot = (float) ($q->qte_price ?: ($qBase + $qSst + $qGst));
            return [
                'qte_id' => (int) $q->qte_id,
                'qte_num' => (int) ($q->qte_num ?? 0),
                'firm_name' => (string) ($q->firm?->frm_name ?? $q->qte_firmname),
                'qte_price' => $qTot,
                'qte_subtotal' => $qBase,
                'qte_inttax' => $qSst,
                'qte_midtax' => $qGst,
                'qte_tax' => $qSst + $qGst,
                'tax_type' => $qSst > 0 ? 'SST' : 'GST',
                'attachment_path' => $filePath,
                'attachment_name' => $fileName ?? 'Quote Document',
            ];
        })->values();

        $quoteIds = $quotes->pluck('qte_id')->toArray();
        $quoteItems = [];
        if (count($quoteIds) > 0) {
            $rows = DB::table('pur.quoteitems')
                ->whereIn('qti_qte_id', $quoteIds)
                ->get(['qti_qte_id', 'qti_pci_id', 'qti_price']);
            foreach ($rows as $r) {
                $qid = (string) $r->qti_qte_id;
                $pid = (string) $r->qti_pci_id;
                if (!isset($quoteItems[$qid])) $quoteItems[$qid] = [];
                $quoteItems[$qid][$pid] = (float) $r->qti_price;
            }
        }

        $attachments = $purchase->attachments->values()->map(fn($a) => [
            'pat_id' => (int) $a->pat_id,
            'pat_path' => (string) $a->pat_path,
            'pat_type' => (string) ($a->pat_type ?: ''),
            'pat_filename' => basename(str_replace('\\', '/', (string)($a->pat_path ?? ''))),
        ])->values();

        return [
            'pcs_id' => (int) $purchase->pcs_id,
            'pcs_title' => (string) $purchase->pcs_title,
            'pcs_remarks' => (string) ($purchase->pcs_remarks ?? ''),
            'pcs_intprice' => (float) ($purchase->pcs_intprice ?? 0),
            'pcs_inttax' => (float) ($purchase->pcs_inttax ?? 0),
            'pcs_midprice' => (float) ($purchase->pcs_midprice ?? 0),
            'pcs_midtax' => (float) ($purchase->pcs_midtax ?? 0),
            'pcs_price' => (float) ($purchase->pcs_price ?? 0),
            'items' => $items,
            'quotes' => $quotes,
            'attachments' => $attachments,
            'quote_items' => $quoteItems,
        ];
    }

    /**
     * Re-price a case the way legacy did (App\Services\PurchasePricingService).
     *
     * Replaces the old flat "18% on the lowest quote" arithmetic, which wrote the
     * same tax figure into both pcs_inttax and pcs_midtax and set pcs_midprice to
     * the tax-inclusive total - breaking the legacy cascade
     * (pcs_midprice = pcs_intprice + pcs_inttax, pcs_price = pcs_midprice + pcs_midtax)
     * and leaving list and detail screens reading different numbers.
     *
     * @param  array|null  $declaredTax  ['type' => 'GST'|'SST', 'percent' => float]
     */
    protected function recalcCasePricing(int $pcsId, ?array $declaredTax = null): void
    {
        $pricing = app(\App\Services\PurchasePricingService::class);

        $quoteIds = DB::table('pur.quotes')->where('qte_pcs_id', $pcsId)->pluck('qte_id')->all();

        if (count($quoteIds) === 0) {
            // No quotes yet: the case is still an estimate off its own item lines.
            $pricing->recalcCaseFromItems($pcsId, $declaredTax);
            $pricing->updateFirmAndRecomm($pcsId);
            return;
        }

        $quoteType = (int) (DB::table('pur.purcases')->where('pcs_id', $pcsId)->value('pcs_quotetype') ?: 1);

        foreach ($quoteIds as $qteId) {
            $pricing->recalcQuoteFromItems((int) $qteId, $quoteType, $declaredTax);
        }

        // Keep a selection the user already made; otherwise pick the cheapest
        // technically acceptable offer, as CompareQuotesAndMarkLowest() did.
        $current = DB::table('pur.quotes')
            ->where('qte_pcs_id', $pcsId)
            ->where('qte_recomm', true)
            ->value('qte_id');

        $pricing->markRecommended($pcsId, $current ? (int) $current : null);
        $pricing->applyRecommendedQuote($pcsId, $declaredTax);
    }

    /**
     * Tax type / percentage declared by the case form, or null when it sent none.
     */
    protected function declaredTaxFrom(Request $request): ?array
    {
        if (!$request->filled('tax_percent') && !$request->filled('tax_type')) {
            return null;
        }

        return [
            'type'    => strtoupper(trim((string) $request->input('tax_type', 'GST'))),
            'percent' => (float) $request->input('tax_percent', 0),
        ];
    }

    /**
     * JSON endpoint for JQuery polling of statuses
     */
    public function getStatuses()
    {
        $user = Auth::user();
        $unitId = $user->acc_unt_id;

        $statuses = Purchase::where('pcs_unt_id', $unitId)
            ->select('pcs_id', 'pcs_status')
            ->get()
            ->pluck('pcs_status', 'pcs_id');

        return response()->json($statuses);
    }
}
