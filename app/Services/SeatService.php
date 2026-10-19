<?php

namespace App\Services;

use App\Models\Hall;
use App\Models\Seat;
use Illuminate\Validation\ValidationException;

/**
 * Manual seat management inside an already generated hall grid.
 */
class SeatService
{
    /**
     * @param  array{row_number: int, seat_number: int, is_active?: bool}  $data
     *
     * @throws ValidationException
     */
    public function create(Hall $hall, array $data): Seat
    {
        $row = (int) $data['row_number'];
        $number = (int) $data['seat_number'];

        $this->ensurePositionIsFree($hall, $row, $number);

        return $hall->seats()->create([
            'row_number' => $row,
            'seat_number' => $number,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);
    }

    /**
     * @param  array{row_number: int, seat_number: int, is_active?: bool}  $data
     *
     * @throws ValidationException
     */
    public function update(Seat $seat, array $data): Seat
    {
        $row = (int) $data['row_number'];
        $number = (int) $data['seat_number'];

        $this->ensurePositionIsFree($seat->hall, $row, $number, $seat->id);

        $seat->update([
            'row_number' => $row,
            'seat_number' => $number,
            'is_active' => array_key_exists('is_active', $data)
                ? (bool) $data['is_active']
                : $seat->is_active,
        ]);

        return $seat;
    }

    /**
     * Delete a seat that is not part of any booking.
     *
     * @throws ValidationException
     */
    public function delete(Seat $seat): void
    {
        if ($seat->bookingSeats()->exists()) {
            throw ValidationException::withMessages([
                'seat' => 'Место участвует в бронировании: его можно только отключить.',
            ]);
        }

        $seat->delete();
    }

    /**
     * @throws ValidationException
     */
    private function ensurePositionIsFree(Hall $hall, int $row, int $number, ?int $ignoreId = null): void
    {
        $exists = $hall->seats()
            ->where('row_number', $row)
            ->where('seat_number', $number)
            ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'row_number' => "В ряду {$row} уже есть место {$number}.",
            ]);
        }
    }
}
