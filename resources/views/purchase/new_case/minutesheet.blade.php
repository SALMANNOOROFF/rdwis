@extends('welcome')

@section('content')
<div class="content-wrapper">
    <style>
        .minute-sheet {
            background: #fff;
            padding: 30px;
            border: 1px solid #d1d4d7;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
            margin: 20px;
            border-radius: 4px;
        }
        .sheet-header {
            border-bottom: 2px solid #007bff;
            margin-bottom: 20px;
            padding-bottom: 10px;
            color: #007bff;
            font-weight: bold;
            font-size: 1.5rem;
        }
        .table-minute thead th {
            background-color: #f8f9fa;
            font-size: 12px;
            text-transform: uppercase;
            border: 1px solid #dee2e6;
        }
        .financial-table {
            width: 100%;
            margin-top: 15px;
            font-size: 13px;
        }
        .financial-table th, .financial-table td {
            border: 1px solid #dee2e6;
            padding: 5px 8px;
        }
        .financial-table thead {
            background: #f4f6f9;
        }
        .section-label {
            font-weight: bold;
            background: #f1f1f1;
            padding: 5px 10px;
            margin: 20px 0 10px 0;
            border-left: 4px solid #007bff;
            display: block;
        }
        label { font-size: 13px; font-weight: 600; color: #555; }
        .form-control-sm { border-radius: 0; border: 1px solid #ccc; }
        .text-blue { color: #007bff; }
    </style>

    <div class="minute-sheet">
        <div class="sheet-header">
            <i class="fas fa-file-alt mr-2"></i> Minute Sheet
        </div>

        <!-- 1. Minute Log Table (From Image 1) -->
        <div class="table-responsive">
            <table class="table table-bordered table-minute">
                <thead>
                    <tr>
                        <th style="width: 50px;">Min</th>
                        <th style="width: 200px;">Title</th>
                        <th>Reference</th>
                        <th style="width: 120px;">Date</th>
                        <th>From</th>
                        <th style="width: 60px;">Encl.</th>
                        <th style="width: 60px;">Flag</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>
                            <select class="form-control form-control-sm">
                                <option>Purchase Case</option>
                                <option>TA/DA Case</option>
                                <option>Market Research Report</option>
                            </select>
                        </td>
                        <td><input type="text" class="form-control form-control-sm"></td>
                        <td><input type="date" class="form-control form-control-sm"></td>
                        <td><input type="text" class="form-control form-control-sm"></td>
                        <td><input type="text" class="form-control form-control-sm"></td>
                        <td><input type="text" class="form-control form-control-sm"></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="row mt-2">
            <div class="col-md-6">
                <small>Pur Cases: <strong>1487</strong> &nbsp; | &nbsp; This minute: <strong>9</strong></small>
            </div>
        </div>

        <!-- 2. Project Section (From Image 1) -->
        <span class="section-label">X. Project Details</span>
        <div class="row">
            <div class="col-md-12 mb-2">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="omitCheck">
                    <label class="custom-control-label" for="omitCheck">Omit Project Details</label>
                </div>
            </div>
            <div class="col-md-8">
                <div class="form-group row mb-2">
                    <label class="col-sm-3">Project Title</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control form-control-sm" value="Functional replacement of CDS display of PNS ALAMGIR">
                    </div>
                </div>
                <div class="form-group row mb-2">
                    <label class="col-sm-3">Proj Code</label>
                    <div class="col-sm-3">
                        <input type="text" class="form-control form-control-sm" value="CDS">
                    </div>
                    <label class="col-sm-3 text-right">Start Date (To)</label>
                    <div class="col-sm-3">
                        <input type="text" class="form-control form-control-sm" value="20 Oct 21">
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <label>Remarks (optional)</label>
                <textarea class="form-control form-control-sm" rows="2"></textarea>
            </div>
        </div>

        <!-- 3. General Section (From Image 2) -->
        <span class="section-label">1. General</span>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>For</label>
                    <input type="text" class="form-control form-control-sm" value="CDS">
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label>Reason</label>
                    <textarea class="form-control form-control-sm">Purchase case is required in CDS project....</textarea>
                </div>
            </div>
        </div>

        <!-- 4. Financial Status Tables (From Image 2 & 3) -->
        <span class="section-label">3. Financial Status</span>
        <div class="row">
            <div class="col-md-3">
                <label>Head:</label> <span class="text-blue font-weight-bold">CDS</span>
            </div>
            <div class="col-md-3">
                <label>Price:</label> <span class="text-blue font-weight-bold">0</span>
            </div>
        </div>

        <div class="row mt-3">
            <!-- Account Figures Table (Left) -->
            <div class="col-md-6">
                <label>Account Figures</label>
                <table class="financial-table">
                    <thead>
                        <tr><th>#</th><th>Description</th><th class="text-right">Amount</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>a.</td><td>Project Share</td><td class="text-right">7,182,009.00</td></tr>
                        <tr><td>b.</td><td>Received</td><td class="text-right">3,533,504.00</td></tr>
                        <tr><td>c.</td><td>Expenditure</td><td class="text-right">7,178,254.00</td></tr>
                        <tr><td>d.</td><td>Commitments</td><td class="text-right">0.00</td></tr>
                        <tr><td>e.</td><td>Available in account</td><td class="text-right text-danger">-3,644,750.00</td></tr>
                        <tr><td>f.</td><td>Receivable for Completed Milestones</td><td class="text-right">2,189,103.00</td></tr>
                    </tbody>
                </table>
            </div>

            <!-- Project Figures Table (Right) -->
            <div class="col-md-6">
                <label>Project Figures</label>
                <table class="financial-table">
                    <thead class="text-center">
                        <tr>
                            <th>#</th>
                            <th>Description</th>
                            <th>Overall</th>
                            <th>Equipment</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>k.</td><td>Max Spending Limit</td><td class="text-right">7,182,009</td><td class="text-right">5,287,009</td></tr>
                        <tr><td>l.</td><td>Expenditure</td><td class="text-right">7,178,254</td><td class="text-right">5,117,746</td></tr>
                        <tr><td>m.</td><td>Commitments</td><td class="text-right">0.00</td><td class="text-right">0.00</td></tr>
                        <tr><td>n.</td><td>Can be spent</td><td class="text-right text-success">3,755.00</td><td class="text-right">169,263</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 5. Paragraphs (From Image 3) -->
        <span class="section-label">4. Final Paragraphs</span>
        <div class="row mt-2">
            <div class="col-md-12 mb-3">
                <label>X. Additional Paragraph (optional)</label>
                <textarea class="form-control form-control-sm" rows="2"></textarea>
            </div>
            <div class="col-md-12 mb-4">
                <label>4. Final Paragraph</label>
                <textarea class="form-control form-control-sm" rows="3"></textarea>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="text-right border-top pt-3">
            <button class="btn btn-outline-secondary mr-2"><i class="fas fa-arrow-left"></i> Back</button>
<!-- Link to the static preview page -->
<a href="{{ route('purchase.new_case.print_minute') }}" target="_blank" class="btn btn-outline-primary btn-sm">
    <i class="fas fa-print"></i> Print Minute
</a>            
        </div>
    </div>
</div>
@endsection