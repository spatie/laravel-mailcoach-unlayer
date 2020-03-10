<?php

use Spatie\MailcoachUnlayer\Http\Controllers\UploadController;

Route::post('uploads', '\\' . UploadController::class)->name('mailcoach-unlayer.upload');
