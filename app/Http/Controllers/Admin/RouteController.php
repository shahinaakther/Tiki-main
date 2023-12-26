<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Route;
use Illuminate\Http\Request;

class RouteController extends Controller
{
    public function index()
    {
        $routes = Route::all();

        return view("admin.routes.index", compact("routes"));
    }

    public function create()
    {
        return view("admin.routes.create");
    }


    public function store(Request $request)
    {
        // Form Validation
        $request->validate([
            "origin" => "required",
            "destination" => "required",
            "distance" => "nullable|numeric",
            "status" => "required|digits_between:0,1",
        ]);

        $route = new Route();
            $route->fill([
                "origin" => $request->input("origin"),
                "destination" => $request->input("destination"),
                "distance" => $request->input("distance"),
                "status" => $request->input("status")
            ]);

        $route->save();

        return redirect()->back()->with("success", "Route has been inserted successfully.");
    }

    public function show(Route $route)
    {
        return view("admin.routes.show", compact("route"));
    }

    public function edit(Route $route)
    {
        return view("admin.routes.edit", compact("route"));
    }

    public function update(Request $request, Route $route)
    {
        // Form Validation
        $request->validate([
            "origin" => "required",
            "destination" => "required",
            "distance" => "nullable|numeric",
            "status" => "required|digits_between:0,1",
        ]);

        $route->fill([
            "origin" => $request->input("origin"),
            "destination" => $request->input("destination"),
            "distance" => $request->input("distance"),
            "status" => $request->input("status")
        ]);
        $route->save();

        return redirect()->back()->with("success", "Route has been updated successfully.");
    }


    public function destroy(Route $route)
    {
        $route->deleteOrFail();

        return redirect()->back()->with("success", "Bus has been deleted successfully.");
    }
}
