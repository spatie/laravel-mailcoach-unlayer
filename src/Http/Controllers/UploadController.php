<?php

namespace Spatie\MailcoachUnlayer\Http\Controllers;

use Spatie\MailcoachUnlayer\Http\Requests\UploadRequest;
use Spatie\MailcoachUnlayer\Models\Upload;

class UploadController
{
    public function __invoke(UploadRequest $request)
    {
        $diskName = config('mailcoach.unlayer.disk_name') ?? config('media-library.disk_name') ?? config('medialibrary.disk_name') ?? 'public';
        
        $upload = Upload::create();
        $media = $upload
            ->addMediaFromRequest('file')
            ->toMediaCollection(
                'default',
                $diskName,
            );

        return response()->json(['url' => $media->getFullUrl('image')]);
    }
}
