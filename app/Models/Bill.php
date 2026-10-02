<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    // Mengizinkan semua kolom diisi secara massal dari Controller
    protected $guarded = [];
}