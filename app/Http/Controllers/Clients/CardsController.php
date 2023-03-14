<?php

namespace App\Http\Controllers\Clients;

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

    public function index(Request $request)
    {
        return $this->cardsService->cards($request, \Auth::user());
    }

    public function create()
    {
        return $this->cardsService->create(\Auth::user());
    }

    public function store(CardRequest $request)
    {
        return $this->cardsService->saveOrUpdate($request, true, \Auth::user());
    }

    public function edit(Card $card)
    {
        return $this->cardsService->edit(\Auth::user(), $card);
    }

    public function update(CardRequest $request, Card $card)
    {
        return $this->cardsService->saveOrUpdate($request, true, \Auth::user(), $card);
    }

    public function destroy(Card $card)
    {
        return $this->cardsService->destroy(\Auth::user(), $card);
    }

    public function theme()
    {
        return $this->cardsService->theme(\Auth::user());
    }

    public function storeTheme(ThemeRequest $request)
    {
        return $this->cardsService->storeTheme($request, \Auth::user());
    }

    public function updateCardNumber(Request $request, Card $card)
    {
        return $this->cardsService->updateCardNumber($request, $card, \Auth::user());
    }

    public function createMultiple()
    {
        return $this->cardsService->createMultiple(\Auth::user());
    }

    public function templateMultiple()
    {
        return $this->cardsService->templateMultiple(\Auth::user());
    }

    public function storeMultiple(Request $request)
    {
        return $this->cardsService->storeMultiple($request, \Auth::user());
    }
}
