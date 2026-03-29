<?php

namespace Modules\Restaurants\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
// use Modules\Restaurants\Database\Factories\ModifierGroupFactory;

class ModifierGroup extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['name', 'is_required', 'is_multiple', 'min_selections', 'max_selections'];

    protected $casts = [
        'is_required' => 'boolean',
        'is_multiple' => 'boolean',
    ];

    public function modifiers():HasMany{
        return $this->hasMany(Modifier::class);
    }
}
