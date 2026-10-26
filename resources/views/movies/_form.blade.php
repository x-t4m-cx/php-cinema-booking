@php($editing = $movie->exists)

<form class="form" method="POST" action="{{ $editing ? route('movies.update', $movie) : route('movies.store') }}">
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <div class="field">
        <label class="field__label" for="title">Название</label>
        <input class="field__input" id="title" name="title" type="text" maxlength="255" required
               value="{{ old('title', $movie->title) }}">
        @error('title')<p class="field__error">{{ $message }}</p>@enderror
    </div>

    <div class="field">
        <label class="field__label" for="duration_minutes">Длительность, минут</label>
        <input class="field__input" id="duration_minutes" name="duration_minutes" type="number" min="1" max="600" required
               value="{{ old('duration_minutes', $movie->duration_minutes) }}">
        <p class="field__hint">Например, 135 минут — это 2 ч 15 мин.</p>
        @error('duration_minutes')<p class="field__error">{{ $message }}</p>@enderror
    </div>

    <div class="field">
        <label class="field__label" for="description">Описание</label>
        <textarea class="field__input" id="description" name="description" rows="6" maxlength="5000">{{ old('description', $movie->description) }}</textarea>
        @error('description')<p class="field__error">{{ $message }}</p>@enderror
    </div>

    @can('update', $movie)
        <div class="field">
            <input type="hidden" name="is_active" value="0">
            <label class="checkbox">
                <input name="is_active" type="checkbox" value="1" @checked(old('is_active', $movie->is_active ?? true))>
                В прокате
            </label>
            <p class="field__hint">
                Снятый с показа фильм остаётся в старых сеансах, но новые на него создать нельзя.
            </p>
        </div>
    @endcan

    <div class="form__actions">
        <button class="btn btn--primary" type="submit">{{ $editing ? 'Сохранить' : 'Добавить фильм' }}</button>
        <a class="btn btn--ghost" href="{{ $editing ? route('movies.show', $movie) : route('movies.index') }}">Отмена</a>
    </div>
</form>
