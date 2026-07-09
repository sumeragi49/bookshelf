<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        $reviewStat = DB::table('reviews')
                   -> selectRaw('
                   COUNT(*) as total_reviews,
                   AVG(rating) as average_rating,
                   SUM(rating) as_total_rating,
                   ')

        return view('reports.index');
    }
}
