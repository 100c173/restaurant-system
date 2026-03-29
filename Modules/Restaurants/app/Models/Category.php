<?php

namespace Modules\Restaurants\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
// use Modules\Restaurants\Database\Factories\CategoryFactory;

class Category extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = 
    [
        'menu_id',
        'name',
        'description',
        'img_path',
        'position',
        'is_active',
    ];

    public function menu():BelongsTo
    {
        return $this->BelongsTo(Menu::class);
    }

    public function menuItems():HasMany{
        return $this->hasMany(MenuItem::class);
    }
}
