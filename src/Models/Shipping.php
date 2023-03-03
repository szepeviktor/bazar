<?php

namespace Cone\Bazar\Models;

use Cone\Bazar\Database\Factories\ShippingFactory;
use Cone\Bazar\Interfaces\Models\Shipping as Contract;
use Cone\Bazar\Support\Facades\Shipping as Manager;
use Cone\Bazar\Traits\Addressable;
use Cone\Bazar\Traits\InteractsWithTaxes;
use Cone\Root\Traits\InteractsWithProxy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;
use Throwable;

class Shipping extends Model implements Contract
{
    use Addressable;
    use HasFactory;
    use InteractsWithProxy;
    use InteractsWithTaxes;

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<string>
     */
    protected $appends = [
        'driver_name',
        'formatted_total',
    ];

    /**
     * The attributes that should have default values.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'cost' => 0,
        'tax' => 0,
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'cost' => 'float',
        'tax' => 'float',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'cost',
        'driver',
        'tax',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'bazar_shippings';

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(static function (self $shipping): void {
            $shipping->driver = $shipping->driver ?: Manager::getDefaultDriver();
        });
    }

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
        return ShippingFactory::new();
    }

    /**
     * Get the shippable model for the shipping.
     */
    public function shippable(): MorphTo
    {
        return $this->morphTo()->withDefault(static function (): Cart {
            return Cart::proxy()->newInstance();
        });
    }

    /**
     * Get the driver attribute.
     */
    protected function driver(): Attribute
    {
        return new Attribute(
            get: static function (string $value = null): string {
                return $value ?: Manager::getDefaultDriver();
            }
        );
    }

    /**
     * Get the total attribute.
     */
    protected function total(): Attribute
    {
        return new Attribute(
            get: function (): float {
                return $this->getTotal();
            }
        );
    }

    /**
     * Get the formatted total attribute.
     */
    protected function formattedTotal(): Attribute
    {
        return new Attribute(
            get: function (): string {
                return $this->getFormattedTotal();
            }
        );
    }

    /**
     * Get the net total attribute.
     */
    protected function netTotal(): Attribute
    {
        return new Attribute(
            get: function (): float {
                return $this->getNetTotal();
            }
        );
    }

    /**
     * Get the formatted net total attribute.
     */
    protected function formattedNetTotal(): Attribute
    {
        return new Attribute(
            get: function (): string {
                return $this->getFormattedNetTotal();
            }
        );
    }

    /**
     * Get the name of the shipping method.
     */
    protected function driverName(): Attribute
    {
        return new Attribute(
            get: static function (mixed $value, array $attributes): string {
                try {
                    return Manager::driver($attributes['driver'])->getName();
                } catch (Throwable $exception) {
                    return $attributes['driver'];
                }
            }
        );
    }

    /**
     * Get the price.
     */
    public function getPrice(): float
    {
        return $this->cost;
    }

    /**
     * Get the formatted price.
     */
    public function getFormattedPrice(): string
    {
        return Str::currency($this->getPrice(), $this->shippable->getCurrency());
    }

    /**
     * Get the shipping's total.
     */
    public function getTotal(): float
    {
        return $this->getPrice() + $this->getTax();
    }

    /**
     * Get the shipping's formatted total.
     */
    public function getFormattedTotal(): string
    {
        return Str::currency($this->getTotal(), $this->shippable->getCurrency());
    }

    /**
     * Get the shipping's net total.
     */
    public function getNetTotal(): float
    {
        return $this->getPrice();
    }

    /**
     * Get the shipping's formatted net total.
     */
    public function getFormattedNetTotal(): string
    {
        return Str::currency($this->getNetTotal(), $this->shippable->getCurrency());
    }

    /**
     * Get the quantity.
     */
    public function getQuantity(): float
    {
        return 1;
    }

    /**
     * Calculate the cost.
     */
    public function calculateCost(bool $update = true): float
    {
        try {
            $this->cost = Manager::driver($this->driver)->calculate($this->shippable);

            if ($update) {
                $this->save();
            }
        } catch (Throwable $exception) {
            //
        } finally {
            return $this->cost;
        }
    }
}
