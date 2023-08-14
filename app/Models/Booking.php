<?php

namespace App\Models;

use App\Models\Zone;
use App\Models\Coupon;
use App\Models\Address;
use App\Models\Service;
use App\Models\Campaign;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Handyman;
use App\Models\Provider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
     * @return  \App\Models\Handyman
     */
    public function handyman()
    {
        return $this->belongsTo(Handyman::class);
    }


    /**
     * Display the specified resource.
     *
     * @return  \App\Models\Service
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Display the specified resource.
     *
     * @return  \App\Models\Zone
     */
    public function zone()
    {
        return $this->belongsTo(Zone::class)->select("id", "name",);
    }

    /**
     * Display the specified resource.
     *
     * @return  \App\Models\Campaign
     */
    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }


    /**
     * Display the specified resource.
     *
     * @return  \App\Models\Coupon
     */
    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Display the specified resource.
     *
     * @return  \App\Models\Address
     */
    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    /**
     * Display the specified resource.
     *
     * @return  \App\Models\Customer
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class)->with("address");
    }
}
