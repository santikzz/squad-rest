<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class Group extends Model
{
    use HasFactory;

    // protected $fillable = [
    //     'ulid',
    //     'owner_id',  
    //     'title',
    //     'description',
    //     'privacy',
    //     'max_members',
    // ];

    // a group can be owned by one user (one-to-many)
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'group_tag', 'group_id', 'tag_id');
    }

    // a group can have many users (many-to-many)
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_group');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'user_group', 'group_id', 'user_id');
    }

    public function joinRequests()
    {
        return $this->hasMany(UserGroupJoinRequest::class, 'group_id');
    }

    public function carrera()
    {
        return $this->belongsTo(Carrera::class, 'id_carrera');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'group_id');
    }
}
