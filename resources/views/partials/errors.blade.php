@if ($errors->any())
    <div class="alert alert--error">
        <strong>Проверьте поля формы:</strong>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
