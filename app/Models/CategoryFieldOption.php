<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoryFieldOption extends Model
{
    use HasFactory;
    protected $fillable = [
        'olx_id',
        'parent_olx_id',
        'category_field_id',
        'option_value',
        'option_label',
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
