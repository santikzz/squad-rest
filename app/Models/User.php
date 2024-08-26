<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // a user can own many groyps (one-to-many)
    public function groups()
    {
        return $this->hasMany(Group::class);
    }

    public function ownedGroups()
    {
        return $this->hasMany(Group::class, 'owner_id');
    }

    // a user can belong to many groups (many-to-many)
    public function joinedGroups()
    {
        return $this->belongsToMany(Group::class, 'user_group');
    }

    public function joinRequests()
    {
        return $this->hasMany(UserGroupJoinRequest::class, 'user_id');
    }

    public function carrera(){
        return $this->belongsTo(Carrera::class, 'id_carrera');
    }

    public function facultad(){
        return $this->belongsTo(Facultad::class);
    }

    public function reportsMade(){
        return $this->hasMany(Report::class, 'reporter_user_id');
    }

    public function reportsReceived(){
        return $this->hasMany(Report::class, 'reported_user_id');
    }

    public function notifications(){
        return $this->hasMany(Notification::class, 'user_id');
    }

    protected $fillable = [
        'ulid',
        'name',
        'surname',
        'email',
        'password',
        'id_carrera',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
