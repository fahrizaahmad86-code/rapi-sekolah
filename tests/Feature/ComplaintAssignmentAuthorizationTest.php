<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ComplaintAssignmentAuthorizationTest extends TestCase
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

    public function test_pic_only_sees_and_can_update_complaints_assigned_to_them(): void
    {
        $wildan = $this->pic('wildan');
        $budi = $this->pic('budi');
        $wildanComplaint = $this->complaintAssignedTo($wildan);
        $budiComplaint = $this->complaintAssignedTo($budi);

        $this->actingAs($wildan)
            ->get(route('pengaduan.index'))
            ->assertOk()
            ->assertSee($wildanComplaint->description)
            ->assertSee($budiComplaint->description)
            ->assertSee('modalUpdateStatus' . $wildanComplaint->id)
            ->assertDontSee('modalUpdateStatus' . $budiComplaint->id);

        $this->put(route('pengaduan.update', $budiComplaint), [
            'status_repair' => 'Menunggu Spare Part',
        ])->assertNotFound();

        $this->assertDatabaseHas('complaints', [
            'id' => $budiComplaint->id,
            'status_repair' => 'Belum Diperbaiki',
        ]);

        $this->put(route('pengaduan.update', $wildanComplaint), [
            'status_repair' => 'Menunggu Spare Part',
        ])->assertRedirect();

        $this->assertDatabaseHas('complaints', [
            'id' => $wildanComplaint->id,
            'status_repair' => 'Menunggu Spare Part',
        ]);
    }

    private function pic(string $username): User
    {
        return User::create([
            'fullname' => ucfirst($username),
            'username' => $username,
            'email' => $username . '@example.test',
            'password' => 'password',
            'role_id' => 3,
        ]);
    }

    private function complaintAssignedTo(User $pic): Complaint
    {
        $room = Room::create([
            'room_name' => 'Ruang ' . $pic->fullname,
            'capacity' => 20,
            'location' => 'Lantai 1',
        ]);

        return Complaint::create([
            'room_id' => $room->id,
            'reporter_id' => $pic->id,
            'handler_id' => $pic->id,
            'description' => 'Laporan khusus ' . $pic->username,
            'photo_path' => 'complaints/test.jpg',
            'status_repair' => 'Belum Diperbaiki',
        ]);
    }
}
