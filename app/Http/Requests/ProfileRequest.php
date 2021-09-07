<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
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
        $id = \Auth::user()->id;

        $validation = [
            'name' => ['required', 'string', 'max:100', 'min:5'],
            'email' => ['required', 'string', 'email', "unique:users,email,$id"],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];

        return $validation;
    }
}
