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
    /**
     * @var CardsService
     */
    protected $cardsService;

    public function __construct()
    {
        $this->cardsService = new CardsService();
    }

    public function index(Request $request)
    {
        return $this->cardsService->cards($request, auth()->user());
    }

    public function create()
    {
        return $this->cardsService->create(auth()->user());
    }

    public function store(CardRequest $request)
    {
        return $this->cardsService->saveOrUpdate($request, true, auth()->user());
    }

    public function edit(Card $card)
    {
        return $this->cardsService->edit(auth()->user(), $card);
    }

    public function update(CardRequest $request, Card $card)
    {
        return $this->cardsService->saveOrUpdate($request, true, auth()->user(), $card);
    }

    public function destroy(Card $card)
    {
        return $this->cardsService->destroy(auth()->user(), $card);
    }

    public function theme()
    {
        return $this->cardsService->theme(auth()->user());
    }

    public function storeTheme(ThemeRequest $request)
    {
        return $this->cardsService->storeTheme($request, auth()->user());
    }

    public function updateCardNumber(Request $request, Card $card)
    {
        return $this->cardsService->updateCardNumber($request, $card, auth()->user());
    }

    public function createMultiple()
    {
        return $this->cardsService->createMultiple(auth()->user());
    }

    public function templateMultiple()
    {
        return $this->cardsService->templateMultiple(auth()->user());
    }

    public function storeMultiple(Request $request)
    {
        return $this->cardsService->storeMultiple($request, auth()->user());
    }
}
