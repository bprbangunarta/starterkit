<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    use HasFactory;
    protected $fillable = [
        'app_name',
        'meta_description',
        'meta_keyword',
        'meta_image',
        'favicon',
        'logo',
        'footer_left',
        'footer_right',
    ];
}
