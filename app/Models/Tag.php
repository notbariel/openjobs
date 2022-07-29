<?php

namespace App\Models;

use App\Services\NanoidService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Tag extends Model
{
    use HasFactory;

    protected $guarded = [];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($tag) {
            $tag->nanoid = app()->make(NanoidService::class)->generate();

            while (Tag::where('slug', $tag->slug)->count() > 0) {
                $tag->slug = $tag->slug . '-' . Str::random(4);
            }
        });
    }

    public function listings()
    {
        return $this->belongsToMany(Listing::class);
    }
}
