<?php

namespace App\Models;

use App\Models\Traits\AdjustsOrder;
use Illuminate\Database\Eloquent\Model;

class NavLink extends Model
{
    use AdjustsOrder;

    protected $fillable = ['title', 'url', 'location', 'order'];
}
