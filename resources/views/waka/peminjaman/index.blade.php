@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark">Data Peminjaman Ruangan</h3>
        <button type="button" class="btn text-white fw-semibold px-3" style="background-color: #0EA5E9; border-radius: 8px;" data-bs-toggle="modal" data-bs-target="#modalTambahPeminjaman">
            Ajukan Peminjaman
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
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

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama Kegiatan</th>
                            <th>Ruangan</th>
                            <th>Pemohon</th>
                            <th>Waktu / Sesi</th>
                            <th>Surat</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($events as $index => $event)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="fw-semibold">
                                    {{ $event->event_name }}
                                    <br>
                                    <span class="badge text-white fw-normal mt-1" style="background-color: {{ $event->warna }}; font-size: 11px;">
                                        {{ $event->label_jenis }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-semibold">{{ $event->room->room_name ?? '-' }}</span>
                                    
                                    {{-- Menampilkan Alasan Pemindahan Jika Ada --}}
                                    @if($event->move_reason)
                                        <br>
                                        <small class="badge bg-warning text-dark text-wrap text-start mt-1" style="font-size: 11px; font-weight: normal; line-height: 1.3; max-width: 220px;">
                                            <i class="bi bi-info-circle me-1"></i> <strong>Dipindahkan:</strong> {{ $event->move_reason }}
                                        </small>
                                    @endif
                                </td>
                                <td>{{ $event->user->fullname ?? '-' }}</td>
                                <td>
                                    <small>
                                        <i class="bi bi-calendar"></i> {{ \Carbon\Carbon::parse($event->date)->format('d-m-Y') }}<br>
                                        <i class="bi bi-clock"></i> {{ \App\Support\SesiPeminjaman::labelDenganJam((int) $event->start_session, (int) $event->end_session) }}
                                    </small>
                                </td>
                                <td>
                                    @if($event->document_path)
                                        <a href="{{ route('public.storage', ['path' => $event->document_path]) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary"><i class="bi bi-file-earmark-image"></i> Lihat</a>
                                    @else
                                        <span class="text-muted small">Tidak ada</span>
                                    @endif
                                </td>
                                <td>
                                    @if($event->status == 'Pending')
                                        Pending
                                    @elseif($event->status == 'Approved')
                                        Approved
                                    @else
                                        Rejected
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1 justify-content-center">
                                        @if(auth()->check() && auth()->user()->role_id == 1)
                                            <!-- Tombol Validasi Status -->
                                            <button class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalValidasi{{ $event->id }}">
                                                Validasi
                                            </button>

                                            <!-- Tombol Pindah Ruangan & Sesi -->
                                            <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalPindah{{ $event->id }}">
                                                Pindah
                                            </button>
                                        @endif
                                        
                                        @if(auth()->user()->role_id == 1 || $event->user_id == auth()->id())
                                            <form action="{{ route('peminjaman.destroy', $event->id) }}" method="POST" onsubmit="return confirm('Hapus pengajuan ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm">Hapus</button>
                                            </form>
                                        @endif

                                        @if(in_array(auth()->user()->role_id, [1, 2, 4]) && $event->user_id == auth()->id() && $event->status != 'Rejected')
                                            <button type="button" class="btn btn-outline-warning btn-sm text-nowrap"
                                                    data-bs-toggle="modal" data-bs-target="#modalLaporKerusakan"
                                                    data-room-id="{{ $event->room_id }}"
                                                    data-room-name="{{ $event->room->room_name ?? '-' }}"
                                                    data-location="{{ $event->room->location ?? '-' }}"
                                                    data-pic="{{ $event->room->pic->fullname ?? 'Belum ditentukan' }}">
                                                    Lapor Kerusakan
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            <!-- MODAL VALIDASI ADMIN -->
                            <div class="modal fade" id="modalValidasi{{ $event->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold">Validasi Peminjaman</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('peminjaman.update', $event->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="room_id" value="{{ $event->room_id }}">
                                            <input type="hidden" name="start_session" value="{{ $event->start_session }}">
                                            <input type="hidden" name="end_session" value="{{ $event->end_session }}">
                                            <div class="modal-body text-start">
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Status Persetujuan</label>
                                                    <select name="status" class="form-select" required>
                                                        <option value="Pending" {{ $event->status == 'Pending' ? 'selected' : '' }}>Pending</option>
                                                        <option value="Approved" {{ $event->status == 'Approved' ? 'selected' : '' }}>Setujui (Approved)</option>
                                                        <option value="Rejected" {{ $event->status == 'Rejected' ? 'selected' : '' }}>Tolak (Rejected)</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Jenis Agenda</label>
                                                    <select name="agenda_type" class="form-select" required>
                                                        @foreach(\App\Models\Event::JENIS_AGENDA as $kode => $j)
                                                            <option value="{{ $kode }}" {{ $event->agenda_type == $kode ? 'selected' : '' }}>{{ $j['label'] }} - {{ $j['keterangan'] }}</option>
                                                        @endforeach
                                                    </select>
                                                    <small class="text-muted">Periksa dan koreksi jika jenis agenda yang dipilih pemohon kurang tepat.</small>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Catatan / Alasan (Opsional)</label>
                                                    <textarea name="move_reason" class="form-control" rows="3" placeholder="Masukkan alasan penolakan atau catatan...">{{ $event->move_reason }}</textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-success fw-semibold">Simpan Validasi</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- MODAL PINDAH RUANGAN & SESI KHUSUS ADMIN -->
                            <div class="modal fade" id="modalPindah{{ $event->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold">Pindahkan Ruangan & Sesi Kegiatan</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('peminjaman.update', $event->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="{{ $event->status }}">
                                            <div class="modal-body text-start">
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Pilih Ruangan Baru</label>
                                                    <select name="room_id" class="form-select" required>
                                                        @foreach($ruangans as $room)
                                                            <option value="{{ $room->id }}" {{ $event->room_id == $room->id ? 'selected' : '' }}>
                                                                {{ $room->room_name }} (Kapasitas: {{ $room->capacity }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label fw-bold">Sesi Mulai Baru</label>
                                                        <select name="start_session" class="form-select" required>
                                                            @for ($i = 1; $i <= 10; $i++)
                                                                <option value="{{ $i }}" {{ $event->start_session == $i ? 'selected' : '' }}>{{ \App\Support\SesiPeminjaman::labelDropdown($i) }}</option>
                                                            @endfor
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label fw-bold">Sesi Selesai Baru</label>
                                                        <select name="end_session" class="form-select" required>
                                                            @for ($i = 1; $i <= 10; $i++)
                                                                <option value="{{ $i }}" {{ $event->end_session == $i ? 'selected' : '' }}>{{ \App\Support\SesiPeminjaman::labelDropdown($i) }}</option>
                                                            @endfor
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Alasan Pemindahan Ruangan / Sesi</label>
                                                    <textarea name="move_reason" class="form-control" rows="3" placeholder="Masukkan alasan pemindahan..." required>{{ $event->move_reason }}</textarea>
                                                    <small class="text-muted">Alasan ini akan tampil di dashboard pemohon agar pemohon mengetahui alasan pemindahannya.</small>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary fw-semibold">Simpan Pemindahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">Belum ada data peminjaman ruangan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH PEMINJAMAN -->
<div class="modal fade" id="modalTambahPeminjaman" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Form Pengajuan Peminjaman</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('peminjaman.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pilih Ruangan Utama</label>
                        <select name="room_id" class="form-select" required>
                            <option value="" selected disabled>-- Pilih Ruangan --</option>
                            @foreach($ruangans as $room)
                                <option value="{{ $room->id }}">{{ $room->room_name }} (Kapasitas: {{ $room->capacity }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Kegiatan</label>
                        <input type="text" name="event_name" class="form-control" placeholder="Contoh: Rapat Koordinasi OSIS" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Jenis Agenda</label>
                        <select name="agenda_type" id="selectAgendaType" class="form-select" required>
                            @foreach(\App\Models\Event::JENIS_AGENDA as $kode => $j)
                                <option value="{{ $kode }}" @selected(old('agenda_type', 'internal') === $kode)>{{ $j['label'] }} - {{ $j['keterangan'] }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted d-block mt-1" id="contohAgendaType"></small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tanggal Kegiatan</label>
                        <input type="date" name="date" id="tanggalPinjam" class="form-control" min="{{ now()->toDateString() }}" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Sesi Mulai</label>
                            <select name="start_session" id="sesiMulai" class="form-select" required>
                                <option value="" selected disabled>-- Pilih Sesi --</option>
                                @for ($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}">{{ \App\Support\SesiPeminjaman::labelDropdown($i) }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Sesi Selesai</label>
                            <select name="end_session" id="sesiSelesai" class="form-select" required>
                                <option value="" selected disabled>-- Pilih Sesi --</option>
                                @for ($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}">{{ \App\Support\SesiPeminjaman::labelDropdown($i) }}</option>
                                @endfor
                            </select>
                            <small class="text-muted d-block mt-1" id="infoSesiLewat"></small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Upload Foto Surat Peminjaman</label>
                        <input type="file" name="document_path" id="fotoSuratPeminjaman" class="form-control" accept="image/png, image/jpeg, image/jpg" required>
                        @include('partials.foto-kamera', ['target' => 'fotoSuratPeminjaman'])
                        <small class="text-danger">Format JPG/PNG, Maksimal 2MB.</small>
                    </div>

                    @if(auth()->check() && auth()->user()->role_id == 1)
                        <hr class="my-3">
                        <div class="alert alert-info py-2 px-3 small mb-3">
                            <i class="bi bi-info-circle me-1"></i> <strong>Pencegahan Bentrok (Relokasi):</strong> Jika ruangan utama di atas sudah dipinjam orang lain, sistem akan memindahkan jadwal lama ke ruangan pengganti di bawah ini.
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">Ruangan Pengganti (Jika Bentrok)</label>
                            <select name="new_room_id" class="form-select">
                                <option value="" selected>-- Pilih Ruangan Pengganti --</option>
                                @foreach($ruangans as $room)
                                    <option value="{{ $room->id }}">{{ $room->room_name }} (Kapasitas: {{ $room->capacity }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">Alasan Pemindahan Jadwal</label>
                            <textarea name="move_reason" class="form-control" rows="2" placeholder="Contoh: Digunakan untuk acara dinas/sekolah mendesak..."></textarea>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn text-white fw-semibold" style="background-color: #0EA5E9;">Ajukan Sekarang</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    // Sesi yang jamnya sudah lewat dikunci secara real-time (memakai jam server, zona Asia/Jakarta)
    document.addEventListener('DOMContentLoaded', function () {
        var sesi      = @json(\App\Support\SesiPeminjaman::daftar());
        var offset    = {{ now()->timestamp * 1000 }} - Date.now(); // selisih jam server dengan jam perangkat
        var tanggal   = document.getElementById('tanggalPinjam');
        var mulai     = document.getElementById('sesiMulai');
        var selesai   = document.getElementById('sesiSelesai');
        var info      = document.getElementById('infoSesiLewat');
        if (!tanggal || !mulai || !selesai) return;

        function sekarang() {
            var d = new Date(Date.now() + offset);
            var tgl = new Intl.DateTimeFormat('en-CA', { timeZone: 'Asia/Jakarta', year: 'numeric', month: '2-digit', day: '2-digit' }).format(d);
            var jam = new Intl.DateTimeFormat('en-GB', { timeZone: 'Asia/Jakarta', hour: '2-digit', minute: '2-digit', hour12: false }).format(d);
            var p = jam.split(':');
            return { tgl: tgl, menit: parseInt(p[0], 10) * 60 + parseInt(p[1], 10) };
        }
        function menitDari(hhmm) { var p = hhmm.split(':'); return parseInt(p[0], 10) * 60 + parseInt(p[1], 10); }

        function perbarui() {
            var now = sekarang();
            tanggal.min = now.tgl;
            var hariIni = tanggal.value === now.tgl;
            var terlewat = 0;

            sesi.forEach(function (s) {
                var lewat = hariIni && menitDari(s.mulai) <= now.menit;
                if (lewat) terlewat++;
                [mulai, selesai].forEach(function (el) {
                    var opt = el.querySelector('option[value="' + s.no + '"]');
                    if (!opt) return;
                    // sesi selesai juga tidak boleh lebih kecil dari sesi mulai
                    var kecil = el === selesai && mulai.value && s.no < parseInt(mulai.value, 10);
                    opt.disabled = lewat || kecil;
                    opt.textContent = s.label + (lewat ? ' - sudah lewat' : '');
                    if (opt.disabled && el.value === String(s.no)) el.value = '';
                });
            });

            info.textContent = terlewat > 0
                ? 'Sesi yang jamnya sudah lewat tidak bisa dipilih.'
                : '';
        }

        tanggal.addEventListener('change', perbarui);
        mulai.addEventListener('change', perbarui);
        perbarui();
        setInterval(perbarui, 15000); // cek ulang tiap 15 detik agar sesi yang lewat otomatis terkunci
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var contoh = @json(collect(\App\Models\Event::JENIS_AGENDA)->map(fn ($j) => 'Contoh: ' . $j['contoh']));
        var pilih  = document.getElementById('selectAgendaType');
        var info   = document.getElementById('contohAgendaType');
        if (!pilih) return;
        function ubah() { info.textContent = contoh[pilih.value] || ''; }
        pilih.addEventListener('change', ubah);
        ubah();
    });
</script>
@if(in_array(auth()->user()->role_id, [1, 2, 4]))
<!-- MODAL LAPOR KERUSAKAN (terisi otomatis dari baris peminjaman) -->
<div class="modal fade" id="modalLaporKerusakan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Lapor Kerusakan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('pengaduan.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="room_id" id="lkRoomId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Ruangan</label>
                        <input type="text" id="lkRoomName" class="form-control bg-light" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Lokasi Ruangan</label>
                        <input type="text" id="lkLocation" class="form-control bg-light" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">PIC yang Bertugas</label>
                        <input type="text" id="lkPic" class="form-control bg-light" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Deskripsi Kerusakan</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Jelaskan kerusakan yang ditemukan..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Foto Bukti Kerusakan</label>
                        <input type="file" name="photo_path" id="fotoLaporKerusakanPeminjaman" class="form-control" accept="image/jpeg,image/png" required>
                        @include('partials.foto-kamera', ['target' => 'fotoLaporKerusakanPeminjaman'])
                        <small class="text-danger">* Format JPG/PNG, Maksimal 2MB.</small>
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
<script>
    // Isi form lapor kerusakan dari tombol yang diklik
    document.getElementById('modalLaporKerusakan').addEventListener('show.bs.modal', function (e) {
        const b = e.relatedTarget;
        if (!b) return;
        document.getElementById('lkRoomId').value = b.dataset.roomId;
        document.getElementById('lkRoomName').value = b.dataset.roomName;
        document.getElementById('lkLocation').value = b.dataset.location;
        document.getElementById('lkPic').value = b.dataset.pic;
    });
</script>
@endif
@endsection