<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMovieSessionRequest extends FormRequest
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
            'movie_id' => ['required', 'integer', Rule::exists('movies', 'id')],
            'hall_id' => ['required', 'integer', Rule::exists('halls', 'id')],
            'date' => ['required', 'date_format:Y-m-d'],
            'time' => ['required', 'date_format:H:i'],
            'price' => ['required', 'numeric', 'min:0', 'max:100000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'movie_id' => 'фильм',
            'hall_id' => 'зал',
            'date' => 'дата',
            'time' => 'время начала',
            'price' => 'цена',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'date.date_format' => 'Укажите дату в формате ГГГГ-ММ-ДД.',
            'time.date_format' => 'Укажите время в формате ЧЧ:ММ.',
            'price.min' => 'Цена не может быть отрицательной.',
        ];
    }
}
