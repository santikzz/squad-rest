<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    public function attachedGroups(){
        return $this->belongsToMany(Group::class, 'group_tag');
    }
}
