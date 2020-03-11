# Mailcoach Unlayer Editor

This package is an optional add on for [Mailcoach](https://mailcoach.app). When installed, it adds the free version of [Unlayer editor](https://unlayer.com), a beautiful drag and drop editor, to Mailcoach. Should you want to customize the looks or need extra behaviour, take a look at [the pricing plans of Unlayer](https://unlayer.com/pricing).

![screenshot](./docs/screenshot.png)


## Installation

You can install the package via composer:

```bash
composer require spatie/laravel-mailcoach-unlayer
```

### Publish and run the migration

```bash
php artisan vendor:publish --provider="Spatie\MailcoachUnlayer\MailcoachUnlayerServiceProvider" --tag="mailcoach-unlayer-migrations"
php artisan migrate
```

### Add the route macro

You must register the routes needed to handle uploads. We recommend that you don't put this in your routes file, but in the map method of your `RouteServiceProvider`.

```php
Route::mailcoachUnlayer('mailcoachUnlayer');
```

## Usage

Set the `mailcoach.editor` config value to `\Spatie\MailcoachUnlayer\UnlayerEditor::class`

### Image uploads

> **Note**: Mailcoach Unlayer does not delete uploads, if you upload files and replace them, the original files will still be stored on disk.
> Make sure to clean your Uploads and Medialibrary regularly if this matters to you.

The Mailcoach Unlayer editor supports image uploads, to configure the `disk_name` and maximum images size, add the following configuration to your `mailcoach.php` config file.

```php
'unlayer' => [
    'disk_name' => env('MAILCOACH_UPLOAD_DISK', 'public'),
    'max_width' => 1500,
    'max_height' => 1500,
],
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email freek@spatie.be instead of using the issue tracker.

## Credits

- [Rias Van der Veken](https://github.com/riasvdv)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
