@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark">Data Ruangan</h3>
        <button type="button" class="btn text-white fw-semibold px-3" style="background-color: #0EA5E9; border-radius: 8px;" data-bs-toggle="modal" data-bs-target="#modalTambahRuangan">
            Tambah Ruangan
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('ruangan.index') }}" class="d-flex gap-2 align-items-center">
            <div class="col">
                <input type="text" name="q" class="form-control flex-grow-1" placeholder="Cari berdasarkan ruangan, kapasitas, atau lokasi..." value="{{ request('q') }}">
            </div>
            <div class="col-auto d-flex gap-2">
                <button type="submit" class="btn text-white fw-semibold px-4" style="background-color: #0EA5E9; border-radius: 8px; white-space: nowrap;">
                    Cari
                </button>
                <div class="col-auto">
                    <a href="{{ route('ruangan.index') }}" class="btn btn-outline-secondary" style="border-radius: 8px;">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama Ruangan</th>
                            <th>Kapasitas</th>
                            <th>Lokasi</th>
                            <th>PIC Ruangan</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ruangans as $index => $room)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="fw-semibold">{{ $room->room_name }}</td>
                                <td>{{ $room->capacity }} orang</td>
                                <td>{{ $room->location }}</td>
                                <td>
                                        {{ $room->pic ? $room->pic->fullname : 'Belum Ditentukan' }}
                                </td>
                                <td>
                                    <div class="d-flex gap-1 justify-content-center">
                                        <button class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEditRuangan{{ $room->id }}">
                                            Edit
                                        </button>
                                        
                                        <form action="{{ route('ruangan.destroy', $room->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus ruangan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <div class="modal fade" id="modalEditRuangan{{ $room->id }}" tabindex="-1" aria-labelledby="modalEditRuanganLabel{{ $room->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold" id="modalEditRuanganLabel{{ $room->id }}">Edit Data Ruangan</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('ruangan.update', $room->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body text-start">
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Nama Ruangan</label>
                                                    <input type="text" name="room_name" class="form-control" value="{{ $room->room_name }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Kapasitas (Orang)</label>
                                                    <input type="number" name="capacity" class="form-control" value="{{ $room->capacity }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Lokasi Ruangan</label>
                                                    <input type="text" name="location" class="form-control" value="{{ $room->location }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Pilih PIC Ruangan</label>
                                                    <select name="pic_id" class="form-select">
                                                        <option value="">-- Belum Ditentukan --</option>
                                                        @foreach($pics as $pic)
                                                            <option value="{{ $pic->id }}" {{ $room->pic_id == $pic->id ? 'selected' : '' }}>
                                                                {{ $pic->fullname }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-warning fw-semibold">Simpan Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Belum ada data ruangan yang tersimpan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambahRuangan" tabindex="-1" aria-labelledby="modalTambahRuanganLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalTambahRuanganLabel">Tambah Data Ruangan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('ruangan.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Ruangan</label>
                        <input type="text" name="room_name" class="form-control" placeholder="Contoh: Lab Komputer 1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Kapasitas (Orang)</label>
                        <input type="number" name="capacity" class="form-control" placeholder="Contoh: 36" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Lokasi Ruangan</label>
                        <input type="text" name="location" class="form-control" placeholder="Contoh: Gedung B Lantai 2" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pilih PIC Ruangan</label>
                        <select name="pic_id" class="form-select">
                            <option value="" selected>-- Pilih PIC Ruangan --</option>
                            @foreach($pics as $pic)
                                <option value="{{ $pic->id }}">{{ $pic->fullname }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn text-white fw-semibold" style="background-color: #0EA5E9; border-radius: 8px;">
                        Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection