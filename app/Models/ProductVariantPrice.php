<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariantPrice extends Model
{
    protected $fillable = [
        'price',
        'stock',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariantOne()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_one');
    }

    public function productVariantTwo()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_two');
    }

    public function productVariantThree()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_three');
    }

    public function productVeriant()
    {
        $value = "";
        if ($this->productVariantOne != null) {
            $value .= $this->productVariantOne->variant . "/";
        }
        if ($this->productVariantTwo != null) {
            $value .= $this->productVariantTwo->variant . "/";
        }
        if ($this->productVariantThree != null) {
            $value .= $this->productVariantThree->variant;
        }
        return str_ends_with($value, '/') ? substr($value, 0, -1) : $value;
    }
}
