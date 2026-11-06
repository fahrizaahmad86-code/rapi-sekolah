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
        <div class="card-body p-2" data-pager="list">
            @forelse($upcomingEvents as $item)
                <div class="p-2 mb-2 bg-light rounded border-start border-4 border-primary shadow-sm">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="fw-bold text-dark fs-6">{{ $item->event_name }}</div>
                        <span class="badge bg-info text-dark ms-2">
                            {{ \Carbon\Carbon::parse($item->date)->translatedFormat('d M') }}
                        </span>
                    </div>
                    
                    <div class="mt-2 small text-muted">
                        <div class="d-flex align-items-center mb-1">
                            <span>{{ $item->room->room_name ?? 'Lokasi Belum Ditentukan' }}</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <span> Sesi {{ $item->start_session }} - Sesi {{ $item->end_session }}</span>
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
        
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            themeSystem: 'bootstrap5',
            locale: 'id',
            height: 383,
            aspectRatio: 1.8,
            dayMaxEvents: 2,
            events: @json($calendarEvents), 
            
            eventClick: function(info) {
                info.jsEvent.preventDefault();
                
                document.getElementById('modalEventName').innerText = info.event.title;
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