<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // This 'role' in users table might be legacy, but keep for now if used elsewhere
        // 'employee_id', // <-- REMOVE THIS LINE IF users table DOES NOT HAVE employee_id
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
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
        ];
    }

    /**
     * Check if the user has a specific role.
     *
     * @param string $role
     * @return bool
     */
    public function hasRole(string $role): bool
    {
         // This logic is correct IF employee relation loads successfully
         return $this->employee && $this->employee->role === $role;
    }

     /**
     * Get the student record associated with the user.
     * This assumes a user can have one student profile.
     */
    public function student()
    {
        return $this->hasOne(Student::class);
    }

    // Relationship to Employee model
    // A User has one Employee record
    public function employee()
    {
        // This assumes 'user_id' is the foreign key on the 'employees' table
        return $this->hasOne(Employee::class); // <-- CORRECTED THIS LINE
    }
}