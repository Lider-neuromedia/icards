<?php

namespace App\Http\Controllers;

use App\Card;
use App\CardField;
use Carbon\Carbon;

class CardsController extends Controller
{
    public function clientCard(String $client, String $card)
    {
        $card = Card::query()
            ->whereSlug($card)
            ->whereHas('client', function ($q) use ($client) {
                $q->whereSlug($client)
                    ->whereHas('subscriptions', function ($q) {
                        $now = Carbon::now()->format('Y-m-d H:i:s');
                        $q->where('finish_at', '>', $now);
                    });
            })
            ->firstOrFail();

        return $this->cardTemplate($card);
    }

    public function card(String $card)
    {
        $card = Card::query()
            ->whereSlug($card)
            ->whereHas('client', function ($q) {
                $q->whereHas('subscriptions', function ($q) {
                    $now = Carbon::now()->format('Y-m-d H:i:s');
                    $q->where('finish_at', '>', $now);
                });
            })
            ->firstOrFail();

        return $this->cardTemplate($card);
    }

    private function cardTemplate(Card $card)
    {
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

        // Llenar campos que estén vacíos.
        foreach (CardField::TEMPLATE_FIELDS as $group_key => $fields) {
            foreach ($fields['values'] as $field) {
                if ($group_key == 'others') {
                    if (!isset($ecard[$field['key']])) {
                        $ecard[$field['key']] = $field['default'];
                    }
                }
                if ($group_key == 'theme') {
                    if (!isset($theme[$field['key']])) {
                        $theme[$field['key']] = $field['default'];
                    }
                }
            }
        }

        $ecard = (Object) $ecard;
        $theme = (Object) $theme;

        return view('ecard.ecard', compact('card', 'ecard', 'theme'));
    }
}
