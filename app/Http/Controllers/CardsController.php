<?php

namespace App\Http\Controllers;

use App\Card;
use App\CardField;
use Carbon\Carbon;

class CardsController extends Controller
{
    public function card(String $card)
    {
        $card = Card::query()
            ->whereSlug($card)
            ->whereHas('client', function ($q) {
                $q->whereHas('subscriptions', function ($q) {
                    $q->where('finish_at', '>', Carbon::now()->format('Y-m-d H:i:s'));
                });
            })
            ->firstOrFail();

        $template = $card->fields()->get()->groupBy('group');

        $ecard = [
            CardField::GROUP_ACTION_CONTACTS => [],
            CardField::GROUP_CONTACT_LIST => [],
            CardField::GROUP_SOCIAL_LIST => [],
        ];
        $theme = [];

        foreach ($template[CardField::GROUP_OTHERS] as $field) {
            $ecard[$field->key] = $field->value;
        }
        foreach ($template[CardField::GROUP_THEME] as $field) {
            $theme[$field->key] = $field->value;
        }
        foreach ($template[CardField::GROUP_ACTION_CONTACTS] as $field) {
            $ecard[CardField::GROUP_ACTION_CONTACTS][] = (Object) [$field->key => $field->value];
        }
        foreach ($template[CardField::GROUP_CONTACT_LIST] as $field) {
            $ecard[CardField::GROUP_CONTACT_LIST][] = (Object) [$field->key => $field->value];
        }
        foreach ($template[CardField::GROUP_SOCIAL_LIST] as $field) {
            $ecard[CardField::GROUP_SOCIAL_LIST][] = (Object) [$field->key => $field->value];
        }

        $ecard = (Object) $ecard;
        $theme = (Object) $theme;

        return view('ecard.ecard', compact('card', 'ecard', 'theme'));
    }
}
