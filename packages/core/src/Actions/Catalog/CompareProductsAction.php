<?php

declare(strict_types=1);

namespace Reyhan\Core\Actions\Catalog;

use Reyhan\Core\Models\Product;
use Illuminate\Database\Eloquent\Collection;

final class CompareProductsAction
{
    /**
     * Compare up to 4 products and build a unified specification comparison matrix.
     *
     * @param  list<string|int>  $identifiers  List of product slugs or IDs
     * @return array{
     *     products: list<array<string, mixed>>,
     *     specification_groups: list<array<string, mixed>>,
     * }
     */
    public function execute(array $identifiers): array
    {
        $identifiers = array_slice(array_unique($identifiers), 0, 4);

        if (empty($identifiers)) {
            return [
                'products' => [],
                'specification_groups' => [],
            ];
        }

        /** @var Collection<int, Product> $products */
        $products = Product::query()
            ->active()
            ->where(function ($q) use ($identifiers): void {
                $q->whereIn('slug', $identifiers)
                    ->orWhereIn('id', array_filter($identifiers, 'is_numeric'));
            })
            ->with([
                'brand',
                'category',
                'media',
                'activeVariants',
                'specifications.specification.group',
            ])
            ->get();

        $productList = [];
        $specGroupsMap = [];

        foreach ($products as $product) {
            $thumbnail = $product->getFirstMediaUrl('gallery', 'thumb')
                ?: $product->getFirstMediaUrl('gallery')
                ?: null;

            $productList[] = [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'brand' => $product->brand ? [
                    'id' => $product->brand->id,
                    'name' => $product->brand->name,
                    'slug' => $product->brand->slug,
                ] : null,
                'category' => $product->category ? [
                    'id' => $product->category->id,
                    'name' => $product->category->name,
                    'slug' => $product->category->slug,
                ] : null,
                'thumbnail' => $thumbnail,
                'price_range' => $product->price_range,
                'has_stock' => $product->activeVariants->some(fn ($v) => $v->stock > 0),
                'review_stats' => $product->getReviewStats(),
            ];

            // Process specifications
            foreach ($product->specifications as $prodSpec) {
                $spec = $prodSpec->specification;
                $group = $spec->group;

                $groupId = $group->id;
                $groupName = $group->name;
                $groupOrder = $group->order;

                if (! isset($specGroupsMap[$groupId])) {
                    $specGroupsMap[$groupId] = [
                        'id' => $groupId,
                        'name' => $groupName,
                        'order' => $groupOrder,
                        'specs' => [],
                    ];
                }

                $specId = $spec->id;
                if (! isset($specGroupsMap[$groupId]['specs'][$specId])) {
                    $specGroupsMap[$groupId]['specs'][$specId] = [
                        'id' => $specId,
                        'name' => $spec->name,
                        'unit' => $spec->unit,
                        'order' => $spec->order,
                        'values' => [],
                    ];
                }

                $displayVal = $prodSpec->value.($spec->unit ? ' '.$spec->unit : '');
                $specGroupsMap[$groupId]['specs'][$specId]['values'][(string) $product->id] = $displayVal;
            }
        }

        // Sort groups and specs
        uasort($specGroupsMap, fn ($a, $b) => $a['order'] <=> $b['order']);

        $formattedGroups = [];
        foreach ($specGroupsMap as $groupData) {
            uasort($groupData['specs'], fn ($a, $b) => $a['order'] <=> $b['order']);

            $specsList = [];
            foreach ($groupData['specs'] as $specItem) {
                // Ensure every product has a value or null
                $normalizedValues = [];
                foreach ($products as $p) {
                    $normalizedValues[(string) $p->id] = $specItem['values'][(string) $p->id] ?? '-';
                }

                $specsList[] = [
                    'id' => $specItem['id'],
                    'name' => $specItem['name'],
                    'unit' => $specItem['unit'],
                    'values' => $normalizedValues,
                ];
            }

            $formattedGroups[] = [
                'id' => $groupData['id'],
                'name' => $groupData['name'],
                'items' => $specsList,
            ];
        }

        return [
            'products' => $productList,
            'specification_groups' => $formattedGroups,
        ];
    }
}
