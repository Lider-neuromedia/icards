<?php

namespace App\Http\Controllers\Clients;

use App\Card;
use App\Http\Controllers\Controller;
use App\Http\Requests\CardRequest;
use App\Http\Requests\ThemeRequest;
use App\Services\CardsService;
use Illuminate\Http\Request;

class CardsController extends Controller
{
    public function index(Request $request)
    {
        return (new CardsService())->cards($request, auth()->user());
    }

    public function create(Request $request)
    {
        return (new CardsService())->create($request, auth()->user());
    }

    public function store(CardRequest $request)
    {
        return (new CardsService())->saveOrUpdate($request, true, auth()->user());
    }

    public function edit(Request $request, Card $card)
    {
        return (new CardsService())->edit($request, auth()->user(), $card);
    }

    public function update(CardRequest $request, Card $card)
    {
        return (new CardsService())->saveOrUpdate($request, true, auth()->user(), $card);
    }

    public function destroy(Card $card)
    {
        return (new CardsService())->destroy(auth()->user(), $card);
    }

    public function theme(Request $request)
    {
        return (new CardsService())->theme($request, auth()->user());
    }

    public function storeTheme(ThemeRequest $request)
    {
        return (new CardsService())->storeTheme($request, auth()->user());
    }

    public function updateCardNumber(Request $request, Card $card)
    {
        return (new CardsService())->updateCardNumber($request, $card, auth()->user());
    }

    public function createMultiple(Request $request)
    {
        return (new CardsService())->createMultiple($request, auth()->user());
    }

    public function templateMultiple(Request $request)
    {
        return (new CardsService())->templateMultiple(auth()->user());
    }

    public function storeMultiple(Request $request)
    {
        return (new CardsService())->storeMultiple($request, auth()->user());
    }
}
