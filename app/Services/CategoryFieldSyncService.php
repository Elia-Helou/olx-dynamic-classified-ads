<?php

namespace App\Services;

use App\Models\Category;
use App\Models\CategoryField;
use App\Models\CategoryFieldOption;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CategoryFieldSyncService
{
    public function syncAll(): array
    {
        $categories = Category::whereNotNull('external_id')->get();

        if ($categories->isEmpty()) {
            return [
                'success' => false,
                'message' => 'No categories found. Please sync categories first.',
                'success_count' => 0,
                'error_count' => 0,
            ];
        }

        $successCount = 0;
        $errorCount = 0;

        foreach ($categories as $category) {
            try {
                $this->syncCategoryFields($category);
                $successCount++;
            } catch (Exception $e) {
                $errorCount++;
                Log::error("Failed to sync fields for category {$category->id} ({$category->external_id}): " . $e->getMessage(), [
                    'category_id' => $category->id,
                    'external_id' => $category->external_id,
                    'exception' => $e,
                ]);
            }
        }

        return [
            'success' => true,
            'message' => "Synced fields for {$successCount} categories. {$errorCount} failed.",
            'success_count' => $successCount,
            'error_count' => $errorCount,
        ];
    }

    public function syncCategoryFields(Category $category): void
    {
        $externalId = $category->external_id;
        $fields = $this->fetchCategoryFields($externalId, $category->olx_id);

        if (empty($fields)) {
            return;
        }

        $flatFields = $fields['flatFields'] ?? [];
        $childrenFields = $fields['childrenFields'] ?? [];
        $categoryFields = array_merge($flatFields, $childrenFields);

        if (empty($categoryFields)) {
            return;
        }

        foreach ($categoryFields as $fieldData) {
            if (empty($fieldData['id'])) {
                continue;
            }

            $field = CategoryField::updateOrCreate(
                [
                    'category_id' => $category->id,
                    'external_id' => $fieldData['id'],
                ],
                [
                    'name' => $fieldData['name'] ?? 'Unnamed',
                    'field_type' => $fieldData['valueType'] ?? 'string',
                    'is_required' => $fieldData['isMandatory'] ?? false,
                    'description' => null,
                    'min_value' => $fieldData['minValue'] ?? null,
                    'max_value' => $fieldData['maxValue'] ?? null,
                ]
            );

            if (empty($fieldData['choices'])) {
                continue;
            }

            $choices = $fieldData['choices'];
            if (array_is_list($choices)) {
                $this->syncFieldOptions($field, $choices);
            } else {
                $data = collect($choices)->flatten(1)->all();
                $this->syncFieldOptions($field, $data);
            }
        }
    }

    private function syncFieldOptions(CategoryField $field, array $choices): void
    {
        foreach ($choices as $choice) {
            if (empty($choice['id'])) {
                continue;
            }

            CategoryFieldOption::updateOrCreate(
                [
                    'olx_id' => $choice['id'],
                    'category_field_id' => $field->id,
                ],
                [
                    'option_label' => $choice['label'] ?? '',
                    'option_value' => $choice['value'] ?? '',
                    'parent_olx_id' => $choice['parentID'] ?? null,
                ]
            );
        }
    }

    private function fetchCategoryFields(string $externalId, int $olx_id): array
    {
        $url = sprintf(
            'https://www.olx.com.lb/api/categoryFields?categoryExternalIDs=%s&includeWithoutCategory=true&splitByCategoryIDs=true&flatChoices=true&groupChoicesBySection=true&flat=true',
            urlencode($externalId)
        );

        $response = Http::timeout(120)->get($url);

        if ($response->failed()) {
            Log::warning("Failed to fetch category fields for external_id: {$externalId}", [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return [];
        }

        $responseData = $response->json();
        $fields = $responseData[$olx_id] ?? [];

        if (empty($fields)) {
            Log::debug("No fields found for category olx_id: {$olx_id}, external_id: {$externalId}");
        }

        return $fields;
    }
}

