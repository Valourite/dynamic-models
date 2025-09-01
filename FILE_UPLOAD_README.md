# Dynamic Models Package File Upload Recommendations

## File Upload Storage

By default, file uploads in this package use the Laravel public disk with a `dynamic-models/uploads` directory. The file uploads are stored in the `public` disk to ensure they are easily accessible.

## Integration with Spatie Media Library

For more advanced file handling, we recommend integrating with the Spatie Media Library package. This package provides:

1. Automatic file organization with collections
2. On-the-fly image manipulations and conversions
3. Easy attachment of media to any model
4. Responsive images

### How to Integrate with Spatie Media Library

1. Install the package:

```bash
composer require spatie/laravel-medialibrary
```

2. Publish and run the migrations:

```bash
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="migrations"
php artisan migrate
```

3. Publish the config file:

```bash
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="config"
```

4. Make your model use media:

```php
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class YourModel extends Model implements HasMedia
{
    use InteractsWithMedia;
    
    // ...
}
```

5. Modify your dynamic model implementation to use Spatie's media library for file uploads.

For full documentation, visit: https://spatie.be/docs/laravel-medialibrary

## Custom Implementation

You can also implement your own file handling system by extending this package and overriding the file upload functionality in the FieldRenderer class.
