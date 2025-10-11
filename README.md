## Laravel TryCatcher

A lightweight exception guard for Laravel to simplify try/catch handling with consistent logging, safe responses, and attribute-driven policies.

### Why this package?
- **Eliminate boilerplate**: Replace repetitive try/catch blocks with concise guards
- **Consistent behavior**: Centralized policies for logging and responses
- **Flexible usage**: Use a Facade, helper, or trait with PHP 8 attributes
- **Production-safe**: Return safe messages in production without leaking traces
- **Persistent logs**: Store errors in `error_logs` table and Laravel logs

---

### Requirements
- PHP >= 8.0
- Laravel 9+ (auto-discovery enabled)

---

### Installation
```bash
composer require mzshovon/laravel-trycatcher
```

The package supports Laravel package discovery. No manual provider registration is required.

Publish configuration and migrations:
```bash
php artisan vendor:publish --provider="Mzshovon\LaravelTryCatcher\ExceptionGuardServiceProvider" --tag=config
php artisan vendor:publish --provider="Mzshovon\LaravelTryCatcher\ExceptionGuardServiceProvider" --tag=migrations
php artisan migrate
```

---

### Configuration
Published to `config/exception-guard.php`:

```php
return [
    'default_policy' => \Mzshovon\LaravelTryCatcher\Policies\ExceptionPolicy::THROW,
    'prod_policy' => \Mzshovon\LaravelTryCatcher\Policies\ExceptionPolicy::PROD_SAFE,
    'log_channel' => env('EXCEPTION_GUARD_LOG_CHANNEL', 'stack'),
    'integrations' => [
        'sentry' => env('SENTRY_DSN') ? true : false,
        'slack' => env('EXCEPTION_GUARD_SLACK_WEBHOOK') ? true : false,
    ],
    'safe_prod_guard' => 1,
];
```

- **default_policy**: Used when no explicit policy/attribute is found
- **prod_policy**: Recommended production-safe policy
- **safe_prod_guard**: When enabled and `app.env=production`, responses avoid traces regardless of requested policy

---

### Policies
Available values in `\Mzshovon\LaravelTryCatcher\Policies\ExceptionPolicy`:

- `THROW`: Rethrow to Laravel’s handler
- `LOG`: Persist to DB and Laravel log (no stack trace in DB)
- `LOG_WITH_TRACE`: Persist to DB and Laravel log with stack trace
- `EXCEPTION_ONLY`: Return structured error payload (includes trace only when `app.debug=true`)
- `PROD_SAFE`: Log minimally and return a safe generic message
- `LOG_AND_THROW`: Log then rethrow

Note: When `safe_prod_guard` is enabled and the app is in production, responses are converted to a safe generic payload even if a policy would otherwise include details.

---

### Usage

#### 1) Trait + Attribute
Annotate methods with a policy and call them normally; exceptions will be intercepted and handled.

```php
use Mzshovon\LaravelTryCatcher\Traits\Guardable;
use Mzshovon\LaravelTryCatcher\Attributes\ExceptionPolicyAttr;
use Mzshovon\LaravelTryCatcher\Policies\ExceptionPolicy;

class PaymentService
{
    use Guardable;

    #[ExceptionPolicyAttr(ExceptionPolicy::LOG_AND_THROW, options: ['context' => ['service' => 'payment']])]
    protected function charge(array $payload)
    {
        // ... risky operation ...
        return 'ok';
    }

    public function runCharge(array $payload)
    {
        // Will honor the attribute policy
        return $this->runGuardedMethod('charge', [$payload]);
    }
}
```

##### Protected and parent methods
`runGuardedMethod` supports calling protected/private methods (including those declared on a parent class). The trait uses reflection and will set accessibility when required.

```php
use Mzshovon\LaravelTryCatcher\Traits\Guardable;
use Mzshovon\LaravelTryCatcher\Policies\ExceptionPolicy;

abstract class BaseService
{
    use Guardable;

    // Declared in parent, not public
    #[\Mzshovon\LaravelTryCatcher\Attributes\ExceptionPolicyAttr(ExceptionPolicy::LOG)]
    protected function persist(array $data)
    {
        // ... risky work ...
        return 'saved';
    }
}

class UserService extends BaseService
{
    public function save(array $data)
    {
        // Calls protected parent method with args; attribute is honored
        return $this->runGuardedMethod('persist', [$data]);
    }
}
```

#### 2) Facade
```php
use Guard; // alias of \Mzshovon\LaravelTryCatcher\Facades\Guard
use Mzshovon\LaravelTryCatcher\Policies\ExceptionPolicy;

$result = Guard::run(function () {
    // risky work
    return doSomething();
}, ExceptionPolicy::LOG_WITH_TRACE, [
    'context' => ['user_id' => auth()->id()],
    'status' => 500,
]);
```

#### 3) Helper
```php
use Mzshovon\LaravelTryCatcher\Policies\ExceptionPolicy;

$response = guarded(function () {
    return doAnotherThing();
}, ExceptionPolicy::PROD_SAFE, [
    'message' => 'Please try again later',
    'status' => 422,
]);
```

You can also pass method callables. If no policy is provided, the helper attempts to discover an `ExceptionPolicyAttr` attribute on that method and falls back to `default_policy` otherwise.

Alternatively, when calling methods directly on an object using `Guardable`, the magic `__call` will intercept and apply the attribute policy automatically.

##### Passing arguments and runtime objects
- Attributes in PHP cannot accept runtime objects; they only allow constant expressions. If you need to work with runtime object references (e.g., a repository or DTO), pass them as method arguments and invoke via `runGuardedMethod`:

```php
use Mzshovon\LaravelTryCatcher\Policies\ExceptionPolicy;

class ReportService
{
    use \Mzshovon\LaravelTryCatcher\Traits\Guardable;

    // No attribute here; we want dynamic control
    protected function generate($reportGenerator, array $params)
    {
        return $reportGenerator->build($params);
    }

    public function safeGenerate($reportGenerator, array $params)
    {
        return $this->runGuardedMethod(
            'generate',
            [$reportGenerator, $params],
            ExceptionPolicy::PROD_SAFE,
            [
                'context' => ['type' => 'report'],
                'message' => 'Report generation failed. Please try again later.',
                'status' => 503,
            ]
        );
    }
}
```

- Alternatively, wrap your logic in a closure and use the `guarded()` helper or the `Guard` facade when you need full control over captured variables.

---

### Options
Pass via the third parameter to guards/handlers or via the attribute’s `options`:

- `context` (array): Extra data included in logs
- `level` (string): Log level persisted to DB (default `error`)
- `status` (int): HTTP status for JSON responses
- `message` (string): Safe message for production responses

---

### Database Logging
Errors are persisted in `error_logs` with the following schema:

```php
$table->id();
$table->string('level')->nullable();
$table->string('message');
$table->text('trace')->nullable();
$table->json('context')->nullable();
$table->timestamps();
```

Model: `\Mzshovon\LaravelTryCatcher\Models\ErrorLog` (casts `context` to array).

On any failure to persist, the package falls back to Laravel file logging to avoid losing error details.

---

### JSON Response Shape

Non-throwing policies return a JSON response similar to:

```json
{
  "error": true,
  "message": "Something went wrong.",
  "trace": [] // included only when applicable and app.debug=true
}
```

In production with `safe_prod_guard` enabled, responses omit traces and use the provided `message` option or a generic message.

---

### Testing
- Use policies like `EXCEPTION_ONLY` to assert structured error payloads
- Seed a failure and assert a row exists in `error_logs`
- In production-like tests, enable `safe_prod_guard` and assert generic responses

---

### License
Licensed under the MIT License. See `LICENSE`.


