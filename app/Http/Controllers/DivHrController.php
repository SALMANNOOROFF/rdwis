<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DivHrController extends Controller
{
    // Employee list page
    public function employeelist()
    {
        return view('divhr.employelist');
    }

    // Employee detail page (ID from URL)
    public function employeedetail($id)
    {
        // For now: frontend only (static data later will come from DB)
        return view('divhr.employee-details', compact('id'));
    }
}
