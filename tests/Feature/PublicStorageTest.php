<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicStorageTest extends TestCase
{
    public function test_public_files_can_be_opened_without_a_storage_symlink(): void
    {
        Storage::fake('public');
        Storage::disk('public')->putFileAs('complaints', UploadedFile::fake()->image('photo.jpg'), 'photo.jpg');

        $response = $this->get(route('public.storage', ['path' => 'complaints/photo.jpg']));

        $response->assertOk();
        $this->assertStringStartsWith('image/jpeg', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('inline', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('/media/complaints/photo.jpg', route('public.storage', [
            'path' => 'complaints/photo.jpg',
        ]));
    }
}
