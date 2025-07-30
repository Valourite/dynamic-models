# Form Builder for Filament & Laravel

**Form Builder** is a Laravel package built on top of [Filament v4](https://filamentphp.com) that enables visual creation and management of versioned forms. Key features:

- **Model-specific forms** - Attach forms to any Eloquent model
- **Version control** - New version created automatically when forms are modified
- **Response storage** - Separate table for form responses with schema versioning
- **Visual builder** - Drag-and-drop form construction with sections and fields
- **Type safety** - Strongly typed fields with validation support

---

## Features

- 🧩 Filament v4 integration - Native UI components and resource management
- 📝 Visual form builder - Create forms with sections, fields, and options
- 🔄 Automatic versioning - New form versions created on schema changes
- 📦 Response storage - Dedicated `form_responses` table with JSON data
- 🔒 Data integrity - Responses always linked to their form version
- ⚙️ Field types - Text, number, email, select, radio, date/time, and more
- 🏷️ Custom IDs - Unique identifiers for form field data binding
- 📊 Relationship management - Connect forms to specific Eloquent models

---

## Installation

> Requires Laravel 12+ and Filament 4+

1. Install via Composer:
```bash
composer require dayne-valourite/form-builder
```

2. Run the installer:
```bash
php artisan form-builder:install
```

This will:
- Publish configuration to `config/form-builder.php`
- Create database tables:
  - `form_builder_forms` (form definitions)
  - `form_builder_form_responses` (response data)

---

## Register the plugin

In your `PanelProvider`:

```php
use Valourite\FormBuilder\FormBuilderPlugin;

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

Use the `HasResponse` trait and relationship:

```php
use Valourite\FormBuilder\Concerns\HasResponse;

class Client extends Model
{
    use HasResponse;
    
    public function formResponses()
    {
        return $this->morphMany(FormResponse::class, 'model');
    }
}
```

---

### 2. Create Filament Resource

```php
use Valourite\FormBuilder\Filament\Support\Injectors\FormSchemaInjector;
use Valourite\FormBuilder\Filament\Support\Injectors\FormInfoListInjector;

class ClientResource extends Resource
{
    // ...
    
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Your existing fields
                TextInput::make('name')->required(),
                TextInput::make('email')->email()->required(),
                
                // Add form builder integration
                ...FormSchemaInjector::make()
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Your existing info fields
                TextEntry::make('name'),
                TextEntry::make('email'),
                
                // Add form response display
                ...FormInfoListInjector::make()
            ]);
    }
}
```

---

### 3. Use the base page class

```php
use Valourite\FormBuilder\Filament\Pages\FormBuilderCreateRecord;

class CreateClient extends FormBuilderCreateRecord
{
    protected static string $resource = ClientResource::class;
}
```

This handles:

* Dynamic rendering of the form schema
* Saving the form response into `form_response`
* Linking the correct `form_id`, version, and structure

And inside your Edit page class
```php
use Valourite\FormBuilder\Filament\Pages\FormBuilderEditRecord;

class EditClient extends FormBuilderEditRecord
{
    protected static string $resource = ClientResource::class;
}
```

This handles:
* Dynamic rendering of the form schema
* Allowing updates to be made to the form values

---

## How It Works

1. **Form Creation**:
   - Forms are created in the Filament admin with versioned schemas
   - Each schema change creates a new form version
   - Existing responses remain linked to their original version

2. **Response Handling**:
   - Responses are stored in `form_responses` table
   - Each response references the exact form version used
   - Data stored as JSON with field IDs as keys

3. **Data Integrity**:
   - Form schema changes don't affect existing responses
   - Historical data remains viewable with original schema
   - Version tracking through semantic versioning (major.minor.patch)

---

## Configuration

Configure in `config/form-builder.php`:

```php
return [
    'table_prefix' => 'form_builder_', // Database table prefix
    'models' => [ // Models that can have forms
        \App\Models\Client::class,
        \App\Models\Project::class,
    ],
    'increment_count' => '0.0.1', // Version increment step
    'grouped' => true, // Show in Filament navigation group
    'group' => 'Form Builder' // Navigation group name
];
```

---

## Testing

Example test case:

```php
public function test_form_submission()
{
    $client = Client::factory()->create();
    $form = Form::where('form_model', Client::class)->first();
    
    $response = $this->post(route('form.submit'), [
        'form_id' => $form->form_id,
        'field_1' => 'Test value',
        'field_2' => 'other@example.com'
    ]);
    
    $this->assertDatabaseHas('form_builder_form_responses', [
        'model_id' => $client->id,
        'model_type' => Client::class,
        'form_id' => $form->form_id
    ]);
}
```

---

## 🚧 Roadmap

* [x] Core form builder implementation
* [x] Version control system
* [x] Response storage system
* [x] Filament v4 integration
* [ ] File upload field support
* [ ] Multi-page form wizard
* [ ] Advanced validation rules
* [ ] Form export/import
* [ ] Response data export


---

## 📄 License

MIT © [Dayne Valourite](https://github.com/dayne-valourite)

