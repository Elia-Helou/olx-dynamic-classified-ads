<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\Category;
use App\Models\CategoryField;
use App\Models\CategoryFieldOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;
    private string $token;
    private Category $category;
    private CategoryField $requiredSelectField;
    private CategoryField $requiredIntegerField;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'name' => 'Test User',
        ]);

        $this->token = $this->user->createToken('test-token')->plainTextToken;

        $this->category = Category::factory()->create([
            'name' => 'Test Category',
            'external_id' => 'test-category-123',
            'olx_id' => 123,
        ]);

        $this->requiredSelectField = CategoryField::factory()->create([
            'category_id' => $this->category->id,
            'external_id' => 'brand',
            'name' => 'Brand',
            'field_type' => 'select',
            'is_required' => true,
        ]);

        CategoryFieldOption::factory()->create([
            'category_field_id' => $this->requiredSelectField->id,
            'olx_id' => 1,
            'option_value' => 'toyota',
            'option_label' => 'Toyota',
        ]);

        CategoryFieldOption::factory()->create([
            'category_field_id' => $this->requiredSelectField->id,
            'olx_id' => 2,
            'option_value' => 'honda',
            'option_label' => 'Honda',
        ]);

        $this->requiredIntegerField = CategoryField::factory()->create([
            'category_id' => $this->category->id,
            'external_id' => 'year',
            'name' => 'Year',
            'field_type' => 'integer',
            'is_required' => true,
        ]);
    }

    public function test_unauthenticated_user_cannot_create_ad(): void
    {
        $response = $this->postJson('/api/v1/ads', []);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthenticated',
            ]);
    }

    public function test_can_create_ad_with_valid_data(): void
    {
        $adData = [
            'category_id' => $this->category->id,
            'title' => 'Test Ad Title',
            'description' => 'Test ad description',
            'price' => 10000,
            'brand' => 'toyota',
            'year' => 2020,
        ];

        $response = $this->authenticatedRequest($this->token)
            ->postJson('/api/v1/ads', $adData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'title',
                    'description',
                    'price',
                    'category',
                    'user',
                    'fields',
                    'created_at',
                    'updated_at',
                ],
                'message',
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'title' => 'Test Ad Title',
                    'description' => 'Test ad description',
                    'price' => 10000.0,
                ],
            ]);

        $this->assertDatabaseHas('ads', [
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'title' => 'Test Ad Title',
            'price' => 10000,
        ]);
    }

    public function test_cannot_create_ad_without_required_fields(): void
    {
        $adData = [
            'category_id' => $this->category->id,
            'title' => 'Test Ad Title',
            'description' => 'Test ad description',
            'price' => 10000,
        ];

        $response = $this->authenticatedRequest($this->token)
            ->postJson('/api/v1/ads', $adData);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors',
            ])
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed',
            ])
            ->assertJsonValidationErrors(['brand', 'year']);
    }

    public function test_cannot_create_ad_with_invalid_select_field_value(): void
    {
        $adData = [
            'category_id' => $this->category->id,
            'title' => 'Test Ad Title',
            'description' => 'Test ad description',
            'price' => 10000,
            'brand' => 'invalid-brand',
            'year' => 2020,
        ];

        $response = $this->authenticatedRequest($this->token)
            ->postJson('/api/v1/ads', $adData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['brand']);
    }

    public function test_can_list_own_ads(): void
    {
        Ad::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
        ]);

        $otherUser = User::factory()->create();
        Ad::factory()->count(2)->create([
            'user_id' => $otherUser->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->authenticatedRequest($this->token)
            ->getJson('/api/v1/my-ads');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'title',
                            'description',
                            'price',
                            'category',
                            'user',
                            'fields',
                        ],
                    ],
                    'pagination',
                ],
                'message',
            ])
            ->assertJson([
                'success' => true,
            ]);

        $responseData = $response->json('data.data');
        $this->assertCount(3, $responseData);
        $this->assertTrue(collect($responseData)->every(fn($ad) => $ad['user']['id'] === $this->user->id));
    }

    public function test_my_ads_endpoint_is_paginated(): void
    {
        Ad::factory()->count(20)->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->authenticatedRequest($this->token)
            ->getJson('/api/v1/my-ads?per_page=5');

        $response->assertStatus(200)
            ->assertJsonPath('data.pagination.per_page', 5)
            ->assertJsonPath('data.pagination.total', 20)
            ->assertJsonPath('data.pagination.last_page', 4);
        
        $this->assertCount(5, $response->json('data.data'));
    }

    public function test_can_view_specific_ad(): void
    {
        $ad = Ad::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'title' => 'Specific Ad Title',
        ]);

        $response = $this->authenticatedRequest($this->token)
            ->getJson("/api/v1/ads/{$ad->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'title',
                    'description',
                    'price',
                    'category',
                    'user',
                    'fields',
                    'created_at',
                    'updated_at',
                ],
                'message',
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $ad->id,
                    'title' => 'Specific Ad Title',
                ],
            ]);
    }

    public function test_cannot_view_nonexistent_ad(): void
    {
        $response = $this->authenticatedRequest($this->token)
            ->getJson('/api/v1/ads/99999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Ad not found',
            ]);
    }

    public function test_ad_resource_includes_dynamic_fields(): void
    {
        $ad = Ad::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
        ]);

        $option = $this->requiredSelectField->options->first();

        $ad->fieldValues()->create([
            'category_field_id' => $this->requiredSelectField->id,
            'category_field_option_id' => $option->id,
            'value' => null,
        ]);

        $ad->fieldValues()->create([
            'category_field_id' => $this->requiredIntegerField->id,
            'value' => '2020',
        ]);

        $response = $this->authenticatedRequest($this->token)
            ->getJson("/api/v1/ads/{$ad->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.fields.brand.value', 'toyota')
            ->assertJsonPath('data.fields.year.value', '2020')
            ->assertJsonStructure([
                'data' => [
                    'fields' => [
                        'brand' => ['name', 'type', 'value'],
                        'year' => ['name', 'type', 'value'],
                    ],
                ],
            ]);
    }

    public function test_price_validation(): void
    {
        $adData = [
            'category_id' => $this->category->id,
            'title' => 'Test Ad Title',
            'description' => 'Test ad description',
            'price' => -100,
            'brand' => 'toyota',
            'year' => 2020,
        ];

        $response = $this->authenticatedRequest($this->token)
            ->postJson('/api/v1/ads', $adData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['price']);
    }

    private function authenticatedRequest(string $token)
    {
        return $this->withHeader('Authorization', 'Bearer ' . $token);
    }
}
