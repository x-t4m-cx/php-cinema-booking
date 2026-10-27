<form class="form" method="POST" action="{{ route('users.store') }}">
    @csrf

    <div class="field">
        <label class="field__label" for="name">Имя</label>
        <input class="field__input" id="name" name="name" type="text" maxlength="255" required
               value="{{ old('name') }}">
        @error('name')<p class="field__error">{{ $message }}</p>@enderror
    </div>

    <div class="field">
        <label class="field__label" for="email">Email</label>
        <input class="field__input" id="email" name="email" type="email" maxlength="255" required
               value="{{ old('email') }}">
        @error('email')<p class="field__error">{{ $message }}</p>@enderror
    </div>

    <div class="field-row">
        <div class="field">
            <label class="field__label" for="password">Пароль</label>
            <input class="field__input" id="password" name="password" type="password" required>
            @error('password')<p class="field__error">{{ $message }}</p>@enderror
        </div>

        <div class="field">
            <label class="field__label" for="password_confirmation">Повторите пароль</label>
            <input class="field__input" id="password_confirmation" name="password_confirmation" type="password" required>
        </div>
    </div>

    <p class="field__hint">Роль назначается автоматически: новый сотрудник станет менеджером.</p>

    <div class="form__actions">
        <button class="btn btn--primary" type="submit">Создать менеджера</button>
        <a class="btn btn--ghost" href="{{ route('users.index') }}">Отмена</a>
    </div>
</form>
