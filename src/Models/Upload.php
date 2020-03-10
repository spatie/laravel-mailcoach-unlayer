<?php

namespace Spatie\MailcoachUnlayer\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Image\Manipulations;
use Spatie\Mailcoach\Models\Campaign;
use Spatie\Mailcoach\Models\Template;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Upload extends Model implements HasMedia
{
    use InteractsWithMedia;

    public $table = 'mailcoach_uploads';

    public $guarded = [];

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('image')
            ->fit(
                Manipulations::FIT_MAX,
                config('mailcoach.unlayer.max_width', 1500),
                config('mailcoach.unlayer.max_height', 1500)
            )
            ->nonQueued();
    }

    public function templates(): BelongsToMany
    {
        return $this->belongsToMany(Template::class, 'mailcoach_template_uploads');
    }

    public function campaigns(): BelongsToMany
    {
        return $this->belongsToMany(Campaign::class, 'mailcoach_campaign_uploads');
    }
}
