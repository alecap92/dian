<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ConfigurationEnvironmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'type_environment_id' => 'nullable|exists:type_environments,id',
            'payroll_type_environment_id' => 'nullable|exists:type_environments,id',
            'eqdocs_type_environment_id' => 'nullable|exists:type_environments,id',
        ];
    }
}
