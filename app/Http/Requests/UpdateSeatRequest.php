<?php

namespace App\Http\Requests;

use App\Models\Seat;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSeatRequest extends FormRequest
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
        /** @var Seat $seat */
        $seat = $this->route('seat');
        $hall = $seat->hall;

        return [
            'row_number' => ['required', 'integer', 'min:1', 'max:'.$hall->rows_count],
            'seat_number' => ['required', 'integer', 'min:1', 'max:'.$hall->seats_per_rows],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'row_number' => 'ряд',
            'seat_number' => 'место',
            'is_active' => 'доступность',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'row_number.max' => 'В зале всего :max рядов.',
            'seat_number.max' => 'В ряду всего :max мест.',
        ];
    }
}
