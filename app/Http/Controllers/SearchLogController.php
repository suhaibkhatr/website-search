<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class SearchLogController extends Controller
{
    public function index(Request $request)
    {
        $limit = (int) $request->query('limit', 20);

        $query = DB::table('search_logs')
            ->select('query', DB::raw('COUNT(*) as times'), DB::raw('MAX(created_at) as last_at'))
            ->groupBy('query')
            ->orderByDesc('times')
            ->limit($limit)
            ->get();

        return response()->json(['data' => $query]);
    }
}
