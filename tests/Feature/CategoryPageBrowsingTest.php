<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\StatisticService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class CategoryPageBrowsingTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_parent_category_page_includes_descendant_products_and_top_categories(): void
    {
        $statistics = Mockery::mock(StatisticService::class);
        $statistics->shouldReceive('logCategoryView')->once();
        $this->app->instance(StatisticService::class, $statistics);

        $brand = Brand::factory()->create();
        $parent = Category::factory()->create(['slug' => 'kids-clothes']);
        $child = Category::factory()->create(['slug' => 'boys-shirts', 'parent_id' => $parent->id]);
        $other = Category::factory()->create(['slug' => 'toys']);

        $directProduct = Product::factory()->create([
            'name' => 'Direct parent product',
            'category_id' => $parent->id,
            'brand_id' => $brand->id,
        ]);

        $childProduct = Product::factory()->create([
            'name' => 'Child category product',
            'category_id' => $child->id,
            'brand_id' => $brand->id,
        ]);

        Product::factory()->create([
            'name' => 'Outside category product',
            'category_id' => $other->id,
            'brand_id' => $brand->id,
        ]);

        $response = $this->get(route('shop.category', $parent->slug));

        $response->assertOk();
        $response->assertViewHas('category', fn ($category) => $category->is($parent));
        $response->assertViewHas('relatedCategories', fn ($categories) => $categories->contains('id', $child->id));
        $response->assertSee($directProduct->name);
        $response->assertSee($childProduct->name);
        $response->assertDontSee('Outside category product');
    }

    public function test_leaf_child_category_top_categories_include_parent_context(): void
    {
        $statistics = Mockery::mock(StatisticService::class);
        $statistics->shouldReceive('logCategoryView')->once();
        $this->app->instance(StatisticService::class, $statistics);

        $brand = Brand::factory()->create();
        $parent = Category::factory()->create([
            'name' => 'Kids Clothes',
            'slug' => 'kids-clothes',
        ]);
        $leaf = Category::factory()->create([
            'name' => 'Baby Shirts',
            'slug' => 'baby-shirts',
            'parent_id' => $parent->id,
        ]);
        $sibling = Category::factory()->create([
            'name' => 'Baby Pants',
            'slug' => 'baby-pants',
            'parent_id' => $parent->id,
        ]);

        Product::factory()->create([
            'name' => 'Leaf category product',
            'category_id' => $leaf->id,
            'brand_id' => $brand->id,
        ]);

        $response = $this->get(route('shop.category', $leaf->slug));

        $response->assertOk();
        $response->assertViewHas('category', fn ($category) => $category->is($leaf));
        $response->assertViewHas('relatedCategories', function ($categories) use ($parent, $leaf, $sibling) {
            return $categories->first()->is($leaf)
                && $categories->get(1)?->is($parent)
                && $categories->contains('id', $leaf->id)
                && $categories->contains('id', $sibling->id);
        });
        $response->assertSee('Kids Clothes');
        $response->assertSee('Leaf category product');
    }

    public function test_child_category_with_children_top_categories_show_family_tree_context(): void
    {
        $statistics = Mockery::mock(StatisticService::class);
        $statistics->shouldReceive('logCategoryView')->once();
        $this->app->instance(StatisticService::class, $statistics);

        $brand = Brand::factory()->create();
        $grandparent = Category::factory()->create([
            'name' => 'All Kids',
            'slug' => 'all-kids',
        ]);
        $parent = Category::factory()->create([
            'name' => 'Kids Clothes',
            'slug' => 'kids-clothes',
            'parent_id' => $grandparent->id,
        ]);
        $current = Category::factory()->create([
            'name' => 'Baby Girls',
            'slug' => 'baby-girls',
            'parent_id' => $parent->id,
        ]);
        $child = Category::factory()->create([
            'name' => 'Baby Dresses',
            'slug' => 'baby-dresses',
            'parent_id' => $current->id,
        ]);
        $grandchild = Category::factory()->create([
            'name' => 'Cotton Dresses',
            'slug' => 'cotton-dresses',
            'parent_id' => $child->id,
        ]);

        Product::factory()->create([
            'name' => 'Nested child product',
            'category_id' => $grandchild->id,
            'brand_id' => $brand->id,
        ]);

        $response = $this->get(route('shop.category', $current->slug));

        $response->assertOk();
        $response->assertViewHas('relatedCategories', function ($categories) use ($current, $parent, $grandparent, $child, $grandchild) {
            return $categories->first()->is($current)
                && $categories->get(1)?->is($parent)
                && $categories->get(2)?->is($grandparent)
                && $categories->contains('id', $child->id)
                && $categories->contains('id', $grandchild->id);
        });
        $response->assertSee('Nested child product');
    }

    public function test_category_products_api_returns_structured_category_payload_with_pivot_products(): void
    {
        $brand = Brand::factory()->create();
        $primary = Category::factory()->create(['slug' => 'baby-care']);
        $extra = Category::factory()->create(['slug' => 'newborn-gifts']);

        $pivotProduct = Product::factory()->create([
            'name' => 'Shared category product',
            'category_id' => $extra->id,
            'brand_id' => $brand->id,
        ]);
        $pivotProduct->categories()->sync([$extra->id, $primary->id]);

        $response = $this->getJson("/api/v1/categories/{$primary->slug}/products");

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'data' => [
                    'selected_category',
                    'top_categories',
                    'products' => [
                        'data',
                        'meta',
                        'links',
                    ],
                ],
                'meta',
                'links',
            ])
            ->assertJsonPath('data.selected_category.slug', $primary->slug)
            ->assertJsonFragment(['name' => $pivotProduct->name]);
    }
}
