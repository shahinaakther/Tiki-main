<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bus;
use App\Models\Route;
use App\Models\SubRoute;
use App\Models\Trip;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TripController extends Controller
{
    public function index()
    {
        $trips = Trip::with(["bus", "route"])->get();

        return view("admin.trips.index", compact("trips"));
    }

    public function create()
    {
        $buses = Bus::all()->where("status", 1);
        $routes = Route::all()->where("status", 1);

        return view("admin.trips.create", compact("buses", "routes"));
    }

    public function store(Request $request)
    {
        // Form Validation
        $request->validate([
            "bus_id" => "required|exists:buses,id",
            "route_id" => "required|exists:routes,id",
            "departure_time" => "required",
            "arrival_time" => "required",
            "days" => "required|array|in:Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday",
            "status" => "required|digits_between:0,1",
        ]);

        try {
            // Save the Trip
            $trip = new Trip();
            $trip->fill([
                "bus_id" => $request->input("bus_id"),
                "route_id" => $request->input("route_id"),
                "departure_time" => $request->input("departure_time"),
                "arrival_time" => $request->input("arrival_time"),
                "days" => implode(",", $request->input("days")),
                "status" => $request->input("status"),
            ]);
            $trip->save();

            foreach ($request->input('sub_routes') as $data) {
                if (empty($data["origin"]) || empty($data["destination"])) {
                    continue;
                }

                $subRoute = new SubRoute();
                $subRoute->fill($data);
                $subRoute->setAttribute("trip_id", $trip->__get("id"));
                $subRoute->save();
            }
        } catch (QueryException $exception) {
            return redirect()->back()->with("error", "QueryException code: " . $exception->getCode());
        }

        return redirect()->back()->with("success", "Trip has been inserted successfully.");
    }

    public function show(Trip $trip)
    {
        $buses = Bus::all()->where("status", 1);
        $routes = Route::all()->where("status", 1);
        $subRoutes = SubRoute::all()->where("trip_id", $trip->__get("id"));

        return view("admin.trips.show", compact("trip", "buses", "routes", "subRoutes"));
    }

    public function edit(Trip $trip)
    {
        $buses = Bus::all()->where("status", 1);
        $routes = Route::all()->where("status", 1);
        $subRoutes = SubRoute::all()->where("trip_id", $trip->__get("id"));

        return view("admin.trips.edit", compact("trip", "buses", "routes", "subRoutes"));
    }

    public function update(Request $request, Trip $trip)
    {
        // Form Validation
        $request->validate([
            "bus_id" => "required|exists:buses,id",
            "route_id" => "required|exists:routes,id",
            "departure_time" => "required",
            "arrival_time" => "required",
            "days" => "required|array|in:Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday",
            "status" => "required|digits_between:0,1",
        ]);

        try {
            $trip->fill([
                "bus_id" => $request->input("bus_id"),
                "route_id" => $request->input("route_id"),
                "departure_time" => $request->input("departure_time"),
                "arrival_time" => $request->input("arrival_time"),
                "days" => implode(",", $request->input("days")),
                "status" => $request->input("status"),
            ]);
            $trip->save();

            // Insert New Sub Routes
            foreach ($request->input('sub_routes') as $data) {
                if (empty($data["origin"]) || empty($data["destination"])) {
                    continue;
                }

                $subRoute = new SubRoute();
                $subRoute->fill($data);
                $subRoute->setAttribute("trip_id", $trip->__get("id"));
                $subRoute->save();
            }

            if ($request->input('exist_sub_routes')) {
                foreach ($request->input('exist_sub_routes') as $data) {
                    $subRoute = SubRoute::query()->find($data["id"]);

                    if (empty($data["origin"]) || empty($data["destination"])) {
                        if ($subRoute) {
                            $subRoute->delete();
                        }
                    } else {
                        // Update Sub Routes
                        if ($subRoute) {
                            $subRoute->update($data);
                        }
                    }
                }
            }
        } catch (QueryException $exception) {
            return redirect()->back()->with("error", "QueryException code: " . $exception->getCode());
        }

        return redirect()->back()->with("success", "Trip has been updated successfully.");
    }

    /**
     * @param Trip $trip
     * @return RedirectResponse
     */
    public function destroy(Trip $trip): RedirectResponse
    {
        $trip->deleteOrFail();

        return redirect()->back()->with("success", "Trip has been deleted successfully.");
    }
}
