<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, \App\Traits\LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'image',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'two_factor_otp',
        'two_factor_otp_expires_at',
        'is_active',
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        return $this->image 
            ? asset('storage/' . $this->image) 
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF';
    }

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
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Determine if the user has two-factor authentication enabled.
     */
    public function hasTwoFactorEnabled(): bool
    {
        return !is_null($this->two_factor_confirmed_at);
    }

    /**
     * Generate a new OTP for the user.
     */
    public function generateOtp(): string
    {
        $otp = (string) rand(100000, 999999);
        $this->update([
            'two_factor_otp' => $otp,
            'two_factor_otp_expires_at' => now()->addMinutes(10),
        ]);
        return $otp;
    }

    /**
     * Verify the provided OTP.
     */
    public function verifyOtp(string $otp): bool
    {
        if ($this->two_factor_otp !== $otp) {
            return false;
        }

        if (now()->gt($this->two_factor_otp_expires_at)) {
            return false;
        }

        // Clear OTP after successful verification
        $this->update([
            'two_factor_otp' => null,
            'two_factor_otp_expires_at' => null,
        ]);

        return true;
    }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class)
            ->withPivot(['is_default', 'role_id'])
            ->withTimestamps();
    }

    public function defaultDepartment(): ?Department
    {
        return $this->departments()
            ->wherePivot('is_default', true)
            ->first();
    }

    public function createdAssets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Asset::class, 'created_by_id');
    }
}
