<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Users\UserStatus;
use App\Models\Products\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'avatar',
        'company_name',
        'country',
        'email_verified_at',
        'phone_verified_at',
        'password',
        'note',
        'last_activity_at',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_activity_at' => 'datetime',
            'status' => UserStatus::class,
        ];
    }

    public function watchlist(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_user_watchlist')
            ->withTimestamps();
    }

    public function chats(): HasMany
    {
        return $this->hasMany(Chat::class, 'buyer_id')->orWhere('seller_id', $this->id);
    }

    public function messages(): MorphMany
    {
        return $this->morphMany(Message::class, 'sender');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function escrows()
    {
        return Escrow::where('buyer_id', $this->id)
            ->orWhere('seller_id', $this->id);
    }

    public function escrowsAsBuyer(): HasMany
    {
        return $this->hasMany(Escrow::class, 'buyer_id');
    }

    public function escrowsAsSeller(): HasMany
    {
        return $this->hasMany(Escrow::class, 'seller_id');
    }
}
