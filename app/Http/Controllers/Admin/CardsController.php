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
    public function index(Request $request, User $client)
    {
        return (new CardsService())->cards($request, $client);
    }

    public function create(Request $request, User $client)
    {
        return (new CardsService())->create($request, $client);
    }

    public function store(CardRequest $request, User $client)
    {
        return (new CardsService())->saveOrUpdate($request, true, $client);
    }

    public function edit(Request $request, User $client, Card $card)
    {
        return (new CardsService())->edit($request, $client, $card);
    }

    public function update(CardRequest $request, User $client, Card $card)
    {
        return (new CardsService())->saveOrUpdate($request, true, $client, $card);
    }

    public function destroy(User $client, Card $card)
    {
        return (new CardsService())->destroy($client, $card);
    }

    public function theme(Request $request, User $client)
    {
        return (new CardsService())->theme($request, $client);
    }

    public function storeTheme(ThemeRequest $request, User $client)
    {
        return (new CardsService())->storeTheme($request, $client);
    }

    public function updateCardNumber(Request $request, Card $card)
    {
        return (new CardsService())->updateCardNumber($request, $card, $card->client);
    }

    public function createMultiple(Request $request, User $client)
    {
        return (new CardsService())->createMultiple($request, $client);
    }

    public function templateMultiple(Request $request, User $client)
    {
        return (new CardsService())->templateMultiple($client);
    }

    public function storeMultiple(Request $request, User $client)
    {
        return (new CardsService())->storeMultiple($request, $client);
    }
}
