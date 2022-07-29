<?php

namespace App\Models;

use App\Enums\EmploymentType;
use App\Models\Click;
use App\Services\NanoidService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Listing extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'employment_type' => EmploymentType::class,
        'is_highlighted' => 'boolean',
        'is_pinned' => 'boolean',
        'is_closed' => 'boolean',
    ];

    protected $withCount = ['clicks'];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($listing) {
            $listing->nanoid = app()->make(NanoidService::class)->generate();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function clicks()
    {
        return $this->hasMany(Click::class);
    }

    public function scopePaid($query)
    {
        return $query->where('paid_at', '!=', null);
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    public function scopeHighlighted($query)
    {
        return $query->where('is_highlighted', true);
    }

    public static function salaryRange()
    {
        $range = [];
        for ($i = 0; $i <= 40; $i++) {
            $range[] = $i * 10000;
        }

        return $range;
    }
}
