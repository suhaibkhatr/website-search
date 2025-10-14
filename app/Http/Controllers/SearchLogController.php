<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SearchLogController extends Controller
{
    public function index(Request $request)
    {
        $limit = (int) $request->query('limit', 20);
        $since = $request->query('since'); // optional ISO date filter

        $query = DB::table('search_logs')
            ->select('query', DB::raw('COUNT(*) as times'), DB::raw('MAX(created_at) as last_at'))
            ->when($since, fn($q) => $q->where('created_at', '>=', $since))
            ->groupBy('query')
            ->orderByDesc('times')
            ->limit($limit)
            ->get();

        return response()->json(['data' => $query]);
    }
}
