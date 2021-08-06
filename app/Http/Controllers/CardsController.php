<?php

namespace App\Http\Controllers;

use Storage;

class CardsController extends Controller
{
    public function card(String $card)
    {
        if (!Storage::exists("cards/$card.json")) {
            return abort(404);
        }

        $data = json_decode(Storage::get("cards/$card.json"));
        $ecard = $data->fields;

        return view('ecard.ecard', compact('card', 'ecard'));
    }
}
