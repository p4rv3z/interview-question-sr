<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'title', 'sku', 'description'
    ];

    public function createdAt()
    {
//        return $this->created_at->format('d-M-Y');
        return $this->created_at->diffForHumans();;
    }

    public function productVarients()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function productVarientPrice()
    {
        return $this->hasMany(ProductVariantPrice::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }
}
