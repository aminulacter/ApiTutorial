<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
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
        $roleId = $this->route('role') ? $this->route('role')->id : null;
        
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles')->ignore($roleId)
            ],
            'display_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['exists:permissions,id']
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The role name field is required.',
            'name.max' => 'The role name may not be greater than 255 characters.',
            'name.unique' => 'The role name has already been taken.',
            'display_name.max' => 'The display name may not be greater than 255 characters.',
            'description.max' => 'The description may not be greater than 1000 characters.',
            'permissions.array' => 'The permissions must be an array.',
            'permissions.*.exists' => 'One or more selected permissions are invalid.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'role name',
            'display_name' => 'display name',
            'description' => 'description',
            'permissions' => 'permissions'
        ];
    }
}
