<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bus;
use Illuminate\Http\Request;

class BusController extends Controller
{
    public function index()
    {
        $buses = Bus::all();

        return view("admin.buses.index", compact("buses"));
    }

    public function create()
    {
        return view("admin.buses.create");
    }

    public function store(Request $request)
    {
        // Form Validation
        $request->validate([
            "bus_name" => "required",
            "model" => "required",
            "capacity" => "required|integer",
            "status" => "required|digits_between:0,1",
        ]);

        $bus = new Bus();
            $bus->fill([
                "bus_name" => $request->input("bus_name"),
                "model" => $request->input("model"),
                "capacity" => $request->input("capacity"),
                "status" => $request->input("status")
            ]);
        
        $bus->save();

        return redirect()->back()->with("success", "Bus has been inserted successfully.");
    }

    public function show(Bus $bus)
    {
        return view("admin.buses.show", compact("bus"));
    }


    public function edit(Bus $bus)
    {
        return view("admin.buses.edit", compact("bus"));
    }

 
    public function update(Request $request, Bus $bus)
    {
        // Form Validation
        $request->validate([
            "bus_name" => "required",
            "model" => "required",
            "capacity" => "required|integer",
            "status" => "required|digits_between:0,1",
        ]);

        $bus->fill([
            "bus_name" => $request->input("bus_name"),
            "model" => $request->input("model"),
            "capacity" => $request->input("capacity"),
            "status" => $request->input("status")
        ]);
        $bus->save();

        return redirect()->back()->with("success", "Bus has been updated successfully.");
    }

    public function destroy(Bus $bus)
    {
        $bus->deleteOrFail();

        return redirect()->back()->with("success", "Bus has been deleted successfully.");
    }
}
