<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function index()
    {
        return view("admin.auth.index");
    }


    public function login(Request $request)
    {
        $request->validate([
            "email" => "required|email",
            "password" => "required",
        ]);

        if (Auth::attempt(["email" => $request->input("email"), "password" => $request->input("password")])) {
            return redirect()->route("admin.dashboard.index");
        } else {
            return back()->with("error", "Email or Password is not valid.");
        }
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route("admin.auth.index");
    }
}
