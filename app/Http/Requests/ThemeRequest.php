<?php

namespace App\Http\Requests;

use App\CardField;
use App\Enums\FieldType;
use Illuminate\Foundation\Http\FormRequest;

class ThemeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return \Auth::check();
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

                if ($field['general'] == true) {
                    if ($field_key == "others_name") {
                        $validation[$field_key] = ['required', 'string', 'max:100'];
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
}
