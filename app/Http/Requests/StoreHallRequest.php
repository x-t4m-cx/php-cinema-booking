<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHallRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:100', Rule::unique('halls', 'name')],
            'rows_count' => ['required', 'integer', 'min:1', 'max:50'],
            'seats_per_rows' => ['required', 'integer', 'min:1', 'max:60'],
            'is_active' => ['sometimes', 'boolean'],
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
            'rows_count.max' => 'Максимум 50 рядов.',
            'seats_per_rows.max' => 'Максимум 60 мест в ряду.',
        ];
    }
}
