<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMassiveDomainRequest extends FormRequest
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
            'project_id' => 'required|integer|exists:projects,id',
            'domains' => 'required|array',
            'domains.*.name' => 'required|string|max:255',
            'domains.*.objective' => 'nullable|string',
            'domains.*.owner_user_id' => 'nullable|integer|exists:users,id',
            'domains.*.description' => 'nullable|string',
        ];
    }
}
