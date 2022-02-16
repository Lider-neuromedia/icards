<?php

namespace App\Http\Requests;

use App\Card;
use App\CardField;
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
            } else if (Card::whereId($id)->exists() && !\Auth::user()->cards()->whereId($id)->exists()) {
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

                if ($field['general'] == false) {
                    if ($field_key == "others_name") {
                        $validation[$field_key] = ['required', 'string', 'max:100'];
                    } else if ($field['type'] === 'image') {
                        $validation[$field_key] = ['nullable', 'file', 'mimes:jpeg,jpg,png', 'max:900'];
                    } else if ($field['type'] === 'text') {
                        $validation[$field_key] = ['nullable', 'string', 'max:250'];
                    } else if ($field['type'] === 'textarea') {
                        $validation[$field_key] = ['nullable', 'string', 'max:10000'];
                    }
                }
            }
        }

        return $validation;
    }
}
