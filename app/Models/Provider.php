<?php

namespace App\Models;


use App\Models\Review;
use DateTimeInterface;
use App\Models\Booking;
use App\Models\Service;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\NewAccessToken;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Provider extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "zone_id",
        "company_name",
        "first_name",
        "last_name",
        "email",
        "password",
        "phone",
        "identity_number",
        "contact_person_name",
        "contact_person_phone",
        "account_email",
        "image",
        "identity_image",
        "order_count",
        "service_man_count",
        "service_capacity_per_day",
        "rating_count",
        "avg_rating",
        "commission_status",
        "commission_percentage",
        "is_active",
        "is_approved",
        "start",
        "end",
        "off_day",
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
        'identity_image' => AsCollection::class,
        'off_day' => AsCollection::class,
    ];

    /**
     * Interact with the user's first name.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function avgRating(): Attribute
    {
        $ratings = $this->reviews()->pluck('review_rating')->toArray();
        $averageRating = collect($ratings)->average();
        return Attribute::make(
            get: fn ($value) => $averageRating,
            // set: fn ($value) => strtolower($value),
        );
    }

    /**
     * Display the specified resource.
     *
     * @return  \App\Models\Service
     */
    public function services()
    {
        return $this->hasMany(Service::class)->orderBy('created_at', 'desc');
    }

    /**
     * Display the specified resource.
     *
     * @return  \App\Models\Service
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class)->orderBy('created_at', 'desc');
    }

    /**
     * Display the specified resource.
     *
     * @return  \App\Models\Service
     */
    public function reviews()
    {
        return $this->hasMany(Review::class)->orderBy('created_at', 'desc');
    }

    /**
     * Interact with the user's first name.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    // protected function meta(): Attribute
    // {
    //     return Attribute::make(
    //         get: fn ($value) => Collection::make($value),
    //         set: fn ($value) => strtolower($value),
    //     );
    // }


    /**
     * Create a new personal access token for the user.
     *
     * @param  string  $name
     * @param  array  $abilities
     * @param  \DateTimeInterface|null  $expiresAt
     * @return \Laravel\Sanctum\NewAccessToken
     */
    public function createToken(string $name, array $abilities = ['*'], DateTimeInterface $expiresAt = null)
    {
        $token = $this->tokens()->create([
            'name' => $name,
            'token' => hash('sha256', $plainTextToken = Str::random(70)),
            'abilities' => $abilities,
            'expires_at' => $expiresAt,
        ]);

        return new NewAccessToken($token, $plainTextToken);
    }
}
