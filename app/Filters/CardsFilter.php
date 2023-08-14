<?php

namespace App\Filters;

use App\Filters\CardsFilterListsDTO;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Authenticatable;
use stdClass;

class CardsFilter
{
    // Filters
    public $search;
    public $account;

    // Filters Lists
    public $accounts;
    public $accounts_ids;

    /**
     * @param Request $request
     * @param User|Authenticatable $authUser
     */
    public function __construct(Request $request, User $authUser)
    {
        $this->search = $request->get('search') ?: null;
        $this->account = $request->get('account') ?: null;

        $this->accounts = $authUser->allowedAccounts()
            ->orderBy('name', 'asc')
            ->get()
            ->map(function ($x) {
                $option = new stdClass();
                $option->id = $x->id;
                $option->name = $x->name;
                return $option;
            });
        $this->accounts_ids = $this->accounts->pluck('id');
    }

    public function getFilters(): stdClass
    {
        $filters = new stdClass();
        $filters->search = $this->search;
        $filters->account = $this->account;
        return $filters;
    }

    public function getFiltersLists(): stdClass
    {
        $filtersLists = new stdClass();
        $filtersLists->accounts = $this->accounts;
        $filtersLists->accounts_ids = $this->accounts_ids;
        return $filtersLists;
    }

    /**
     * Obtener cuenta de cliente seleccionada
     * entre las cuentas habilitadas.
     *
     * @return User|Authenticatable
     */
    public function selectedAccount(): User
    {
        $client = User::query()
            ->whereIn('id', $this->accounts_ids)
            ->where('id', $this->account)
            ->firstOrFail();
        return $client;
    }
}
