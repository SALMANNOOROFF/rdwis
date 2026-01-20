@extends('welcome')
@section('content')
<div class="content-wrapper">
    <div class="container-fluid pt-4">
        <h3 class="mb-4 text-uppercase font-weight-bold"><i class="fas fa-file-invoice-dollar mr-2"></i> Report Center</h3>

        <div id="reportAccordion">
            
            <!-- SECTION 1: COMPARATIVE STATEMENT FORM -->
            <div class="card card-dark shadow-lg mb-4">
                <div class="card-header" data-toggle="collapse" data-target="#compCollapse" style="cursor:pointer">
                    <h3 class="card-title text-white"><i class="fas fa-table mr-2"></i> 1. Comparative Statement Form</h3>
                </div>
                <div id="compCollapse" class="collapse show" data-parent="#reportAccordion">
                    <form action="{{ route('reports.generate.comparative') }}" method="POST" target="_blank">
                        @csrf
                        <div class="card-body">
                            <!-- Header Info -->
                            <div class="row mb-3">
                                <div class="col-md-4"><label>RFQ No</label><input type="text" name="rfq_no" class="form-control" value="R&D/NDB-2/02"></div>
                                <div class="col-md-4"><label>Date</label><input type="text" name="rfq_date" class="form-control" value="07 Jan 25"></div>
                                <div class="col-md-4"><label>Subject</label><input type="text" name="subject" class="form-control" value="for procurement of IT item and Fabrication of PCB"></div>
                            </div>

                            <hr>
                            <h5 class="mb-3">Firms Information (CS)</h5>
                            
                            <div id="comp-firms-area">
                                <div class="firm-block border rounded p-3 mb-4 bg-light shadow-sm">
                                    <div class="row">
                                        <div class="col-md-3 mb-2"><label class="small font-weight-bold">Firm Name</label><input type="text" name="firms[0][name]" class="form-control form-control-sm" required></div>
                                        <div class="col-md-2 mb-2"><label class="small font-weight-bold">Q-No/Date</label><input type="text" name="firms[0][q_no]" class="form-control form-control-sm"></div>
                                        <div class="col-md-2 mb-2"><label class="small font-weight-bold">Rate (Total)</label><input type="text" name="firms[0][rate]" class="form-control form-control-sm"></div>
                                        <div class="col-md-3 mb-2"><label class="small font-weight-bold">Address</label><input type="text" name="firms[0][address]" class="form-control form-control-sm"></div>
                                        <div class="col-md-2 mb-2"><label class="small font-weight-bold">NTN/STRN</label><input type="text" name="firms[0][ntn]" class="form-control form-control-sm"></div>
                                        
                                        <div class="col-md-4"><label class="small font-weight-bold">Contact/Email</label><input type="text" name="firms[0][contact]" class="form-control form-control-sm"></div>
                                        <div class="col-md-4"><label class="small font-weight-bold">Auth. Dealer</label><input type="text" name="firms[0][dealer]" class="form-control form-control-sm"></div>
                                        <div class="col-md-4"><label class="small font-weight-bold">Remarks</label><input type="text" name="firms[0][remarks]" class="form-control form-control-sm" value="Accepted"></div>
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="btn btn-secondary btn-sm" onclick="addCompRow()">+ Add More Firm</button>
                        </div>
                        <div class="card-footer bg-white text-right">
                            <button type="submit" class="btn btn-dark shadow-sm px-4">Generate Comparative Statement</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- SECTION 2: IT LETTER (RFQ) FORM -->
            <div class="card card-primary shadow-lg">
                <div class="card-header collapsed" data-toggle="collapse" data-target="#itCollapse" style="cursor:pointer">
                    <h3 class="card-title text-white"><i class="fas fa-envelope-open-text mr-2"></i> 2. IT Letter / Request for Quotation</h3>
                </div>
                <div id="itCollapse" class="collapse" data-parent="#reportAccordion">
                    <form action="{{ route('reports.generate.itletter') }}" method="POST" target="_blank">
                        @csrf
                        <div class="card-body">
                            <!-- Letter Header Fields -->
                            <div class="row mb-3">
                                <div class="col-md-3"><label>Ref No</label><input type="text" name="ref_no" class="form-control" value="R&D/TLI-2/01"></div>
                                <div class="col-md-3"><label>Letter Date</label><input type="text" name="letter_date" class="form-control" value="30 December 2024"></div>
                                <div class="col-md-4"><label>Procurement Item Description</label><input type="text" name="item_desc" class="form-control" value="Sensor Cable for Main Engine Monitoring"></div>
                                <div class="col-md-2"><label>Deadline Date</label><input type="text" name="deadline" class="form-control" value="13 Jan 2025"></div>
                            </div>

                            <hr>
                            <h5 class="mb-3">Recipients Information (Firms)</h5>
                            
                            <div id="it-firms-area">
                                <div class="it-firm-block border rounded p-3 mb-3 bg-light shadow-sm">
                                    <div class="row">
                                        <div class="col-md-4"><label class="small font-weight-bold">Firm Name</label><input type="text" name="firms[0][name]" class="form-control form-control-sm" required></div>
                                        <div class="col-md-5"><label class="small font-weight-bold">Address</label><input type="text" name="firms[0][address]" class="form-control form-control-sm"></div>
                                        <div class="col-md-3"><label class="small font-weight-bold">Tel / Contact</label><input type="text" name="firms[0][tel]" class="form-control form-control-sm"></div>
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="btn btn-primary btn-sm" onclick="addITRow()">+ Add More Firm</button>
                        </div>
                        <div class="card-footer bg-white text-right">
                            <button type="submit" class="btn btn-primary shadow-sm px-4">Generate IT Letter</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    let cIdx = 1;
    let iIdx = 1;

    // Comparative Statement Row Function
    function addCompRow() {
        let html = `
        <div class="firm-block border rounded p-3 mb-4 bg-light shadow-sm">
            <div class="row">
                <div class="col-md-3 mb-2"><label class="small font-weight-bold">Firm Name</label><input type="text" name="firms[${cIdx}][name]" class="form-control form-control-sm"></div>
                <div class="col-md-2 mb-2"><label class="small font-weight-bold">Q-No/Date</label><input type="text" name="firms[${cIdx}][q_no]" class="form-control form-control-sm"></div>
                <div class="col-md-2 mb-2"><label class="small font-weight-bold">Rate (Total)</label><input type="text" name="firms[${cIdx}][rate]" class="form-control form-control-sm"></div>
                <div class="col-md-3 mb-2"><label class="small font-weight-bold">Address</label><input type="text" name="firms[${cIdx}][address]" class="form-control form-control-sm"></div>
                <div class="col-md-2 mb-2"><label class="small font-weight-bold">NTN/STRN</label><input type="text" name="firms[${cIdx}][ntn]" class="form-control form-control-sm"></div>
                <div class="col-md-4"><label class="small font-weight-bold">Contact/Email</label><input type="text" name="firms[${cIdx}][contact]" class="form-control form-control-sm"></div>
                <div class="col-md-3"><label class="small font-weight-bold">Auth. Dealer</label><input type="text" name="firms[${cIdx}][dealer]" class="form-control form-control-sm"></div>
                <div class="col-md-4"><label class="small font-weight-bold">Remarks</label><input type="text" name="firms[${cIdx}][remarks]" class="form-control form-control-sm" value="Accepted"></div>
                <div class="col-md-1 d-flex align-items-end"><button type="button" class="btn btn-danger btn-sm w-100" onclick="this.parentElement.parentElement.parentElement.remove()">X</button></div>
            </div>
        </div>`;
        document.getElementById('comp-firms-area').insertAdjacentHTML('beforeend', html);
        cIdx++;
    }

    // IT Letter Row Function
    function addITRow() {
        let html = `
        <div class="it-firm-block border rounded p-3 mb-3 bg-light shadow-sm">
            <div class="row">
                <div class="col-md-4"><label class="small font-weight-bold">Firm Name</label><input type="text" name="firms[${iIdx}][name]" class="form-control form-control-sm" required></div>
                <div class="col-md-6"><label class="small font-weight-bold">Address</label><input type="text" name="firms[${iIdx}][address]" class="form-control form-control-sm"></div>
                <div class="col-md-2 d-flex align-items-end"><input type="text" name="firms[${iIdx}][tel]" class="form-control form-control-sm mr-2" placeholder="Tel"><button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.parentElement.parentElement.remove()">X</button></div>
            </div>
        </div>`;
        document.getElementById('it-firms-area').insertAdjacentHTML('beforeend', html);
        iIdx++;
    }
</script>
@endsection