<div class="hall">
    <div class="hall__screen">Экран</div>

    <div class="hall__grid">
        @foreach ($grid as $row => $cells)
            <div class="hall__row">
                <span class="hall__row-label">{{ $row }}</span>

                <div class="hall__row-seats">
                    @foreach ($cells as $cell)
                        @php($seat = $cell['seat'])
                        @can('update', $seat)
                            @php($seatUrl = route('seats.edit', $seat))
                        @else
                            @php($seatUrl = route('seats.show', $seat))
                        @endcan
                        <a class="seat {{ $seat->is_active ? 'seat--active' : 'seat--inactive' }} {{ $cell['booked'] ? 'seat--booked' : '' }}"
                           href="{{ $seatUrl }}"
                           title="{{ $seat->label }}{{ $cell['booked'] ? ' · есть брони' : '' }}">
                            {{ $seat->seat_number }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
