{{-- ===== 1. Ganti header di resources/views/waka/user/index.blade.php ===== --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark">Kelola Pengguna</h3>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-success fw-semibold px-3" style="border-radius: 8px;" data-bs-toggle="modal" data-bs-target="#modalImportGuru">
            Import Guru
        </button>
        <button type="button" class="btn text-white fw-semibold px-3" style="background-color: #0EA5E9; border-radius: 8px;" data-bs-toggle="modal" data-bs-target="#modalTambahUser">
            Tambah Pengguna
        </button>
    </div>
</div>

{{-- ===== 2. Taruh di bawah blok @if(session('error')) (untuk daftar baris gagal) ===== --}}
@if(session('import_errors'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <strong>{{ count(session('import_errors')) }} baris dilewati:</strong>
        <ul class="mb-0 mt-1 small">
            @foreach(session('import_errors') as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- ===== 3. Taruh sebelum @endsection (modal import) ===== --}}
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
                    <div class="alert alert-info small">
                        <ol class="mb-0 ps-3">
                            <li>Unduh template, lalu isi data guru.</li>
                            <li>Kolom <code>nama_lengkap</code>, <code>username</code>, dan <code>email</code> wajib diisi.</li>
                            <li>Kolom <code>password</code> boleh kosong (otomatis <code>guru12345</code>).</li>
                            <li>Semua data yang diimpor otomatis berperan sebagai <b>Guru</b>.</li>
                        </ol>
                    </div>
                    <a href="{{ route('user.template-guru') }}" class="btn btn-sm btn-outline-secondary mb-3">
                        Unduh Template Excel
                    </a>
                    <div class="mb-3">
                        <label class="form-label">File Excel / CSV</label>
                        <input type="file" name="file_guru" class="form-control" accept=".xlsx,.xls,.csv" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success fw-semibold px-4">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
