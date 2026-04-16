<?php

namespace App\Http\Requests;

use App\Enums\ModuleStatusEnum;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMassiveModules extends FormRequest
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
            'project_id' => 'required|exists:projects,id|integer',
            'modules' => 'required|array',
            'modules.*.id' => 'required|integer|exists:modules,id',
            'modules.*.name' => 'sometimes|string|max:255',
            'modules.*.status' => 'sometimes|in:' . implode(',', ModuleStatusEnum::values()),
            'modules.*.objective' => 'nullable|string',
            'modules.*.inputs' => 'nullable|array',
            'modules.*.data_structure' => 'nullable|array',
            'modules.*.logic_rules' => 'nullable|string',
            'modules.*.outputs' => 'nullable|array',
            'modules.*.responsibility' => 'nullable|string',
            'modules.*.failure_scenarios' => 'nullable|string',
            'modules.*.audit_trail_requirements' => 'nullable|string',
            'modules.*.dependencies' => 'nullable|array',
            'modules.*.version_note' => 'nullable|string',
            'modules.*.priority' => 'nullable|string',
            'modules.*.phase' => 'nullable|string',
            'modules.*.domain_id' => 'nullable|exists:domains,id',
        ];
    }
}
