<?php

namespace App\Http\Controllers;

use App\User;

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

    public function index()
    {
        if (auth()->check()) {
            /** @var User */
            $authUser = auth()->user();

            if ($authUser->isClient()) {
                return redirect('/clients/cards');
            }
            if ($authUser->isAdmin()) {
                return redirect('/admin/clients');
            }
        }
        return view('home');
    }
}
