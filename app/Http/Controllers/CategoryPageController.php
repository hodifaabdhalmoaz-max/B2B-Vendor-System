<?php

namespace App\Http\Controllers;

use App\Services\CategoryPageService;
use App\Services\StatisticService;
use Illuminate\Http\Request;

class CategoryPageController extends Controller
{
    public function __construct(
        private CategoryPageService $categoryPage,
        private StatisticService $statistics
    ) {}

    public function show(Request $request, string $slug)
    {
        $data = $this->categoryPage->getPageData($request, $slug);

        $this->statistics->logCategoryView($data['category']->id, [
            'slug' => $data['category']->slug,
        ]);

        return view('category-shop', $data);
    }
}
