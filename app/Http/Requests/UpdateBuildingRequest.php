<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBuildingRequest extends FormRequest
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
            'location_id' => ['required', 'integer', 'exists:locations,id'],
            'name_en' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('buildings', 'name_en')->where(function ($query) {
                    return $query->where('location_id', $this->location_id);
                })->ignore($this->route('building'))
            ],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'is_shared' => ['boolean'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ];
    }
}
