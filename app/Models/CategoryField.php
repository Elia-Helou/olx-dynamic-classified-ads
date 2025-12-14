<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoryField extends Model
{
    use HasFactory;
    protected $fillable = [
        'category_id',
        'external_id',
        'name',
        'field_type',
        'is_required',
        'min_value',
        'max_value',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(CategoryFieldOption::class);
    }

    public function adFieldValues(): HasMany
    {
        return $this->hasMany(AdFieldValue::class);
    }

    public function isSelectType(): bool
    {
        return in_array($this->field_type, ['select', 'radio', 'enum']);
    }

    public function hasOptions(): bool
    {
        return $this->options()->exists();
    }
}
