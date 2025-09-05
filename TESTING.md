# VMS CRM Testing Guide

This document explains how to test the entire Visitor Management System CRM using the comprehensive test suite we've created.

## 🧪 Test Suite Overview

Our test suite covers **7 test files** with **45 test methods** across the following categories:

### Test Categories

1. **Authentication Tests** (7 tests)
   - Login/logout functionality
   - Role-based access control
   - Session management

2. **Admin Functionality Tests** (9 tests)
   - Dashboard access
   - User management
   - Location management
   - Visitor search
   - Analytics access

3. **Front Desk Tests** (9 tests)
   - Visitor registration
   - Mobile number checking
   - Location management
   - Visitor search

4. **Employee Tests** (6 tests)
   - Dashboard access
   - Remark updates
   - Visitor history viewing

5. **API Tests** (9 tests)
   - JSON responses
   - Data validation
   - Error handling
   - Role-based access

6. **Database Tests** (8 tests)
   - Table structure
   - Data integrity
   - Relationships
   - Constraints

7. **Model Tests** (6 tests)
   - Model attributes
   - Relationships
   - Data validation

## 🚀 Running Tests

### Prerequisites

1. **Install Dependencies**
   ```bash
   composer install
   ```

2. **Set up Environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Configure Test Database**
   - The tests use SQLite in-memory database
   - No additional setup required

### Quick Test Commands

#### Run All Tests
```bash
php artisan test
```

#### Run Specific Test Categories
```bash
# Authentication tests only
php artisan test tests/Feature/AuthenticationTest.php

# Admin functionality tests only
php artisan test tests/Feature/AdminTest.php

# Front desk tests only
php artisan test tests/Feature/FrontDeskTest.php

# Employee tests only
php artisan test tests/Feature/EmployeeTest.php

# API tests only
php artisan test tests/Feature/ApiTest.php

# Database tests only
php artisan test tests/Feature/DatabaseTest.php

# Model tests only
php artisan test tests/Unit/ModelTest.php
```

#### Using the Test Runner Script
```bash
# Run all tests
php run-tests.php

# Run specific category
php run-tests.php --auth
php run-tests.php --admin
php run-tests.php --frontdesk
php run-tests.php --employee
php run-tests.php --api
php run-tests.php --database
php run-tests.php --models

# Run with coverage
php run-tests.php --coverage

# Show help
php run-tests.php --help
```

## 📊 Test Coverage

### What We Test

✅ **Authentication & Authorization**
- User login/logout
- Role-based access control
- Session management
- Route protection

✅ **Admin Features**
- Dashboard access
- User creation and management
- Location management
- Visitor search by mobile
- Analytics access
- Visitor profile export

✅ **Front Desk Operations**
- Visitor registration forms
- Mobile number verification
- Location addition
- Visitor search
- Data validation

✅ **Employee Functions**
- Dashboard access
- Visit remark updates
- Visitor history viewing
- Status management

✅ **API Endpoints**
- JSON responses
- Data validation
- Error handling
- Authentication requirements

✅ **Database Operations**
- Table structures
- Data relationships
- Constraints and validation
- Data integrity

✅ **Model Behavior**
- Attribute handling
- Relationship methods
- Data validation rules

## 🔧 Test Configuration

### TestCase.php
- Uses `RefreshDatabase` trait for clean test data
- Uses `WithFaker` trait for realistic test data
- Configured for SQLite in-memory database

### phpunit.xml
- Configured for testing environment
- Uses SQLite database
- Disables external services during testing

### Factories
- **VmsUserFactory**: Creates test users with different roles
- **LocationFactory**: Generates realistic location data
- **VisitorFactory**: Creates visitor records
- **VisitHistoryFactory**: Generates visit history data
- **RemarkFactory**: Creates remark records

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Errors**
   - Ensure SQLite is available
   - Check `phpunit.xml` configuration

2. **Factory Errors**
   - Verify all models have proper relationships
   - Check factory definitions match model attributes

3. **Test Failures**
   - Review test output for specific error messages
   - Check if models have required methods (e.g., `isAdmin()`)

### Debug Mode
```bash
# Run tests with verbose output
php artisan test --verbose

# Run specific test with debug
php artisan test --filter test_method_name
```

## 📈 Adding New Tests

### Creating Feature Tests
```bash
php artisan make:test NewFeatureTest
```

### Creating Unit Tests
```bash
php artisan make:test NewUnitTest --unit
```

### Test Structure
```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\YourModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NewFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_feature_works()
    {
        // Arrange
        $data = YourModel::factory()->create();

        // Act
        $response = $this->get('/your-route');

        // Assert
        $response->assertStatus(200);
        $response->assertSee($data->name);
    }
}
```

## 🎯 Best Practices

1. **Test Naming**: Use descriptive test method names
2. **Arrange-Act-Assert**: Follow the AAA pattern
3. **Database Cleanup**: Always use `RefreshDatabase` trait
4. **Realistic Data**: Use factories for consistent test data
5. **Isolation**: Each test should be independent
6. **Coverage**: Aim for high test coverage of critical paths

## 📋 Test Checklist

Before running tests, ensure:

- [ ] All dependencies are installed
- [ ] Environment is configured
- [ ] Database migrations are ready
- [ ] Models have proper relationships
- [ ] Controllers handle errors gracefully
- [ ] Routes are properly protected

## 🚨 Important Notes

- Tests use in-memory SQLite database
- Each test runs in isolation
- Test data is automatically cleaned up
- External services are mocked during testing
- Tests run in the `testing` environment

## 📞 Support

If you encounter issues with the test suite:

1. Check the test output for specific error messages
2. Verify all required models and relationships exist
3. Ensure database migrations are up to date
4. Check if all required methods exist in models

---

**Happy Testing! 🎉**
