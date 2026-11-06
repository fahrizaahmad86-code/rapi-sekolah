<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Room;
use App\Models\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        DB::table('roles')->updateOrInsert(['id' => 1], ['role_name' => 'Waka Sarpras']);
        DB::table('roles')->updateOrInsert(['id' => 2], ['role_name' => 'Guru']);
        DB::table('roles')->updateOrInsert(['id' => 3], ['role_name' => 'PIC Ruangan']);
        DB::table('roles')->updateOrInsert(['id' => 4], ['role_name' => 'Organisasi']);

        $waka = User::create([
            'fullname' => 'Waka Sarpras',
            'username' => 'admin',
            'email'    => 'admin@rapi-sekolah.test',
            'password' => Hash::make('waka123'),
            'role_id'  => 1,
        ]);

        $guru = User::create([
            'fullname' => 'Diah Utaminingsih',
            'username' => 'budiah',
            'email'    => 'budiah@rapi-sekolah.test',
            'password' => Hash::make('diah123'),
            'role_id'  => 2,
        ]);

        $pic1 = User::create([
            'fullname' => 'Budi Santosa',
            'username' => 'budi',
            'email'    => 'budi@rapi-sekolah.test',
            'password' => Hash::make('budi123'),
            'role_id'  => 3,
        ]);

        $pic2 = User::create([
            'fullname' => 'Wildan',
            'username' => 'wilpoy',
            'email'    => 'wilpoy@rapi-sekolah.test',
            'password' => Hash::make('wilpoy123'),
            'role_id'  => 3,
        ]);

        $organisasi = User::create([
            'fullname' => 'OSIS SMKN 1 Bantul',
            'username' => 'osis',
            'email'    => 'osis@rapi-sekolah.test',
            'password' => Hash::make('osis123'),
            'role_id'  => 4,
        ]);

        $ruangLab = Room::create([
            'room_name' => 'Lab Komputer 18',
            'capacity'  => 36,
            'location'  => 'Lorong, Dekat kantor guru RPL',
            'pic_id'    => $pic1->id,
            'is_active' => true,
        ]);

        $ruangAula = Room::create([
            'room_name' => 'Aula Bawah',
            'capacity'  => 200,
            'location'  => 'Lantai 1, Aula Utama',
            'pic_id'    => null,
            'is_active' => true,
        ]);

        $ruangWijaya = Room::create([
            'room_name' => 'Wijaya Kusuma',
            'capacity'  => 135,
            'location'  => 'Lantai 1, sebelah timur taman',
            'pic_id'    => null,
            'is_active' => true,
        ]);

        Event::create([
            'room_id'       => $ruangLab->id,
            'user_id'       => $guru->id, 
            'event_name'    => 'Pelatihan Desain Grafis',
            'date'          => date('Y-m-d'),
            'start_session' => 1,
            'end_session'   => 4,
            'status'        => 'Approved',
            'document_path' => null,
            'move_reason'   => null,
        ]);

        Event::create([
            'room_id'       => $ruangAula->id,
            'user_id'       => $organisasi->id,
            'event_name'    => 'Persiapan Pentas Seni',
            'date'          => date('Y-m-d', strtotime('+1 days')),
            'start_session' => 5,
            'end_session'   => 8,
            'status'        => 'Pending',
            'document_path' => null,
            'move_reason'   => null,
        ]);
    }
}