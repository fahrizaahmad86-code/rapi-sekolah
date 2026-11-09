<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Room;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EventAgendaTypeTest extends TestCase
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
        Storage::fake('public');

        Role::create(['role_name' => 'Waka Sarpras']);
        Role::create(['role_name' => 'Guru']);
    }

    public function test_teacher_can_submit_all_supported_agenda_types(): void
    {
        $teacher = User::create([
            'fullname' => 'Guru Penguji',
            'username' => 'gurupenguji',
            'email' => 'guru@example.test',
            'password' => 'password',
            'role_id' => 2,
        ]);
        $room = Room::create([
            'room_name' => 'Aula',
            'capacity' => 100,
            'location' => 'Gedung Utama',
            'is_active' => true,
        ]);

        $this->actingAs($teacher);

        foreach (array_keys(Event::JENIS_AGENDA) as $index => $agendaType) {
            $response = $this->post(route('peminjaman.store'), [
                'room_id' => $room->id,
                'event_name' => 'Agenda ' . $agendaType,
                'agenda_type' => $agendaType,
                'date' => now()->addDays($index + 1)->toDateString(),
                'start_session' => 1,
                'end_session' => 2,
                'document_path' => UploadedFile::fake()->image('surat.jpg'),
            ]);

            $response->assertRedirect();
            $this->assertDatabaseHas('events', [
                'event_name' => 'Agenda ' . $agendaType,
                'agenda_type' => $agendaType,
                'user_id' => $teacher->id,
            ]);
        }

        $this->assertSame(count(Event::JENIS_AGENDA), Event::count());
    }
}
