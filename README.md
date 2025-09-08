[![Latest Version on Packagist](https://img.shields.io/packagist/v/valourite/dynamic-models.svg?style=flat-square)](https://packagist.org/packages/valourite/dynamic-models)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](MIT)
[![Total Downloads](https://img.shields.io/packagist/dt/valourite/dynamic-models.svg?style=flat-square)](https://packagist.org/packages/valourite/dynamic-models)


# Dynamic Models for Filament & Laravel

**Dynamic Models** is a Laravel package built on top of [Filament v4](https://filamentphp.com) that allows user to define base models which they can then add information to depending on the form used on model creation.
- This concept can be seen as `database inheritance`. 
- The concept is that we can define a base table `User`, then create different model types for different types of users, allowing all user models that get created to `inherit` the base user table required fields as well as having to input newly required fields based on the form the model is attached to. 
- This allows us to define a single base model, and dynamically create new types for that model.

Key Features:

- **Dynamic Model Creation** - Allow the same model to host different values on different instances
- **Version control** - The ability to implement custom version control code when the model type changes, or use the default strategy implemented. 
- **Response storage** - Separate table for model instance values with schema versioning
- **Visual builder** - Repeatable model type sections and fields
- **Type safety** - Strongly typed fields with validation support

---

## Features

- Filament v4 integration - Native UI components and resource management
- Visual dynamic model builder - Create new model types with sections, fields, and options for dynamic models
- Automatic versioning - New form versions created on schema changes
- Response storage - Dedicated `model_instances` table with JSON data
- Data integrity - Responses always linked to their model type version
- Field types - Text, number, email, select, radio, date/time, and more
- Custom IDs - Unique identifiers for model type field data binding

---

## Installation

> Requires Laravel 12+ and Filament 4+

1. Install via Composer:
```bash
composer require valourite/dynamic-models
```

2. Run the installer:
```bash
php artisan dynamic-models:install
```

This will:
- Publish configuration to `config/dynamic-models.php`
- Create database tables:
  - `model_types` (model type definitions)
  - `model_instances` (model instance data)

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

Use the `isDynamic` trait:

```php
use Valourite\DynamicModels\Concerns\isDynamic;

class Client extends Model
{
    use isDynamic;
    
}
```
This will allow your model to link to a model type as well as store the model instance data upon creation.
Note: Only one `model instance` can belong to one model as a `model instance` is essentially an extension of the model's required fields.

---

### 2. Update Filament components

This allows the form schema to be injected into your resource form schema. 

```php
use Valourite\DynamicModels\Filament\Support\Injectors\ModelTypeSchemaInjector;

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
                ...ModelTypeSchemaInjector::make()
            ]);
    }
}

```

This allows the table schema to be injected into your resource's table schema
- This would inidicate the type of model that has been created.

```php
use Valourite\DynamicModels\Filament\Support\Injectors\ModelTypeTableInjector;

class ClientTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),

                //inject table schema
                ...ModelTypeTableInjector::make(),
            ]);
    }
}

```

This allows the infolist schema to be injected into your resource's infolist schema. 

```php
use Valourite\DynamicModels\Filament\Support\Injectors\ModelTypeInfoListInjector;

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
                ...ModelTypeInfoListInjector::make()
            ])
    }
}

```

---

### 3. Add Traits Into The Create And Edit Page Of Your Resource

```php

class CreateClient extends CreateRecord
{
    use HandlesModelInstance;
    use CustomNotification;

    protected static string $resource = ClientResource::class;
}
```

```php

class EditClient extends EditRecord
{
    use HandlesModelInstance;
    use CustomNotification;
    
    protected static string $resource = ClientResource::class;
}
```

This handles:

* Saving the model type values into `model_instance`
* Linking the correct `model_type_id`, version, and structure
* Allowing updates to be made to the model instance values

We can display a custom message set inside the model type by making use of the trait `CustomNotification`

---

## How It Works

1. **Form Creation**:
   - Model Ttpes are created in the Filament admin with versioned schemas
   - Each schema change creates a new model type with an updated version (depends on config settings)
   - All changes to model type data unrelated to the model type schema will not generate a new model type, but rather update the current version (depends on config settings)
   - Existing model instance values remain linked to their original model type version

2. **Model Instance Handling**:
   - Model instance values are stored in `model_instances` table when a new model is created from a model type
   - Each model instance references the exact model type version used
   - Data stored as JSON with field IDs as keys

3. **Data Integrity**:
   - Model Type schema changes don't affect existing responses
   - Model instances will always link to the model type they were generated from
   - Historical data remains viewable with original schema
   - Version tracking through semantic versioning (major.minor.patch)

---

## Testing

No tests have been written as of yet

---

## Roadmap

* [x] Core form builder implementation
* [x] Version control system
* [x] Response storage system
* [x] Filament v4 integration
* [x] Extract model instance json data in seperate key:value table
* [x] Add more functionality to sections (non-collapsible)
* [x] File upload field support
* [x] More customization on fields and sections
* [x] Allow users to hook into modelType lifecycle
* [ ] Allow user to style infolist and form sections
* [ ] Implementing prefix and suffix icons with colour handling
* [ ] Advanced validation rules

---

## 📄 License

MIT © [Dayne Valourite](https://github.com/dayne-valourite)

