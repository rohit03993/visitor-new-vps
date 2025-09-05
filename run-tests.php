<?php

/**
 * VMS CRM Test Runner Script
 * 
 * This script provides an easy way to run different types of tests
 * Usage: php run-tests.php [option]
 * 
 * Options:
 * --all          Run all tests
 * --auth         Run authentication tests only
 * --admin        Run admin functionality tests only
 * --frontdesk    Run front desk tests only
 * --employee     Run employee tests only
 * --api          Run API tests only
 * --database     Run database tests only
 * --models       Run model tests only
 * --coverage     Run tests with coverage report
 * --help         Show this help message
 */

$option = $argv[1] ?? '--all';

echo "🚀 VMS CRM Test Runner\n";
echo "======================\n\n";

switch ($option) {
    case '--all':
        echo "Running all tests...\n";
        system('php artisan test');
        break;
        
    case '--auth':
        echo "Running authentication tests...\n";
        system('php artisan test tests/Feature/AuthenticationTest.php');
        break;
        
    case '--admin':
        echo "Running admin functionality tests...\n";
        system('php artisan test tests/Feature/AdminTest.php');
        break;
        
    case '--frontdesk':
        echo "Running front desk tests...\n";
        system('php artisan test tests/Feature/FrontDeskTest.php');
        break;
        
    case '--employee':
        echo "Running employee tests...\n";
        system('php artisan test tests/Feature/EmployeeTest.php');
        break;
        
    case '--api':
        echo "Running API tests...\n";
        system('php artisan test tests/Feature/ApiTest.php');
        break;
        
    case '--database':
        echo "Running database tests...\n";
        system('php artisan test tests/Feature/DatabaseTest.php');
        break;
        
    case '--models':
        echo "Running model tests...\n";
        system('php artisan test tests/Unit/ModelTest.php');
        break;
        
    case '--coverage':
        echo "Running tests with coverage report...\n";
        system('php artisan test --coverage');
        break;
        
    case '--help':
    default:
        echo "Available options:\n";
        echo "  --all          Run all tests\n";
        echo "  --auth         Run authentication tests only\n";
        echo "  --admin        Run admin functionality tests only\n";
        echo "  --frontdesk    Run front desk tests only\n";
        echo "  --employee     Run employee tests only\n";
        echo "  --api          Run API tests only\n";
        echo "  --database     Run database tests only\n";
        echo "  --models       Run model tests only\n";
        echo "  --coverage     Run tests with coverage report\n";
        echo "  --help         Show this help message\n\n";
        echo "Example: php run-tests.php --auth\n";
        break;
}

echo "\n✅ Test execution completed!\n";
