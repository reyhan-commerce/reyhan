<?php

declare(strict_types=1);

namespace Reyhan\Core\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property int $order
 * @property-read Collection<int, Specification> $specifications
 */
#[Guarded(['id'])]
class SpecificationGroup extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }

    /**
     * @return HasMany<Specification, $this>
     */
    public function specifications(): HasMany
    {
        return $this->hasMany(Specification::class)->orderBy('order');
    }
}
