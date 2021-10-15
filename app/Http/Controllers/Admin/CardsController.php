<?php

namespace App\Http\Controllers\Admin;

use App\Card;
use App\Http\Controllers\Controller;
use App\Http\Requests\CardRequest;
use App\Http\Requests\ThemeRequest;
use App\Services\CardsService;
use App\User;
use Illuminate\Http\Request;

class CardsController extends Controller
{
    protected $cardsService;

    public function __construct()
    {
        $this->cardsService = new CardsService();
    }

    public function index(Request $request, User $client)
    {
        return $this->cardsService->cards($request, $client);
    }

    public function create(User $client)
    {
        return $this->cardsService->create($client);
    }

    public function store(CardRequest $request, User $client)
    {
        return $this->cardsService->saveOrUpdate($request, $client);
    }

    public function edit(User $client, Card $card)
    {
        return $this->cardsService->edit($client, $card);
    }

    public function update(CardRequest $request, User $client, Card $card)
    {
        return $this->cardsService->saveOrUpdate($request, $client, $card);
    }

    public function destroy(User $client, Card $card)
    {
        return $this->cardsService->destroy($client, $card);
    }

    public function theme(User $client)
    {
        return $this->cardsService->theme($client);
    }

    public function storeTheme(ThemeRequest $request, User $client)
    {
        return $this->cardsService->storeTheme($request, $client);
    }
}
