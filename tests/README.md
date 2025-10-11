# Laravel TryCatcher Package Test Suite

This directory contains comprehensive tests for the Laravel TryCatcher package, ensuring code quality, performance, and reliability.

## 📁 Test Structure

```
tests/
├── Unit/                    # Unit tests for individual components
│   ├── ExceptionGuardTest.php
│   ├── PolicyHandlerTest.php
│   ├── ExceptionPolicyTest.php
│   ├── ErrorLogTest.php
│   └── PerformanceTest.php
├── Feature/                  # Feature tests for user-facing functionality
│   ├── GuardedHelperTest.php
│   └── ExampleUsageTest.php
├── Integration/              # Integration tests for Laravel components
│   └── ServiceProviderTest.php
├── config/                   # Test configuration files
│   └── test-config.php
├── code-quality-test.php    # Code quality validation script
└── TestCase.php             # Base test case class
```

## 🚀 Quick Start

### Prerequisites

- PHP 8.0 or higher
- Composer
- Laravel 8.0 or higher (for integration tests)

### Installation

1. Install dependencies:
```bash
composer install
```

2. Run all tests:
```bash
# Linux/Mac
./run-tests.sh

# Windows
run-tests.bat
```

## 🧪 Test Categories

### 1. Unit Tests

Test individual components in isolation:

```bash
# Run all unit tests
composer test-unit

# Run specific test
vendor/bin/phpunit tests/Unit/ExceptionGuardTest.php
```

**Coverage:**
- `ExceptionGuard` service
- `PolicyHandler` class
- `ExceptionPolicy` enum
- `ErrorLog` model
- Performance benchmarks

### 2. Feature Tests

Test user-facing functionality and helper functions:

```bash
# Run all feature tests
composer test-feature

# Run specific test
vendor/bin/phpunit tests/Feature/GuardedHelperTest.php
```

**Coverage:**
- `guarded()` helper function
- `isProdEnv()` helper function
- Facade usage
- Example usage scenarios

### 3. Integration Tests

Test Laravel framework integration:

```bash
# Run all integration tests
composer test-integration

# Run specific test
vendor/bin/phpunit tests/Integration/ServiceProviderTest.php
```

**Coverage:**
- Service provider registration
- Configuration merging
- Facade binding
- Helper function loading

### 4. Performance Tests

Benchmark performance and memory usage:

```bash
# Run performance tests
composer test-performance

# Run with detailed output
vendor/bin/phpunit tests/Unit/PerformanceTest.php --verbose
```

**Metrics:**
- Execution time benchmarks
- Memory usage analysis
- Memory leak detection
- Database performance
- Concurrent operations simulation

### 5. Code Quality Tests

Validate code standards and best practices:

```bash
# Run code quality tests
composer quality

# Run specific quality checks
php tests/code-quality-test.php
```

**Checks:**
- PSR-12 compliance
- Security vulnerability scanning
- Performance issue detection
- Documentation completeness
- Dependency analysis

## 🔧 Test Configuration

### Environment Variables

Create a `.env.testing` file:

```env
APP_ENV=testing
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
LOG_CHANNEL=stack
EXCEPTION_GUARD_LOG_CHANNEL=stack
```

### Test Configuration

Modify `tests/config/test-config.php` to customize:

- Database connections
- Performance thresholds
- Coverage requirements
- Quality check settings

## 📊 Coverage Reports

Generate coverage reports:

```bash
# Generate HTML coverage report
composer test-coverage

# View coverage report
open coverage/html/index.html
```

**Coverage Targets:**
- Minimum 80% code coverage
- 100% coverage for critical paths
- Exclude migrations and helper files

## 🛠️ Development Tools

### Static Analysis

```bash
# PHPStan analysis
composer stan

# Psalm analysis
composer psalm
```

### Code Style

```bash
# Check code style
composer cs-check

# Fix code style issues
composer cs-fix
```

### Security Audit

```bash
# Check for security vulnerabilities
composer audit
```

## 📈 Performance Benchmarks

The performance tests measure:

- **Execution Time**: Operations per second
- **Memory Usage**: Peak memory consumption
- **Memory Leaks**: Long-term memory growth
- **Database Performance**: Query execution time
- **Concurrent Operations**: Multi-threaded scenarios

### Benchmark Targets

| Metric | Target | Warning |
|--------|--------|---------|
| Execution Time | < 1ms per operation | < 5ms per operation |
| Memory Usage | < 10MB for 1000 ops | < 50MB for 1000 ops |
| Memory Growth | < 5MB over 500 ops | < 20MB over 500 ops |
| Database Time | < 10ms per query | < 50ms per query |

## 🐛 Debugging Tests

### Verbose Output

```bash
# Run tests with verbose output
vendor/bin/phpunit --verbose

# Run specific test with debug info
vendor/bin/phpunit tests/Unit/ExceptionGuardTest.php --verbose
```

### Test Database

```bash
# Use MySQL for integration tests
DB_CONNECTION=mysql vendor/bin/phpunit tests/Integration/
```

### Logging

```bash
# Enable test logging
LOG_LEVEL=debug vendor/bin/phpunit
```

## 📝 Writing Tests

### Test Structure

```php
<?php

namespace Mzshovon\LaravelTryCatcher\Tests\Unit;

use Mzshovon\LaravelTryCatcher\Tests\TestCase;

class MyTest extends TestCase
{
    public function test_something()
    {
        // Arrange
        $input = 'test';
        
        // Act
        $result = someFunction($input);
        
        // Assert
        $this->assertEquals('expected', $result);
    }
}
```

### Best Practices

1. **Descriptive Test Names**: Use `test_what_it_does` format
2. **Arrange-Act-Assert**: Structure tests clearly
3. **One Assertion Per Test**: Focus on single behavior
4. **Mock External Dependencies**: Use Mockery for external services
5. **Test Edge Cases**: Include boundary conditions
6. **Performance Considerations**: Keep tests fast and isolated

## 🔄 Continuous Integration

### GitHub Actions

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v2
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.0'
        
    - name: Install dependencies
      run: composer install --no-interaction --prefer-dist
      
    - name: Run tests
      run: ./run-tests.sh
```

### Pre-commit Hooks

```bash
# Install pre-commit hook
cp tests/hooks/pre-commit .git/hooks/
chmod +x .git/hooks/pre-commit
```

## 📚 Additional Resources

- [PHPUnit Documentation](https://phpunit.readthedocs.io/)
- [Laravel Testing Guide](https://laravel.com/docs/testing)
- [PSR-12 Coding Standard](https://www.php-fig.org/psr/psr-12/)
- [PHPStan Documentation](https://phpstan.org/user-guide/getting-started)

## 🤝 Contributing

When adding new tests:

1. Follow the existing test structure
2. Add appropriate test cases for edge conditions
3. Update performance benchmarks if applicable
4. Ensure code coverage remains above 80%
5. Run the full test suite before submitting

## 📞 Support

For test-related issues:

1. Check the test output for specific error messages
2. Review the test configuration
3. Ensure all dependencies are installed
4. Check PHP and Laravel version compatibility

---

**Happy Testing! 🎉**

