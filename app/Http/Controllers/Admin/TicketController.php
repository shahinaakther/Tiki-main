<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bus;
use App\Models\Passenger;
use App\Models\Route;
use App\Models\SubRoute;
use App\Models\Ticket;
use App\Models\Trip;
use Illuminate\Http\Request;
use Throwable;

class TicketController extends Controller
{

    public function index(Request $request)
    {
        $tickets = Ticket::with(["passenger", "trip", "subRoute"]);
        $tickets->selectRaw("ticket_number, passenger_id, trip_id, sub_route_id, booking_date, GROUP_CONCAT(seat_number ORDER BY seat_number ASC) AS seat_numbers");
        $tickets->groupBy("ticket_number");

        if (!empty($request->input("route_id"))) {
            $tickets->whereHas("trip", function ($query) use ($request) {
                $query->where("route_id", $request->input("route_id"));
            });
        }

        if (!empty($request->input("booking_date"))) {
            $tickets->where("booking_date", $request->input("booking_date"));
        }


        $tickets = $tickets->get()->toArray();
        $routes = Route::all();

        return view("admin.tickets.index", compact("tickets", "routes"));
    }

    public function create()
    {
        $trips = Trip::query()
            ->where("status", 1)
            ->get();

        return view("admin.tickets.create", compact("trips"));
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
        return view("admin.tickets.show", compact("bus"));
    }

    public function edit(Bus $bus)
    {
        return view("admin.tickets.edit", compact("bus"));
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

    public function subRouteAjax(Request $request)
    {
        $trip = Trip::with(["bus", "route"])->find($request->input("trip_id"));

         $subRoutes = SubRoute::query()
             ->where("trip_id", $request->input("trip_id"))
             ->get();

         if ($trip->count()) {
             return response()->json([
                 "success" => true,
                 "data" => [
                     "trip" => $trip,
                     "subRoutes" => $subRoutes,
                 ],
             ]);
         } else {
             return response()->json([
                 "success" => false,
                 "data" => null,
             ]);
         }
    }

    public function ticketAjax(Request $request)
    {
        $trip_id = $request->input("trip_id");
//        $sub_route_id = $request->input("sub_route_id");
        $booking_time = empty($request->input("booking_time")) ? now()->toDateString() : $request->input("booking_time");
        $day = empty($request->input("day")) ? now()->day : $request->input("day");

        $tripIsAvailable = Trip::query()
            ->whereRaw("FIND_IN_SET(?, days) > 0", $day)
            ->count();

        $tickets = Ticket::query();
        $tickets->where("trip_id", $trip_id);
        $tickets->where("booking_date", $booking_time);
//        if (!empty($sub_route_id)) {
//            $tickets->where("sub_route_id", $sub_route_id);
//        }

        sleep(1);
        if ($tickets->count() && $tripIsAvailable > 0) {
            return response()->json([
                "success" => true,
                "data" => $tickets->get(),
            ]);
        } else {
            return response()->json([
                "success" => false,
                "data" => null,
            ]);
        }
    }

    public function ticketCreateAjax(Request $request)
    {
        // Passengers
        $name = $request->input("name");
        $email_or_phone = $request->input("email_or_phone");

        // Ticket
        $trip_id = $request->input("trip_id");
        $sub_route_id = $request->input("sub_route_id");
        $seat_numbers = $request->input("seat_numbers");
        $booking_date = $request->input("booking_date");

        try {
            $ticket_number = time();

            $passenger = Passenger::query()
                ->updateOrCreate(
                    ["name" => $name, "email_or_phone" => $email_or_phone],
                    ["name" => $name, "email_or_phone" => $email_or_phone]
                );

            foreach ($seat_numbers as $seat_number) {
                Ticket::query()->create([
                        "passenger_id" => $passenger->__get("id"),
                        "trip_id" => $trip_id,
                        "sub_route_id" => $sub_route_id,
                        "ticket_number" => $ticket_number,
                        "seat_number" => $seat_number,
                        "booking_date" => $booking_date,
                    ]);
            }

            $response = [
                "success" => true,
                "message" => "Ticket has been reserved successfully",
                "error" => null,
            ];
        } catch (Throwable $exception) {
            $response = [
                "success" => true,
                "message" => "Ticket has been reserved successfully",
                "error" => [
                    "code" => $exception->getCode(),
                    "message" => $exception->getMessage(),
                ],
            ];
        }
        return response()->json($response);
    }
}
