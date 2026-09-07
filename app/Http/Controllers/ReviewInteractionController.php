<?php

namespace App\Http\Controllers;

use App\Models\ProductReview;
use App\Models\ProductReviewComment;
use App\Models\ProductReviewLike;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewInteractionController extends Controller
{
    /**
     * Toggle like/unlike on a product review.
     */
    public function toggleLike(ProductReview $review): JsonResponse|RedirectResponse
    {
        $userId = Auth::id();

        $existingLike = ProductReviewLike::where('product_review_id', $review->id)
            ->where('user_id', $userId)
            ->first();

        if ($existingLike) {
            $existingLike->delete();
            $liked = false;
            $message = 'Batal menyukai ulasan.';
        } else {
            ProductReviewLike::create([
                'product_review_id' => $review->id,
                'user_id' => $userId,
            ]);
            $liked = true;
            $message = 'Ulasan berhasil disukai!';
        }

        $likesCount = $review->likes()->count();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'liked' => $liked,
                'likes_count' => $likesCount,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Store a comment/reply on a product review.
     */
    public function storeComment(Request $request, ProductReview $review): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'comment' => ['required', 'string', 'min:2', 'max:1000'],
        ], [
            'comment.required' => 'Komentar tidak boleh kosong.',
            'comment.min' => 'Komentar minimal 2 karakter.',
            'comment.max' => 'Komentar maksimal 1000 karakter.',
        ]);

        $comment = ProductReviewComment::create([
            'product_review_id' => $review->id,
            'user_id' => Auth::id(),
            'comment' => trim($validated['comment']),
        ]);

        $comment->load('user');

        $commentsCount = $review->comments()->count();

        // Check if commenter is the seller/owner of this product's store
        $product = $review->product;
        $isSeller = false;
        if ($product && $product->store && $product->store->user_id === Auth::id()) {
            $isSeller = true;
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Komentar ulasan berhasil dikirim!',
                'comments_count' => $commentsCount,
                'comment' => [
                    'id' => $comment->id,
                    'user_id' => $comment->user_id,
                    'user_name' => $comment->user->name ?? 'Pengguna',
                    'user_initial' => strtoupper(substr($comment->user->name ?? 'U', 0, 2)),
                    'comment' => $comment->comment,
                    'is_seller' => $isSeller,
                    'created_at_diff' => $comment->created_at->diffForHumans(),
                ],
            ]);
        }

        return back()->with('success', 'Komentar ulasan berhasil dikirim!');
    }
}
