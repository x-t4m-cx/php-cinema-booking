@php($editing = $hall->exists)

<form class="form" method="POST" action="{{ $editing ? route('halls.update', $hall) : route('halls.store') }}">
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <div class="field">
        <label class="field__label" for="name">Название зала</label>
        <input class="field__input" id="name" name="name" type="text" maxlength="100" required
               value="{{ old('name', $hall->name) }}" placeholder="Например, Красный зал">
        @error('name')<p class="field__error">{{ $message }}</p>@enderror
    </div>

    <div class="field-row">
        <div class="field">
            <label class="field__label" for="rows_count">Рядов</label>
            <input class="field__input" id="rows_count" name="rows_count" type="number" min="1" max="50" required
                   value="{{ old('rows_count', $hall->rows_count ?? 10) }}">
            @error('rows_count')<p class="field__error">{{ $message }}</p>@enderror
        </div>

        <div class="field">
            <label class="field__label" for="seats_per_rows">Мест в ряду</label>
            <input class="field__input" id="seats_per_rows" name="seats_per_rows" type="number" min="1" max="60" required
                   value="{{ old('seats_per_rows', $hall->seats_per_rows ?? 12) }}">
            @error('seats_per_rows')<p class="field__error">{{ $message }}</p>@enderror
        </div>
    </div>

    @if ($editing)
        <p class="field__hint">
            При изменении размеров сетка пересобирается: недостающие места создаются, лишние удаляются.
            Места, которые уже есть в бронях, не удаляются, а отключаются.
        </p>
    @else
        <p class="field__hint">Сетка мест создастся автоматически по размерам зала.</p>
    @endif

    <div class="form__actions">
        <button class="btn btn--primary" type="submit">{{ $editing ? 'Сохранить' : 'Создать зал' }}</button>
        <a class="btn btn--ghost" href="{{ $editing ? route('halls.show', $hall) : route('halls.index') }}">Отмена</a>
    </div>
</form>
