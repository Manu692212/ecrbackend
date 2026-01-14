<?php

use Illuminate\Http\Request;
use App\Models\AcademicCouncil;
use App\Models\Management;
use App\Models\JobPosting;
use App\Models\Career;
use App\Models\Facility;
use App\Models\Setting;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing New CRUD Operations ===\n\n";

// Test Academic Council CRUD
echo "1. Testing Academic Council CRUD:\n";
$council = AcademicCouncil::create([
    'name' => 'Dr. John Smith',
    'position' => 'Dean',
    'email' => 'john.smith@example.com',
    'phone' => '1234567890',
    'bio' => 'Experienced academic with 20 years of service',
    'department' => 'Computer Science',
    'order' => 1,
    'is_active' => true
]);
echo "✓ Academic Council created: ID {$council->id}\n";

$council->update(['position' => 'Senior Dean']);
echo "✓ Academic Council updated\n";

$councils = AcademicCouncil::all();
echo "✓ Academic Councils retrieved: {$councils->count()} total\n";

$council->delete();
echo "✓ Academic Council deleted\n\n";

// Test Management CRUD
echo "2. Testing Management CRUD:\n";
$management = Management::create([
    'name' => 'Jane Doe',
    'position' => 'Director',
    'email' => 'jane.doe@example.com',
    'phone' => '0987654321',
    'bio' => 'Experienced manager with 15 years of service',
    'department' => 'Administration',
    'order' => 2,
    'is_active' => true
]);
echo "✓ Management created: ID {$management->id}\n";

$management->update(['position' => 'Senior Director']);
echo "✓ Management updated\n";

$managements = Management::all();
echo "✓ Management retrieved: {$managements->count()} total\n";

$management->delete();
echo "✓ Management deleted\n\n";

// Test Job Posting CRUD
echo "3. Testing Job Posting CRUD:\n";
$job = JobPosting::create([
    'title' => 'Senior Software Engineer',
    'department' => 'IT',
    'description' => 'We are looking for a senior software engineer...',
    'requirements' => '5+ years of experience in software development',
    'location' => 'New York',
    'employment_type' => 'full-time',
    'salary_min' => 80000,
    'salary_max' => 120000,
    'deadline' => '2026-02-01',
    'order' => 1,
    'is_active' => true
]);
echo "✓ Job Posting created: ID {$job->id}\n";

$job->update(['title' => 'Lead Software Engineer']);
echo "✓ Job Posting updated\n";

$jobs = JobPosting::all();
echo "✓ Job Postings retrieved: {$jobs->count()} total\n";

$job->delete();
echo "✓ Job Posting deleted\n\n";

// Test Career CRUD
echo "4. Testing Career CRUD:\n";
$career = Career::create([
    'title' => 'Data Science Career Path',
    'slug' => 'data-science-career-path',
    'description' => 'Explore career opportunities in data science...',
    'requirements' => 'Strong analytical skills required',
    'location' => 'Remote',
    'employment_type' => 'full-time',
    'salary_min' => 70000,
    'salary_max' => 150000,
    'deadline' => '2026-03-01',
    'category' => 'Technology',
    'is_featured' => true,
    'order' => 1,
    'is_active' => true
]);
echo "✓ Career created: ID {$career->id}\n";

$career->update(['title' => 'Advanced Data Science Career Path']);
echo "✓ Career updated\n";

$careers = Career::all();
echo "✓ Careers retrieved: {$careers->count()} total\n";

$career->delete();
echo "✓ Career deleted\n\n";

// Test Facility CRUD
echo "5. Testing Facility CRUD:\n";
$facility = Facility::create([
    'name' => 'Computer Lab',
    'slug' => 'computer-lab',
    'description' => 'State-of-the-art computer lab with 50 workstations',
    'image' => 'facilities/computer-lab.jpg',
    'icon' => 'computer',
    'category' => 'Academic',
    'capacity' => 50,
    'location' => 'Building A, Floor 2',
    'features' => ['WiFi', 'Projectors', 'Whiteboards', 'Printing'],
    'is_featured' => true,
    'order' => 1,
    'is_active' => true
]);
echo "✓ Facility created: ID {$facility->id}\n";

$facility->update(['capacity' => 60]);
echo "✓ Facility updated\n";

$facilities = Facility::all();
echo "✓ Facilities retrieved: {$facilities->count()} total\n";

$facility->delete();
echo "✓ Facility deleted\n\n";

// Test Settings CRUD
echo "6. Testing Settings CRUD:\n";
$setting = Setting::create([
    'key' => 'site_name',
    'value' => 'ECR Academy',
    'type' => 'text',
    'group' => 'general',
    'description' => 'Website name',
    'is_public' => true
]);
echo "✓ Setting created: ID {$setting->id}\n";

$setting->update(['value' => 'ECR Academy Updated']);
echo "✓ Setting updated\n";

$settings = Setting::all();
echo "✓ Settings retrieved: {$settings->count()} total\n";

$setting->delete();
echo "✓ Setting deleted\n\n";

// Test special Setting methods
echo "7. Testing Setting Helper Methods:\n";
Setting::setValue('test_key', 'test_value', 'text');
echo "✓ Setting value set using helper method\n";

$value = Setting::getValue('test_key', 'default');
echo "✓ Setting value retrieved: {$value}\n";

$groupSettings = Setting::where('group', 'general')->get();
echo "✓ Settings by group retrieved: {$groupSettings->count()} items\n\n";

echo "=== All New CRUD Operations Working Correctly! ===\n";
