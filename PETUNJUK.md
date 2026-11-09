# Fitur: 4 Jenis Agenda (Internal / Khusus / Guru / Umum) + Hari Libur Merah

1. Timpa 7 file di zip ini ke projek (struktur folder sama).
   Jika sebelumnya sudah memasang versi lama, HAPUS file:
   database/migrations/2026_11_08_000001_add_audience_to_events_table.php
2. Jalankan:
   php artisan migrate
   php artisan config:clear
   php artisan view:clear
3. Agenda lama otomatis tercatat sebagai "Internal" (biru).

Jenis & warna diatur di app/Models/Event.php (JENIS_AGENDA).
Hari libur nasional diatur di config/hari_libur.php.
