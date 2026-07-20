-- Query: hr_salreq_add
-- Type: 64

INSERT INTO hr_salreqs ( srq_emp_id, srq_unt_id, srq_hed_id, srq_effhed_id, srq_effunt_id, srq_month, srq_status, srq_salary, srq_empnamecomp, srq_ctrsalary, srq_grosalary, srq_netsalary, srq_contracts, srq_bnkaccdetail, srq_bnkacctitle, srq_remarks, srq_unpaiddays, srq_paidholidays, srq_underwork, srq_overwork, srq_award, srq_penalty, srq_loaned, srq_withheld, srq_arrears, srq_dues, srq_paidalready, srq_sudohed, srq_parent )
SELECT [EmployeeId] AS Expr9, [UnitId] AS Expr3, [HeadId] AS Expr4, [EffHeadId] AS Expr6, [EffUnitId] AS Expr8, [Month] AS Expr7, [Status] AS Expr2, [Salary] AS Expr10, [EmpNameComp] AS Expr1, [ContractSalary] AS Expr11, [GroSalary] AS Expr13, [NetSalary] AS Expr14, [Contracts] AS Expr15, [BankAccDetail] AS Expr16, [BankAccTitle] AS Expr17, [Remarks] AS Expr18, [UnpaidWorkDays] AS Expr5, [PaidHolidays] AS Expr12, [UnderWork] AS Expr19, [OverWork] AS Expr20, [Award] AS Expr21, [Penalty] AS Expr22, [Loaned] AS Expr23, [Withheld] AS Expr24, [Arrears] AS Expr25, [Dues] AS Expr26, [AlreadyPaid] AS Expr27, [SudoHead] AS Expr28, [Parent] AS Expr30;

