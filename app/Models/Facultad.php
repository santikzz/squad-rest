<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facultad extends Model
{
    use HasFactory;

    public function carreras(){
        return $this->hasMany(Carrera::class, 'id_facultad');
    }

}
