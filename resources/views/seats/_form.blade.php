@php($editing = $seat->exists)

<form class="form" method="POST" action="{{ $editing ? route('seats.update', $seat) : route('halls.seats.store', $hall) }}">
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <div class="field-row">
        <div class="field">
            <label class="field__label" for="row_number">Ряд</label>
            <input class="field__input" id="row_number" name="row_number" type="number" min="1" max="{{ $hall->rows_count }}" required
                   value="{{ old('row_number', $seat->row_number) }}">
            @error('row_number')<p class="field__error">{{ $message }}</p>@enderror
        </div>

        <div class="field">
            <label class="field__label" for="seat_number">Место</label>
            <input class="field__input" id="seat_number" name="seat_number" type="number" min="1" max="{{ $hall->seats_per_rows }}" required
                   value="{{ old('seat_number', $seat->seat_number) }}">
            @error('seat_number')<p class="field__error">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="field">
        <input type="hidden" name="is_active" value="0">
        <label class="checkbox">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $seat->is_active ?? true))>
            <span>Место доступно для бронирования</span>
        </label>
        @error('is_active')<p class="field__error">{{ $message }}</p>@enderror
    </div>

    <div class="form__actions">
        <button class="btn btn--primary" type="submit">{{ $editing ? 'Сохранить' : 'Добавить место' }}</button>
        <a class="btn btn--ghost" href="{{ route('halls.show', $hall) }}">К сетке зала</a>
    </div>
</form>
