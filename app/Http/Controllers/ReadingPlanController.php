<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReadingPlanController extends Controller
{
    public function index()
    {
        return view('reading-plans.index');
    }
}
