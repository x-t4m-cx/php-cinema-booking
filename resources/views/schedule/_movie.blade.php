<div class="movie-block">
    <div class="movie-block__head">
        <h3 class="movie-block__title">
            <a class="link link--strong" href="{{ route('movies.show', $group['movie']) }}">
                {{ $group['movie']->title }}
            </a>
        </h3>
        <span class="movie-block__duration">{{ $group['movie']->duration_label }}</span>
    </div>

    <div class="session-list">
        @foreach ($group['sessions'] as $session)
            <a class="session-card" href="{{ route('bookings.create', $session) }}">
                <span class="session-card__time">
                    {{ $session->starts_at->format('H:i') }}–{{ $session->ends_at->format('H:i') }}
                </span>
                <span class="session-card__meta">{{ $session->hall->name }}</span>
                <span class="session-card__price">{{ number_format((float) $session->price, 0, ',', ' ') }} ₽</span>
            </a>
        @endforeach
    </div>
</div>
