[![Latest Version on Packagist](https://img.shields.io/packagist/v/dayne-valourite/dynamic-models.svg?style=flat-square)](https://packagist.org/packages/valourite/dynamic-models)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](MIT)
[![Total Downloads](https://img.shields.io/packagist/dt/dayne-valourite/dynamic-models.svg?style=flat-square)](https://packagist.org/packages/valourite/dynamic-models)


# Dynamic Models for Filament & Laravel

**Dynamic Models** is a Laravel package built on top of [Filament v4](https://filamentphp.com) that allows user to define base models which they can then add information to depending on the form used on model creation.
- This concept can be seen as `database inheritance`. 
- The concept is that we can define a base table `User`, then create different forms for different types of users, allowing all user models that get created to `inherit` the base user table required fields as well as having to input newly required fields based on the form the model is attached to. 
- This allows us to define a single base model, and dynamically create new types for that model.

Key Features:

- **Dynamic Model Creation** - Allow the same model to host different values on different instances
- **Version control** - New version created automatically when forms are modified
- **Response storage** - Separate table for form responses with schema versioning
- **Visual builder** - Repeatable form sections and fields
- **Type safety** - Strongly typed fields with validation support

---

## Features

- Filament v4 integration - Native UI components and resource management
- Visual dynamic model builder - Create forms with sections, fields, and options for dynamic models
- Automatic versioning - New form versions created on schema changes
- Response storage - Dedicated `model_instances` table with JSON data
- Data integrity - Responses always linked to their form version
- Field types - Text, number, email, select, radio, date/time, and more
- Custom IDs - Unique identifiers for form field data binding

---

## Installation

> Requires Laravel 12+ and Filament 4+

1. Install via Composer:
```bash
composer require dayne-valourite/dynamic-models
```

2. Run the installer:
```bash
php artisan dynamic-models:install
```

This will:
- Publish configuration to `config/dynamic-models.php`
- Create database tables:
  - `forms` (form definitions)
  - `model_instances` (response data)

---

## Register the plugin

In your `PanelProvider`:

```php
use Valourite\DynamicModels\FormBuilderPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            FormBuilderPlugin::make(),
        ]);
}
```

---

## Usage

### 1. Prepare Your Model

Use the `HasResponse` trait:

```php
use Valourite\DynamicModels\Concerns\HasResponse;

class Client extends Model
{
    use HasResponse;
    
}
```
This will allow your model to link to a response as well as form.
Note: Only one response can belong to one model as a response is essentially an extension of the model's required fields.

---

### 2. Create Filament Resources

```php

class ClientInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Your existing info fields
                TextEntry::make('name'),
                TextEntry::make('email'),

                // Add form response display
                ...FormInfoListInjector::make()
            ])
    }
}

```

```php

class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Your existing fields
                TextInput::make('name')->required(),
                TextInput::make('email')->email()->required(),

                //inject form schema
                ...FormSchemaInjector::make()
            ]);
    }
}

```

This allows for the schema for the form to be injected into your models current form schema and infolist schema. 

---

### 3. Add Traits Into The Create And Edit Page Of Your Resource

```php

class CreateClient extends CreateRecord
{
    use HandlesFormResponseLifeCycle;
    use CustomFormNotification;

    protected static string $resource = ClientResource::class;
}
```

```php

class EditClient extends EditRecord
{
    use HandlesFormResponseLifeCycle;
    use CustomFormNotification;
    
    protected static string $resource = ClientResource::class;
}
```

This handles:

* Saving the form response into `form_response`
* Linking the correct `model_type_id`, version, and structure
* Allowing updates to be made to the form values

We can display a custom message set inside the form by making use of the trait `CustomFormNotification`

---

## How It Works

1. **Form Creation**:
   - Forms are created in the Filament admin with versioned schemas
   - Each schema change creates a new form with an updated version
   - All changes to form data unrelated to the form schema will not generate a new form, but rather update the current version
   - Existing responses remain linked to their original version

2. **Response Handling**:
   - Responses are stored in `model_instances` table
   - Each response references the exact form version used
   - Data stored as JSON with field IDs as keys

3. **Data Integrity**:
   - Form schema changes don't affect existing responses
   - Responses will always link to the form they were generated from
   - Historical data remains viewable with original schema
   - Version tracking through semantic versioning (major.minor.patch)

---

## Testing

No tests have been written as of yet

---

## 🚧 Roadmap

* [x] Core form builder implementation
* [x] Version control system
* [x] Response storage system
* [x] Filament v4 integration
* [ ] Extract form response json data in seperate key:value table
* [ ] File upload field support
* [ ] More customization on fields and sections
* [ ] Implementing prefix and suffix icons with colour handling
* [ ] Multi-page form wizard
* [ ] Advanced validation rules

---

## 📄 License

MIT © [Dayne Valourite](https://github.com/dayne-valourite)

