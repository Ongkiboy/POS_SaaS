<?php

namespace Tests\Feature\Catalog;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_categories_sorted_by_sort_order(): void
    {
        Category::factory()->create(['name' => 'B', 'sort_order' => 2]);
        Category::factory()->create(['name' => 'A', 'sort_order' => 1]);

        $this->getJson('/api/categories')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'A');
    }

    public function test_can_create_category(): void
    {
        $this->postJson('/api/categories', ['name' => 'Minuman', 'status' => 'active'])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Minuman');

        $this->assertDatabaseHas('categories', ['name' => 'Minuman']);
    }

    public function test_name_must_be_unique(): void
    {
        Category::factory()->create(['name' => 'Minuman']);

        $this->postJson('/api/categories', ['name' => 'Minuman', 'status' => 'active'])
            ->assertUnprocessable();
    }

    public function test_can_update_status_and_sort_order(): void
    {
        $category = Category::factory()->create(['status' => 'active', 'sort_order' => 1]);

        $this->putJson("/api/categories/{$category->id}", ['status' => 'inactive', 'sort_order' => 5])
            ->assertOk()
            ->assertJsonPath('data.status', 'inactive');
    }

    public function test_can_delete_category(): void
    {
        $category = Category::factory()->create();

        $this->deleteJson("/api/categories/{$category->id}")->assertNoContent();
    }
}