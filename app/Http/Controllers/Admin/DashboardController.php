<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {

        $today = 0;
        $yesterday = 0;
        $thisMonth = 0;
        $lastMonth = 0;

        return view("admin.index", compact("today", "yesterday", "thisMonth", "lastMonth"));
    }
}
