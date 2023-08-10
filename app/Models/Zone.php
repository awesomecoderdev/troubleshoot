<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Zone extends Model
{
    use HasFactory;

    /**
     * Display the specified resource.
     *
     * @return  \App\Models\Campaign
     */
    public function campaigns()
    {
        return $this->hasMany(Campaign::class)->where('end', '>=', Carbon::now())->orderBy('created_at', 'desc');
    }
}
