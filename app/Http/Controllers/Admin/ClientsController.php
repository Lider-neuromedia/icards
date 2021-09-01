<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;

class ClientsController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $clients = User::query()
            ->whereRole(User::ROLE_CLIENT)
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%");
                });
            })
            ->orderBy('name', 'asc')
            ->paginate(12);

        return view('admin.clients.index', compact('clients', 'search'));
    }

    public function create()
    {
        return view('admin.clients.create');
    }

    public function store(Request $request)
    {
        //
    }

    public function show(User $client)
    {
        //
    }

    public function edit(User $client)
    {
        return view('admin.clients.edit');
    }

    public function update(Request $request, User $client)
    {
        //
    }

    public function destroy(User $client)
    {
        //
    }
}
