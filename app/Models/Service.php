<?php

namespace App\Models;

use App\Models\Review;
use App\Models\Category;
use App\Models\Provider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

    /**
     * Display the specified resource.
     *
     * @return  \App\Models\Campaign
     */
    public function campaigns()
    {
        return $this->hasMany(Campaign::class)->orderBy('created_at', 'desc');
    }

    /**
     * Display the specified resource.
     *
     * @return  \App\Models\Provider
     */
    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    /**
     * Display the specified resource.
     *
     * @return  \App\Models\Category
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Display the specified resource.
     *
     * @return  \App\Models\Category
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }



    /**
     * Interact with the image.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value != null && file_exists(public_path($value)) ? asset($value) : asset("assets/images/service/default.png"),
            // set: fn ($value) => strtolower($value),
        );
    }
}
