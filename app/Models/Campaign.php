<?php

namespace App\Models;

use App\Models\Service;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Campaign extends Model
{
    use HasFactory;

    /**
     * Display the specified resource.
     *
     * @return  \App\Models\Service
     */
    public function services()
    {
        return $this->belongsTo(Service::class)->orderBy('created_at', 'desc');
    }
}
