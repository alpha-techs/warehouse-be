<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Str;

class BaseModel extends Model
{
    protected static $unguarded = true;

    public function getAttribute($key)
    {
        $key = Str::snake($key);
        return parent::getAttribute($key);
    }

    public function setAttribute($key, $value)
    {
        $key = Str::snake($key);
        return parent::setAttribute($key, $value);
    }
}
