<?php

namespace App\Http\Controllers;

use App\Card;
use App\CardField;
use Carbon\Carbon;
use \Parsedown;

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

    private function markdown($content)
    {
        $parsedown = new Parsedown();
        $parsedown->setBreaksEnabled(true);
        $parsedown->setSafeMode(true);
        return $parsedown->text($content);
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

        $whatsapp_message = $card->field(CardField::GROUP_ACTION_CONTACTS, 'whatsapp_message');
        $whatsapp_message = trim($whatsapp_message) != '' ? rawurlencode($whatsapp_message) : '';
        $ecard['whatsapp_message'] = $whatsapp_message ?: 'Hola, en que te puedo ayudar';

        if (isset($template[CardField::GROUP_OTHERS])) {
            foreach ($template[CardField::GROUP_OTHERS] as $field) {
                $value = $field->value;
                $isJson = $field->type == 'gradient';
                $isMarkdown = $field->type == 'textarea';

                $ecard[$field->key] = $value;

                if ($isJson) {
                    $ecard[$field->key] = json_decode($value);
                } else if ($isMarkdown) {
                    $ecard[$field->key] = $this->markdown($value);
                }
            }
        }
        if (isset($template[CardField::GROUP_THEME])) {
            foreach ($template[CardField::GROUP_THEME] as $field) {
                $value = $field->value;
                $isJson = $field->type == 'gradient';
                $theme[$field->key] = $isJson ? json_decode($value) : $value;
            }
        }
        if (isset($template[CardField::GROUP_ACTION_CONTACTS])) {
            foreach ($template[CardField::GROUP_ACTION_CONTACTS] as $field) {
                $value = $field->value;
                $isJson = $field->type == 'gradient';
                $ecard[CardField::GROUP_ACTION_CONTACTS][] = (Object) [
                    $field->key => $isJson ? json_decode($value) : $value,
                ];
            }
        }
        if (isset($template[CardField::GROUP_CONTACT_LIST])) {
            foreach ($template[CardField::GROUP_CONTACT_LIST] as $field) {
                $value = $field->value;
                $isJson = $field->type == 'gradient';
                $isMarkdown = $field->type == 'textarea';

                $tempValue = $value;

                if ($isJson) {
                    $tempValue = json_decode($value);
                } else if ($isMarkdown) {
                    $tempValue = $this->markdown($value);
                }

                $ecard[CardField::GROUP_CONTACT_LIST][] = (Object) [
                    $field->key => $tempValue,
                ];
            }
        }
        if (isset($template[CardField::GROUP_SOCIAL_LIST])) {
            foreach ($template[CardField::GROUP_SOCIAL_LIST] as $field) {
                $value = $field->value;
                $isJson = $field->type == 'gradient';
                $ecard[CardField::GROUP_SOCIAL_LIST][] = (Object) [
                    $field->key => $isJson ? json_decode($value) : $value,
                ];
            }
        }

        // Llenar campos que estén vacíos.
        foreach (CardField::TEMPLATE_FIELDS as $group_key => $fields) {
            foreach ($fields['values'] as $field) {
                $isJson = $field['type'] == 'gradient';
                $value = $field['default'];

                if ($group_key == 'others') {
                    if (!isset($ecard[$field['key']])) {
                        $ecard[$field['key']] = $isJson ? json_decode($value) : $value;
                    }
                }
                if ($group_key == 'theme') {
                    if (!isset($theme[$field['key']])) {
                        $theme[$field['key']] = $isJson ? json_decode($value) : $value;
                    }
                }
            }
        }

        $ecard = (Object) $ecard;
        $theme = (Object) $theme;

        $templateFiles = (Object) collect(CardField::TEMPLATES)
            ->first(function ($x) use ($theme) {
                return $x['id'] == $theme->template;
            });

        return view($templateFiles->templatePath, compact('card', 'ecard', 'theme', 'templateFiles'));
    }
}
