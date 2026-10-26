<div class="card" id="hall-availability"
     data-url="{{ route('sessions.occupied') }}"
     data-session="{{ $session->exists ? $session->id : '' }}">
    <div class="card__head">
        <h2 class="section-title">Занятость зала</h2>
        <p class="muted" data-occupied-hint>
            @if ($hall)
                {{ $hall->name }} · {{ $date_label }}
            @else
                Выберите зал и дату, чтобы увидеть занятые интервалы.
            @endif
        </p>
    </div>

    <div data-occupied-list>
        @if ($hall)
            @if ($occupied->isEmpty())
                <p class="empty">На {{ $date_label }} зал «{{ $hall->name }}» свободен.</p>
            @else
                <ul class="slots">
                    @foreach ($occupied as $busy)
                        <li class="slot">
                            <span class="slot__time">
                                {{ $busy->starts_at->format('d.m H:i') }} — {{ $busy->ends_at->format('d.m H:i') }}
                            </span>
                            <span class="slot__title">{{ $busy->movie->title }}</span>
                            <span class="slot__buffer">зал занят до {{ $busy->busy_until->format('d.m H:i') }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        @else
            <p class="empty">Выберите зал и дату, чтобы увидеть занятые интервалы.</p>
        @endif
    </div>

    <p class="field__hint">
        В списке учтён буфер {{ $buffer }} минут: зал занят не только на время фильма.
    </p>

    <script>
        (() => {
            const block = document.getElementById('hall-availability');
            if (!block) {
                return;
            }

            const hall = document.getElementById('hall_id');
            const date = document.getElementById('date');
            const hint = block.querySelector('[data-occupied-hint]');
            const list = block.querySelector('[data-occupied-list]');
            const placeholder = 'Выберите зал и дату, чтобы увидеть занятые интервалы.';

            if (!hall || !date || !hint || !list) {
                return;
            }

            const emptyState = (text) => {
                const empty = document.createElement('p');
                empty.className = 'empty';
                empty.textContent = text;

                return empty;
            };

            const render = (data) => {
                hint.textContent = `${data.hall} · ${data.date_label}`;
                list.replaceChildren();

                if (data.occupied.length === 0) {
                    list.append(emptyState('На этот день зал свободен.'));

                    return;
                }

                const slots = document.createElement('ul');
                slots.className = 'slots';

                data.occupied.forEach((slot) => {
                    const item = document.createElement('li');
                    item.className = 'slot';

                    const time = document.createElement('span');
                    time.className = 'slot__time';
                    time.textContent = `${slot.starts_at} — ${slot.ends_at}`;

                    const title = document.createElement('span');
                    title.className = 'slot__title';
                    title.textContent = slot.title;

                    const buffer = document.createElement('span');
                    buffer.className = 'slot__buffer';
                    buffer.textContent = `зал занят до ${slot.busy_until}`;

                    item.append(time, title, buffer);
                    slots.append(item);
                });

                list.append(slots);
            };

            const load = async () => {
                if (!hall.value || !date.value) {
                    hint.textContent = placeholder;
                    list.replaceChildren(emptyState(placeholder));

                    return;
                }

                const params = new URLSearchParams({ hall_id: hall.value, date: date.value });

                if (block.dataset.session) {
                    params.set('session_id', block.dataset.session);
                }

                try {
                    const response = await fetch(`${block.dataset.url}?${params.toString()}`, {
                        headers: { Accept: 'application/json' },
                    });

                    if (!response.ok) {
                        throw new Error('Request failed');
                    }

                    render(await response.json());
                } catch {
                    hint.textContent = placeholder;
                    list.replaceChildren(emptyState('Не удалось загрузить занятость. Обновите страницу.'));
                }
            };

            hall.addEventListener('change', load);
            date.addEventListener('change', load);
        })();
    </script>
</div>
