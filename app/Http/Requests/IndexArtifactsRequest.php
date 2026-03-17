<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexArtifactsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => 'required|exists:projects,id',
            'type' => 'sometimes|string',
            'status' => 'sometimes|string',
            'owner_user_id' => 'sometimes|integer|exists:users,id',
            'order_by' => 'sometimes|string',
            'order_dir' => 'sometimes|in:asc,desc',
            'per_page' => 'sometimes|integer|min:1',
        ];
    }
}
