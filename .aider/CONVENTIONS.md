# Programming Rules & Guidelines

**Aider, read and internalize these rules. You MUST adhere to them strictly.**

## General Directives

1. **Surgical Precision:** Make only the exact changes explicitly requested. Do not add, remove, or modify any code that
   is not directly pertinent to the current task.
2. **No Extraneous Changes:** Do not auto-format, re-indent, reorder, or refactor code unless specifically instructed.
   Preserve the existing code style, formatting, and structure.
3. **No Unsolicited Refactoring:** Do not introduce new classes, methods, or architectural patterns unless explicitly
   outlined in the task. If a suggestion for refactoring is implicit, ask for explicit approval first.
4. **Prioritize Clarity over Brevity:** Code must be clear, readable, and maintainable. Do not optimize for line count
   at the expense of understanding.

## Filament v4 Specific Directives (CRITICAL)

1. **Exact Method Signatures:** When modifying or implementing Filament resource methods (`form()`, `table()`,
   `getColumns()`, `getActions()`, `getPages()`, `getHeaderActions()`, etc.), you **MUST** preserve their exact Filament
   v4 method signatures, including type hints and return types (e.g., `public static function form(Form $form): Form`,
   `public static function table(Table $table): Table`, `public static function makeHeaderActions(Page $page): array`).
2. **Filament `Action` Syntax:** Use the correct Filament v4 `Action` syntax (`Action::make(...)`) with its fluent
   methods (`->label()`, `->icon()`, `->action()`, `->modal()`, `->requiresConfirmation()`, and dynamic properties using
   `fn (Model $record): type => ...`).
3. **Resource `getPages()`:** When modifying `getPages()`, ensure it only lists the specified page routes. **Do NOT**
   automatically add `Create` or `Edit` pages unless explicitly instructed for that specific resource.
4. **No Raw `created_at` or `updated_at` in Tables/Infolists:** Avoid directly displaying
   `TextColumn::make('created_at')` or `TextEntry::make('created_at')`. Use dedicated timestamp columns or accessors if
   temporal data is needed.

## Database & Model Strictness

1. **No `timestamps()` or `softDeletes()` in Migrations (Unless Specified):** Unless explicitly told otherwise for a
   specific table, new migrations and modifications to existing migrations **MUST NOT** include `->timestamps()` or
   `->softDeletes()`.
2. **No `$fillable` Array in Models:** Models **MUST NOT** use the `$fillable` property. Rely on specific factory
   methods, service methods, or guarded properties if absolutely necessary.
3. **No `timestamps` or `softDeletes` Properties/Traits in Models:** Unless explicitly told otherwise for a specific
   model, models **MUST NOT** use the `$timestamps = false;` property or the `SoftDeletes` trait.
4. **Type Hinting & Casts:** Use native PHP type hints for properties and method arguments/return types. Utilize
   `$casts` for Enum fields and JSON columns.

## SOLID Principles & Dynamic Programming

1. **Single Responsibility Principle (SRP):** Each class and method should have only one reason to change. Break down
   complex logic into smaller, focused units.
2. **Open/Closed Principle (OCP):** Software entities should be open for extension, but closed for modification. Favor
   composition and interfaces over inheritance where appropriate.
3. **Liskov Substitution Principle (LSP):** Objects in a program should be replaceable with instances of their subtypes
   without altering the correctness of that program. Ensure proper use of polymorphism.
4. **Interface Segregation Principle (ISP):** Clients should not be forced to depend on interfaces they do not use.
   Create small, role-specific interfaces.
5. **Dependency Inversion Principle (DIP):**
    * Use interfaces where appropriate to define contracts for services and clients.
6. **Dynamic & Flexible Code:**
    * **Enums for Fixed Options:** Use PHP Enums for fixed sets of options (e.g., `TranscriptStatus`, `AIProvider`).
    * **Factories for Implementations:** Use factory classes/methods to retrieve specific implementations of
      interfaces (e.g., `AIClientFactory`).

## API Clients

1. **All API Calls Through Clients:** Any interaction with external APIs (e.g., OpenAI, AWS, Salesforce) **MUST** go
   through a dedicated API client class (e.g., `OpenAIApiClient`, `AwsS3Client`, `SalesforceTaskApiClient`). These
   clients should wrap the HTTP calls and handle API-specific details.

## Testing

1. **Test Coverage:** For any new feature or significant refactoring, consider how unit and integration tests would be
   written. The design should facilitate easy testing through proper dependency injection.

## Error Handling & Resilience

1. **Explicit Error Handling:** Implement robust `try-catch` blocks for external API calls and critical operations. Log
   errors clearly.
2. **Asynchronous Operations for External Systems:** For operations involving external APIs (especially write operations
   like Salesforce sync), favor asynchronous processing via jobs to prevent UI blocking and allow for retries.

## Configuration

1. **Use Configuration Files/Environment Variables:** All API keys, endpoint URLs, and changeable settings must be
   defined in `.env` and accessed via `config()` helpers, not hardcoded.

---

## Api client -Specific Rules

1. DTOs **must NOT have constructors** — all population happens in Transformers.
2. Transformers use `toDto()` and `toSpotify()`.
3. All mass data Clients use:

```php
public function getMassData(): Collection;
public function saveMassData(object $dto): mixed;
