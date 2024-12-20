<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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
            'tree_space' => 'required',
            'cut_type' => 'required',
            'stack_number' => 'required',
            'log_length' => 'required',
            'average_diameter' => 'required',
            'log_count' => 'required',
            'stack_placement' => 'required',
            'property_name' => 'required',
            'volume' => 'required',
            'geo_location' => 'required|json',
            'image' => 'nullable|array',
            'image.*' => 'nullable|file|mimes:jpg,jpeg,png',
        ];
    }
}
