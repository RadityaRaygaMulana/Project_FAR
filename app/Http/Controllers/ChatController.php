<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\Conversation;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatController extends Controller
{
    /**
     * Display the buyer chat inbox and active conversation.
     */
    public function index(Request $request, ?Conversation $conversation = null): View
    {
        $user = $request->user();

        // All conversations of this buyer
        $conversations = Conversation::with(['store', 'latestMessage'])
            ->where('user_id', $user->id)
            ->orderByDesc('last_message_at')
            ->orderByDesc('updated_at')
            ->get();

        // If conversation is specified, verify ownership
        if ($conversation) {
            abort_unless($conversation->user_id === $user->id, 403, 'Akses percakapan ditolak.');
        } elseif ($conversations->isNotEmpty()) {
            $conversation = $conversations->first();
        }

        $activeProduct = null;
        if ($request->filled('product_id')) {
            $activeProduct = Product::find($request->integer('product_id'));
        }

        if ($conversation) {
            // Mark incoming messages as read
            $conversation->messages()
                ->where('sender_id', '!=', $user->id)
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);

            $conversation->load(['store', 'messages.product', 'messages.sender']);
        }

        return view('chat.index', compact('conversations', 'conversation', 'activeProduct'));
    }

    /**
     * Start a new conversation with a store or continue existing one.
     */
    public function start(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'store_id' => ['nullable', 'exists:stores,id'],
            'product_id' => ['nullable', 'exists:products,id'],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        $storeId = $validated['store_id'] ?? null;
        $product = null;

        if (! empty($validated['product_id'])) {
            $product = Product::find($validated['product_id']);
            if ($product) {
                $storeId = $storeId ?: $product->store_id;
            }
        }

        if (! $storeId) {
            return redirect()->back()->with('error', 'Toko tujuan tidak valid.');
        }

        $store = Store::findOrFail($storeId);

        // Prevent chatting with own store
        if ($store->user_id === $user->id) {
            return redirect()->route('seller.chat.index')
                ->with('warning', 'Ini adalah toko Anda sendiri. Silakan buka menu Chat Pelanggan di Seller Center.');
        }

        $conversation = Conversation::firstOrCreate(
            [
                'user_id' => $user->id,
                'store_id' => $storeId,
            ],
            [
                'last_message_at' => now(),
            ]
        );

        // Send initial message if requested (or default inquiry if initiated from product)
        if ($request->filled('message') || $product) {
            $initialText = $validated['message'] ?? 'Halo kak, apakah produk '.$product->name.' ini masih tersedia? 😊';

            ChatMessage::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $user->id,
                'message' => $initialText,
                'product_id' => $product?->id,
                'is_read' => false,
            ]);

            $conversation->update(['last_message_at' => now()]);
        }

        return redirect()->route('chat.show', $conversation->id);
    }

    /**
     * Send a message in an existing conversation.
     */
    public function sendMessage(Request $request, Conversation $conversation): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        abort_unless($conversation->user_id === $user->id, 403, 'Akses percakapan ditolak.');

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'product_id' => ['nullable', 'exists:products,id'],
        ]);

        $message = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'message' => $validated['message'],
            'product_id' => $validated['product_id'] ?? null,
            'is_read' => false,
        ]);

        $conversation->update(['last_message_at' => now()]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => [
                    'id' => $message->id,
                    'sender_id' => $message->sender_id,
                    'is_mine' => true,
                    'message' => $message->message,
                    'time' => $message->created_at->format('H:i'),
                    'is_read' => $message->is_read,
                    'product' => $message->product ? [
                        'id' => $message->product->id,
                        'name' => $message->product->name,
                        'price' => $message->product->formatted_effective_price,
                        'image' => $message->product->product_image_url ?? '',
                        'url' => route('product.detail', $message->product->slug),
                    ] : null,
                ],
            ]);
        }

        return redirect()->route('chat.show', $conversation->id);
    }

    /**
     * Fetch messages JSON for live polling.
     */
    public function fetchMessages(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();
        abort_unless($conversation->user_id === $user->id, 403, 'Akses percakapan ditolak.');

        // Mark incoming messages as read
        $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        $messages = $conversation->messages()
            ->with(['product'])
            ->oldest()
            ->get()
            ->map(fn (ChatMessage $m) => [
                'id' => $m->id,
                'sender_id' => $m->sender_id,
                'is_mine' => $m->sender_id === $user->id,
                'message' => $m->message,
                'time' => $m->created_at->format('H:i'),
                'is_read' => $m->is_read,
                'product' => $m->product ? [
                    'id' => $m->product->id,
                    'name' => $m->product->name,
                    'price' => $m->product->formatted_effective_price,
                    'image' => $m->product->product_image_url ?? '',
                    'url' => route('product.detail', $m->product->slug),
                ] : null,
            ]);

        return response()->json([
            'status' => 'success',
            'messages' => $messages,
        ]);
    }

    /**
     * Get unread messages count for navbar badge.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user();

        $unreadCount = ChatMessage::whereHas('conversation', fn ($q) => $q->where('user_id', $user->id))
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json(['unread_count' => $unreadCount]);
    }
}
