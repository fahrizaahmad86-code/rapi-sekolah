<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;
use OpenSpout\Writer\XLSX\Writer;
use Tests\TestCase;

class GuruImportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');
        Artisan::call('migrate:fresh', ['--database' => 'sqlite']);
    }

    public function test_admin_can_import_guru_from_xlsx_and_skip_invalid_rows(): void
    {
        $this->signInAsWaka();

        $file = $this->xlsxUpload([
            ['nama_lengkap', 'username', 'email', 'password'],
            ['Guru Baru', 'gurubaru', 'guru.baru@example.test', ''],
            ['Duplikat', 'gurubaru', 'duplikat@example.test', 'rahasia123'],
        ]);

        $response = $this->post(route('user.import-guru'), ['file_guru' => $file]);

        $response->assertRedirect();
        $response->assertSessionHas('success', '1 guru berhasil diimpor.');
        $response->assertSessionHas('import_errors');

        $guru = User::where('username', 'gurubaru')->firstOrFail();
        $this->assertSame(2, $guru->role_id);
        $this->assertTrue(Hash::check('guru12345', $guru->password));
        $this->assertDatabaseCount('users', 2);

        $this->post(route('logout'));
        $this->post(route('login'), [
            'username' => 'gurubaru',
            'password' => 'guru12345',
        ])->assertRedirect('/guru/dashboard');
    }

    public function test_import_rejects_workbooks_with_unrecognized_headers(): void
    {
        $this->signInAsWaka();

        $response = $this->post(route('user.import-guru'), [
            'file_guru' => $this->xlsxUpload([
                ['NIP', 'Status'],
                ['12345', 'Aktif'],
            ]),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Tidak ada data yang berhasil diimpor.');
        $response->assertSessionHas('import_errors');
        $this->assertDatabaseCount('users', 1);
    }

    public function test_xlsx_with_only_teacher_names_imports_with_generated_usernames_and_defaults(): void
    {
        $this->signInAsWaka();

        $response = $this->post(route('user.import-guru'), [
            'file_guru' => $this->xlsxUpload([
                ['Daftar Guru SMK'],
                ['Nama Guru', 'NIP', 'Status'],
                ['Budi Santoso', '12345', 'Aktif'],
                ['Budi Santoso', '67890', 'Aktif'],
            ]),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', '2 guru berhasil diimpor.');
        $this->assertDatabaseHas('users', [
            'fullname' => 'Budi Santoso',
            'username' => 'budisantoso',
            'email' => null,
            'role_id' => 2,
        ]);
        $this->assertDatabaseHas('users', [
            'fullname' => 'Budi Santoso',
            'username' => 'budisantoso2',
            'email' => null,
            'role_id' => 2,
        ]);

        $this->assertTrue(Hash::check('guru12345', User::where('username', 'budisantoso')->firstOrFail()->password));
    }

    public function test_admin_can_import_guru_from_csv(): void
    {
        $this->signInAsWaka();

        $response = $this->post(route('user.import-guru'), [
            'file_guru' => UploadedFile::fake()->createWithContent(
                'guru.csv',
                "nama_lengkap,username,email,password\nGuru CSV,gurucsv,guru.csv@example.test,rahasia123\n"
            ),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', '1 guru berhasil diimpor.');
        $this->assertDatabaseHas('users', [
            'username' => 'gurucsv',
            'role_id' => 2,
        ]);
    }

    public function test_admin_can_download_an_xlsx_template(): void
    {
        $this->signInAsWaka();

        $response = $this->get(route('user.template-guru'));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->assertHeader('content-disposition');

        $path = $response->baseResponse->getFile()->getPathname();
        $reader = new XlsxReader();
        $reader->open($path);
        $rows = [];
        try {
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    $rows[] = $row->toArray();
                }
                break;
            }
        } finally {
            $reader->close();
            unlink($path);
        }

        $this->assertSame([['nama_lengkap', 'username', 'email', 'password']], $rows);
    }

    private function signInAsWaka(): void
    {
        Role::create(['role_name' => 'Waka Sarpras']);
        Role::create(['role_name' => 'Guru']);

        $this->actingAs(User::create([
            'fullname' => 'Admin Sekolah',
            'username' => 'admin',
            'email' => 'admin@example.test',
            'password' => 'password',
            'role_id' => 1,
        ]));
    }

    private function xlsxUpload(array $rows): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'guru-import-test-');
        $writer = new Writer();
        $writer->openToFile($path);

        foreach ($rows as $row) {
            $writer->addRow(Row::fromValues($row));
        }

        $writer->close();
        $file = UploadedFile::fake()->createWithContent('guru.xlsx', file_get_contents($path));
        unlink($path);

        return $file;
    }
}
