<?php

declare(strict_types=1);

namespace Reyhan\Core\Filament\Resources\BlogPosts\Pages;

use Reyhan\Core\Filament\Resources\BlogPosts\BlogPostResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBlogPost extends CreateRecord
{
    protected static string $resource = BlogPostResource::class;
}
