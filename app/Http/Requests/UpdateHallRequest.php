<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHallRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('halls', 'name')->ignore($this->route('hall')),
            ],
            'rows_count' => ['required', 'integer', 'min:1', 'max:50'],
            'seats_per_rows' => ['required', 'integer', 'min:1', 'max:60'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'название зала',
            'rows_count' => 'количество рядов',
            'seats_per_rows' => 'мест в ряду',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'Зал с таким названием уже существует.',
        ];
    }
}
