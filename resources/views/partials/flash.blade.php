@foreach (['success' => ['ok', 'check-circle-fill'], 'error' => ['bad', 'exclamation-triangle-fill']] as $key => [$tone, $icon])
    @if (session($key))
        <div class="notice notice-{{ $tone }} alert alert-dismissible fade show" role="alert">
            <i class="bi bi-{{ $icon }}"></i>
            <div>{{ session($key) }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif
@endforeach
@if ($errors->any())
    <div class="notice notice-bad" role="alert">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <ul>
            @foreach ($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
@endif
