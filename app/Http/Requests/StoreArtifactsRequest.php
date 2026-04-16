<?php

namespace App\Http\Requests;

use App\Enums\ArtifactStatusEnum;
use App\Enums\ArtifactTypeEnum;
use Illuminate\Foundation\Http\FormRequest;

class StoreArtifactsRequest extends FormRequest

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
        $rules = [
            'type' => 'required|in:'. implode(',', ArtifactTypeEnum::values()),
            'owner_user_id' => 'nullable|exists:users,id',
            'project_id' => 'required|exists:projects,id',
            'content_json' => 'required|array',
        ];

        $type = $this->input('type');
        $jsonPrefix = 'content_json';
        switch ($type) {
            case ArtifactTypeEnum::STRATEGIC_ALIGNMENT->value:
                $rules["$jsonPrefix.transformation"] = 'required|string';
                $rules["$jsonPrefix.supported_decisions"] = 'required|array';
                $rules["$jsonPrefix.supported_decisions.*"] = 'string';
                $rules["$jsonPrefix.measurable_success"] = 'required|array';
                $rules["$jsonPrefix.measurable_success.*.metric"] = 'required|string';
                $rules["$jsonPrefix.measurable_success.*.target"] = 'required';
                $rules["$jsonPrefix.out_of_scope"] = 'required|array';
                $rules["$jsonPrefix.out_of_scope.*"] = 'string';
                break;
            case ArtifactTypeEnum::BIG_PICTURE->value:
                $rules["$jsonPrefix.ecosystem_vision"] = 'required|string';
                $rules["$jsonPrefix.impacted_domains"] = 'required|array';
                $rules["$jsonPrefix.impacted_domains.*"] = 'integer|exists:domains,id';
                $rules["$jsonPrefix.success_definition"] = 'required|string';
                break;
            case ArtifactTypeEnum::DOMAIN_BREAKDOWN->value:
                $rules["$jsonPrefix.domains"] = 'required|array';
                $rules["$jsonPrefix.domains.*"] = 'integer|exists:domains,id';
                break;
            case ArtifactTypeEnum::MODULE_MATRIX->value:
                $rules["$jsonPrefix.modules_overview"] = 'required|array';
                $rules["$jsonPrefix.modules_overview.*"] = 'integer|exists:modules,id';
                //$rules["$jsonPrefix.modules_overview.*.name"] = 'required|string';
                //$rules["$jsonPrefix.modules_overview.*.domain"] = 'required|integer|exists:domains,id';
                //$rules["$jsonPrefix.modules_overview.*.priority"] = 'required|string';
                //$rules["$jsonPrefix.modules_overview.*.phase"] = 'required|string';
                break;
            case ArtifactTypeEnum::SYSTEM_ARCHITECTURE->value:
                $rules["$jsonPrefix.auth_model"] = 'required|string';
                $rules["$jsonPrefix.api_style"] = 'required|string';
                $rules["$jsonPrefix.data_model_notes"] = 'required|string';
                $rules["$jsonPrefix.scalability_notes"] = 'required|string';
                $rules["$jsonPrefix.min_validated_modules"] = 'default:3|integer';
                break;
            case ArtifactTypeEnum::PHASE_SCOPE->value:
                $rules["$jsonPrefix.included_modules"] = 'required|array';
                $rules["$jsonPrefix.included_modules.*"] = 'integer';
                $rules["$jsonPrefix.excluded_items"] = 'required|array';
                $rules["$jsonPrefix.excluded_items.*"] = 'string';
                $rules["$jsonPrefix.acceptance_criteria"] = 'required|array';
                $rules["$jsonPrefix.acceptance_criteria.*"] = 'string';
                break;
        }
        return $rules;
    }
    /**
     * Validar que content_json solo tenga los campos permitidos para el tipo de artefacto
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $type = $this->input('type');
            $content = $this->input('content_json', []);
            $allowedFields = [];
            switch ($type) {
                case ArtifactTypeEnum::STRATEGIC_ALIGNMENT->value:
                    $allowedFields = [
                        'transformation',
                        'supported_decisions',
                        'measurable_success',
                        'out_of_scope',
                    ];
                    break;
                case ArtifactTypeEnum::BIG_PICTURE->value:
                    $allowedFields = [
                        'ecosystem_vision',
                        'impacted_domains',
                        'success_definition',
                    ];
                    break;
                case ArtifactTypeEnum::DOMAIN_BREAKDOWN->value:
                    $allowedFields = [
                        'domains',
                    ];
                    break;
                case ArtifactTypeEnum::MODULE_MATRIX->value:
                    $allowedFields = [
                        'modules_overview',
                    ];
                    break;
                case ArtifactTypeEnum::SYSTEM_ARCHITECTURE->value:
                    $allowedFields = [
                        'auth_model',
                        'api_style',
                        'data_model_notes',
                        'scalability_notes',
                        'min_validated_modules',
                    ];
                    break;
                case ArtifactTypeEnum::PHASE_SCOPE->value:
                    $allowedFields = [
                        'included_modules',
                        'excluded_items',
                        'acceptance_criteria',
                    ];
                    break;
            }
            $extraFields = array_diff(array_keys($content), $allowedFields);
            if (count($extraFields) > 0) {
                $validator->errors()->add('content_json', 'Los siguientes campos no están permitidos para el tipo seleccionado: '.implode(', ', $extraFields));
            }
        });
    }
}
