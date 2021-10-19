<?php

namespace App\Models;

use App\Traits\LoggingTraits;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens, LoggingTraits;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email',
        'phone',
        'first_name',
        'last_name',
        'middle_name',
        'position',
        'company',
        'password',
        'role_id',
        'contractor_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'role_id',
    ];

    protected $with = ['role'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'deleted' => 'bool',
    ];

    protected $dates = [
        'deleted_at'
    ];

    public function role()
    {
        return $this->belongsTo(Role::class)
            ->select('id', 'name');
    }

    public function contractor()
    {
        return $this->belongsTo(Contractor::class);
    }

    public function firebaseTokens()
    {
        return $this->hasMany(FirebaseToken::class);
    }

}
