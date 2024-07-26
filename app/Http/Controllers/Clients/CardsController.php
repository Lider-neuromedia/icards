<?php

namespace App\Http\Controllers\Clients;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\CardRequest;
use App\Http\Requests\ThemeRequest;
use App\Services\CardsService;
use App\Card;

class CardsController extends Controller
{
    public function index(Request $request, CardsService $cardsService)
    {
        return $cardsService->cards($request, auth()->user());
    }

    public function create(Request $request, CardsService $cardsService)
    {
        return $cardsService->create($request, auth()->user());
    }

    public function store(CardRequest $request, CardsService $cardsService)
    {
        return $cardsService->saveOrUpdate($request, true, auth()->user());
    }

    public function edit(Request $request, Card $card, CardsService $cardsService)
    {
        return $cardsService->edit($request, auth()->user(), $card);
    }

    public function update(CardRequest $request, Card $card, CardsService $cardsService)
    {
        return $cardsService->saveOrUpdate($request, true, auth()->user(), $card);
    }

    public function destroy(Card $card, CardsService $cardsService)
    {
        return $cardsService->destroy(auth()->user(), $card);
    }

    public function theme(Request $request, CardsService $cardsService)
    {
        return $cardsService->theme($request, auth()->user());
    }

    public function storeTheme(ThemeRequest $request, CardsService $cardsService)
    {
        return $cardsService->storeTheme($request, auth()->user());
    }

    public function updateCardNumber(Request $request, Card $card, CardsService $cardsService)
    {
        return $cardsService->updateCardNumber($request, $card, auth()->user());
    }

    public function createMultiple(Request $request, CardsService $cardsService)
    {
        return $cardsService->createMultiple($request, auth()->user());
    }

    public function templateMultiple(Request $request, CardsService $cardsService)
    {
        return $cardsService->templateMultiple(auth()->user());
    }

    public function storeMultiple(Request $request, CardsService $cardsService)
    {
        return $cardsService->storeMultiple($request, auth()->user());
    }
}
