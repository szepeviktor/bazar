<?php

namespace Bazar\Contracts\Models;

use Bazar\Contracts\Stockable;
use Bazar\Contracts\Taxable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

interface Item extends Taxable
{
    /**
     * Get the product for the item.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product(): BelongsTo;

    /**
     * Get the itemable model for the item.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function itemable(): MorphTo;

    /**
     * Get the stockable attribute.
     *
     * @return \Bazar\Contracts\Stockable|null
     */
    public function getStockableAttribute(): ?Stockable;

    /**
     * Get the formatted price attribute.
     *
     * @return string
     */
    public function getFormattedPriceAttribute(): string;

    /**
     * Get the total attribute.
     *
     * @return float
     */
    public function getTotalAttribute(): float;

    /**
     * Get the formatted total attribute.
     *
     * @return string
     */
    public function getFormattedTotalAttribute(): string;

    /**
     * Get the net total attribute.
     *
     * @return float
     */
    public function getNetTotalAttribute(): float;

    /**
     * Get the formatted net total attribute.
     *
     * @return string
     */
    public function getFormattedNetTotalAttribute(): string;

    /**
     * Get the item's price.
     *
     * @return float
     */
    public function price(): float;

    /**
     * Get the item's formatted price.
     *
     * @return string
     */
    public function formattedPrice(): string;

    /**
     * Get the item's total.
     *
     * @return float
     */
    public function total(): float;

    /**
     * Get the item's formatted total.
     *
     * @return string
     */
    public function formattedTotal(): string;

    /**
     * Get the item's net total.
     *
     * @return float
     */
    public function netTotal(): float;

    /**
     * Get the item's formatted net total.
     *
     * @return string
     */
    public function formattedNetTotal(): string;
}