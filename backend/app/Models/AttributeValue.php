<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\AttributeValueFactory;
use Illuminate\Database\Eloquent\Casts\Attribute as CastAttribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AttributeValue extends Model
{
    /** @use HasFactory<AttributeValueFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'attribute_id',
        'value',
        'label',
        'hex_code',
        'order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    public function productVariants(): BelongsToMany
    {
        return $this->belongsToMany(ProductVariant::class, 'product_variant_values');
    }

    /**
     * @return CastAttribute<string, never>
     */
    protected function displayLabel(): CastAttribute
    {
        return CastAttribute::make(
            get: fn (): string => $this->label ?? $this->value,
        );
    }
}
