<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function dashboard()
    {
        $totalStudents = Student::count();
        $totalCourses = Course::count();
        $totalEnrollments = Enrollment::count();
        $activeEnrollments = Enrollment::where('enrollment_status', 'active')->count();
        
        $revenue = Enrollment::where('payment_status', 'paid')->sum('amount_paid');
        
        $recentEnrollments = Enrollment::with(['student', 'course'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'stats' => [
                'total_students' => $totalStudents,
                'total_courses' => $totalCourses,
                'total_enrollments' => $totalEnrollments,
                'active_enrollments' => $activeEnrollments,
                'total_revenue' => $revenue,
            ],
            'recent_enrollments' => $recentEnrollments
        ]);
    }

    public function enrollmentStats()
    {
        $enrollmentsByMonth = Enrollment::select(
                DB::raw('MONTH(enrollment_date) as month'),
                DB::raw('YEAR(enrollment_date) as year'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->take(12)
            ->get();

        $enrollmentsByStatus = Enrollment::select('enrollment_status', DB::raw('COUNT(*) as count'))
            ->groupBy('enrollment_status')
            ->get();

        $enrollmentsByPayment = Enrollment::select('payment_status', DB::raw('COUNT(*) as count'))
            ->groupBy('payment_status')
            ->get();

        return response()->json([
            'by_month' => $enrollmentsByMonth,
            'by_status' => $enrollmentsByStatus,
            'by_payment' => $enrollmentsByPayment
        ]);
    }

    public function courseStats()
    {
        $coursesByEnrollment = Course::withCount('enrollments')
            ->orderBy('enrollments_count', 'desc')
            ->take(10)
            ->get();

        $coursesByRevenue = Course::select('courses.*', DB::raw('COALESCE(SUM(enrollments.amount_paid), 0) as revenue'))
            ->leftJoin('enrollments', 'courses.id', '=', 'enrollments.course_id')
            ->where('enrollments.payment_status', 'paid')
            ->groupBy('courses.id')
            ->orderBy('revenue', 'desc')
            ->take(10)
            ->get();

        $coursesByLevel = Course::select('level', DB::raw('COUNT(*) as count'))
            ->groupBy('level')
            ->get();

        return response()->json([
            'by_enrollment' => $coursesByEnrollment,
            'by_revenue' => $coursesByRevenue,
            'by_level' => $coursesByLevel
        ]);
    }

    public function studentStats()
    {
        $studentsByMonth = Student::select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('YEAR(created_at) as year'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->take(12)
            ->get();

        $studentsByEducation = Student::select('education_level', DB::raw('COUNT(*) as count'))
            ->whereNotNull('education_level')
            ->groupBy('education_level')
            ->get();

        $activeVsInactive = Student::select('is_active', DB::raw('COUNT(*) as count'))
            ->groupBy('is_active')
            ->get();

        return response()->json([
            'by_month' => $studentsByMonth,
            'by_education' => $studentsByEducation,
            'active_status' => $activeVsInactive
        ]);
    }

    public function revenueStats()
    {
        $revenueByMonth = Enrollment::select(
                DB::raw('MONTH(enrollment_date) as month'),
                DB::raw('YEAR(enrollment_date) as year'),
                DB::raw('SUM(amount_paid) as revenue')
            )
            ->where('payment_status', 'paid')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->take(12)
            ->get();

        $revenueByCourse = Course::select('courses.title', DB::raw('COALESCE(SUM(enrollments.amount_paid), 0) as revenue'))
            ->leftJoin('enrollments', 'courses.id', '=', 'enrollments.course_id')
            ->where('enrollments.payment_status', 'paid')
            ->groupBy('courses.id', 'courses.title')
            ->orderBy('revenue', 'desc')
            ->take(10)
            ->get();

        $paymentStatusBreakdown = Enrollment::select('payment_status', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount_paid) as total'))
            ->groupBy('payment_status')
            ->get();

        return response()->json([
            'by_month' => $revenueByMonth,
            'by_course' => $revenueByCourse,
            'payment_breakdown' => $paymentStatusBreakdown
        ]);
    }

    public function exportData(Request $request)
    {
        $type = $request->get('type', 'students');
        
        switch ($type) {
            case 'students':
                $data = Student::all();
                break;
            case 'courses':
                $data = Course::all();
                break;
            case 'enrollments':
                $data = Enrollment::with(['student', 'course'])->get();
                break;
            default:
                return response()->json(['message' => 'Invalid export type'], 400);
        }

        return response()->json($data);
    }
}
