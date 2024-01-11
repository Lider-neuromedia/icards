<?php

namespace App\Http\Requests;

use App\Card;
use App\CardField;
use App\Enums\FieldType;
use App\Models\Field;
use Illuminate\Foundation\Http\FormRequest;
use App\Services\FieldService;

class CardRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $id = trim($this->get('id')) ?: null;

        if (!auth()->check()) {
            return false;
        } elseif (isUserAdmin()) {
            return true;
        } elseif ($id == null || $id == '') {
            // Si es nueva tarjeta.
            $cardsLimitReached = auth()->user()->isCardsLimitReached();
            return !$cardsLimitReached;
        }

        // Si es editar tarjeta.
        $allowedAccounts = auth()->user()
            ->allowedAccounts()
            ->get()
            ->pluck('id')
            ->toArray();

        $allowedAccounts = array_merge(
            [auth()->user()->id],
            $allowedAccounts
        );

        $canAccessCard = Card::query()
            ->whereIn('client_id', $allowedAccounts)
            ->where('id', $id)
            ->exists();

        return $canAccessCard;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $client = $this->route('client');
        if (!$client) {
            $client = auth()->user();
        }

        $groups = CardField::TEMPLATE_FIELDS;
        $validation = [];

        foreach ($groups as $group_key => $group) {
            foreach ($group['values'] as $field) {
                $field_key = $group_key . '_' . $field['key'];
                $isFieldSpecific = FieldService::isFieldSpecific($client, $group_key, $field['key']);

                if ($isFieldSpecific) {
                    if ($field_key == "others_name") {
                        $validation[$field_key] = ['required', 'string', 'max:100'];
                    } elseif ($field_key == "action_contacts_email") {
                        $validation[$field_key] = ['required', 'string', 'email', 'max:50'];
                    } elseif ($field['type'] === FieldType::IMAGE) {
                        $max = $field['max'];
                        $validation[$field_key] = ['nullable', 'file', 'mimes:jpeg,jpg,png', "max:$max"];
                    } elseif ($field['type'] === FieldType::TEXT) {
                        $validation[$field_key] = ['nullable', 'string', 'max:250'];
                    } elseif ($field['type'] === FieldType::TEXTAREA) {
                        $validation[$field_key] = ['nullable', 'string', 'max:10000'];
                    } elseif ($field['type'] === FieldType::BOOLEAN) {
                        $validation[$field_key] = ['nullable', 'string', 'in:0,1'];
                    } elseif ($field['type'] === FieldType::GRADIENT) {
                        $validation[$field_key] = ['nullable', 'array', 'min:3', 'max:3'];
                        $validation["$field_key.*"] = ['required', 'string', 'min:7', 'max:10'];
                    }
                }
            }
        }

        return $validation;
    }

    public function attributes()
    {
        return [
            'action_contacts_email' => 'E-mail de Contacto Principal',
            'others_name' => 'Nombre de Datos de Tarjeta',
        ];
    }
}
