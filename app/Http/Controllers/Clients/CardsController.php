<?php

namespace App\Http\Controllers\Clients;

use App\Card;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CardsController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $cards = \Auth::user()
            ->cards()
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('slug', 'like', "%$search%")
                        ->orWhereHas('fields', function ($q) use ($search) {
                            $q->where('group', 'others')
                                ->where('key', 'name')
                                ->where('value', 'like', "%$search%");
                        });
                });
            })
            ->orderBy('slug', 'asc')
            ->paginate(12);

        return view('clients.cards.index', compact('cards', 'search'));
    }

    public function create()
    {
        return view('clients.cards.create');
    }

    public function store(Request $request)
    {
        //
    }

    public function show(Card $card)
    {
        //
    }

    public function edit(Card $card)
    {
        return view('clients.cards.edit');
    }

    public function update(Request $request, Card $card)
    {
        //
    }

    public function destroy(Card $card)
    {
        //
    }
}
