<?php

namespace App\Http\Requests;

use App\Enums\ModuleStatusEnum;
use Illuminate\Foundation\Http\FormRequest;

class UpdateModulesRequest extends FormRequest
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
            'name' => 'sometimes|string|max:255',
            'status' => 'sometimes|in:' . implode(',', ModuleStatusEnum::values()),
            'objective' => 'nullable|string',
            'inputs' => 'nullable|array',
            'data_structure' => 'nullable|array',
            'logic_rules' => 'nullable|string',
            'outputs' => 'nullable|array',
            'responsibility' => 'nullable|string',
            'failure_scenarios' => 'nullable|string',
            'audit_trail_requirements' => 'nullable|string',
            'dependencies' => 'nullable|array',
            'version_note' => 'nullable|string',
        ];
    }
}
