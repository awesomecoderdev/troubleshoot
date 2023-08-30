<?php

namespace App\Models;

use App\Models\Booking;
use App\Models\Handyman;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Schedule extends Model
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
    // protected $hidden = [
    //     "created_at",
    //     "updated_at"
    // ];

    /**
     * Display the specified resource.
     *
     * @return  \App\Models\Booking
     */
    public function booking()
    {
        return $this->hasOne(Booking::class);
    }

    /**
     * Display the specified resource.
     *
     * @return  \App\Models\Handyman
     */
    public function handyman()
    {
        return $this->hasOne(Handyman::class);
    }
}
