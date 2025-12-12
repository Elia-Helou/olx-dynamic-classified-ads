<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'external_id',
        'name',
        'name_ar',
        'slug',
        'level',
        'parent_id',
        'purpose',
        'roles',
    ];

    protected $casts = [
        'roles' => 'array',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function fields(): HasMany
    {
        return $this->hasMany(CategoryField::class);
    }

    public function ads(): HasMany
    {
        return $this->hasMany(Ad::class);
    }
}
