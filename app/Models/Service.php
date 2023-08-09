<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "name",
        "parent_id",
        "category_id",
        "provider_id",
        "zone_id",
        "price",
        "type",
        "duration",
        'image',
        "discount",
        'status',
        "short_description",
        "long_description",
        "tax",
        "order_count",
        "rating_count",
        "avg_rating",
        "is_featured",
        "by_admin",
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
    ];
}
