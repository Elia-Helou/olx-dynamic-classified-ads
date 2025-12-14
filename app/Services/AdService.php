<?php

namespace App\Services;

use App\Models\Ad;
use App\Models\AdFieldValue;
use App\Models\Category;
use App\Models\CategoryField;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdService
{
    public function create(array $data, User $user): array
    {
        try {
            DB::beginTransaction();

            $category = Category::find($data['category_id']);
            if (!$category) {
                return [
                    'success' => false,
                    'message' => 'Category not found',
                ];
            }

            $ad = $user->ads()->create([
                'category_id' => $data['category_id'],
                'title' => $data['title'],
                'description' => $data['description'],
                'price' => $data['price'],
            ]);

            $categoryFields = CategoryField::where('category_id', $data['category_id'])
                ->with('options')
                ->get();

            $fieldValuesToCreate = [];

            foreach ($categoryFields as $field) {
                $fieldValue = $data[$field->external_id] ?? null;

                if ($fieldValue === null && !$field->is_required) {
                    continue;
                }

                $optionId = null;
                $value = null;

                if ($field->isSelectType() && $fieldValue !== null) {
                    $option = $field->options->firstWhere('option_value', $fieldValue);

                    if ($option) {
                        $optionId = $option->id;
                    } else {
                        Log::warning("Option not found for field {$field->external_id} with value: {$fieldValue}");
                    }
                } else {
                    $value = $fieldValue;
                }

                $fieldValuesToCreate[] = [
                    'ad_id' => $ad->id,
                    'category_field_id' => $field->id,
                    'category_field_option_id' => $optionId,
                    'value' => $value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($fieldValuesToCreate)) {
                AdFieldValue::insert($fieldValuesToCreate);
            }

            DB::commit();

            $ad->load(['category', 'fieldValues.field', 'fieldValues.option']);

            return [
                'success' => true,
                'data' => $ad,
                'message' => 'Ad created successfully',
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create ad: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'data' => $data,
                'exception' => $e,
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create ad',
            ];
        }
    }

    public function getUserAds(User $user, int $perPage = 15): array
    {
        try {
            $ads = $user->ads()
                ->with(['category', 'fieldValues.field', 'fieldValues.option'])
                ->latest()
                ->paginate($perPage);

            return [
                'success' => true,
                'data' => $ads,
                'message' => 'Ads retrieved successfully',
            ];
        } catch (Exception $e) {
            Log::error('Failed to retrieve user ads: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'exception' => $e,
            ]);

            return [
                'success' => false,
                'message' => 'Failed to retrieve ads',
            ];
        }
    }

    // Public endpoint which means any authenticated user can view any ad.
    // To restrict to "ad owner" only, we can add: if ($ad->user_id !== Auth::id()) return 403
    public function getAd(int $adId): array
    {
        try {
            $ad = Ad::with(['category', 'user', 'fieldValues.field', 'fieldValues.option'])
                ->findOrFail($adId);

            return [
                'success' => true,
                'data' => $ad,
                'message' => 'Ad retrieved successfully',
            ];
        } catch (ModelNotFoundException $e) {
            return [
                'success' => false,
                'message' => 'Ad not found',
            ];
        } catch (Exception $e) {
            Log::error('Failed to retrieve ad: ' . $e->getMessage(), [
                'ad_id' => $adId,
                'exception' => $e,
            ]);

            return [
                'success' => false,
                'message' => 'Failed to retrieve ad',
            ];
        }
    }
}

