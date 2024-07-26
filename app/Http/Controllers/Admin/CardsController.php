<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\CardRequest;
use App\Http\Requests\ThemeRequest;
use App\Services\CardsService;
use App\Card;
use App\User;

class CardsController extends Controller
{
    public function index(Request $request, User $client, CardsService $cardsService)
    {
        return $cardsService->cards($request, $client);
    }

    public function create(Request $request, User $client, CardsService $cardsService)
    {
        return $cardsService->create($request, $client);
    }

    public function store(CardRequest $request, User $client, CardsService $cardsService)
    {
        return $cardsService->saveOrUpdate($request, true, $client);
    }

    public function edit(Request $request, User $client, Card $card, CardsService $cardsService)
    {
        return $cardsService->edit($request, $client, $card);
    }

    public function update(CardRequest $request, User $client, Card $card, CardsService $cardsService)
    {
        return $cardsService->saveOrUpdate($request, true, $client, $card);
    }

    public function destroy(User $client, Card $card, CardsService $cardsService)
    {
        return $cardsService->destroy($client, $card);
    }

    public function theme(Request $request, User $client, CardsService $cardsService)
    {
        return $cardsService->theme($request, $client);
    }

    public function storeTheme(ThemeRequest $request, User $client, CardsService $cardsService)
    {
        return $cardsService->storeTheme($request, $client);
    }

    public function updateCardNumber(Request $request, Card $card, CardsService $cardsService)
    {
        return $cardsService->updateCardNumber($request, $card, $card->client);
    }

    public function createMultiple(Request $request, User $client, CardsService $cardsService)
    {
        return $cardsService->createMultiple($request, $client);
    }

    public function templateMultiple(Request $request, User $client, CardsService $cardsService)
    {
        return $cardsService->templateMultiple($client);
    }

    public function storeMultiple(Request $request, User $client, CardsService $cardsService)
    {
        return $cardsService->storeMultiple($request, $client);
    }
}
