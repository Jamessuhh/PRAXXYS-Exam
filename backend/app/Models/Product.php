<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'description',
        'datetime'
    ];

    protected $casts = [
        'datetime' => 'datetime'
    ];

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }
}