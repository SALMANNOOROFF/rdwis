<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function index() {
        return view('purchase.reports.index');
    }

    public function generateComparative(Request $request) {
        return view('purchase.reports.comparative_pdf', $request->all());
    }

    public function generateITLetter(Request $request) {
        return view('purchase.reports.it_letter_pdf', $request->all());
    }
}