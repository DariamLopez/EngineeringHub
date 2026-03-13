<?php

namespace App\Http\Requests;

use App\Enums\ArtifactStatusEnum;
use App\Enums\ArtifactTypeEnum;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Http\FormRequest;

class UpdateArtifactsRequest extends FormRequest
{
    use AuthorizesRequests;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        //$this->authorize('update', $this->route('artifact'));
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

            'status' => 'sometimes|in:'. implode(',', ArtifactStatusEnum::values()),
            'owner_user_id' => 'nullable|exists:users,id',
            'completed_at' => 'nullable|date',
            'content_json' => 'sometimes|array',
        ];

        $type = $this->input('type');
        $jsonPrefix = 'content_json';
        switch ($type) {
            case ArtifactTypeEnum::STRATEGIC_ALIGNMENT->value:
                $rules["$jsonPrefix.transformation"] = 'sometimes|string';
                $rules["$jsonPrefix.supported_decisions"] = 'sometimes|array';
                $rules["$jsonPrefix.supported_decisions.*"] = 'string';
                $rules["$jsonPrefix.measurable_success"] = 'sometimes|array';
                $rules["$jsonPrefix.measurable_success.*.metric"] = 'sometimes|string';
                $rules["$jsonPrefix.measurable_success.*.target"] = 'sometimes';
                $rules["$jsonPrefix.out_of_scope"] = 'sometimes|array';
                $rules["$jsonPrefix.out_of_scope.*"] = 'string';
                break;
            case ArtifactTypeEnum::BIG_PICTURE->value:
                $rules["$jsonPrefix.ecosystem_vision"] = 'sometimes|string';
                $rules["$jsonPrefix.impacted_domains"] = 'sometimes|array';
                $rules["$jsonPrefix.impacted_domains.*"] = 'string';
                $rules["$jsonPrefix.success_definition"] = 'sometimes|string';
                break;
            case ArtifactTypeEnum::DOMAIN_BREAKDOWN->value:
                $rules["$jsonPrefix.domains"] = 'sometimes|array';
                $rules["$jsonPrefix.domains.*"] = 'integer|exists:domains,id';
                break;
            case ArtifactTypeEnum::MODULE_MATRIX->value:
                $rules["$jsonPrefix.modules_overview"] = 'sometimes|array';
                $rules["$jsonPrefix.modules_overview.*.name"] = 'sometimes|string';
                $rules["$jsonPrefix.modules_overview.*.domain"] = 'sometimes|string';
                $rules["$jsonPrefix.modules_overview.*.priority"] = 'sometimes';
                $rules["$jsonPrefix.modules_overview.*.phase"] = 'sometimes';
                break;
            case ArtifactTypeEnum::SYSTEM_ARCHITECTURE->value:
                $rules["$jsonPrefix.auth_model"] = 'sometimes|string';
                $rules["$jsonPrefix.api_style"] = 'sometimes|string';
                $rules["$jsonPrefix.data_model_notes"] = 'sometimes|string';
                $rules["$jsonPrefix.scalability_notes"] = 'sometimes|string';
                $rules["$jsonPrefix.min_validated_modules"] = 'default:3|integer';
                break;
            case ArtifactTypeEnum::PHASE_SCOPE->value:
                $rules["$jsonPrefix.included_modules"] = 'sometimes|array';
                $rules["$jsonPrefix.included_modules.*"] = 'integer';
                $rules["$jsonPrefix.excluded_items"] = 'sometimes|array';
                $rules["$jsonPrefix.excluded_items.*"] = 'string';
                $rules["$jsonPrefix.acceptance_criteria"] = 'sometimes|array';
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
