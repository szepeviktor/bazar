<?php

namespace Cone\Bazar\Models;

use Cone\Bazar\Database\Factories\VariantFactory;
use Cone\Bazar\Interfaces\Itemable;
use Cone\Bazar\Interfaces\Models\Variant as Contract;
use Cone\Bazar\Traits\HasPrices;
use Cone\Bazar\Traits\HasProperties;
use Cone\Bazar\Traits\InteractsWithItemables;
use Cone\Bazar\Traits\InteractsWithStock;
use Cone\Root\Traits\HasMedia;
use Cone\Root\Traits\HasMetaData;
use Cone\Root\Traits\InteractsWithProxy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Variant extends Model implements Contract
{
    use HasFactory;
    use HasMedia;
    use HasMetaData;
    use HasPrices {
        HasPrices::getPrice as __getPrice;
    }
    use HasProperties;
    use InteractsWithItemables;
    use InteractsWithProxy;
    use InteractsWithStock;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'alias',
        'description',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'bazar_variants';

    /**
     * Get the proxied interface.
     */
    public static function getProxiedInterface(): string
    {
        return Contract::class;
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return VariantFactory::new();
    }

    /**
     * {@inheritdoc}
     */
    public function getMorphClass(): string
    {
        return static::getProxiedClass();
    }

    /**
     * Get the product for the transaction.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::getProxiedClass())
            ->withDefault();
    }

    /**
     * Get the alias attribute.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<string|null, never>
     */
    protected function alias(): Attribute
    {
        return new Attribute(
            get: function (?string $value): ?string {
                return $this->exists ? ($value ?: "#{$this->getKey()}") : $value;
            }
        );
    }

    /**
     * Get the price by the given type and currency.
     */
    public function getPrice(?string $currency = null): ?float
    {
        return $this->__getPrice($currency) ?: $this->product->getPrice($currency);
    }

    /**
     * Get the item representation of the buyable instance.
     */
    public function toItem(Itemable $itemable, array $attributes = []): Item
    {
        return $this->items()->make(array_merge($attributes, [
            'name' => sprintf('%s - %s', $this->product->name, $this->alias),
            'price' => $this->getPrice($itemable->getCurrency()),
        ]))->setRelation('buyable', $this);
    }
}
