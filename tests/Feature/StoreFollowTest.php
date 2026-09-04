<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreFollowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_follow_and_unfollow_store(): void
    {
        $seller = User::factory()->create(['role' => 'seller']);
        $store = Store::factory()->approved()->create([
            'user_id' => $seller->id,
            'name' => 'Kairo Apparel',
            'slug' => 'kairo-apparel',
        ]);
        $buyer = User::factory()->create(['role' => 'customer']);

        // First request follows the store
        $response = $this->actingAs($buyer)->postJson(route('store.toggle_follow', ['store' => $store->slug]));

        $response->assertStatus(200);
        $response->assertJson([
            'is_following' => true,
            'followers_count' => 1,
        ]);
        $this->assertDatabaseHas('store_followers', [
            'store_id' => $store->id,
            'user_id' => $buyer->id,
        ]);

        // Second request unfollows the store
        $unfollowResponse = $this->actingAs($buyer)->postJson(route('store.toggle_follow', ['store' => $store->slug]));

        $unfollowResponse->assertStatus(200);
        $unfollowResponse->assertJson([
            'is_following' => false,
            'followers_count' => 0,
        ]);
        $this->assertDatabaseMissing('store_followers', [
            'store_id' => $store->id,
            'user_id' => $buyer->id,
        ]);
    }

    public function test_store_owner_cannot_follow_own_store(): void
    {
        $seller = User::factory()->create(['role' => 'seller']);
        $store = Store::factory()->approved()->create([
            'user_id' => $seller->id,
            'name' => 'Kairo Apparel',
            'slug' => 'kairo-apparel',
        ]);

        $response = $this->actingAs($seller)->postJson(route('store.toggle_follow', ['store' => $store->slug]));

        $response->assertStatus(422);
        $response->assertJson([
            'error' => 'Kamu tidak dapat mengikuti toko milikmu sendiri.',
        ]);
    }

    public function test_guest_cannot_follow_store(): void
    {
        $seller = User::factory()->create(['role' => 'seller']);
        $store = Store::factory()->approved()->create([
            'user_id' => $seller->id,
            'name' => 'Kairo Apparel',
            'slug' => 'kairo-apparel',
        ]);

        $response = $this->postJson(route('store.toggle_follow', ['store' => $store->slug]));

        $response->assertStatus(401);
    }
}
