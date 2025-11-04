<?php

namespace App\Http\Controllers\Homework;

use App\Http\Controllers\Controller;
use App\Models\Homework;
use App\Models\HomeworkUser;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        // Get date range from request or use defaults
        $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        // Get total statistics
        $stats = [
            'total_students' => HomeworkUser::where('role', 'student')->count(),
            'total_teachers' => HomeworkUser::whereIn('role', ['admin', 'teacher'])->count(),
            'total_classes' => SchoolClass::count(),
            'total_homework' => Homework::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])->count(),
            'total_views' => DB::table('homework_views')->distinct()->count(['homework_id', 'student_id']),
        ];

        // Get homework per class with view statistics (filtered by date)
        $classStats = SchoolClass::with(['homework' => function($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            }, 'homework.views', 'students'])
            ->get()
            ->map(function ($class) {
                $homeworkCount = $class->homework->count();
                $totalStudents = $class->students->count();
                $totalViews = $class->homework->sum(function ($hw) {
                    return $hw->views->count();
                });
                
                return [
                    'class_name' => $class->name,
                    'total_students' => $totalStudents,
                    'homework_count' => $homeworkCount,
                    'total_views' => $totalViews,
                    'avg_views_per_homework' => $homeworkCount > 0 ? round($totalViews / $homeworkCount, 1) : 0,
                ];
            });

        // Get homework with most views (filtered by date)
        $topHomework = Homework::withCount('views')
            ->with('schoolClass')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('views_count', 'desc')
            ->take(10)
            ->get();

        // Get students who viewed the most homework
        $topStudents = DB::table('homework_views')
            ->join('homework_users', 'homework_views.student_id', '=', 'homework_users.id')
            ->join('homework', 'homework_views.homework_id', '=', 'homework.id')
            ->whereBetween('homework.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select('homework_users.id', 'homework_users.name', 'homework_users.mobile_number', DB::raw('COUNT(DISTINCT homework_views.homework_id) as homework_viewed'))
            ->groupBy('homework_users.id', 'homework_users.name', 'homework_users.mobile_number')
            ->orderBy('homework_viewed', 'desc')
            ->take(10)
            ->get();

        return view('homework.reports.index', compact('stats', 'classStats', 'topHomework', 'topStudents', 'startDate', 'endDate'));
    }
}

