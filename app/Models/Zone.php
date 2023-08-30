<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\Service;
use App\Models\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Zone extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "*"
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        "coordinates",
        "created_at",
        "updated_at"
    ];

    /**
     * Display the specified resource.
     *
     * @return  \App\Models\Campaign
     */
    public function campaigns()
    {
        return $this->hasMany(Campaign::class)->where("end", ">=", Carbon::now())->orderBy("created_at", "desc");
    }

    /**
     * Display the specified resource.
     *
     * @return  \App\Models\Category
     */
    public function categories()
    {
        return $this->hasMany(Category::class)->orderBy("created_at", "desc");
    }

    /**
     * Display the specified resource.
     *
     * @return  \App\Models\Service
     */
    public function services()
    {
        return $this->hasMany(Service::class)->orderBy("created_at", "desc");
    }
}
