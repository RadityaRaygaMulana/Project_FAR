<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ChatMessage;
use App\Models\Conversation;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_can_start_conversation_from_product(): void
    {
        $sellerUser = User::factory()->create(['role' => 'seller']);
        $store = Store::factory()->approved()->create(['user_id' => $sellerUser->id]);
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'name' => 'Basreng Pedas Jeruk',
        ]);

        $buyer = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($buyer)->post(route('chat.start'), [
            'product_id' => $product->id,
            'message' => 'Apakah produk ini masih ready kak?',
        ]);

        $conversation = Conversation::where('user_id', $buyer->id)
            ->where('store_id', $store->id)
            ->first();

        $this->assertNotNull($conversation);
        $response->assertRedirect(route('chat.show', $conversation->id));

        $this->assertDatabaseHas('chat_messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $buyer->id,
            'product_id' => $product->id,
            'message' => 'Apakah produk ini masih ready kak?',
        ]);
    }

    public function test_buyer_can_send_message_in_existing_conversation(): void
    {
        $sellerUser = User::factory()->create(['role' => 'seller']);
        $store = Store::factory()->approved()->create(['user_id' => $sellerUser->id]);
        $buyer = User::factory()->create(['role' => 'user']);

        $conversation = Conversation::create([
            'user_id' => $buyer->id,
            'store_id' => $store->id,
            'last_message_at' => now(),
        ]);

        $response = $this->actingAs($buyer)->postJson(route('chat.send', $conversation->id), [
            'message' => 'Bisa kirim instan hari ini?',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message.message', 'Bisa kirim instan hari ini?');

        $this->assertDatabaseHas('chat_messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $buyer->id,
            'message' => 'Bisa kirim instan hari ini?',
        ]);
    }

    public function test_seller_can_view_conversation_and_reply_in_seller_center(): void
    {
        $sellerUser = User::factory()->create(['role' => 'seller']);
        $store = Store::factory()->approved()->create(['user_id' => $sellerUser->id]);
        $buyer = User::factory()->create(['role' => 'user']);

        $conversation = Conversation::create([
            'user_id' => $buyer->id,
            'store_id' => $store->id,
            'last_message_at' => now(),
        ]);

        ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $buyer->id,
            'message' => 'Halo kak!',
            'is_read' => false,
        ]);

        // Seller views conversation in seller center
        $viewResponse = $this->actingAs($sellerUser)->get(route('seller.chat.show', $conversation->id));
        $viewResponse->assertOk()
            ->assertSee('Halo kak!')
            ->assertSee($buyer->name);

        // Message should now be marked as read
        $this->assertDatabaseHas('chat_messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $buyer->id,
            'is_read' => true,
        ]);

        // Seller sends a reply
        $replyResponse = $this->actingAs($sellerUser)->postJson(route('seller.chat.send', $conversation->id), [
            'message' => 'Halo juga kak, ready silakan diorder ya!',
        ]);

        $replyResponse->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message.message', 'Halo juga kak, ready silakan diorder ya!');

        $this->assertDatabaseHas('chat_messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $sellerUser->id,
            'message' => 'Halo juga kak, ready silakan diorder ya!',
        ]);
    }

    public function test_user_cannot_access_other_users_conversation(): void
    {
        $sellerUser = User::factory()->create(['role' => 'seller']);
        $store = Store::factory()->approved()->create(['user_id' => $sellerUser->id]);
        $buyer = User::factory()->create(['role' => 'user']);
        $otherUser = User::factory()->create(['role' => 'user']);

        $conversation = Conversation::create([
            'user_id' => $buyer->id,
            'store_id' => $store->id,
            'last_message_at' => now(),
        ]);

        $response = $this->actingAs($otherUser)->get(route('chat.show', $conversation->id));
        $response->assertForbidden();

        $postResponse = $this->actingAs($otherUser)->postJson(route('chat.send', $conversation->id), [
            'message' => 'Pesan terlarang',
        ]);
        $postResponse->assertForbidden();
    }

    public function test_unread_count_endpoint_returns_correct_number(): void
    {
        $sellerUser = User::factory()->create(['role' => 'seller']);
        $store = Store::factory()->approved()->create(['user_id' => $sellerUser->id]);
        $buyer = User::factory()->create(['role' => 'user']);

        $conversation = Conversation::create([
            'user_id' => $buyer->id,
            'store_id' => $store->id,
            'last_message_at' => now(),
        ]);

        // 2 messages from seller to buyer
        ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $sellerUser->id,
            'message' => 'Pesan 1',
            'is_read' => false,
        ]);
        ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $sellerUser->id,
            'message' => 'Pesan 2',
            'is_read' => false,
        ]);

        $response = $this->actingAs($buyer)->getJson(route('chat.unread_count'));
        $response->assertOk()
            ->assertJson(['unread_count' => 2]);
    }
}
