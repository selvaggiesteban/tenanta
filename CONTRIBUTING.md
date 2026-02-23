# Contributing to Tenanta

First off, thank you for considering contributing to Tenanta! It's people like you that make Tenanta such a great tool.

## Code of Conduct

By participating in this project, you are expected to uphold our Code of Conduct: be respectful, inclusive, and constructive.

## How Can I Contribute?

### Reporting Bugs

Before creating bug reports, please check the existing issues to avoid duplicates. When you create a bug report, include as many details as possible:

- **Use a clear and descriptive title**
- **Describe the exact steps to reproduce the problem**
- **Provide specific examples**
- **Describe the behavior you observed and what you expected**
- **Include screenshots if possible**
- **Include your environment details**

### Suggesting Enhancements

Enhancement suggestions are tracked as GitHub issues. When creating an enhancement suggestion:

- **Use a clear and descriptive title**
- **Provide a detailed description of the suggested enhancement**
- **Explain why this enhancement would be useful**
- **List any alternatives you've considered**

### Pull Requests

1. **Fork the repo** and create your branch from `main`
2. **Install dependencies**: `composer install && npm install`
3. **Make your changes** following our coding standards
4. **Add tests** if applicable
5. **Run the test suite**: `php artisan test && npm run typecheck`
6. **Commit your changes** with a descriptive commit message
7. **Push to your fork** and submit a pull request

## Development Setup

```bash
# Clone your fork
git clone https://github.com/YOUR_USERNAME/tenanta.git
cd tenanta

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate
php artisan jwt:secret

# Run migrations
php artisan migrate

# Start development
composer dev
```

## Coding Standards

### PHP (Backend)

- Follow **PSR-12** coding standard
- Use **type hints** for all parameters and return types
- Write **PHPDoc** blocks for public methods
- Use **Form Requests** for validation
- Use **API Resources** for JSON responses

```php
// Good
public function store(StoreClientRequest $request): JsonResponse
{
    $client = Client::create($request->validated());

    return response()->json([
        'data' => new ClientResource($client),
    ], 201);
}
```

### TypeScript/Vue (Frontend)

- **No semicolons**
- **2-space indentation**
- Use **camelCase** for variables and functions
- Use **PascalCase** for components
- Use **Composition API** with `<script setup>`
- Only use **mdi icons** (Material Design Icons)

```vue
<script setup lang="ts">
const userName = ref('')
const isLoading = ref(false)

const handleSubmit = async () => {
  isLoading.value = true
  // ...
}
</script>
```

### Commit Messages

Use clear and descriptive commit messages:

```
feat: add client export to CSV
fix: resolve lead conversion issue
docs: update API documentation
refactor: simplify quote calculation
test: add unit tests for Pipeline model
```

Prefix your commits with:
- `feat:` - New feature
- `fix:` - Bug fix
- `docs:` - Documentation changes
- `refactor:` - Code refactoring
- `test:` - Adding tests
- `chore:` - Maintenance tasks

## Project Structure

```
tenanta/
├── app/
│   ├── Http/Controllers/Api/  # API Controllers
│   ├── Models/                # Eloquent Models
│   ├── Services/              # Business Logic
│   └── Events/                # WebSocket Events
├── resources/js/
│   ├── pages/                 # Vue Pages
│   ├── stores/                # Pinia Stores
│   ├── composables/           # Vue Composables
│   └── components/            # Reusable Components
└── tests/
    ├── Feature/               # Feature Tests
    └── Unit/                  # Unit Tests
```

## Testing

### PHP Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/CRM/ClientTest.php

# Run with coverage
php artisan test --coverage
```

### Frontend Tests

```bash
# Run tests
npm run test

# Type checking
npm run typecheck

# Linting
npm run lint
```

## Questions?

Feel free to open an issue with your question or reach out to the maintainers.

---

Thank you for contributing! 🎉
