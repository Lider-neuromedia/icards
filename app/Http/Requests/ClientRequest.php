<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $validation = [
            'name' => ['required', 'string', 'max:100', 'min:5'],
            'email' => ['required', 'string', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'start_at' => ['required', "date_format:Y-m-d\TH:i"],
            'finish_at' => ['required', "date_format:Y-m-d\TH:i", 'after:start_at'],
            'cards' => ['required', 'integer', 'min:1'],
            'seller_id' => ['required', 'integer', 'exists:sellers,id'],
            'allowed_accounts' => ['nullable', 'array'],
            'allowed_accounts.*' => ['required', 'integer', 'exists:users,id'],
        ];

        if ($this->has('id')) {
            $id = $this->get('id');
            $validation['email'] = ['required', 'string', 'email', "unique:users,email,$id"];
            $validation['password'] = ['nullable', 'string', 'min:8', 'confirmed'];
        }

        return $validation;
    }
}
