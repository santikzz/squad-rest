<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    public function owner(){
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function tags(){
        return $this->belongsToMany(Tag::class, 'group_tag', 'group_id', 'tag_id');
    }

    // a group can have many users (many-to-many)
    public function users(){
        return $this->belongsToMany(User::class, 'user_group');
    }
    
    public function members(){
        return $this->belongsToMany(User::class, 'user_group', 'group_id', 'user_id');
    }

}
