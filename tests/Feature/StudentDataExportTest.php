<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\HomeworkUser;
use App\Models\SchoolClass;
use App\Models\HomeworkUserPhoneNumber;
use App\Models\ClassStudent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class StudentDataExportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that all student data is exported correctly
     * This is a CRITICAL test to ensure no data loss
     */
    public function test_export_includes_all_student_data()
    {
        // Create test classes
        $class1 = SchoolClass::create(['name' => 'Class A', 'description' => 'Test Class A']);
        $class2 = SchoolClass::create(['name' => 'Class B', 'description' => 'Test Class B']);
        
        // Create test students with complete data
        $student1 = HomeworkUser::create([
            'name' => 'John Doe',
            'roll_number' => 'ROLL001',
            'mobile_number' => '+919876543210',
            'password' => Hash::make('password123'),
            'password_plain' => 'password123',
            'role' => 'student',
        ]);
        
        $student2 = HomeworkUser::create([
            'name' => 'Jane Smith',
            'roll_number' => 'ROLL002',
            'mobile_number' => '+919876543211',
            'password' => Hash::make('password456'),
            'password_plain' => 'password456',
            'role' => 'student',
        ]);
        
        // Add additional phone numbers
        HomeworkUserPhoneNumber::create([
            'homework_user_id' => $student1->id,
            'phone_number' => '+919876543212',
            'whatsapp_enabled' => true,
        ]);
        
        // Assign classes
        ClassStudent::create(['class_id' => $class1->id, 'student_id' => $student1->id]);
        ClassStudent::create(['class_id' => $class2->id, 'student_id' => $student1->id]);
        ClassStudent::create(['class_id' => $class1->id, 'student_id' => $student2->id]);
        
        // Create an admin user to authenticate (without factory to avoid branch dependency)
        $admin = \App\Models\VmsUser::create([
            'name' => 'Admin User',
            'username' => 'admin',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);
        
        // Make request to export endpoint
        $response = $this->actingAs($admin, 'web')
            ->get(route('homework.admin.users.export-all'));
        
        // Assert response is successful
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        
        // Get CSV content
        $csvContent = $response->getContent();
        
        // Verify CSV contains BOM for UTF-8
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csvContent);
        
        // Parse CSV
        $lines = explode("\n", trim($csvContent));
        $headers = str_getcsv($lines[0]);
        
        // Verify headers include all expected fields
        $expectedHeaders = [
            'ID',
            'Name',
            'Roll Number',
            'Mobile Number',
            'Additional Phone 1',
            'Additional Phone 2',
            'Additional Phone 3',
            'Additional Phone 4',
            'Password (Plain Text)',
            'Registration Date',
            'Last Updated',
        ];
        
        foreach ($expectedHeaders as $header) {
            $this->assertContains($header, $headers, "Header '{$header}' is missing from export");
        }
        
        // Verify class columns are present
        $this->assertContains('Class: Class A', $headers);
        $this->assertContains('Class: Class B', $headers);
        
        // Verify data rows (skip header row)
        $dataRows = array_slice($lines, 1);
        
        // Verify all students are exported
        $this->assertCount(2, array_filter($dataRows, function($row) {
            return !empty(trim($row));
        }), 'Not all students were exported');
        
        // Verify student1 data
        $student1Row = str_getcsv($dataRows[0]);
        $this->assertEquals($student1->id, $student1Row[0]);
        $this->assertEquals('John Doe', $student1Row[1]);
        $this->assertEquals('ROLL001', $student1Row[2]);
        $this->assertEquals('+919876543210', $student1Row[3]);
        $this->assertEquals('+919876543212', $student1Row[4]); // Additional phone 1
        $this->assertEquals('password123', $student1Row[8]); // Password plain text
        
        // Find class columns index
        $classAIndex = array_search('Class: Class A', $headers);
        $classBIndex = array_search('Class: Class B', $headers);
        
        // Verify class enrollment
        $this->assertEquals('YES', $student1Row[$classAIndex]);
        $this->assertEquals('YES', $student1Row[$classBIndex]);
        
        // Verify student2 data
        $student2Row = str_getcsv($dataRows[1]);
        $this->assertEquals($student2->id, $student2Row[0]);
        $this->assertEquals('Jane Smith', $student2Row[1]);
        $this->assertEquals('ROLL002', $student2Row[2]);
        $this->assertEquals('password456', $student2Row[8]); // Password plain text
        $this->assertEquals('YES', $student2Row[$classAIndex]);
        $this->assertEquals('NO', $student2Row[$classBIndex]);
    }

    /**
     * Test that export includes all students regardless of filters
     */
    public function test_export_includes_all_students()
    {
        // Create 10 test students
        for ($i = 1; $i <= 10; $i++) {
            HomeworkUser::create([
                'name' => "Student {$i}",
                'roll_number' => "ROLL{$i}",
                'mobile_number' => "+9198765432{$i}",
                'password' => Hash::make('password'),
                'password_plain' => 'password',
                'role' => 'student',
            ]);
        }
        
        $admin = \App\Models\VmsUser::create([
            'name' => 'Admin User',
            'username' => 'admin' . rand(1000, 9999),
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);
        
        $response = $this->actingAs($admin, 'web')
            ->get(route('homework.admin.users.export-all'));
        
        $response->assertStatus(200);
        
        $csvContent = $response->getContent();
        $lines = explode("\n", trim($csvContent));
        
        // Should have 1 header + 10 students = 11 lines
        $this->assertCount(11, $lines);
        
        // Verify all students are present
        for ($i = 1; $i <= 10; $i++) {
            $found = false;
            foreach ($lines as $line) {
                if (strpos($line, "ROLL{$i}") !== false) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, "Student with roll number ROLL{$i} not found in export");
        }
    }

    /**
     * Test that export handles students with no classes gracefully
     */
    public function test_export_handles_students_without_classes()
    {
        $student = HomeworkUser::create([
            'name' => 'Student No Class',
            'roll_number' => 'ROLL999',
            'mobile_number' => '+919876543299',
            'password' => Hash::make('password'),
            'password_plain' => 'password',
            'role' => 'student',
        ]);
        
        $admin = \App\Models\VmsUser::create([
            'name' => 'Admin User',
            'username' => 'admin' . rand(1000, 9999),
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);
        
        $response = $this->actingAs($admin, 'web')
            ->get(route('homework.admin.users.export-all'));
        
        $response->assertStatus(200);
        
        $csvContent = $response->getContent();
        $lines = explode("\n", trim($csvContent));
        $headers = str_getcsv($lines[0]);
        $studentRow = str_getcsv($lines[1]);
        
        // Verify student is exported
        $this->assertEquals('ROLL999', $studentRow[2]);
        
        // Verify class columns show NO for all classes (if classes exist)
        // If no classes exist, there should be no class columns
        $classColumns = array_filter($headers, function($header) {
            return strpos($header, 'Class:') === 0;
        });
        
        foreach ($classColumns as $index => $header) {
            $headerIndex = array_search($header, $headers);
            if ($headerIndex !== false && isset($studentRow[$headerIndex])) {
                $this->assertEquals('NO', $studentRow[$headerIndex]);
            }
        }
    }

    /**
     * Test that export includes all phone numbers
     */
    public function test_export_includes_all_phone_numbers()
    {
        $student = HomeworkUser::create([
            'name' => 'Student Multi Phone',
            'roll_number' => 'ROLL555',
            'mobile_number' => '+919876543255',
            'password' => Hash::make('password'),
            'password_plain' => 'password',
            'role' => 'student',
        ]);
        
        // Add 4 additional phone numbers
        for ($i = 1; $i <= 4; $i++) {
            HomeworkUserPhoneNumber::create([
                'homework_user_id' => $student->id,
                'phone_number' => "+9198765432{$i}",
                'whatsapp_enabled' => true,
            ]);
        }
        
        $admin = \App\Models\VmsUser::create([
            'name' => 'Admin User',
            'username' => 'admin' . rand(1000, 9999),
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);
        
        $response = $this->actingAs($admin, 'web')
            ->get(route('homework.admin.users.export-all'));
        
        $response->assertStatus(200);
        
        $csvContent = $response->getContent();
        $lines = explode("\n", trim($csvContent));
        $studentRow = str_getcsv($lines[1]);
        
        // Verify all phone numbers are exported
        $this->assertEquals('+919876543255', $studentRow[3]); // Primary
        $this->assertEquals('+91987654321', $studentRow[4]);  // Additional 1
        $this->assertEquals('+91987654322', $studentRow[5]);  // Additional 2
        $this->assertEquals('+91987654323', $studentRow[6]);  // Additional 3
        $this->assertEquals('+91987654324', $studentRow[7]);  // Additional 4
    }

    /**
     * Test data integrity - verify export matches database exactly
     */
    public function test_export_data_integrity()
    {
        $class = SchoolClass::create(['name' => 'Test Class', 'description' => 'Test']);
        
        $student = HomeworkUser::create([
            'name' => 'Integrity Test',
            'roll_number' => 'INT001',
            'mobile_number' => '+919876543200',
            'password' => Hash::make('secret123'),
            'password_plain' => 'secret123',
            'role' => 'student',
        ]);
        
        ClassStudent::create(['class_id' => $class->id, 'student_id' => $student->id]);
        
        $admin = \App\Models\VmsUser::create([
            'name' => 'Admin User',
            'username' => 'admin' . rand(1000, 9999),
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);
        
        $response = $this->actingAs($admin, 'web')
            ->get(route('homework.admin.users.export-all'));
        
        $response->assertStatus(200);
        
        $csvContent = $response->getContent();
        $lines = explode("\n", trim($csvContent));
        $studentRow = str_getcsv($lines[1]);
        
        // Verify all data matches database
        $this->assertEquals($student->id, $studentRow[0]);
        $this->assertEquals($student->name, $studentRow[1]);
        $this->assertEquals($student->roll_number, $studentRow[2]);
        $this->assertEquals($student->mobile_number, $studentRow[3]);
        $this->assertEquals($student->password_plain, $studentRow[8]);
        
        // Verify timestamps
        $this->assertNotEmpty($studentRow[9]); // Registration Date
        $this->assertNotEmpty($studentRow[10]); // Last Updated
    }
}

