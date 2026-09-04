<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SellerChatController extends Controller
{
    /**
     * Display seller chat inbox and active customer conversation.
     */
    public function index(Request $request, ?Conversation $conversation = null): View
    {
        $store = $request->user()->store;
        abort_unless($store && $store->isApproved(), 403, 'Akses toko belum disetujui.');

        $search = $request->input('q');

        // All customer conversations for this store
        $conversations = Conversation::with(['user', 'latestMessage'])
            ->where('store_id', $store->id)
            ->when($search, function ($query) use ($search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'ILIKE', "%{$search}%");
                });
            })
            ->orderByDesc('last_message_at')
            ->orderByDesc('updated_at')
            ->get();

        if ($conversation) {
            abort_unless($conversation->store_id === $store->id, 403, 'Akses percakapan ditolak.');
        } elseif ($conversations->isNotEmpty()) {
            $conversation = $conversations->first();
        }

        if ($conversation) {
            // Mark customer messages as read
            $conversation->messages()
                ->where('sender_id', $conversation->user_id)
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);

            $conversation->load(['user', 'messages.product', 'messages.sender']);
        }

        return view('seller.chat.index', compact('store', 'conversations', 'conversation', 'search'));
    }

    /**
     * Send a seller reply in a customer conversation.
     */
    public function sendMessage(Request $request, Conversation $conversation): JsonResponse|RedirectResponse
    {
        $store = $request->user()->store;
        abort_unless($store && $store->isApproved(), 403, 'Akses toko belum disetujui.');
        abort_unless($conversation->store_id === $store->id, 403, 'Akses percakapan ditolak.');

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'product_id' => ['nullable', 'exists:products,id'],
        ]);

        $message = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $request->user()->id,
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

        return redirect()->route('seller.chat.show', $conversation->id);
    }

    /**
     * Fetch messages JSON for live polling on seller side.
     */
    public function fetchMessages(Request $request, Conversation $conversation): JsonResponse
    {
        $store = $request->user()->store;
        abort_unless($store && $store->isApproved(), 403, 'Akses toko belum disetujui.');
        abort_unless($conversation->store_id === $store->id, 403, 'Akses percakapan ditolak.');

        // Mark customer messages as read
        $conversation->messages()
            ->where('sender_id', $conversation->user_id)
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
                'is_mine' => $m->sender_id === $request->user()->id,
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
     * Get unread messages count for seller header badge.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $store = $request->user()->store;
        if (! $store) {
            return response()->json(['unread_count' => 0]);
        }

        $unreadCount = ChatMessage::whereHas('conversation', fn ($q) => $q->where('store_id', $store->id))
            ->where('sender_id', '!=', $request->user()->id)
            ->where('is_read', false)
            ->count();

        return response()->json(['unread_count' => $unreadCount]);
    }
}
