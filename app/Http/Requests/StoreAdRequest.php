<?php

namespace App\Http\Requests;

use App\Models\Category;
use App\Models\CategoryField;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
