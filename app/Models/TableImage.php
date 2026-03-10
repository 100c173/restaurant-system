<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class TableImage extends Model
{
    public function image(): MorphTo{
        return $this->morphTo();
    }
}
