<?php

namespace App\Services\Auth;

class PermissionRegistry
{
    // Project permissions
    public const PROJECT_VIEW = 'project.view';
    public const PROJECT_CREATE = 'project.create';
    public const PROJECT_EDIT = 'project.edit';
    public const PROJECT_DELETE = 'project.delete';

    // Purchase / Procurement permissions
    public const PURCHASE_VIEW = 'purchase.view';
    public const PURCHASE_CREATE = 'purchase.create';
    public const PURCHASE_EDIT = 'purchase.edit';
    public const PURCHASE_SUBMIT = 'purchase.submit';
    public const PURCHASE_PROCUREMENT_ACTION = 'purchase.procurement_action';
    public const PURCHASE_FINANCE_REVIEW = 'purchase.finance_review';
    public const PURCHASE_MD_ACTION = 'purchase.md_action';
    public const PURCHASE_DDG_ACTION = 'purchase.ddg_action';
    public const PURCHASE_DG_ACTION = 'purchase.dg_action';
    public const PURCHASE_CANCEL = 'purchase.cancel';
    public const PURCHASE_GOODS_RECEIPT = 'purchase.goods_receipt';

    // MPR (Monthly Progress Report) permissions
    public const MPR_VIEW = 'mpr.view';
    public const MPR_SUBMIT = 'mpr.submit';
    public const MPR_REVIEW = 'mpr.review';
    public const MPR_RETURN = 'mpr.return';
    public const MPR_FINALIZE = 'mpr.finalize';
    public const MPR_COMPILE = 'mpr.compile';

    // Contract Case permissions
    public const CONTRACT_VIEW = 'contract.view';
    public const CONTRACT_CREATE = 'contract.create';
    public const CONTRACT_EDIT = 'contract.edit';
    public const CONTRACT_SUBMIT = 'contract.submit';
    public const CONTRACT_HR_REVIEW = 'contract.hr_review';
    public const CONTRACT_HR_FORWARD = 'contract.hr_forward';
    public const CONTRACT_MD_ACTION = 'contract.md_action';
    public const CONTRACT_DDG_ACTION = 'contract.ddg_action';
    public const CONTRACT_DG_ACTION = 'contract.dg_action';

    // Finance & Salary permissions
    public const FINANCE_VIEW = 'finance.view';
    public const FINANCE_COMMITMENTS_VIEW = 'finance.commitments.view';
    public const FINANCE_COMMITMENTS_SETTLE = 'finance.commitments.settle';
    public const SALARY_VIEW = 'salary.view';
    public const SALARY_GENERATE = 'salary.generate';
    public const SALARY_OVERRIDE = 'salary.override';
    public const SALARY_APPROVE = 'salary.approve';

    // HR & Attendance permissions
    public const HR_EMPLOYEE_VIEW = 'hr.employee.view';
    public const HR_EMPLOYEE_MANAGE = 'hr.employee.manage';
    public const ATTENDANCE_VIEW = 'attendance.view';
    public const ATTENDANCE_RECORD = 'attendance.record';

    // Admin, Reports & Support permissions
    public const ADMIN_SETTINGS_MANAGE = 'admin.settings.manage';
    public const ADMIN_USERS_VIEW = 'admin.users.view';
    public const ADMIN_USERS_MANAGE = 'admin.users.manage';
    public const REPORTS_VIEW = 'reports.view';
    public const SUPPORT_TICKET_CREATE = 'support.ticket.create';
    public const SUPPORT_TICKET_REPLY = 'support.ticket.reply';
    public const SUPPORT_TICKET_MANAGE = 'support.ticket.manage';

    /**
     * All registered permissions in the system.
     */
    public static function all(): array
    {
        return [
            self::PROJECT_VIEW,
            self::PROJECT_CREATE,
            self::PROJECT_EDIT,
            self::PROJECT_DELETE,

            self::PURCHASE_VIEW,
            self::PURCHASE_CREATE,
            self::PURCHASE_EDIT,
            self::PURCHASE_SUBMIT,
            self::PURCHASE_PROCUREMENT_ACTION,
            self::PURCHASE_FINANCE_REVIEW,
            self::PURCHASE_MD_ACTION,
            self::PURCHASE_DDG_ACTION,
            self::PURCHASE_DG_ACTION,
            self::PURCHASE_CANCEL,
            self::PURCHASE_GOODS_RECEIPT,

            self::MPR_VIEW,
            self::MPR_SUBMIT,
            self::MPR_REVIEW,
            self::MPR_RETURN,
            self::MPR_FINALIZE,
            self::MPR_COMPILE,

            self::CONTRACT_VIEW,
            self::CONTRACT_CREATE,
            self::CONTRACT_EDIT,
            self::CONTRACT_SUBMIT,
            self::CONTRACT_HR_REVIEW,
            self::CONTRACT_HR_FORWARD,
            self::CONTRACT_MD_ACTION,
            self::CONTRACT_DDG_ACTION,
            self::CONTRACT_DG_ACTION,

            self::FINANCE_VIEW,
            self::FINANCE_COMMITMENTS_VIEW,
            self::FINANCE_COMMITMENTS_SETTLE,
            self::SALARY_VIEW,
            self::SALARY_GENERATE,
            self::SALARY_OVERRIDE,
            self::SALARY_APPROVE,

            self::HR_EMPLOYEE_VIEW,
            self::HR_EMPLOYEE_MANAGE,
            self::ATTENDANCE_VIEW,
            self::ATTENDANCE_RECORD,

            self::ADMIN_SETTINGS_MANAGE,
            self::ADMIN_USERS_VIEW,
            self::ADMIN_USERS_MANAGE,
            self::REPORTS_VIEW,
            self::SUPPORT_TICKET_CREATE,
            self::SUPPORT_TICKET_REPLY,
            self::SUPPORT_TICKET_MANAGE,
        ];
    }
}
