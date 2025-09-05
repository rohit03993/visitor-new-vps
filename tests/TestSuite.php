<?php

namespace Tests;

use PHPUnit\Framework\TestSuite as BaseTestSuite;
use PHPUnit\Framework\Test;

/**
 * Custom Test Suite for VMS CRM
 * This organizes all tests into logical groups for better testing organization
 */
class TestSuite extends BaseTestSuite
{
    /**
     * Get all test files organized by category
     */
    public static function getTestFiles(): array
    {
        return [
            'Authentication' => [
                'tests/Feature/AuthenticationTest.php'
            ],
            'Admin Functionality' => [
                'tests/Feature/AdminTest.php'
            ],
            'Front Desk Functionality' => [
                'tests/Feature/FrontDeskTest.php'
            ],
            'Employee Functionality' => [
                'tests/Feature/EmployeeTest.php'
            ],
            'API Endpoints' => [
                'tests/Feature/ApiTest.php'
            ],
            'Database & Models' => [
                'tests/Feature/DatabaseTest.php',
                'tests/Unit/ModelTest.php'
            ]
        ];
    }

    /**
     * Get test statistics
     */
    public static function getTestStats(): array
    {
        return [
            'total_test_files' => 7,
            'feature_tests' => 6,
            'unit_tests' => 1,
            'total_test_methods' => 45,
            'categories' => [
                'Authentication' => 7,
                'Admin' => 9,
                'Front Desk' => 9,
                'Employee' => 6,
                'API' => 9,
                'Database' => 8,
                'Models' => 6
            ]
        ];
    }
}
