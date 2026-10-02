<?php

declare(strict_types=1);

namespace Reyhan\Core\Database\Factories;

use Reyhan\Core\Models\Admin;
use Reyhan\Core\Models\BlogCategory;
use Reyhan\Core\Models\BlogPost;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BlogPost>
 */
class BlogPostFactory extends Factory
{
    protected $model = BlogPost::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(4);

        return [
            'category_id' => BlogCategory::factory(),
            'author_id' => Admin::factory(),
            'title' => 'راهنمای '.$title,
            'slug' => fake()->unique()->slug(),
            'summary' => fake()->paragraph(),
            'content' => '<p>'.fake()->paragraphs(3, true).'</p>',
            'featured_image' => null,
            'reading_time' => 5,
            'views_count' => fake()->numberBetween(10, 1000),
            'is_featured' => false,
            'is_published' => true,
            'published_at' => now(),
            'tags' => ['زیبایی', 'پوست', 'مراقبت'],
            'meta_title' => null,
            'meta_description' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => true,
            'published_at' => now(),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
            'published_at' => null,
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }
}
