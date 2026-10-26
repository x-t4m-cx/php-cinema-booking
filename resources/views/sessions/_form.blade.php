@php($editing = $session->exists)
@php($today = now()->toDateString())
@php($leadStart = now()->addMinutes(App\Models\MovieSession::MIN_LEAD_MINUTES))
@php($minTime = $leadStart->isSameDay(now()) ? $leadStart->format('H:i') : '23:59')

<form class="form" method="POST" action="{{ $editing ? route('sessions.update', $session) : route('sessions.store') }}">
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <div class="field-row">
        <div class="field">
            <label class="field__label" for="movie_id">Фильм</label>
            <select class="field__input" id="movie_id" name="movie_id" required>
                <option value="">Выберите фильм</option>
                @foreach ($movies as $movie)
                    <option value="{{ $movie->id }}" @selected((int) old('movie_id', $session->movie_id) === $movie->id)>
                        {{ $movie->title }} ({{ $movie->duration_label }}){{ $movie->is_active ? '' : ' — снят с показа' }}
                    </option>
                @endforeach
            </select>
            @if ($movies->isEmpty())
                <p class="field__hint">Нет фильмов в прокате.</p>
            @endif
            @error('movie_id')<p class="field__error">{{ $message }}</p>@enderror
        </div>

        <div class="field">
            <label class="field__label" for="hall_id">Зал</label>
            <select class="field__input" id="hall_id" name="hall_id" required>
                <option value="">Выберите зал</option>
                @foreach ($halls as $hall)
                    <option value="{{ $hall->id }}" @selected((int) old('hall_id', $session->hall_id) === $hall->id)>
                        {{ $hall->name }}{{ $hall->is_active ? '' : ' — закрыт' }}
                    </option>
                @endforeach
            </select>
            @if ($halls->isEmpty())
                <p class="field__hint">Нет открытых залов.</p>
            @endif
            @error('hall_id')<p class="field__error">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="field-row">
        <div class="field">
            <label class="field__label" for="date">Дата</label>
            <input class="field__input" id="date" name="date" type="date" required
                   min="{{ $today }}" data-today="{{ $today }}" data-min-time="{{ $minTime }}"
                   value="{{ old('date', $date) }}">
            @error('date')<p class="field__error">{{ $message }}</p>@enderror
        </div>

        <div class="field">
            <label class="field__label" for="time">Начало</label>
            <input class="field__input" id="time" name="time" type="time" required
                   min="{{ $date === $today ? $minTime : '' }}"
                   value="{{ old('time', $time) }}">
            @error('time')<p class="field__error">{{ $message }}</p>@enderror
        </div>

        <div class="field">
            <label class="field__label" for="price">Цена билета, ₽</label>
            <input class="field__input" id="price" name="price" type="number" min="0" max="100000" step="0.01" required
                   value="{{ old('price', $session->price ?? '300.00') }}">
            @error('price')<p class="field__error">{{ $message }}</p>@enderror
        </div>
    </div>

    <p class="field__hint">
        Окончание считается по длительности фильма. Между сеансами заложен буфер
        {{ $buffer }} минут на уборку зала и посадку зрителей.
    </p>

    <div class="form__actions">
        <button class="btn btn--primary" type="submit">{{ $editing ? 'Сохранить' : 'Создать сеанс' }}</button>
        <a class="btn btn--ghost" href="{{ route('sessions.index') }}">Отмена</a>
    </div>
</form>

<script>
    (() => {
        const date = document.getElementById('date');
        const time = document.getElementById('time');

        if (!date || !time) {
            return;
        }

        const syncMinTime = () => {
            if (date.value === date.dataset.today) {
                time.min = date.dataset.minTime;
            } else {
                time.removeAttribute('min');
            }
        };

        date.addEventListener('change', syncMinTime);
        syncMinTime();
    })();
</script>
