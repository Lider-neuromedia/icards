<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if (auth()->check()) {
            if (auth()->user()->isClient()) {
                return redirect('/clients/cards');
            }
            if (auth()->user()->isAdmin()) {
                return redirect('/admin/clients');
            }
        }
        return view('home');
    }
}
