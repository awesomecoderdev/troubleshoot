<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "provider_id",
        "address_id",
        "customer_id",
        "coupon_id",
        "handyman_id",
        "campaign_id",
        "service_id",
        "category_id",
        "zone_id",
        "status",
        "is_paid",
        "payment_method",
        "total_amount",
        "total_tax",
        "total_discount",
        "additional_charge",
        "is_rated",
    ];
}
