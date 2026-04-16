<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMassiveDomains extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'domains' => 'required|array',
            'domains.*.id' => 'required|integer|exists:domains,id',
            'domains.*.name' => 'required|string',
            'domains.*.objective' => 'nullable|string',
            'domains.*.description' => 'nullable|string',
            'domains.*.owner_user_id' => 'nullable|integer|exists:users,id',
        ];
    }
}
