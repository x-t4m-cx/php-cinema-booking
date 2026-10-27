<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Hall;
use App\Models\Seat;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Hall lifecycle: the seat grid is always kept in sync with the hall size.
 */
class HallService
{
    /**
     * Create a hall together with its full seat grid.
     *
     * @param  array{name: string, rows_count: int, seats_per_rows: int, is_active?: bool}  $data
     */
    public function create(array $data): Hall
    {
        return DB::transaction(function () use ($data): Hall {
            $hall = Hall::create($data);
            $this->createMissingSeats($hall);

            return $hall;
        });
    }

    /**
     * Update a hall and resize its seat grid.
     *
     * @param  array{name: string, rows_count: int, seats_per_rows: int, is_active?: bool}  $data
     */
    public function update(Hall $hall, array $data): Hall
    {
        return DB::transaction(function () use ($hall, $data): Hall {
            $hall->update($data);
            $hall->refresh();

            $this->createMissingSeats($hall);
            $this->dropSeatsOutsideGrid($hall);

            return $hall;
        });
    }

    /**
     * Delete a hall that is not used by any booking yet.
     *
     * @throws ValidationException
     */
    public function delete(Hall $hall): void
    {
        if ($hall->bookings()->exists()) {
            throw ValidationException::withMessages([
                'hall' => 'Зал нельзя удалить: по его сеансам уже есть бронирования.',
            ]);
        }

        $hall->delete();
    }

    /**
     * Create the seats that are missing for the current hall size.
     *
     * @return int number of created seats
     */
    public function createMissingSeats(Hall $hall): int
    {
        $existing = $hall->seats()
            ->get(['row_number', 'seat_number'])
            ->mapWithKeys(fn (Seat $seat): array => [$seat->row_number.':'.$seat->seat_number => true]);

        $now = now();
        $rows = [];

        for ($row = 1; $row <= (int) $hall->rows_count; $row++) {
            for ($number = 1; $number <= (int) $hall->seats_per_rows; $number++) {
                if ($existing->has($row.':'.$number)) {
                    continue;
                }

                $rows[] = [
                    'hall_id' => $hall->id,
                    'row_number' => $row,
                    'seat_number' => $number,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            Seat::insert($chunk);
        }

        return count($rows);
    }

    /**
     * Remove (or deactivate) seats that no longer fit into the hall grid.
     *
     * Seats that already take part in a booking are only deactivated, so the
     * booking history stays readable.
     *
     * @return array{deleted: int, deactivated: int}
     */
    public function dropSeatsOutsideGrid(Hall $hall): array
    {
        $seats = $hall->seats()
            ->where(function ($query) use ($hall): void {
                $query->where('row_number', '>', $hall->rows_count)
                    ->orWhere('seat_number', '>', $hall->seats_per_rows);
            })
            ->get();

        $deleted = 0;
        $deactivated = 0;

        foreach ($seats as $seat) {
            if ($seat->bookingSeats()->exists()) {
                if ($seat->is_active) {
                    $seat->update(['is_active' => false]);
                }

                $deactivated++;

                continue;
            }

            $seat->delete();
            $deleted++;
        }

        return ['deleted' => $deleted, 'deactivated' => $deactivated];
    }

    /**
     * Seats of the hall grouped by row, together with the booking flag.
     *
     * @return array<int, array<int, array{seat: Seat, booked: bool}>>
     */
    public function seatGrid(Hall $hall): array
    {
        $booked = $this->bookedSeatIds($hall);

        return $hall->seats()
            ->orderBy('row_number')
            ->orderBy('seat_number')
            ->get()
            ->groupBy('row_number')
            ->map(fn (Collection $seats): array => $seats
                ->map(fn (Seat $seat): array => [
                    'seat' => $seat,
                    'booked' => $booked->contains($seat->id),
                ])
                ->all())
            ->all();
    }

    /**
     * Short summary shown above the seat grid.
     *
     * @return array{total: int, active: int, inactive: int, booked: int}
     */
    public function seatStats(Hall $hall): array
    {
        return [
            'total' => $hall->seats()->count(),
            'active' => $hall->seats()->where('is_active', true)->count(),
            'inactive' => $hall->seats()->where('is_active', false)->count(),
            'booked' => $this->bookedSeatIds($hall)->count(),
        ];
    }

    /**
     * Ids of the seats that belong to a booking which is not cancelled.
     *
     * @return Collection<int, int>
     */
    private function bookedSeatIds(Hall $hall): Collection
    {
        return DB::table('booking_seats')
            ->join('bookings', 'bookings.id', '=', 'booking_seats.booking_id')
            ->join('seats', 'seats.id', '=', 'booking_seats.seat_id')
            ->where('seats.hall_id', $hall->id)
            ->where('bookings.status', '!=', BookingStatus::Cancelled->value)
            ->distinct()
            ->pluck('booking_seats.seat_id');
    }
}
