<?php

namespace Spatie\MailcoachUnlayer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'file' => 'required|image',
        ];
    }
}
