<?php

namespace Spatie\MailcoachUnlayer\Tests;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MailcoachUnlayer\Models\Upload;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class UploadControllerTest extends TestCase
{
    /** @test * */
    public function it_uploads_files_and_returns_the_absolute_url()
    {
        Storage::fake('public');

        $this->postJson(route('mailcoach-unlayer.upload'), [
            'file' => UploadedFile::fake()->image('my-upload.jpg', 100, 100),
        ])->assertSuccessful()->assertJson(['url' => 'http://localhost/storage/1/conversions/my-upload-image.jpg']);

        $this->assertEquals(1, Upload::count());
        $this->assertEquals(1, Media::count());
        $this->assertEquals(1, Upload::first()->getMedia()->count());
    }

    /** @test * */
    public function it_must_be_an_image()
    {
        $this->withExceptionHandling();
        Storage::fake('public');

        $this->postJson(route('mailcoach-unlayer.upload'), [
            'file' => UploadedFile::fake()->create('my-upload.jpg', 100, 'text/csv'),
        ])->assertStatus(422)->assertJsonValidationErrors('file');

        $this->assertEquals(0, Upload::count());
        $this->assertEquals(0, Media::count());
    }
}
