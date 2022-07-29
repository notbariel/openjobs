<?php

namespace App\Models;

use App\Services\NanoidService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $guarded = [];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($company) {
            $company->nanoid = app()->make(NanoidService::class)->generate();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function listings()
    {
        return $this->hasMany(Listing::class);
    }

    public function openListings()
    {
        return $this->hasMany(Listing::class)
            ->where('is_closed', false);
    }

    public function getHostAttribute()
    {
        $url = parse_url($this->url);

        return $url['host'];
    }

    public function getLogoSrcAttribute()
    {
        return $this->logo ? route('image.show', $this->logo) : null;
    }
}
