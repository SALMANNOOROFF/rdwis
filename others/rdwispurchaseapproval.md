# RDWIS — Purchase Case Approval Hierarchy, Decision Trail & Notification System
## Antigravity / Cursor Master Prompt

---

## PROJECT CONTEXT

You are working inside **RDWIS** (Research & Development Wing Information System), a **Laravel 12** ERP application for Pakistan Navy's Naval Research & Development Institute (NRDI). The stack is:

- **Backend:** Laravel 12, PHP 8.2+
- **Frontend:** Blade templates, Bootstrap 4, AdminLTE 3.x, jQuery
- **Database:** PostgreSQL — schemas `cen` (auth/users/units) and `procurement` (purchase cases)
- **Auth:** Custom `CenAccountUserProvider`, role-based via `cen.roles`, area-based via `cen.units`
- **Theme:** Navy & Gold dark theme ("Obsidian Navy") — `rdwis-dark.css` already in place
- **Existing approval view:** DG NRDI approval view already exists and is the visual reference for all other approver views

**CRITICAL RULE:** Do NOT break or modify any existing functionality. Only add new files, migrations, and minimally extend existing controllers/views where explicitly instructed.

---

## PART 1 — UNDERSTAND THE APPROVAL FLOW

The purchase case lifecycle is:

```
Division (Draft)
    → [Release] → status: under_scrutiny
        → Director Procurement (verify & vet)
            → [Forward with remarks] → status: with_dfinance
            → [Return with remarks]  → status: returned (back to Division)
        → Director Finance (review)
            → [Forward] → routes by AMOUNT:
                ├── amount ≤ 2,00,000  → status: with_md    (MD can APPROVE directly)
                ├── amount ≤ 6,00,000  → status: with_md    (MD must FORWARD to DDG)
                └── amount > 6,00,000  → status: with_dg    (goes straight to DG)
        → MD (Managing Director)
            → If amount ≤ 2L: buttons = [Approve] [Return]
            → If 2L < amount ≤ 6L: buttons = [Forward to DDG] [Return]
        → DDG (Deputy Director General) — only sees 2L–6L cases
            → buttons = [Approve] [Return]
        → DG NRDI — only sees 6L+ cases (view already exists, use as visual reference)
            → buttons = [Approve] [Return] [Reject]
```

All actions (forward, approve, return, reject) MUST be recorded with remarks in the decision trail table.

---

## PART 2 — DATABASE MIGRATIONS

### Migration 1: `purchase_case_decisions`

Create file: `database/migrations/YYYY_MM_DD_create_purchase_case_decisions_table.php`

```php
Schema::create('procurement.purchase_case_decisions', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->unsignedBigInteger('purchase_case_id');
    $table->unsignedBigInteger('decided_by');          // cen.accounts.id
    $table->string('role_at_time', 50);                // 'dproc','dfinance','md','ddg','dg'
    $table->string('action', 30);                      // 'forwarded','returned','approved','rejected','forwarded_to_ddg','forwarded_to_dg'
    $table->text('remarks')->nullable();
    $table->string('from_status', 40)->nullable();
    $table->string('to_status', 40)->nullable();
    $table->string('forwarded_to_role', 50)->nullable();
    $table->decimal('case_amount_at_time', 15, 2)->nullable();
    $table->unsignedBigInteger('unit_id')->nullable();  // cen.units.id
    $table->timestampTz('decided_at')->useCurrent();

    $table->foreign('purchase_case_id')
          ->references('id')->on('procurement.purchase_cases')
          ->onDelete('cascade');
    $table->foreign('decided_by')
          ->references('id')->on('cen.accounts');

    $table->index('purchase_case_id');
    $table->index('decided_by');
    $table->index('decided_at');
});
```

### Migration 2: `purchase_case_notifications`

Create file: `database/migrations/YYYY_MM_DD_create_purchase_case_notifications_table.php`

```php
Schema::create('procurement.purchase_case_notifications', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->unsignedBigInteger('recipient_id');         // cen.accounts.id
    $table->unsignedBigInteger('purchase_case_id');
    $table->unsignedBigInteger('decision_id')->nullable();
    $table->text('message');
    $table->boolean('is_read')->default(false);
    $table->timestampTz('created_at')->useCurrent();

    $table->foreign('recipient_id')
          ->references('id')->on('cen.accounts');
    $table->foreign('purchase_case_id')
          ->references('id')->on('procurement.purchase_cases')
          ->onDelete('cascade');
    $table->foreign('decision_id')
          ->references('id')->on('procurement.purchase_case_decisions')
          ->onDelete('set null');

    $table->index(['recipient_id', 'is_read']);
    $table->index('purchase_case_id');
});
```

### Migration 3: Extend `purchase_cases` status values

Ensure the `status` column on `procurement.purchase_cases` supports these values:
`draft`, `under_scrutiny`, `with_dfinance`, `with_md`, `with_ddg`, `with_dg`, `approved`, `returned`, `rejected`

If status is a VARCHAR, just document the allowed values. If it's an ENUM, alter it to include all of the above.

---

## PART 3 — ELOQUENT MODELS

### Model 1: `app/Models/Procurement/PurchaseCaseDecision.php`

```php
namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;

class PurchaseCaseDecision extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'procurement.purchase_case_decisions';
    public $timestamps = false;

    protected $fillable = [
        'purchase_case_id', 'decided_by', 'role_at_time', 'action',
        'remarks', 'from_status', 'to_status', 'forwarded_to_role',
        'case_amount_at_time', 'unit_id', 'decided_at',
    ];

    protected $casts = [
        'decided_at'          => 'datetime',
        'case_amount_at_time' => 'decimal:2',
    ];

    public function purchaseCase()
    {
        return $this->belongsTo(PurchaseCase::class, 'purchase_case_id');
    }

    public function decidedBy()
    {
        return $this->belongsTo(\App\Models\Cen\Account::class, 'decided_by');
    }
}
```

### Model 2: `app/Models/Procurement/PurchaseCaseNotification.php`

```php
namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;

class PurchaseCaseNotification extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'procurement.purchase_case_notifications';
    public $timestamps = false;

    protected $fillable = [
        'recipient_id', 'purchase_case_id', 'decision_id', 'message', 'is_read', 'created_at',
    ];

    protected $casts = [
        'is_read'    => 'boolean',
        'created_at' => 'datetime',
    ];

    public function purchaseCase()
    {
        return $this->belongsTo(PurchaseCase::class, 'purchase_case_id');
    }
}
```

### Extend existing `PurchaseCase` model

In `app/Models/Procurement/PurchaseCase.php`, add these relationships (do NOT change anything else):

```php
public function decisions()
{
    return $this->hasMany(PurchaseCaseDecision::class, 'purchase_case_id')->orderBy('decided_at');
}

public function latestDecision()
{
    return $this->hasOne(PurchaseCaseDecision::class, 'purchase_case_id')->latestOfMany('decided_at');
}

public function notifications()
{
    return $this->hasMany(PurchaseCaseNotification::class, 'purchase_case_id');
}
```

---

## PART 4 — SERVICE CLASS (Core Logic)

Create: `app/Services/Procurement/PurchaseCaseApprovalService.php`

```php
namespace App\Services\Procurement;

use App\Models\Procurement\PurchaseCase;
use App\Models\Procurement\PurchaseCaseDecision;
use App\Models\Procurement\PurchaseCaseNotification;
use App\Models\Cen\Account;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PurchaseCaseApprovalService
{
    const THRESHOLD_MD  = 200000;   // 2 lakh
    const THRESHOLD_DDG = 600000;   // 6 lakh

    /**
     * Determine which senior authority ultimately approves based on amount.
     */
    public function determineApprovalAuthority(PurchaseCase $case): string
    {
        if ($case->total_amount <= self::THRESHOLD_MD) {
            return 'md';
        } elseif ($case->total_amount <= self::THRESHOLD_DDG) {
            return 'ddg';
        } else {
            return 'dg';
        }
    }

    /**
     * Main entry point — called from any approver's controller.
     * $action: 'forwarded' | 'returned' | 'approved' | 'rejected'
     * $actorRole: 'dproc' | 'dfinance' | 'md' | 'ddg' | 'dg'
     */
    public function processDecision(
        PurchaseCase $case,
        string $action,
        string $actorRole,
        string $remarks
    ): PurchaseCaseDecision {

        return DB::transaction(function () use ($case, $action, $actorRole, $remarks) {

            $actor      = Auth::user();
            $fromStatus = $case->status;
            $toStatus   = $this->resolveNextStatus($case, $actorRole, $action);
            $nextRole   = $this->resolveNextRole($case, $actorRole, $action);

            // 1. Record decision
            $decision = PurchaseCaseDecision::create([
                'purchase_case_id'    => $case->id,
                'decided_by'          => $actor->id,
                'role_at_time'        => $actorRole,
                'action'              => $action,
                'remarks'             => $remarks,
                'from_status'         => $fromStatus,
                'to_status'           => $toStatus,
                'forwarded_to_role'   => $nextRole,
                'case_amount_at_time' => $case->total_amount,
                'unit_id'             => $case->unit_id,
                'decided_at'          => now(),
            ]);

            // 2. Update case status
            $case->update(['status' => $toStatus]);

            // 3. Notify relevant users
            $this->dispatchNotifications($case, $decision, $action, $nextRole, $actor);

            return $decision;
        });
    }

    private function resolveNextStatus(PurchaseCase $case, string $actorRole, string $action): string
    {
        if ($action === 'returned')  return 'returned';
        if ($action === 'rejected')  return 'rejected';
        if ($action === 'approved')  return 'approved';

        // forwarded
        return match($actorRole) {
            'dproc'   => 'with_dfinance',
            'dfinance' => match($this->determineApprovalAuthority($case)) {
                'md'  => 'with_md',
                'ddg' => 'with_md',   // still goes through MD first
                'dg'  => 'with_dg',
                default => 'with_md',
            },
            'md'      => 'with_ddg',  // MD forwards to DDG (only 2L-6L cases reach here)
            default   => 'with_dg',
        };
    }

    private function resolveNextRole(PurchaseCase $case, string $actorRole, string $action): ?string
    {
        if (in_array($action, ['returned', 'rejected', 'approved'])) {
            return null; // terminal action
        }

        return match($actorRole) {
            'dproc'    => 'dfinance',
            'dfinance' => match($this->determineApprovalAuthority($case)) {
                'md', 'ddg' => 'md',
                'dg'        => 'dg',
                default     => 'md',
            },
            'md'       => 'ddg',
            default    => null,
        };
    }

    private function dispatchNotifications(
        PurchaseCase $case,
        PurchaseCaseDecision $decision,
        string $action,
        ?string $nextRole,
        $actor
    ): void {
        $message = $this->buildMessage($case, $action, $actor, $nextRole);

        if ($nextRole) {
            // Notify next approver(s)
            $recipients = Account::where('procurement_role', $nextRole)->get();
        } else {
            // Terminal action — notify original initiator's unit
            $recipients = Account::where('unit_id', $case->unit_id)
                                  ->whereIn('procurement_role', ['initiator', 'division_head'])
                                  ->get();
        }

        foreach ($recipients as $recipient) {
            if ($recipient->id === $actor->id) continue; // don't notify yourself

            PurchaseCaseNotification::create([
                'recipient_id'     => $recipient->id,
                'purchase_case_id' => $case->id,
                'decision_id'      => $decision->id,
                'message'          => $message,
                'created_at'       => now(),
            ]);
        }
    }

    private function buildMessage(PurchaseCase $case, string $action, $actor, ?string $nextRole): string
    {
        $caseRef = $case->case_number ?? "Case #{$case->id}";
        $name    = $actor->name ?? 'An officer';

        return match($action) {
            'forwarded' => "{$caseRef} has been forwarded by {$name} and is now awaiting " . strtoupper($nextRole ?? '') . " review.",
            'returned'  => "{$caseRef} was returned by {$name} with remarks. Please review and resubmit.",
            'approved'  => "{$caseRef} has been approved by {$name}.",
            'rejected'  => "{$caseRef} was rejected by {$name}. Case is closed.",
            default     => "{$caseRef} status updated by {$name}.",
        };
    }
}
```

---

## PART 5 — CONTROLLER UPDATES

### 5A. New `PurchaseCaseApprovalController.php`

Create: `app/Http/Controllers/Procurement/PurchaseCaseApprovalController.php`

This single controller handles MD, DDG, and DG approvals. The existing DG controller (if any) can be refactored to use this or kept — do NOT delete existing routes.

```php
namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\PurchaseCase;
use App\Services\Procurement\PurchaseCaseApprovalService;
use Illuminate\Http\Request;

class PurchaseCaseApprovalController extends Controller
{
    public function __construct(
        private PurchaseCaseApprovalService $approvalService
    ) {}

    /**
     * Show approval view for MD / DDG / DG
     * $role = 'md' | 'ddg' | 'dg'
     */
    public function show(string $role, PurchaseCase $case)
    {
        $this->authorizeRole($role);

        $decisions = $case->decisions()->with('decidedBy')->get();

        return view('procurement.approvals.' . $role, compact('case', 'decisions', 'role'));
    }

    /**
     * Process the decision (approve / return / reject / forward)
     */
    public function decide(Request $request, string $role, PurchaseCase $case)
    {
        $this->authorizeRole($role);

        $data = $request->validate([
            'action'  => ['required', 'in:forwarded,returned,approved,rejected'],
            'remarks' => ['required', 'string', 'min:5', 'max:2000'],
        ]);

        // Extra validation: only DG can reject
        if ($data['action'] === 'rejected' && $role !== 'dg') {
            abort(403, 'Only DG can reject a case.');
        }

        $decision = $this->approvalService->processDecision(
            $case,
            $data['action'],
            $role,
            $data['remarks']
        );

        $label = ucfirst($data['action']);

        return redirect()
            ->route("procurement.{$role}.index")
            ->with('success', "Case #{$case->case_number} has been {$label} successfully.");
    }

    private function authorizeRole(string $role): void
    {
        // Integrate with your existing CEN role check middleware/gate
        // Example: abort_unless(auth()->user()->procurement_role === $role, 403);
        abort_unless(in_array($role, ['md', 'ddg', 'dg']), 404);
    }
}
```

### 5B. New `NotificationController.php`

Create: `app/Http/Controllers/NotificationController.php`

```php
namespace App\Http\Controllers;

use App\Models\Procurement\PurchaseCaseNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Returns unread notifications for the bell — polled by jQuery every 30s
     */
    public function unread()
    {
        $notifs = PurchaseCaseNotification::with('purchaseCase')
            ->where('recipient_id', auth()->id())
            ->where('is_read', false)
            ->latest('created_at')
            ->take(10)
            ->get()
            ->map(fn($n) => [
                'id'               => $n->id,
                'purchase_case_id' => $n->purchase_case_id,
                'message'          => $n->message,
                'time_ago'         => $n->created_at->diffForHumans(),
            ]);

        return response()->json([
            'count'         => $notifs->count(),
            'notifications' => $notifs,
        ]);
    }

    public function markRead($id)
    {
        PurchaseCaseNotification::where('id', $id)
            ->where('recipient_id', auth()->id())
            ->update(['is_read' => true]);

        return response()->json(['ok' => true]);
    }

    public function markAllRead()
    {
        PurchaseCaseNotification::where('recipient_id', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['ok' => true]);
    }

    /**
     * Full notifications page (optional)
     */
    public function index()
    {
        $notifications = PurchaseCaseNotification::with('purchaseCase')
            ->where('recipient_id', auth()->id())
            ->latest('created_at')
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }
}
```

---

## PART 6 — ROUTES

Add to `routes/web.php` inside the existing `auth` middleware group:

```php
// Notification routes
Route::prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/',              [NotificationController::class, 'index'])->name('index');
    Route::get('/unread',        [NotificationController::class, 'unread'])->name('unread');
    Route::post('/{id}/read',    [NotificationController::class, 'markRead'])->name('read');
    Route::post('/mark-all-read',[NotificationController::class, 'markAllRead'])->name('markAllRead');
});

// Approval routes — MD, DDG, DG
Route::prefix('procurement/approvals')->name('procurement.approvals.')->group(function () {
    foreach (['md', 'ddg', 'dg'] as $role) {
        Route::get("/{$role}/{case}",     [PurchaseCaseApprovalController::class, 'show'])->name("{$role}.show");
        Route::post("/{$role}/{case}",    [PurchaseCaseApprovalController::class, 'decide'])->name("{$role}.decide");
    }
});
```

---

## PART 7 — BLADE VIEWS

### 7A. MD Approval View

Create: `resources/views/procurement/approvals/md.blade.php`

**Visual reference:** Use the existing DG approval view as the exact visual template. The structure must be identical — same card layout, same financial summary component, same timeline/decision trail section.

**Changes from DG view:**
1. Page title: "MD Approval — Purchase Case"
2. Action buttons differ based on `$case->total_amount`:
   ```blade
   @if($case->total_amount <= 200000)
       {{-- MD can approve directly --}}
       <button type="submit" name="action" value="approved" class="btn btn-success btn-sm">
           <i class="fas fa-check-circle"></i> Approve
       </button>
   @else
       {{-- MD must forward to DDG --}}
       <button type="submit" name="action" value="forwarded" class="btn btn-primary btn-sm">
           <i class="fas fa-arrow-circle-right"></i> Forward to DDG
       </button>
   @endif
   <button type="submit" name="action" value="returned" class="btn btn-warning btn-sm">
       <i class="fas fa-undo"></i> Return
   </button>
   ```
3. NO reject button for MD.
4. Include the decision trail section (see Part 7C).

### 7B. DDG Approval View

Create: `resources/views/procurement/approvals/ddg.blade.php`

Same visual as DG view. Buttons:
```blade
<button type="submit" name="action" value="approved" class="btn btn-success btn-sm">
    <i class="fas fa-check-circle"></i> Approve
</button>
<button type="submit" name="action" value="returned" class="btn btn-warning btn-sm">
    <i class="fas fa-undo"></i> Return
</button>
```
NO reject button for DDG.

### 7C. Decision Trail Partial (reusable)

Create: `resources/views/procurement/approvals/_decision_trail.blade.php`

```blade
<div class="card card-outline card-navy mt-3">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-history mr-1"></i> Approval Timeline
        </h3>
    </div>
    <div class="card-body p-0">
        @forelse($decisions as $d)
        <div class="d-flex align-items-start p-3 border-bottom">
            <div class="mr-3 mt-1">
                @if($d->action === 'approved')
                    <span class="badge badge-success">Approved</span>
                @elseif($d->action === 'returned')
                    <span class="badge badge-warning">Returned</span>
                @elseif($d->action === 'rejected')
                    <span class="badge badge-danger">Rejected</span>
                @else
                    <span class="badge badge-primary">Forwarded</span>
                @endif
            </div>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between">
                    <strong class="text-sm">{{ $d->decidedBy->name ?? 'N/A' }}</strong>
                    <small class="text-muted">{{ $d->decided_at->format('d M Y, H:i') }}</small>
                </div>
                <div class="text-muted text-xs mb-1">
                    {{ strtoupper($d->role_at_time) }}
                    &rarr; {{ strtoupper(str_replace('_', ' ', $d->to_status)) }}
                </div>
                @if($d->remarks)
                    <p class="mb-0 text-sm">"{{ $d->remarks }}"</p>
                @endif
            </div>
        </div>
        @empty
            <p class="p-3 text-muted mb-0">No decisions recorded yet.</p>
        @endforelse
    </div>
</div>
```

Include this in **all** approval views (MD, DDG, DG) via:
```blade
@include('procurement.approvals._decision_trail', ['decisions' => $decisions])
```

### 7D. Notification Bell — Header Partial

In `resources/views/layouts/partials/header.blade.php` (or wherever the AdminLTE navbar is), find the existing `<ul class="navbar-nav ml-auto">` and add the notification bell **before** the user dropdown:

```blade
{{-- RDWIS Notification Bell --}}
<li class="nav-item dropdown">
    <a class="nav-link" data-toggle="dropdown" href="#" id="rdwis-notif-bell">
        <i class="far fa-bell"></i>
        <span class="badge badge-warning navbar-badge d-none" id="rdwis-notif-count">0</span>
    </a>
    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right" id="rdwis-notif-panel"
         style="min-width:320px;">
        <div class="dropdown-item d-flex justify-content-between align-items-center">
            <span class="font-weight-bold">Notifications</span>
            <a href="{{ route('notifications.index') }}" class="text-xs text-muted">View all</a>
        </div>
        <div class="dropdown-divider m-0"></div>
        <div id="rdwis-notif-list" style="max-height:320px;overflow-y:auto;">
            <div class="dropdown-item text-muted text-sm text-center py-3">
                <i class="fas fa-circle-notch fa-spin mr-1"></i> Loading...
            </div>
        </div>
        <div class="dropdown-divider m-0"></div>
        <a href="{{ route('notifications.index') }}"
           class="dropdown-item text-center text-sm py-2">
            See all notifications
        </a>
    </div>
</li>
```

---

## PART 8 — JAVASCRIPT (Notification Polling)

Create: `public/js/rdwis-notifications.js`

This file is self-contained. Do NOT modify existing JS files.

```javascript
(function ($) {
    'use strict';

    var POLL_MS      = 30000;
    var csrfToken    = $('meta[name="csrf-token"]').attr('content');

    // ── Fetch unread notifications ──────────────────────────────────────────
    function fetchNotifications() {
        $.ajax({
            url    : '/notifications/unread',
            method : 'GET',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function (res) {
                updateBell(res.count, res.notifications);
            },
            error: function () {
                // silently fail — don't break the page
            }
        });
    }

    // ── Update bell badge & dropdown list ──────────────────────────────────
    function updateBell(count, items) {
        var $badge = $('#rdwis-notif-count');
        var $list  = $('#rdwis-notif-list');

        // Badge
        if (count > 0) {
            $badge.text(count > 99 ? '99+' : count).removeClass('d-none');
        } else {
            $badge.addClass('d-none');
        }

        // List
        if (!items || items.length === 0) {
            $list.html(
                '<div class="dropdown-item text-muted text-sm text-center py-3">' +
                '<i class="far fa-bell-slash mr-1"></i> No new notifications</div>'
            );
            return;
        }

        var html = '';
        $.each(items, function (i, n) {
            html +=
                '<a href="/procurement/cases/' + n.purchase_case_id + '" ' +
                '   class="dropdown-item rdwis-notif-item border-bottom" ' +
                '   data-notif-id="' + n.id + '">' +
                '  <div class="d-flex align-items-start">' +
                '    <i class="fas fa-file-alt mt-1 mr-2 text-navy"></i>' +
                '    <div>' +
                '      <p class="text-sm mb-0">' + escapeHtml(n.message) + '</p>' +
                '      <small class="text-muted">' + n.time_ago + '</small>' +
                '    </div>' +
                '  </div>' +
                '</a>';
        });
        $list.html(html);
    }

    // ── Mark individual as read when clicked ───────────────────────────────
    $(document).on('click', '.rdwis-notif-item', function () {
        var id = $(this).data('notif-id');
        $.post('/notifications/' + id + '/read', { _token: csrfToken });
    });

    // ── Mark all read when dropdown opens ──────────────────────────────────
    $(document).on('click', '#rdwis-notif-bell', function () {
        setTimeout(function () {
            $.post('/notifications/mark-all-read', { _token: csrfToken });
            $('#rdwis-notif-count').addClass('d-none');
        }, 500);
    });

    // ── XSS-safe HTML escaping ─────────────────────────────────────────────
    function escapeHtml(str) {
        return $('<div>').text(str).html();
    }

    // ── Boot ───────────────────────────────────────────────────────────────
    fetchNotifications();
    setInterval(fetchNotifications, POLL_MS);

})(jQuery);
```

### Include in layout

In `resources/views/layouts/app.blade.php` (or master layout), add before `</body>`:

```blade
@auth
    <script src="{{ asset('js/rdwis-notifications.js') }}"></script>
@endauth
```

---

## PART 9 — WHAT TO LEAVE UNTOUCHED

The following files must NOT be modified:

- Existing DG approval controller and its routes (unless explicitly told to refactor)
- `rdwis-dark.css` — no style changes
- Any existing procurement listing/creation views
- `cen.accounts`, `cen.units`, `cen.roles` table structure
- Existing middleware and auth provider (`CenAccountUserProvider`)
- Any existing jQuery plugins or AdminLTE scripts

---

## PART 10 — IMPLEMENTATION ORDER

Execute in this exact sequence to avoid dependency issues:

1. Run Migration 1 (`purchase_case_decisions`)
2. Run Migration 2 (`purchase_case_notifications`)
3. Run Migration 3 (status column update on `purchase_cases`)
4. Create `PurchaseCaseDecision` model
5. Create `PurchaseCaseNotification` model
6. Add relationships to existing `PurchaseCase` model
7. Create `PurchaseCaseApprovalService`
8. Create `PurchaseCaseApprovalController`
9. Create `NotificationController`
10. Add routes to `web.php`
11. Create `_decision_trail.blade.php` partial
12. Create `md.blade.php` approval view
13. Create `ddg.blade.php` approval view
14. Add notification bell markup to header partial
15. Create `public/js/rdwis-notifications.js`
16. Include `rdwis-notifications.js` in master layout

---

## SUMMARY TABLE

| What | File/Location | New or Extend |
|---|---|---|
| Decision trail table | migration | New |
| Notifications table | migration | New |
| PurchaseCaseDecision model | app/Models/Procurement | New |
| PurchaseCaseNotification model | app/Models/Procurement | New |
| PurchaseCase relationships | existing model | Extend (add only) |
| PurchaseCaseApprovalService | app/Services/Procurement | New |
| PurchaseCaseApprovalController | app/Http/Controllers/Procurement | New |
| NotificationController | app/Http/Controllers | New |
| Routes | routes/web.php | Extend (add only) |
| MD approval view | resources/views/procurement/approvals/md | New |
| DDG approval view | resources/views/procurement/approvals/ddg | New |
| Decision trail partial | resources/views/procurement/approvals/_decision_trail | New |
| Notification bell HTML | layouts/partials/header | Extend (add only) |
| rdwis-notifications.js | public/js | New |
| Master layout JS include | layouts/app | Extend (add only) |