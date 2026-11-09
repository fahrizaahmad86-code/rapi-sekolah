<?php
// ===== 1. Tambahkan di bagian atas UserController.php (bersama use lainnya) =====
use App\Imports\GuruImport;
use App\Exports\TemplateGuruExport;
use Maatwebsite\Excel\Facades\Excel;

// ===== 2. Tambahkan dua method ini di dalam class UserController =====

    public function importGuru(Request $request)
    {
        if (auth()->user()->role_id != 1) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $request->validate([
            'file_guru' => 'required|file|mimes:xlsx,xls,csv|max:2048',
        ], [
            'file_guru.required' => 'Pilih file terlebih dahulu.',
            'file_guru.mimes'    => 'File harus berformat .xlsx, .xls, atau .csv.',
            'file_guru.max'      => 'Ukuran file maksimal 2 MB.',
        ]);

        // Hash password butuh waktu per baris, jadi beri batas waktu lebih longgar
        set_time_limit(300);

        $import = new GuruImport();

        try {
            Excel::import($import, $request->file('file_guru'));
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'File tidak bisa dibaca. Pastikan memakai template yang disediakan.');
        }

        $redirect = redirect()->back();

        if ($import->berhasil > 0) {
            $redirect->with('success', "{$import->berhasil} guru berhasil diimpor.");
        }

        if (!empty($import->gagal)) {
            $redirect->with('import_errors', $import->gagal);
            if ($import->berhasil === 0) {
                $redirect->with('error', 'Tidak ada data yang berhasil diimpor.');
            }
        }

        return $redirect;
    }

    public function templateGuru()
    {
        if (auth()->user()->role_id != 1) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        return Excel::download(new TemplateGuruExport, 'template-import-guru.xlsx');
    }
