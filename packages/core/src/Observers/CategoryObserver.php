<?php

declare(strict_types=1);

namespace Reyhan\Core\Observers;

use Reyhan\Core\Models\Category;
use Reyhan\Core\Services\Catalog\CategoryService;

final class CategoryObserver
{
    public function __construct(
        protected CategoryService $categoryService,
    ) {}

    /**
     * Handle the Category "saved" event (covers created and updated).
     */
    public function saved(Category $category): void
    {
        $this->categoryService->forgetTreeCache();
    }

    /**
     * Handle the Category "deleted" event.
     */
    public function deleted(Category $category): void
    {
        $this->categoryService->forgetTreeCache();
    }

    /**
     * Handle the Category "restored" event.
     */
    public function restored(Category $category): void
    {
        $this->categoryService->forgetTreeCache();
    }
}
