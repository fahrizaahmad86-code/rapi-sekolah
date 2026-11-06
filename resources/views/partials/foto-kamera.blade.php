{{--
    Tombol "Ambil Foto dengan Kamera" untuk input file foto.
    Cara pakai (taruh tepat di bawah <input type="file" id="...">):
        @include('partials.foto-kamera', ['target' => 'idInputFile'])
    Foto hasil kamera dimasukkan ke input file yang sama, jadi proses simpan di controller tidak berubah.
    Pilihan "pilih dari file" tetap memakai input file aslinya.
--}}
<div class="foto-kamera mt-2" data-target="{{ $target }}">
    <div class="d-flex flex-wrap align-items-center gap-2">
        <button type="button" class="btn btn-sm btn-outline-primary" data-kamera-buka>
            <i class="bi bi-camera"></i> Ambil Foto dengan Kamera
        </button>
        <span class="small text-muted">atau pilih foto dari file di atas</span>
    </div>

    {{-- input cadangan untuk HP / perangkat tanpa akses kamera langsung (tidak ikut terkirim, tidak punya name) --}}
    <input type="file" accept="image/*" capture="environment" class="d-none kamera-fallback">

    <div class="kamera-error small text-danger mt-2 d-none"></div>

    <div class="kamera-panel d-none mt-2">
        <video class="w-100 rounded bg-dark" style="max-height: 320px;" autoplay playsinline muted></video>
        <div class="d-flex gap-2 mt-2">
            <button type="button" class="btn btn-sm btn-success" data-kamera-jepret>
                <i class="bi bi-camera-fill"></i> Jepret
            </button>
            <button type="button" class="btn btn-sm btn-secondary" data-kamera-tutup>Batal</button>
        </div>
    </div>

    <div class="kamera-preview d-none mt-2 d-flex align-items-center gap-3">
        <img src="" alt="Pratinjau foto" class="rounded border" style="height: 80px; width: auto; max-width: 140px; object-fit: cover;">
        <div class="small">
            <div class="text-success fw-semibold"><i class="bi bi-check-circle"></i> Foto siap dikirim</div>
            <div class="text-muted kamera-nama"></div>
            <button type="button" class="btn btn-link btn-sm p-0 text-danger" data-kamera-hapus>Hapus foto</button>
        </div>
    </div>
</div>

@once
<script>
(function () {
    var aktif = null; // kamera yang sedang menyala: {stream, wrap}

    function $(wrap, sel) { return wrap.querySelector(sel); }
    function inputDari(wrap) { return document.getElementById(wrap.dataset.target); }

    function tampilError(wrap, pesan) {
        var el = $(wrap, '.kamera-error');
        el.textContent = pesan;
        el.classList.toggle('d-none', !pesan);
    }

    function matikan() {
        if (!aktif) return;
        aktif.stream.getTracks().forEach(function (t) { t.stop(); });
        var v = $(aktif.wrap, 'video');
        v.srcObject = null;
        $(aktif.wrap, '.kamera-panel').classList.add('d-none');
        aktif = null;
    }

    // Samakan tampilan widget dengan isi input file (preview, tombol aktif/nonaktif)
    function sinkron(wrap) {
        var input = inputDari(wrap);
        if (!input) return;
        $(wrap, '[data-kamera-buka]').disabled = input.disabled;
        if (input.disabled) { matikan(); tampilError(wrap, ''); }

        var prev  = $(wrap, '.kamera-preview');
        var img   = $(wrap, '.kamera-preview img');
        var file  = input.files && input.files[0];
        if (img.dataset.url) { URL.revokeObjectURL(img.dataset.url); img.dataset.url = ''; }
        if (file) {
            var url = URL.createObjectURL(file);
            img.src = url; img.dataset.url = url;
            $(wrap, '.kamera-nama').textContent = file.name;
            prev.classList.remove('d-none');
        } else {
            img.removeAttribute('src');
            prev.classList.add('d-none');
        }
    }

    function isiInput(input, file) {
        var dt = new DataTransfer();
        dt.items.add(file);
        input.files = dt.files;
        input.dispatchEvent(new Event('change', { bubbles: true }));
    }

    async function bukaKamera(wrap) {
        var input = inputDari(wrap);
        if (!input || input.disabled) return;
        matikan();
        tampilError(wrap, '');

        var md = navigator.mediaDevices;
        if (!(window.isSecureContext && md && md.getUserMedia)) {
            // HP: pakai kamera bawaan lewat input capture. Komputer: kamera langsung butuh HTTPS.
            if (window.matchMedia('(pointer: coarse)').matches) {
                $(wrap, '.kamera-fallback').click();
            } else {
                tampilError(wrap, 'Kamera langsung hanya bisa dipakai lewat HTTPS (atau localhost). Silakan pilih foto dari file.');
            }
            return;
        }

        try {
            var stream = await md.getUserMedia({ video: { facingMode: { ideal: 'environment' } }, audio: false });
            var v = $(wrap, 'video');
            v.srcObject = stream;
            $(wrap, '.kamera-panel').classList.remove('d-none');
            aktif = { stream: stream, wrap: wrap };
            try { await v.play(); } catch (e) {}
        } catch (e) {
            var pesan = 'Kamera tidak bisa dibuka. Silakan pilih foto dari file.';
            if (e && e.name === 'NotAllowedError') pesan = 'Izin kamera ditolak. Izinkan akses kamera di browser, atau pilih foto dari file.';
            if (e && e.name === 'NotFoundError')   pesan = 'Kamera tidak ditemukan di perangkat ini. Silakan pilih foto dari file.';
            if (e && e.name === 'NotReadableError') pesan = 'Kamera sedang dipakai aplikasi lain. Tutup aplikasi itu lalu coba lagi.';
            tampilError(wrap, pesan);
        }
    }

    function jepret(wrap) {
        var v = $(wrap, 'video');
        var input = inputDari(wrap);
        if (!aktif || !v.videoWidth) return;

        var skala = Math.min(1, 1600 / Math.max(v.videoWidth, v.videoHeight));
        var c = document.createElement('canvas');
        c.width = Math.round(v.videoWidth * skala);
        c.height = Math.round(v.videoHeight * skala);
        c.getContext('2d').drawImage(v, 0, 0, c.width, c.height);

        // Server membatasi 2MB, jadi kecilkan kualitas kalau perlu
        function simpan(kualitas) {
            c.toBlob(function (blob) {
                if (!blob) { tampilError(wrap, 'Gagal mengambil foto. Coba lagi.'); return; }
                if (blob.size > 1.9 * 1024 * 1024 && kualitas > 0.4) { simpan(kualitas - 0.2); return; }
                isiInput(input, new File([blob], 'foto-kamera-' + Date.now() + '.jpg', { type: 'image/jpeg' }));
                matikan();
            }, 'image/jpeg', kualitas);
        }
        simpan(0.85);
    }

    document.addEventListener('click', function (e) {
        var wrap = e.target.closest('.foto-kamera');
        if (!wrap) return;
        if (e.target.closest('[data-kamera-buka]'))   bukaKamera(wrap);
        if (e.target.closest('[data-kamera-jepret]')) jepret(wrap);
        if (e.target.closest('[data-kamera-tutup]'))  matikan();
        if (e.target.closest('[data-kamera-hapus]')) {
            var input = inputDari(wrap);
            input.value = '';
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }
    });

    // Foto dari kamera bawaan HP (input cadangan) -> masukkan ke input utama
    document.addEventListener('change', function (e) {
        var wrap = e.target.closest ? e.target.closest('.foto-kamera') : null;
        if (wrap && e.target.classList.contains('kamera-fallback')) {
            if (e.target.files[0]) isiInput(inputDari(wrap), e.target.files[0]);
            e.target.value = '';
            return;
        }
        // Pilih lewat file biasa -> ikut tampil di pratinjau
        if (e.target.type === 'file' && e.target.id) {
            var w = document.querySelector('.foto-kamera[data-target="' + e.target.id + '"]');
            if (w) sinkron(w);
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.foto-kamera').forEach(function (wrap) {
            sinkron(wrap);
            // input foto bisa dinonaktifkan/diaktifkan oleh script lain (mis. status "Sudah Diperbaiki")
            var input = inputDari(wrap);
            if (input) new MutationObserver(function () { sinkron(wrap); }).observe(input, { attributes: true, attributeFilter: ['disabled'] });
        });
    });

    // Matikan kamera saat modal ditutup atau halaman ditinggalkan
    document.addEventListener('hidden.bs.modal', matikan);
    window.addEventListener('beforeunload', matikan);
})();
</script>
@endonce
