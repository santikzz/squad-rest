<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carrera extends Model
{
    use HasFactory;

    public function facultad(){
        return $this->belongsTo(Facultad::class, 'id_facultad');
    }

    public function groups(){
        return $this->hasMany(Group::class);
    }

    public function users(){
        return $this->hasMany(User::class);
    }

    protected $fillable = [
        'id_facultad',
        'name',
    ];



}
