<?php

namespace App\Imports;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\ReaderInterface;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;

class GuruImport
{
    private const PASSWORD_DEFAULT = 'guru12345';

    public int $berhasil = 0;
    public array $gagal = [];

    public function import(string $filePath, string $extension): void
    {
        $roleGuruId = Role::where('role_name', 'Guru')->value('id');
        if ($roleGuruId === null) {
            $this->gagal[] = 'Role Guru tidak ditemukan. Pastikan data role sudah tersedia.';
            return;
        }

        $namaTerdaftar = User::where('role_id', $roleGuruId)
            ->pluck('fullname')
            ->mapWithKeys(fn ($nama) => [$this->normalisasiNama($nama) => true])
            ->all();

        $reader = match (strtolower($extension)) {
            'xlsx' => new XlsxReader(),
            'csv' => new CsvReader(),
            default => throw new \InvalidArgumentException('Format file tidak didukung.'),
        };

        $reader->open($filePath);

        try {
            foreach ($reader->getSheetIterator() as $sheet) {
                $headers = null;
                $headerIndexes = [];
                $baris = 0;
                $usernameDiFile = [];
                $emailDiFile = [];

                foreach ($sheet->getRowIterator() as $row) {
                    $baris++;
                    $values = array_map(
                        static fn ($value): string => trim(match (true) {
                            $value === null => '',
                            $value instanceof \DateTimeInterface => $value->format('Y-m-d H:i:s'),
                            is_scalar($value) => (string) $value,
                            default => '',
                        }),
                        $row->toArray()
                    );

                    if ($headers === null) {
                        $headerIndexes = $this->mapHeaders($values);
                        if (!isset($headerIndexes['fullname'])) {
                            continue;
                        }

                        $headers = $values;
                        continue;
                    }

                    $rowData = [];
                    foreach ($headerIndexes as $field => $index) {
                        $rowData[$field] = $values[$index] ?? '';
                    }

                    if (count(array_filter($values, static fn (string $value): bool => $value !== '')) === 0) {
                        continue;
                    }

                    $fullname = trim($rowData['fullname'] ?? '');
                    $username = trim($rowData['username'] ?? '');
                    $data = [
                        'fullname' => $fullname,
                        'username' => $username !== '' ? $username : $this->generateUsername($fullname, $usernameDiFile),
                        'email' => strtolower(trim($rowData['email'] ?? '')) ?: null,
                        'password' => trim($rowData['password'] ?? ''),
                    ];

                    $errors = Validator::make($data, [
                        'fullname' => 'required|string|max:255',
                        'username' => 'required|string|max:255|unique:users,username',
                        'email' => 'nullable|email|max:255|unique:users,email',
                        'password' => 'nullable|string|min:6',
                    ], [
                        'fullname.required' => 'Nama lengkap kosong',
                        'username.required' => 'Username kosong',
                        'username.unique' => 'Username sudah terdaftar',
                        'email.email' => 'Format email salah',
                        'email.unique' => 'Email sudah terdaftar',
                        'password.min' => 'Password minimal 6 karakter',
                    ])->errors()->all();

                    if (isset($usernameDiFile[$data['username']])) {
                        $errors[] = 'Username dobel di dalam file';
                    }
                    if ($data['email'] !== null && isset($emailDiFile[$data['email']])) {
                        $errors[] = 'Email dobel di dalam file';
                    }
                    $kunciNama = $this->normalisasiNama($data['fullname']);
                    if ($kunciNama !== '' && isset($namaTerdaftar[$kunciNama])) {
                    $errors[] = 'Guru dengan nama ini sudah terdaftar';
                    }
                    if ($errors !== []) {
                        $this->gagal[] = "Baris {$baris} ({$data['fullname']}): " . implode(', ', array_unique($errors));
                        continue;
                    }

                    DB::transaction(static function () use ($data, $roleGuruId): void {
                        User::create([
                            'fullname' => $data['fullname'],
                            'username' => $data['username'],
                            'email' => $data['email'],
                            'password' => Hash::make($data['password'] !== '' ? $data['password'] : self::PASSWORD_DEFAULT),
                            'role_id' => $roleGuruId,
                        ]);
                    });

                    $usernameDiFile[$data['username']] = true; $namaTerdaftar[$this->normalisasiNama($data['fullname'])] = true;
                    if ($data['email'] !== null) {
                        $emailDiFile[$data['email']] = true;
                    }
                    $this->berhasil++;
                }

                break;
            }

            if ($headers === null && $this->gagal === []) {
                $this->gagal[] = 'Kolom nama guru tidak ditemukan. Pastikan ada kolom Nama, Nama Guru, atau Nama Lengkap.';
            }
        } finally {
            $reader->close();
        }
    }

    private function mapHeaders(array $headers): array
    {
        $aliases = [
            'fullname' => ['nama', 'namaguru', 'namalengkap', 'fullname', 'name'],
            'username' => ['username', 'namapengguna', 'userid'],
            'email' => ['email', 'emailaddress', 'surel'],
            'password' => ['password', 'sandi', 'katasandi'],
        ];
        $indexes = [];

        foreach ($headers as $index => $header) {
            $normalized = Str::of(ltrim($header, "\xEF\xBB\xBF"))
                ->lower()
                ->ascii()
                ->replaceMatches('/[^a-z0-9]/', '')
                ->toString();

            foreach ($aliases as $field => $fieldAliases) {
                if (in_array($normalized, $fieldAliases, true)) {
                    if (isset($indexes[$field])) {
                        $this->gagal[] = "Kolom {$field} muncul lebih dari sekali pada header.";
                        return [];
                    }

                    $indexes[$field] = $index;
                    break;
                }
            }
        }

        return $indexes;
    }

    private function generateUsername(string $fullname, array $usernameDiFile): string
    {
        $base = Str::slug($fullname, '');
        $base = $base !== '' ? $base : 'guru';
        $base = Str::limit($base, 240, '');
        $username = $base;
        $suffix = 2;

        while (User::where('username', $username)->exists() || isset($usernameDiFile[$username])) {
            $username = Str::limit($base, 240, '') . $suffix;
            $suffix++;
        }

        return $username;
    }

    private function normalisasiNama(string $nama): string
    {
    return preg_replace('/\s+/u', ' ', mb_strtolower(trim($nama)));
    }
}
