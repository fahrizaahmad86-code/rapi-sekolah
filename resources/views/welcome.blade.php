@extends('layouts.app')

@section('content')
    <style>
    .card {
        border-radius: 12px !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03) !important;
    }

    .text-navy {
        color: #1E293B !important;
    }

    .fc .fc-button-primary {
        background-color: #0EA5E9 !important;
        border-color: #0EA5E9 !important;
        border-radius: 8px !important; 
        font-weight: 500;
        text-transform: capitalize;
    }
    
    /* Sabtu, Minggu & hari libur nasional -> merah */
    .fc .fc-daygrid-day.hari-libur {
        background-color: #FECACA !important;
    }
    .fc .fc-daygrid-day.hari-libur .fc-daygrid-day-number {
        color: #B91C1C !important;
        font-weight: 600;
    }
    .fc .fc-daygrid-day.hari-libur.fc-day-today {
        box-shadow: inset 0 0 0 2px #F59E0B;
    }
    .fc .nama-libur {
        flex: 1;
        min-width: 0;
        padding: 2px 0 0 4px;
        font-size: 10px;
        line-height: 1.2;
        font-weight: 600;
        color: #B91C1C;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Legenda */
    .legenda-kalender { display: flex; flex-wrap: wrap; gap: 6px 16px; font-size: 13px; }
    .legenda-kalender .item { display: inline-flex; align-items: center; gap: 6px; }
    .legenda-kalender .kotak { width: 14px; height: 14px; border-radius: 4px; display: inline-block; }

    .fc .fc-button-primary:not(:disabled):active,
    .fc .fc-button-primary:not(:disabled).fc-button-active,
    .fc .fc-button-primary:hover {
        background-color: #0284C7 !important; 
        border-color: #0284C7 !important;
    }
    </style>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold mb-0 text-navy">Kalender Agenda Sekolah</h4>
                <p class="text-muted mb-0">Menampilkan daftar agenda dan peminjaman ruangan yang telah <strong>Disetujui</strong> bulan ini.</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-2">
                    <div class="legenda-kalender px-2 pt-2 pb-3">
                        @foreach(\App\Models\Event::JENIS_AGENDA as $j)
                            <span class="item"><span class="kotak" style="background: {{ $j['warna'] }}"></span> {{ $j['label'] }} <span class="text-muted">({{ $j['keterangan'] }})</span></span>
                        @endforeach
                        <span class="item"><span class="kotak" style="background: #FECACA; border: 1px solid #F87171"></span> Sabtu, Minggu &amp; Libur Nasional</span>
                    </div>
                    <div id="calendar"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-3">
    <div class="card shadow-sm border-0 h-100">
        <div class="card-header bg-white fw-bold py-3 border-0 d-flex justify-content-between align-items-center">
            <span> Agenda Mendatang</span>
            <span class="badge bg-primary rounded-pill">{{ $upcomingEvents->count() ?? 0}}</span>
        </div>
        <div class="card-body p-2" style="max-height: 420px; overflow-y: auto;">
            @forelse($upcomingEvents as $item)
                <div class="p-2 mb-2 bg-light rounded border-start border-4 shadow-sm" style="border-color: {{ $item->warna }} !important;">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-bold text-dark fs-6">{{ $item->event_name }}</div>
                            <span class="badge text-white fw-normal" style="background-color: {{ $item->warna }};">{{ $item->label_jenis }}</span>
                        </div>
                        <span class="badge bg-info text-dark ms-2">
                            {{ \Carbon\Carbon::parse($item->date)->translatedFormat('d M') }}
                        </span>
                    </div>
                    
                    <div class="mt-2 small text-muted">
                        <div class="d-flex align-items-center mb-1">
                            <span>{{ $item->room->room_name ?? 'Lokasi Belum Ditentukan' }}</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <span> {{ \App\Support\SesiPeminjaman::labelDenganJam((int) $item->start_session, (int) $item->end_session) }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                    <small>Tidak ada agenda kegiatan dalam waktu dekat.</small>
                </div>
            @endforelse
        </div>
    </div>
</div>

<div class="modal fade" id="eventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">Detail Acara</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <th width="35%" class="text-muted">Nama Acara</th>
                        <td width="5%">:</td>
                        <td id="modalEventName" class="fw-bold text-dark"></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Jenis Agenda</th>
                        <td>:</td>
                        <td><span id="modalJenis" class="badge text-white"></span> <small id="modalKeterangan" class="text-muted d-block mt-1"></small></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Ruangan</th>
                        <td>:</td>
                        <td id="modalRoomName"></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Waktu</th>
                        <td>:</td>
                        <td id="modalTime"></td>
                    </tr>
                    <tr>
                        <th class="text-muted">PIC Ruangan</th>
                        <td>:</td>
                        <td id="modalPic"></td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>


<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales-all.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var hariLibur = @json($hariLibur);

        // 'YYYY-MM-DD' dari tanggal lokal (hindari geser hari karena UTC)
        function tglKey(d) {
            var m = String(d.getMonth() + 1).padStart(2, '0');
            var t = String(d.getDate()).padStart(2, '0');
            return d.getFullYear() + '-' + m + '-' + t;
        }
        
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            themeSystem: 'bootstrap5',
            locale: 'id',
            height: 'auto',
            aspectRatio: 1.8,
            dayMaxEvents: 2,
            events: @json($calendarEvents),

            // Sabtu (6), Minggu (0) & hari libur nasional -> kelas merah
            dayCellClassNames: function(arg) {
                var dow = arg.date.getDay();
                if (dow === 0 || dow === 6 || hariLibur[tglKey(arg.date)]) {
                    return ['hari-libur'];
                }
                return [];
            },
            dayCellDidMount: function(arg) {
                var nama = hariLibur[tglKey(arg.date)];
                if (!nama) return;
                arg.el.title = nama;
                var top = arg.el.querySelector('.fc-daygrid-day-top');
                if (top) {
                    var label = document.createElement('div');
                    label.className = 'nama-libur';
                    label.textContent = nama;
                    top.appendChild(label);
                }
            },
            
            eventClick: function(info) {
                info.jsEvent.preventDefault();
                
                document.getElementById('modalEventName').innerText = info.event.title;
                var jenis = document.getElementById('modalJenis');
                jenis.innerText = info.event.extendedProps.jenis;
                jenis.style.backgroundColor = info.event.backgroundColor;
                document.getElementById('modalKeterangan').innerText = info.event.extendedProps.keterangan;
                document.getElementById('modalRoomName').innerText = info.event.extendedProps.ruang;
                document.getElementById('modalTime').innerText = info.event.extendedProps.waktu;
                document.getElementById('modalPic').innerText = info.event.extendedProps.pic;

                var eventModal = new bootstrap.Modal(document.getElementById('eventModal'));
                eventModal.show();
            }
        });
        
        calendar.render();
    });
</script>
@endsection