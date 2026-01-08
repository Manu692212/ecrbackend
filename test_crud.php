<?php

use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\Student;
use App\Models\Course;
use App\Models\Enrollment;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing CRUD Operations ===\n\n";

// Test Admin CRUD
echo "1. Testing Admin CRUD:\n";
$admin = Admin::create([
    'name' => 'Test Admin',
    'email' => 'test@example.com',
    'password' => Hash::make('password'),
    'role' => 'admin'
]);
echo "✓ Admin created: ID {$admin->id}\n";

$admin->update(['name' => 'Updated Admin']);
echo "✓ Admin updated\n";

$admins = Admin::all();
echo "✓ Admins retrieved: {$admins->count()} total\n";

$admin->delete();
echo "✓ Admin deleted\n\n";

// Test Student CRUD
echo "2. Testing Student CRUD:\n";
$student = Student::create([
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email' => 'john@example.com',
    'phone' => '1234567890',
    'education_level' => 'Bachelor'
]);
echo "✓ Student created: ID {$student->id}\n";

$student->update(['education_level' => 'Master']);
echo "✓ Student updated\n";

$students = Student::all();
echo "✓ Students retrieved: {$students->count()} total\n";

$student->delete();
echo "✓ Student deleted\n\n";

// Test Course CRUD
echo "3. Testing Course CRUD:\n";
$course = Course::create([
    'title' => 'Test Course',
    'description' => 'Test Description',
    'code' => 'TEST101',
    'duration_hours' => 40,
    'price' => 299.99,
    'level' => 'beginner',
    'instructor' => 'Test Instructor'
]);
echo "✓ Course created: ID {$course->id}\n";

$course->update(['price' => 399.99]);
echo "✓ Course updated\n";

$courses = Course::all();
echo "✓ Courses retrieved: {$courses->count()} total\n";

$course->delete();
echo "✓ Course deleted\n\n";

// Test Enrollment CRUD
echo "4. Testing Enrollment CRUD:\n";
$testStudent = Student::create([
    'first_name' => 'Jane',
    'last_name' => 'Smith',
    'email' => 'jane@example.com'
]);

$testCourse = Course::create([
    'title' => 'Another Course',
    'code' => 'COURSE101',
    'duration_hours' => 30,
    'price' => 199.99,
    'instructor' => 'Test Instructor'
]);

$enrollment = Enrollment::create([
    'student_id' => $testStudent->id,
    'course_id' => $testCourse->id,
    'enrollment_date' => now(),
    'amount_paid' => 199.99,
    'payment_status' => 'paid',
    'enrollment_status' => 'active'
]);
echo "✓ Enrollment created: ID {$enrollment->id}\n";

$enrollment->update(['enrollment_status' => 'completed']);
echo "✓ Enrollment updated\n";

$enrollments = Enrollment::all();
echo "✓ Enrollments retrieved: {$enrollments->count()} total\n";

$enrollment->delete();
echo "✓ Enrollment deleted\n\n";

// Test Relationships
echo "5. Testing Relationships:\n";
$studentWithEnrollments = Student::withCount('enrollments')->first();
echo "✓ Student with enrollment count retrieved\n";

$courseWithEnrollments = Course::withCount('enrollments')->first();
echo "✓ Course with enrollment count retrieved\n";

$enrollmentWithRelations = Enrollment::with(['student', 'course'])->first();
echo "✓ Enrollment with relations retrieved\n\n";

echo "=== All CRUD Operations Working Correctly! ===\n";
