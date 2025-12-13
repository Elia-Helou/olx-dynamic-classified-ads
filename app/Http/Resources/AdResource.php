<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $fieldValues = [];
        
        foreach ($this->fieldValues ?? [] as $fieldValue) {
            $field = $fieldValue->field;
            if (!$field) {
                continue;
            }

            $value = $fieldValue->option 
                ? $fieldValue->option->option_value 
                : $fieldValue->value;
            
            $fieldValues[$field->external_id] = [
                'name' => $field->name,
                'type' => $field->field_type,
                'value' => $value,
            ];
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'price' => (float) $this->price,
            'category' => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ] : null,
            'user' => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ] : null,
            'fields' => $fieldValues,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
