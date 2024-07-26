<?php

namespace App\Http\Controllers;

use App\Enums\GroupField;
use App\Card;
use App\CardField;
use Parsedown;

class CardsController extends Controller
{
    public function clientCard(String $client, String $card)
    {
        $card = Card::query()
            ->when(!is_numeric($card), function ($q) use ($card) {
                $q->where('slug', $card);
            })
            ->when(is_numeric($card), function ($q) use ($card) {
                $q->where('slug_number', $card);
            })
            ->whereHas('client', function ($q) use ($client) {
                $q->whereSlug($client)
                    ->whereHas('subscriptions', function ($q) {
                        $now = now()->format('Y-m-d H:i:s');
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
                    $now = now()->format('Y-m-d H:i:s');
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
            GroupField::ACTION_CONTACTS => [],
            GroupField::CONTACT_LIST => [],
            GroupField::SOCIAL_LIST => [],
        ];
        $theme = [];

        $whatsapp_message = $card->field(GroupField::ACTION_CONTACTS, 'whatsapp_message');
        $whatsapp_message = trim($whatsapp_message) != '' ? rawurlencode($whatsapp_message) : '';
        $ecard['whatsapp_message'] = $whatsapp_message ?: 'Hola, en que te puedo ayudar';

        if (isset($template[GroupField::OTHERS])) {
            foreach ($template[GroupField::OTHERS] as $field) {
                $value = $field->value;
                $isJson = $field->type == 'gradient';
                $isMarkdown = $field->type == 'textarea';

                $ecard[$field->key] = $value;

                if ($isJson) {
                    $ecard[$field->key] = json_decode($value);
                } elseif ($isMarkdown) {
                    $ecard[$field->key] = $this->markdown($value);
                }
            }
        }

        if (isset($template[GroupField::THEME])) {
            foreach ($template[GroupField::THEME] as $field) {
                $value = $field->value;
                $isJson = $field->type == 'gradient';
                $theme[$field->key] = $isJson ? json_decode($value) : $value;
            }
        }
        if (isset($template[GroupField::ACTION_CONTACTS])) {
            foreach ($template[GroupField::ACTION_CONTACTS] as $field) {
                $value = $field->value;
                $isJson = $field->type == 'gradient';
                $ecard[GroupField::ACTION_CONTACTS][] = (object) [
                    $field->key => $isJson ? json_decode($value) : $value,
                ];
            }
        }
        if (isset($template[GroupField::CONTACT_LIST])) {
            foreach ($template[GroupField::CONTACT_LIST] as $field) {
                $value = $field->value;
                $isJson = $field->type == 'gradient';
                $isMarkdown = $field->type == 'textarea';

                $tempValue = $value;

                if ($isJson) {
                    $tempValue = json_decode($value);
                } elseif ($isMarkdown) {
                    $tempValue = $this->markdown($value);
                }

                $ecard[GroupField::CONTACT_LIST][] = (object) [
                    $field->key => $tempValue,
                ];
            }
        }
        if (isset($template[GroupField::SOCIAL_LIST])) {
            foreach ($template[GroupField::SOCIAL_LIST] as $field) {
                $value = $field->value;
                $isJson = $field->type == 'gradient';
                $ecard[GroupField::SOCIAL_LIST][] = (object) [
                    $field->key => $isJson ? json_decode($value) : $value,
                ];
            }
        }

        // Llenar campos que estén vacíos.
        foreach (CardField::TEMPLATE_FIELDS as $group_key => $fields) {
            foreach ($fields['values'] as $field) {
                $isJson = $field['type'] == 'gradient';
                $value = $field['default'];

                if ($group_key == GroupField::OTHERS) {
                    if (!isset($ecard[$field['key']])) {
                        $ecard[$field['key']] = $isJson ? json_decode($value) : $value;
                    }
                }
                if ($group_key == GroupField::THEME) {
                    if (!isset($theme[$field['key']])) {
                        $theme[$field['key']] = $isJson ? json_decode($value) : $value;
                    }
                }
            }
        }

        $ecard = (object) $ecard;
        $theme = (object) $theme;

        $templateFiles = (object) collect(CardField::TEMPLATES)
            ->first(function ($x) use ($theme) {
                return $x['id'] == $theme->template;
            });

        app()->setLocale($ecard->default_lang);

        return view($templateFiles->templatePath, compact('card', 'ecard', 'theme', 'templateFiles'));
    }
}
