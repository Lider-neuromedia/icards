<?php

namespace App\Http\Requests;

use App\CardField;
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
                    } else if ($field['type'] === 'image') {
                        $max = $field['max'];
                        $validation[$field_key] = ['nullable', 'file', 'mimes:jpeg,jpg,png', "max:$max"];
                    } else if ($field['type'] === 'text') {
                        $validation[$field_key] = ['nullable', 'string', 'max:250'];
                    } else if ($field['type'] === 'textarea') {
                        $validation[$field_key] = ['nullable', 'string', 'max:10000'];
                    } else if ($field['type'] === 'boolean') {
                        $validation[$field_key] = ['nullable', 'string', 'in:0,1'];
                    } else if ($field['type'] === 'gradient') {
                        $validation[$field_key] = ['nullable', 'array', 'min:3', 'max:3'];
                        $validation["$field_key.*"] = ['required', 'string', 'min:7', 'max:10'];
                    }
                }
            }
        }

        return $validation;
    }
}
