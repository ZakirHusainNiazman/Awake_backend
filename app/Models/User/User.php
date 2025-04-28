<?php

namespace App\Models\User;

use App\Models\Seller\Seller;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'profile_image',
        'user_type',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the role that owns the User.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the creator of the User.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all addresses associated with the User.
     */
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    /**
     * Get the default address for the User.
     */
    public function defaultAddress()
    {
        return $this->hasOne(Address::class)
                    ->where('is_default', true);
    }

    /**
     * Get the seller associated with the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function seller(): HasOne
    {
        return $this->hasOne(Seller::class);
    }
}
