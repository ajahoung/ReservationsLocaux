<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Periode extends Model
{
    protected $table="periode";
    use HasFactory;
    protected $fillable = [
        'libelle',
    ];
}
