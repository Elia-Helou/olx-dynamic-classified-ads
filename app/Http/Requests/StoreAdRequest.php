<?php

namespace App\Http\Requests;

use App\Models\CategoryField;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;

class StoreAdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'category_id' => ['required', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
        ];

        $categoryId = $this->input('category_id');
        
        if ($categoryId) {
            $categoryFields = CategoryField::where('category_id', $categoryId)->get();
            
            foreach ($categoryFields as $field) {
                $fieldRules = [];
                
                if ($field->is_required) {
                    $fieldRules[] = 'required';
                } else {
                    $fieldRules[] = 'nullable';
                }

                switch ($field->field_type) {
                    case 'integer':
                    case 'int':
                        $fieldRules[] = 'integer';
                        break;
                    case 'number':
                    case 'float':
                    case 'double':
                        $fieldRules[] = 'numeric';
                        break;
                    case 'boolean':
                    case 'bool':
                        $fieldRules[] = 'boolean';
                        break;
                    case 'select':
                    case 'radio':
                    case 'enum':
                        $options = $field->options()->pluck('option_value')->toArray();
                        if (!empty($options)) {
                            $fieldRules[] = Rule::in($options);
                        }
                        break;
                    default:
                        $fieldRules[] = 'string';
                        break;
                }

                if ($field->min_value !== null) {
                    $fieldRules[] = 'min:' . $field->min_value;
                }

                if ($field->max_value !== null) {
                    $fieldRules[] = 'max:' . $field->max_value;
                }

                $rules[$field->external_id] = $fieldRules;
            }
        }

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $categoryId = $this->input('category_id');
            
            if (!$categoryId) {
                return;
            }

            $categoryFields = CategoryField::where('category_id', $categoryId)
                ->with('options')
                ->get()
                ->keyBy('external_id');

            if ($categoryFields->isEmpty()) {
                return;
            }

            $fieldDependencies = $this->buildFieldDependencies($categoryFields);

            foreach ($fieldDependencies as $childFieldId => $parentFieldId) {
                $parentValue = $this->input($parentFieldId);
                $childValue = $this->input($childFieldId);

                if ($parentValue === null || $childValue === null) {
                    continue;
                }

                if (!isset($categoryFields[$parentFieldId]) || !isset($categoryFields[$childFieldId])) {
                    continue;
                }

                $parentField = $categoryFields[$parentFieldId];
                $childField = $categoryFields[$childFieldId];

                $parentOption = $parentField->options->firstWhere('option_value', $parentValue);
                $childOption = $childField->options->firstWhere('option_value', $childValue);

                if (!$parentOption || !$childOption) {
                    continue;
                }

                if ($childOption->parent_olx_id !== null && $childOption->parent_olx_id != $parentOption->olx_id) {
                    $validator->errors()->add(
                        $childFieldId,
                        "The selected {$childField->name} does not belong to the selected {$parentField->name}."
                    );
                }
            }
        });
    }

    private function buildFieldDependencies($categoryFields): array
    {
        $dependencies = [];

        foreach ($categoryFields as $childField) {
            if (!$childField->isSelectType()) {
                continue;
            }

            if (!$childField->relationLoaded('options')) {
                $childField->load('options');
            }

            $childOptionsWithParent = $childField->options->whereNotNull('parent_olx_id');
            
            if ($childOptionsWithParent->isEmpty()) {
                continue;
            }

            $parentOlxIds = $childOptionsWithParent->pluck('parent_olx_id')->unique()->values();

            foreach ($categoryFields as $parentField) {
                if ($parentField->id === $childField->id) {
                    continue;
                }
                
                if (!$parentField->isSelectType()) {
                    continue;
                }

                if (!$parentField->relationLoaded('options')) {
                    $parentField->load('options');
                }

                $parentOlxIdsInField = $parentField->options->pluck('olx_id')->unique()->values();
                
                $intersection = $parentOlxIds->intersect($parentOlxIdsInField);
                
                if ($intersection->isNotEmpty()) {
                    $dependencies[$childField->external_id] = $parentField->external_id;
                    break;
                }
            }
        }

        return $dependencies;
    }

    public function messages(): array
    {
        $messages = [];
        $categoryId = $this->input('category_id');
        
        if ($categoryId) {
            $categoryFields = CategoryField::where('category_id', $categoryId)->get();
            
            foreach ($categoryFields as $field) {
                $messages[$field->external_id . '.required'] = "The {$field->name} field is required.";
                $messages[$field->external_id . '.integer'] = "The {$field->name} must be an integer.";
                $messages[$field->external_id . '.numeric'] = "The {$field->name} must be a number.";
                $messages[$field->external_id . '.boolean'] = "The {$field->name} must be true or false.";
                $messages[$field->external_id . '.in'] = "The selected {$field->name} is invalid.";
                $messages[$field->external_id . '.min'] = "The {$field->name} must be at least :min.";
                $messages[$field->external_id . '.max'] = "The {$field->name} must not exceed :max.";
            }
        }

        return $messages;
    }
}
