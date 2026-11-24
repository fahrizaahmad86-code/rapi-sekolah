@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark">Kelola Pengguna</h3>
    <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn text-white fw-semibold px-3" style="background-color: #0EA5E9; border-radius: 8px;" data-bs-toggle="modal" data-bs-target="#modalImportGuru">
            Import Data Guru
        </button>
        <button type="button" class="btn text-white fw-semibold px-3" style="background-color: #0EA5E9; border-radius: 8px;" data-bs-toggle="modal" data-bs-target="#modalTambahUser">
            Tambah Pengguna
        </button>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('import_errors'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <strong>Beberapa baris tidak berhasil diimpor:</strong>
        <ul class="mb-0 mt-2">
            @foreach(session('import_errors') as $importError)
                <li>{{ $importError }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- ===================== FORM PENCARIAN USER ===================== --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('user.index') }}" class="d-flex gap-2 align-items-center">
            <div class="col">
                <input type="text" name="q" class="form-control flex-grow-1" placeholder="Cari berdasarkan nama, username, atau email..." value="{{ request('q') }}">
            </div>
            <div class="col-auto d-flex gap-2">
                <button type="submit" class="btn text-white fw-semibold px-4" style="background-color: #0EA5E9; border-radius: 8px; white-space: nowrap;">
                    Cari
                </button>
                <div class="col-auto">
                    <a href="{{ route('user.index') }}" class="btn btn-outline-secondary" style="border-radius: 8px;">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-2 p-md-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Nama Lengkap</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>No. WhatsApp</th>
                        <th>Peran (Role)</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $index => $u)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $u->fullname }}</td>
                            <td>{{ $u->username }}</td>
                            <td>{{ $u->email ?? '-' }}</td>
                            <td>
                                @if($u->phone_local)
                                    {{ $u->phone_local }}
                                @elseif($u->role_id == 3)
                                    <span class="badge bg-danger-subtle text-danger">Belum diisi</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <span>
                                    {{ $u->role->role_name ?? 'Role ID: '.$u->role_id }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEditUser{{ $u->id }}">
                                        Edit
                                    </button>

                                    @if($u->id != auth()->id())
                                        <form action="{{ route('user.destroy', $u->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus pengguna ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm">Hapus</button>
                                        </form>
                                    @else
                                        <span class="text-muted small align-self-center ms-1">(Akun Anda)</span>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        <div class="modal fade" id="modalEditUser{{ $u->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title fw-bold">Edit Pengguna</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="{{ route('user.update', $u->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-body text-start">
                                            <div class="mb-3">
                                                <label class="form-label">Nama Lengkap</label>
                                                <input type="text" name="fullname" class="form-control" value="{{ $u->fullname }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Username</label>
                                                <input type="text" name="username" class="form-control" value="{{ $u->username }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Password <small class="text-muted">(Kosongkan jika tidak ingin diubah)</small></label>
                                                <input type="password" name="password" class="form-control" placeholder="••••••••">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Email <small class="text-muted">(untuk reset password)</small></label>
                                                <input type="email" name="email" class="form-control" value="{{ $u->email }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Nomor WhatsApp <small class="text-muted">(wajib untuk PIC Ruangan)</small></label>
                                                <input type="text" name="phone_number" class="form-control js-phone-input" value="{{ $u->phone_local }}" placeholder="Contoh: 081234567890" inputmode="tel" {{ $u->role_id == 3 ? 'required' : '' }}>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Peran (Role)</label>
                                                <select name="role_id" class="form-select js-role-select" required>
                                                    @foreach($roles as $role)
                                                        <option value="{{ $role->id }}" {{ $u->role_id == $role->id ? 'selected' : '' }}>
                                                            {{ $role->role_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-warning">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalImportGuru" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Import Data Guru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('user.import-guru') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <p class="text-muted">Pilih file Excel (.xlsx) atau CSV. Kolom nama guru wajib tersedia.</p>
                    <div class="mb-3">
                        <label for="fileGuru" class="form-label fw-semibold">File data guru</label>
                        <input type="file" id="fileGuru" name="file_guru" class="form-control" accept=".xlsx,.csv" required>
                        <small class="text-muted">Maksimal 2 MB. Password yang kosong akan dibuatkan otomatis.</small>
                    </div>
                    <a href="{{ route('user.template-guru') }}" class="btn btn-sm btn-outline-secondary">
                        Unduh Template Excel
                    </a>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn text-white fw-semibold px-4" style="background-color: #0EA5E9; border-radius: 8px;">Import Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambahUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Tambah Pengguna Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('user.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="fullname" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email <small class="text-muted">(untuk reset password)</small></label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nomor WhatsApp <small class="text-muted">(wajib untuk PIC Ruangan)</small></label>
                        <input type="text" name="phone_number" class="form-control js-phone-input" placeholder="Contoh: 081234567890" inputmode="tel">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Peran (Role)</label>
                        <select name="role_id" class="form-select js-role-select" required>
                            <option value="">-- Pilih Role --</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->role_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn text-white fw-semibold px-4" style="background-color: #0EA5E9; border-radius: 8px;">
                        Simpan Pengguna</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    // Nomor WhatsApp wajib diisi jika role yang dipilih adalah PIC Ruangan (role_id = 3)
    document.addEventListener('change', function (e) {
        if (!e.target.classList.contains('js-role-select')) return;
        var input = e.target.closest('form').querySelector('.js-phone-input');
        if (input) input.required = e.target.value === '3';
    });
</script>
@endsection