<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\{BlogPost, Product, Page, FAQ};

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $perPage = max(1, (int) $request->query('per_page', 10));
        $page    = max(1, (int) $request->query('page', 1));

        if ($q === '') {
            return response()->json([
                'data' => [],
                'meta' => [
                    'query' => $q,
                    'page' => $page,
                    'per_page' => $perPage,
                    'total' => 0,
                ],
            ]);
        }

        // Fan-out query to each model via Scout
        $types = [
            'blog'   => BlogPost::class,
            'product'=> Product::class,
            'page'   => Page::class,
            'faq'    => FAQ::class,
        ];

        $combined = collect();
        $totalsPerType = [];

        foreach ($types as $type => $model) {
            // Use Scout pagination so we can read totals per index
            $paginator = $model::search($q)->paginate($perPage, 'page', $page);

            $totalsPerType[$type] = method_exists($paginator, 'total') ? (int) $paginator->total() : count($paginator->items());

            $items = collect($paginator->items())->map(function ($item) use ($type) {
                $title = $item->title ?? $item->name ?? $item->question ?? 'Untitled';
                $bodyish = $item->body ?? $item->description ?? $item->answer ?? $item->content ?? '';
                $snippet = Str::limit(strip_tags((string) $bodyish), 180);

                // Recency: prefer published_at, then updated_at, then created_at
                $recency = null;
                if (!empty($item->published_at)) {
                    $recency = strtotime((string) $item->published_at);
                } elseif (!empty($item->updated_at)) {
                    $recency = strtotime((string) $item->updated_at);
                } elseif (!empty($item->created_at)) {
                    $recency = strtotime((string) $item->created_at);
                }

                return [
                    'id'      => (string) $item->id,
                    'type'    => $type,
                    'title'   => $title,
                    'snippet' => $snippet,
                    'link'    => url("/{$type}s/{$item->id}"),
                    'recency' => $recency,
                ];
            });

            $combined = $combined->merge($items);
        }

        // Sort by recency desc (fallback keeps order)
        $combined = $combined->sortByDesc('recency')->values();

        // Build a combined paginator (we slice AFTER sort to get page window)
        $totalCombined = array_sum($totalsPerType);
        $sliced = $combined->forPage($page, $perPage)->values();

        $paginator = new LengthAwarePaginator(
            $sliced,
            $totalCombined,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return response()->json([
            'data' => $paginator->items(),
            'meta' => [
                'query'     => $q,
                'page'      => $paginator->currentPage(),
                'per_page'  => $paginator->perPage(),
                'total'     => $paginator->total(),
                'total_by_type' => $totalsPerType, // helpful for UI
            ],
        ]);
    }

    // Lightweight suggestions from titles/names/questions
    public function suggestions(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        if ($q === '') {
            return response()->json(['data' => []]);
        }

        $suggestions = collect()
            ->merge(BlogPost::search($q)->take(5)->get()->pluck('title'))
            ->merge(Product::search($q)->take(5)->get()->pluck('name'))
            ->merge(Page::search($q)->take(5)->get()->pluck('title'))
            ->merge(FAQ::search($q)->take(5)->get()->pluck('question'))
            ->filter()
            ->unique()
            ->values()
            ->take(10);

        return response()->json(['data' => $suggestions]);
    }
}
