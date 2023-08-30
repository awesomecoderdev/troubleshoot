<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        "created_at",
        // "updated_at"
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "booking_id",
        "service_id",
        "provider_id",
        "customer_name",
        "customer_id",
        "review_rating",
        "review_comment",
        "booking_date",
        "is_active",
    ];
}
