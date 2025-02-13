<?php

namespace App\Http\Controllers\admin;

use App\Models\Comment;
use App\Models\Music;
use App\Models\Report;
use App\Models\User;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IndexController extends Controller
{
    public function index()
    {
        return view('template');
    }

    public function statistics(Request $request)
    {
        $week_ago = Carbon::now()->subWeek();
        $total_users = User::count();
        $new_users = User::where('created_at' , '>' , $week_ago)->count();
        $total_musics = Music::count();
        $new_musics = Music::where('created_at' , '>' , $week_ago)->count();
        $total_comments = Comment::count();
        $pending_comments = Comment::where('status' , 0)->count();
        $week_total_pays = Report::where('type', 0)->where('created_at' , '>' , $week_ago)->sum('val_money');
        $yesterday_total_pays = Report::where('type', 0)->where('created_at' , '<' , Carbon::today()->subMinutes(210)->format('Y-m-d H:i:s'))->where('created_at' , '>' , Carbon::yesterday()->subMinutes(210)->format('Y-m-d H:i:s'))->sum('val_money');
        $today_total_pays = Report::where('type', 0)->where('created_at' , '>' , Carbon::today()->subMinutes(210)->format('Y-m-d H:i:s'))->sum('val_money');

        $statistics = [
            'total_users' => number_format($total_users),
            'new_users' => $new_users,
            'total_musics' => number_format($total_musics),
            'new_musics' => $new_musics,
            'total_comments' => number_format($total_comments),
            'pending_comments' => $pending_comments,
            'today_total_pays' => number_format($today_total_pays),
            'yesterday_total_pays' => number_format($yesterday_total_pays),
            'week_total_pays' => number_format($week_total_pays)
        ];

        return response()->json([
            'data' => [
                'statistics' => $statistics,
                'user_chart_data' => $this->getUserChartData($request->input('user_chart_period')),
                'income_chart_data' => $this->getIncomeChartData($request->input('income_chart_period'))
            ],
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }

    private function getUserChartData($user_chart_period = "this_month")
    {
        $data = [
            ['data' => []],
            ['data' => []],
        ];

        if ($user_chart_period == "this_month") {
            $this_month = DB::table('users')
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as total'))
                ->groupBy('date')
                ->where('created_at', '>=', Carbon::now()->startOfMonth())
                ->get();
            $previous_month = DB::table('users')
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as total'))
                ->groupBy('date')
                ->where('created_at', '>=', Carbon::now()->subMonth()->startOfMonth())
                ->where('created_at', '<', Carbon::now()->subMonth()->endOfMonth())
                ->get();
            $this_month_data = [];
            foreach ($this_month as $month) {
                $this_month_data[] = $month->total;
            }
            $previous_month_data = [];
            foreach ($previous_month as $month) {
                $previous_month_data[] = $month->total;
            }

            $length1 = count($this_month_data);
            $length2 = count($previous_month_data);
            $lengthDifference = abs($length1 - $length2);
            if ($length1 > $length2) {
                $previous_month_data = array_merge($previous_month_data, array_fill(0, $lengthDifference, 0));
            } elseif ($length2 > $length1) {
                $this_month_data = array_merge($this_month_data, array_fill(0, $lengthDifference, 0));
            }

            $data = [
                ['data' => $this_month_data],
                ['data' => $previous_month_data],
            ];
        }

        if ($user_chart_period == 'last_month') {
            $last_month = DB::table('users')
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as total'))
                ->groupBy('date')
                ->where('created_at', '>=', Carbon::now()->subDays(29))
                ->get();
            $previous_last_month = DB::table('users')
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as total'))
                ->groupBy('date')
                ->where('created_at', '>=', Carbon::now()->subDays(59))
                ->where('created_at', '<', Carbon::now()->subDays(30))
                ->get();
            $last_month_data = [];
            foreach ($last_month as $month) {
                $last_month_data[] = $month->total;
            }
            $previous_month_data = [];
            foreach ($previous_last_month as $month) {
                $previous_month_data[] = $month->total;
            }
            $data = [
                ['data' => $last_month_data],
                ['data' => $previous_month_data],
            ];
        }

        if ($user_chart_period == 'this_year') {
            $this_year = DB::table('users')
                ->select(DB::raw('DATE_FORMAT(created_at, "%Y/%m") as date'), DB::raw('COUNT(*) as total'))
                ->groupBy('date')
                ->where('created_at', '>=', Carbon::now()->startOfYear())
                ->get();
            $previous_year = DB::table('users')
                ->select(DB::raw('DATE_FORMAT(created_at, "%Y/%m") as date'), DB::raw('COUNT(*) as total'))
                ->groupBy('date')
                ->where('created_at', '>=', Carbon::now()->subYear()->startOfYear())
                ->where('created_at', '<', Carbon::now()->subYear()->endOfYear())
                ->get();
            $this_year_data = [];
            foreach ($this_year as $year) {
                $this_year_data[] = $year->total;
            }
            $previous_year_data = [];
            foreach ($previous_year as $year) {
                $previous_year_data[] = $year->total;
            }

            $length1 = count($this_year_data);
            $length2 = count($previous_year_data);
            $lengthDifference = abs($length1 - $length2);
            if ($length1 > $length2) {
                $previous_year_data = array_merge($previous_year_data, array_fill(0, $lengthDifference, 0));
            } elseif ($length2 > $length1) {
                $this_year_data = array_merge($this_year_data, array_fill(0, $lengthDifference, 0));
            }

            $data = [
                ['data' => $this_year_data],
                ['data' => $previous_year_data],
            ];
        }

        if ($user_chart_period == 'last_year') {
            $last_year = DB::table('users')
                ->select(DB::raw('DATE_FORMAT(created_at, "%Y/%m") as date'), DB::raw('COUNT(*) as total'))
                ->groupBy('date')
                ->where('created_at', '>=', Carbon::now()->subYear()->addMonth())
                ->get();
            $previous_last_year = DB::table('users')
                ->select(DB::raw('DATE_FORMAT(created_at, "%Y/%m") as date'), DB::raw('COUNT(*) as total'))
                ->groupBy('date')
                ->where('created_at', '>=', Carbon::now()->subYears(2)->addMonth())
                ->where('created_at', '<', Carbon::now()->subYear())
                ->get();
            $last_year_data = [];
            foreach ($last_year as $year) {
                $last_year_data[] = $year->total;
            }
            $previous_year_data = [];
            foreach ($previous_last_year as $year) {
                $previous_year_data[] = $year->total;
            }
            $data = [
                ['data' => $last_year_data],
                ['data' => $previous_year_data],
            ];
        }

        return $data;
    }

    private function getIncomeChartData($income_chart_period = "this_month")
    {
        $data = [
            ['data' => []],
            ['data' => []],
        ];

        if ($income_chart_period == "this_month") {
            $this_month = DB::table('reports')
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(val_money) as total'))
                ->groupBy('date')
                ->where('created_at', '>=', Carbon::now()->startOfMonth())
                ->where('type', 0)
                ->get();
            $previous_month = DB::table('reports')
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(val_money) as total'))
                ->groupBy('date')
                ->where('created_at', '>=', Carbon::now()->subMonth()->startOfMonth())
                ->where('created_at', '<', Carbon::now()->subMonth()->endOfMonth())
                ->where('type', 0)
                ->get();
            $this_month_data = [];
            foreach ($this_month as $month) {
                $this_month_data[] = $month->total;
            }
            $previous_month_data = [];
            foreach ($previous_month as $month) {
                $previous_month_data[] = $month->total;
            }

            $length1 = count($this_month_data);
            $length2 = count($previous_month_data);
            $lengthDifference = abs($length1 - $length2);
            if ($length1 > $length2) {
                $previous_month_data = array_merge($previous_month_data, array_fill(0, $lengthDifference, 0));
            } elseif ($length2 > $length1) {
                $this_month_data = array_merge($this_month_data, array_fill(0, $lengthDifference, 0));
            }

            $data = [
                ['data' => $this_month_data],
                ['data' => $previous_month_data],
            ];
        }

        if ($income_chart_period == 'last_month') {
            $last_month = DB::table('reports')
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(val_money) as total'))
                ->groupBy('date')
                ->where('created_at', '>=', Carbon::now()->subDays(29))
                ->where('type', 0)
                ->get();
            $previous_last_month = DB::table('reports')
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(val_money) as total'))
                ->groupBy('date')
                ->where('created_at', '>=', Carbon::now()->subDays(59))
                ->where('created_at', '<', Carbon::now()->subDays(30))
                ->where('type', 0)
                ->get();
            $last_month_data = [];
            foreach ($last_month as $month) {
                $last_month_data[] = $month->total;
            }
            $previous_month_data = [];
            foreach ($previous_last_month as $month) {
                $previous_month_data[] = $month->total;
            }
            $data = [
                ['data' => $last_month_data],
                ['data' => $previous_month_data],
            ];
        }

        if ($income_chart_period == 'this_year') {
            $this_year = DB::table('reports')
                ->select(DB::raw('DATE_FORMAT(created_at, "%Y/%m") as date'), DB::raw('SUM(val_money) as total'))
                ->groupBy('date')
                ->where('created_at', '>=', Carbon::now()->startOfYear())
                ->where('type', 0)
                ->get();
            $previous_year = DB::table('reports')
                ->select(DB::raw('DATE_FORMAT(created_at, "%Y/%m") as date'), DB::raw('SUM(val_money) as total'))
                ->groupBy('date')
                ->where('created_at', '>=', Carbon::now()->subYear()->startOfYear())
                ->where('created_at', '<', Carbon::now()->subYear()->endOfYear())
                ->where('type', 0)
                ->get();
            $this_year_data = [];
            foreach ($this_year as $year) {
                $this_year_data[] = $year->total;
            }
            $previous_year_data = [];
            foreach ($previous_year as $year) {
                $previous_year_data[] = $year->total;
            }

            $length1 = count($this_year_data);
            $length2 = count($previous_year_data);
            $lengthDifference = abs($length1 - $length2);
            if ($length1 > $length2) {
                $previous_year_data = array_merge($previous_year_data, array_fill(0, $lengthDifference, 0));
            } elseif ($length2 > $length1) {
                $this_year_data = array_merge($this_year_data, array_fill(0, $lengthDifference, 0));
            }

            $data = [
                ['data' => $this_year_data],
                ['data' => $previous_year_data],
            ];
        }

        if ($income_chart_period == 'last_year') {
            $last_year = DB::table('reports')
                ->select(DB::raw('DATE_FORMAT(created_at, "%Y/%m") as date'), DB::raw('SUM(val_money) as total'))
                ->groupBy('date')
                ->where('created_at', '>=', Carbon::now()->subYear()->addMonth())
                ->where('type', 0)
                ->get();
            $previous_last_year = DB::table('reports')
                ->select(DB::raw('DATE_FORMAT(created_at, "%Y/%m") as date'), DB::raw('SUM(val_money) as total'))
                ->groupBy('date')
                ->where('created_at', '>=', Carbon::now()->subYears(2)->addMonth())
                ->where('created_at', '<', Carbon::now()->subYear())
                ->where('type', 0)
                ->get();
            $last_year_data = [];
            foreach ($last_year as $year) {
                $last_year_data[] = $year->total;
            }
            $previous_year_data = [];
            foreach ($previous_last_year as $year) {
                $previous_year_data[] = $year->total;
            }
            $data = [
                ['data' => $last_year_data],
                ['data' => $previous_year_data],
            ];
        }

        return $data;
    }
}
