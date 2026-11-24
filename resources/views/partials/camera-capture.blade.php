{{--
    Fitur "Ambil Foto dengan Kamera" yang bisa dipakai ulang.
    Cara pakai: tambahkan atribut  data-camera  pada <input type="file">,
    lalu @include('partials.camera-capture') satu kali di halaman.
--}}
<script>
(function () {
    const MAX_BYTES = 2 * 1024 * 1024; // batas 2MB sesuai validasi form
    const MAX_WIDTH = 1600;

    function setFile(input, file) {
        const dt = new DataTransfer();
        dt.items.add(file);
        input.files = dt.files;
        input.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function canvasToFile(canvas, name) {
        return new Promise(function (resolve) {
            let q = 0.9;
            (function attempt() {
                canvas.toBlob(function (blob) {
                    if (blob.size > MAX_BYTES && q > 0.4) { q -= 0.1; attempt(); return; }
                    resolve(new File([blob], name, { type: 'image/jpeg' }));
                }, 'image/jpeg', q);
            })();
        });
    }

    function init(input) {
        if (input.dataset.cameraReady) return;
        input.dataset.cameraReady = '1';

        const wrap = document.createElement('div');
        wrap.className = 'mt-2';
        wrap.innerHTML =
            '<button type="button" class="btn btn-outline-primary btn-sm js-open">' +
                '<i class="bi bi-camera me-1"></i>Ambil Foto dengan Kamera</button> ' +
            '<span class="small text-muted">atau pilih foto dari file di atas</span>' +
            '<div class="js-live d-none mt-2">' +
                '<video class="w-100 rounded border bg-dark" autoplay playsinline muted style="max-height:320px"></video>' +
                '<div class="mt-2 d-flex gap-2">' +
                    '<button type="button" class="btn btn-primary btn-sm js-snap"><i class="bi bi-camera-fill me-1"></i>Ambil Foto</button>' +
                    '<button type="button" class="btn btn-outline-secondary btn-sm js-switch"><i class="bi bi-arrow-repeat me-1"></i>Ganti Kamera</button>' +
                    '<button type="button" class="btn btn-secondary btn-sm js-cancel">Batal</button>' +
                '</div>' +
            '</div>' +
            '<div class="js-preview d-none mt-2">' +
                '<img class="img-thumbnail" style="max-height:200px" alt="Hasil foto">' +
                '<div class="small text-success mt-1"><i class="bi bi-check-circle me-1"></i>Foto dari kamera siap diunggah.</div>' +
                '<button type="button" class="btn btn-outline-secondary btn-sm mt-1 js-retake">Ulangi</button>' +
            '</div>' +
            // Cadangan untuk HP / koneksi non-HTTPS: membuka aplikasi kamera bawaan
            '<input type="file" accept="image/*" capture="environment" class="d-none js-fallback">';
        input.insertAdjacentElement('afterend', wrap);

        const $ = function (s) { return wrap.querySelector(s); };
        const video = $('video'), live = $('.js-live'), preview = $('.js-preview'),
              img = $('.js-preview img'), fallback = $('.js-fallback'), openBtn = $('.js-open');
        let stream = null, facing = 'environment';

        function stop() {
            if (stream) { stream.getTracks().forEach(function (t) { t.stop(); }); stream = null; }
            video.srcObject = null;
            live.classList.add('d-none');
            openBtn.classList.remove('d-none');
        }

        async function start() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                fallback.click(); // tidak didukung / bukan HTTPS
                return;
            }
            try {
                if (stream) stream.getTracks().forEach(function (t) { t.stop(); });
                stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: { ideal: facing }, width: { ideal: 1920 } }, audio: false
                });
                video.srcObject = stream;
                live.classList.remove('d-none');
                preview.classList.add('d-none');
                openBtn.classList.add('d-none');
            } catch (e) {
                const denied = e && (e.name === 'NotAllowedError' || e.name === 'SecurityError');
                alert(denied
                    ? 'Akses kamera ditolak. Izinkan kamera pada browser (ikon gembok di address bar), lalu coba lagi.'
                    : 'Kamera tidak dapat dibuka. Anda bisa memilih foto dari file.');
            }
        }

        openBtn.addEventListener('click', start);
        $('.js-retake').addEventListener('click', start);
        $('.js-cancel').addEventListener('click', stop);
        $('.js-switch').addEventListener('click', function () {
            facing = facing === 'environment' ? 'user' : 'environment';
            start();
        });

        $('.js-snap').addEventListener('click', async function () {
            if (!video.videoWidth) return;
            const scale = Math.min(1, MAX_WIDTH / video.videoWidth);
            const canvas = document.createElement('canvas');
            canvas.width = Math.round(video.videoWidth * scale);
            canvas.height = Math.round(video.videoHeight * scale);
            canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
            const file = await canvasToFile(canvas, 'kamera-' + Date.now() + '.jpg');
            setFile(input, file);
            img.src = URL.createObjectURL(file);
            preview.classList.remove('d-none');
            stop();
        });

        fallback.addEventListener('change', function () {
            if (fallback.files[0]) {
                setFile(input, fallback.files[0]);
                img.src = URL.createObjectURL(fallback.files[0]);
                preview.classList.remove('d-none');
            }
        });

        // Jika user memilih file manual, sembunyikan preview kamera
        input.addEventListener('change', function (e) {
            if (e.isTrusted) preview.classList.add('d-none');
        });

        // Matikan kamera saat modal ditutup
        const modal = input.closest('.modal');
        if (modal) modal.addEventListener('hidden.bs.modal', function () {
            stop();
            preview.classList.add('d-none');
        });
    }

    function boot() { document.querySelectorAll('input[type=file][data-camera]').forEach(init); }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot); else boot();
})();
</script>
