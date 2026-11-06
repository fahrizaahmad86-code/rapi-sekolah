@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark">Daftar Pengaduan Kerusakan</h3>
        
        @if(in_array(auth()->user()->role_id, [1, 2, 4]))
            <button type="button" class="btn text-white fw-semibold px-3" style="background-color: #0EA5E9; border-radius: 8px;" data-bs-toggle="modal" data-bs-target="#modalTambahPengaduan">
                Lapor Kerusakan
            </button>
        @endif
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

    {{-- Filter status perbaikan (khusus PIC) --}}
    @if(auth()->user()->role_id == 3)
        <form method="GET" action="{{ route('pengaduan.index') }}" class="mb-3" style="max-width: 320px;">
            <label for="filterStatus" class="form-label fw-bold">Filter Status Perbaikan</label>
            <select name="status" id="filterStatus" class="form-select" onchange="this.form.submit()">
                <option value="">Semua status ({{ $jumlah->sum() }})</option>
                @foreach($daftarStatus as $st)
                    <option value="{{ $st }}" {{ $filter === $st ? 'selected' : '' }}>
                        {{ $st }} ({{ $jumlah[$st] ?? 0 }})
                    </option>
                @endforeach
            </select>
        </form>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Ruangan</th>
                            <th>Pelapor</th>
                            <th>Deskripsi Kerusakan</th>
                            <th>Foto</th>
                            <th>Petugas (PIC)</th>
                            <th>Status Perbaikan</th>
                            @if(in_array(auth()->user()->role_id, [1, 3]))
                                <th class="text-center">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pengaduans as $index => $pengaduan)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="fw-semibold">{{ $pengaduan->room->room_name ?? '-' }}</td>
                                <td>{{ $pengaduan->reporter->fullname ?? '-' }}</td>
                                <td>{{ $pengaduan->description }}</td>
                                <td>
                                    <!-- Foto Kerusakan dari Pelapor -->
                                    @if($pengaduan->photo_path)
                                        <a href="{{ asset('storage/' . $pengaduan->photo_path) }}" target="_blank" class="btn btn-sm btn-outline-primary mb-1">
                                            <i class="bi bi-image"></i> Kerusakan
                                        </a>
                                    @endif

                                    <!-- Foto Bukti Hasil Perbaikan dari PIC -->
                                    @if($pengaduan->completion_photo_path)
                                        <br>
                                        <a href="{{ asset('storage/' . $pengaduan->completion_photo_path) }}" target="_blank" class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-check-circle"></i> Bukti Perbaikan
                                        </a>
                                    @elseif(!$pengaduan->photo_path)
                                        <span class="text-muted small">Tidak Ada</span>
                                    @endif
                                </td>
                                <td>{{ $pengaduan->handler->fullname ?? 'Belum Ditugaskan' }}</td>
                                <td>{{ $pengaduan->status_repair }}</td>
                                
                                @if(in_array(auth()->user()->role_id, [1, 3]))
                                    <td class="text-center">
                                        @if(auth()->user()->role_id == 1)
                                            <form action="{{ route('pengaduan.destroy', $pengaduan->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus laporan pengaduan ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                            </form>
                                        @elseif(auth()->user()->role_id == 3)
                                            @if($pengaduan->isFinished())
                                                <button class="btn btn-sm btn-secondary" disabled title="Sudah diperbaiki dan bukti perbaikan sudah dikirim">
                                                     Selesai
                                                </button>
                                            @else
                                                <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#modalUpdateStatus{{ $pengaduan->id }}">
                                                    Update Status
                                                </button>
                                            @endif
                                        @endif
                                    </td>
                                @endif
                            </tr>

                            {{-- Modal Update Status Perbaikan & Upload Foto Bukti oleh PIC Ruangan --}}
                            @if(auth()->user()->role_id == 3 && !$pengaduan->isFinished())
                            <div class="modal fade" id="modalUpdateStatus{{ $pengaduan->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold">Update Status Perbaikan</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('pengaduan.update', $pengaduan->id) }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body text-start">
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Status Perbaikan</label>
                                                    <select name="status_repair" class="form-select js-status-repair" data-photo="foto{{ $pengaduan->id }}" required>
                                                        <option value="Belum Diperbaiki" {{ $pengaduan->status_repair == 'Belum Diperbaiki' ? 'selected' : '' }}>Belum Diperbaiki</option>
                                                        <option value="Menunggu Spare Part" {{ $pengaduan->status_repair == 'Menunggu Spare Part' ? 'selected' : '' }}>Menunggu Spare Part</option>
                                                        <option value="Sudah Diperbaiki" {{ $pengaduan->status_repair == 'Sudah Diperbaiki' ? 'selected' : '' }}>Sudah Diperbaiki</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Upload Foto Bukti Perbaikan <span class="text-danger">*</span></label>
                                                    <input type="file" name="completion_photo_path" id="foto{{ $pengaduan->id }}" class="form-control"
                                                           accept="image/png, image/jpeg, image/jpg"
                                                           {{ $pengaduan->status_repair == 'Sudah Diperbaiki' ? 'required' : 'disabled' }}>
                                                    <small id="foto{{ $pengaduan->id }}Off" class="text-muted {{ $pengaduan->status_repair == 'Sudah Diperbaiki' ? 'd-none' : '' }}">
                                                        Foto hanya dapat diunggah saat status <strong>Sudah Diperbaiki</strong>.
                                                    </small>
                                                    <small id="foto{{ $pengaduan->id }}On" class="text-muted {{ $pengaduan->status_repair == 'Sudah Diperbaiki' ? '' : 'd-none' }}">
                                                        Format (JPG/PNG, maks. 2MB). Setelah disimpan, status tidak dapat diubah lagi.
                                                    </small>
                                                    @include('partials.foto-kamera', ['target' => 'foto' . $pengaduan->id])
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-success">Update Status</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endif

                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">{{ $filter ? 'Tidak ada laporan dengan status "' . $filter . '".' : 'Belum ada pengaduan kerusakan.' }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal Tambah Pengaduan --}}
@if(in_array(auth()->user()->role_id, [1, 2, 4]))
<div class="modal fade" id="modalTambahPengaduan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Form Lapor Kerusakan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('pengaduan.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pilih Ruangan</label>
                        <select name="room_id" class="form-select" required>
                            <option value="" selected disabled>-- Pilih Ruangan --</option>
                            @foreach($ruangans as $room)
                                <option value="{{ $room->id }}">{{ $room->room_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Deskripsi Kerusakan</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Jelaskan secara singkat fasilitas yang rusak..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Upload Foto Kerusakan</label>
                        <input type="file" name="photo_path" id="fotoLaporKerusakan" class="form-control" accept="image/png, image/jpeg, image/jpg" required>
                        <small class="text-muted">Format JPG/PNG, Maksimal 2MB.</small>
                        @include('partials.foto-kamera', ['target' => 'fotoLaporKerusakan'])
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn text-white fw-semibold" style="background-color: #0EA5E9;">Kirim Laporan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@if(auth()->user()->role_id == 3)
<script>
    // Foto bukti hanya aktif (dan wajib) saat status "Sudah Diperbaiki"
    document.addEventListener('change', function (e) {
        if (!e.target.classList.contains('js-status-repair')) return;
        const id = e.target.dataset.photo;
        const foto = document.getElementById(id);
        const selesai = e.target.value === 'Sudah Diperbaiki';
        foto.disabled = !selesai;
        foto.required = selesai;
        if (!selesai) foto.value = '';
        document.getElementById(id + 'Off').classList.toggle('d-none', selesai);
        document.getElementById(id + 'On').classList.toggle('d-none', !selesai);
    });
</script>
@endif
@endsection