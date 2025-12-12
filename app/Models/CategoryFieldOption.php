<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoryFieldOption extends Model
{
    protected $fillable = [
        'category_field_id',
        'option_value',
        'option_label',
        'order',
    ];

    public function categoryField(): BelongsTo
    {
        return $this->belongsTo(CategoryField::class);
    }

    public function adFieldValues(): HasMany
    {
        return $this->hasMany(AdFieldValue::class);
    }
}
