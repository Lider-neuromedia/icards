<?php

namespace App\Http\Requests;

use App\Card;
use App\CardField;
use App\Enums\FieldType;
use App\Models\Field;
use Illuminate\Foundation\Http\FormRequest;

class CardRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $id = trim($this->get('id'));

        if (!\Auth::check()) {
            return false;
        }
        if (!\Auth::user()->isAdmin()) {
            if ($id == null || $id == '') {
                if (\Auth::user()->isCardsLimitReached()) {
                    return false;
                }
            } elseif (Card::whereId($id)->exists() && !\Auth::user()->cards()->whereId($id)->exists()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $groups = CardField::TEMPLATE_FIELDS;
        $validation = [];

        foreach ($groups as $group_key => $group) {
            foreach ($group['values'] as $field) {
                $field_key = $group_key . '_' . $field['key'];

                if ($field['general'] == Field::SPECIFIC) {
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
