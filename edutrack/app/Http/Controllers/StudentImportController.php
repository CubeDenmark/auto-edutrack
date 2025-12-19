<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StudentsImport;

class StudentImportController extends Controller
{
   public function import(Request $request)
{
    $request->validate([
        'class_id' => 'required|exists:classes,id',
        'students_file' => 'required|file|mimes:xlsx,xls,csv'
    ]);


    Excel::import(new StudentsImport($request->class_id), $request->file('students_file'));



    return back()->with('success', 'Students imported successfully!');
}

}
