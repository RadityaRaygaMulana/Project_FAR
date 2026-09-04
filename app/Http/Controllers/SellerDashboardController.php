<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SellerDashboardController extends Controller
{
    /**
     * Display the seller dashboard overview.
     */
    public function dashboard(Request $request): View
    {
        $store = $request->user()->store;

        $totalProducts = Product::where('store_id', $store->id)->count();
        $activeProducts = Product::where('store_id', $store->id)->where('is_available', true)->count();
        $outOfStock = Product::where('store_id', $store->id)->where('stock', '<=', 0)->count();

        $recentProducts = Product::with('category')
            ->where('store_id', $store->id)
            ->latest()
            ->take(5)
            ->get();

        $storeOrderItems = OrderItem::with(['order', 'product'])
            ->whereHas('product', fn ($q) => $q->where('store_id', $store->id))
            ->latest()
            ->take(5)
            ->get();

        $totalOrdersCount = OrderItem::whereHas('product', fn ($q) => $q->where('store_id', $store->id))->count();
        $totalRevenue = OrderItem::whereHas('product', fn ($q) => $q->where('store_id', $store->id))->sum('subtotal');

        // --- Chart data: daily revenue & orders for last 30 days ---
        $storeProductIds = Product::where('store_id', $store->id)->pluck('id');

        $dailySalesRaw = OrderItem::whereIn('product_id', $storeProductIds)
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->selectRaw('DATE(created_at) as date, SUM(subtotal) as revenue, COUNT(*) as orders_count')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Build a complete 30-day array (fill gaps with 0)
        $chartDates = [];
        $chartRevenue = [];
        $chartOrders = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $label = now()->subDays($i)->format('d M');
            $row = $dailySalesRaw->get($date);
            $chartDates[] = $label;
            $chartRevenue[] = $row ? (int) $row->revenue : 0;
            $chartOrders[] = $row ? (int) $row->orders_count : 0;
        }

        // --- Chart data: top 5 selling products ---
        $topProducts = OrderItem::whereIn('product_id', $storeProductIds)
            ->selectRaw('product_name, SUM(quantity) as total_sold, SUM(subtotal) as total_revenue')
            ->groupBy('product_name')
            ->orderByDesc('total_sold')
            ->take(5)
            ->get();

        // --- This month stats ---
        $thisMonthRevenue = OrderItem::whereIn('product_id', $storeProductIds)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('subtotal');

        $thisMonthOrders = OrderItem::whereIn('product_id', $storeProductIds)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        $lastMonthRevenue = OrderItem::whereIn('product_id', $storeProductIds)
            ->whereYear('created_at', now()->subMonth()->year)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->sum('subtotal');

        $revenueGrowth = $lastMonthRevenue > 0
            ? round((($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : ($thisMonthRevenue > 0 ? 100.0 : 0.0);

        return view('seller.dashboard', compact(
            'store',
            'totalProducts',
            'activeProducts',
            'outOfStock',
            'recentProducts',
            'storeOrderItems',
            'totalOrdersCount',
            'totalRevenue',
            'chartDates',
            'chartRevenue',
            'chartOrders',
            'topProducts',
            'thisMonthRevenue',
            'thisMonthOrders',
            'lastMonthRevenue',
            'revenueGrowth',
        ));
    }

    /**
     * Display seller's products catalog.
     */
    public function products(Request $request): View
    {
        $store = $request->user()->store;

        $search = $request->input('q');
        $status = $request->input('status');

        $products = Product::with('category')
            ->where('store_id', $store->id)
            ->when($search, fn ($q) => $q->where('name', 'ILIKE', "%{$search}%"))
            ->when($status === 'active', fn ($q) => $q->where('is_available', true))
            ->when($status === 'inactive', fn ($q) => $q->where('is_available', false))
            ->when($status === 'out_of_stock', fn ($q) => $q->where('stock', '<=', 0))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('seller.products.index', compact('store', 'products', 'search', 'status'));
    }

    /**
     * Show form to create a new product.
     */
    public function createProduct(): View
    {
        $categories = Category::all();

        return view('seller.products.create', compact('categories'));
    }

    /**
     * Store a newly created product.
     */
    public function storeProduct(Request $request): RedirectResponse
    {
        $store = $request->user()->store;

        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'min:3', 'max:150'],
            'description' => ['required', 'string', 'min:10'],
            'price' => ['required', 'numeric', 'min:1000'],
            'discount_price' => ['nullable', 'numeric', 'lt:price'],
            'stock' => ['required', 'integer', 'min:0'],
            'weight_grams' => ['required', 'integer', 'min:1'],
            'is_available' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['nullable', 'image', 'max:2048'],
            'variants' => ['nullable', 'array'],
            'variants.*.name' => ['required_with:variants', 'string', 'max:100'],
            'variants.*.price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.stock' => ['nullable', 'integer', 'min:0'],
            'variants.*.image' => ['nullable', 'image', 'max:2048'],
        ]);

        $baseSlug = Str::slug($validated['name']);
        $slug = $baseSlug;
        $counter = 1;
        while (Product::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        $imagePath = null;
        $galleryImages = [];

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
            $galleryImages[] = $imagePath;
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store('products', 'public');
                    if (! $imagePath) {
                        $imagePath = $path;
                    }
                    if (! in_array($path, $galleryImages, true)) {
                        $galleryImages[] = $path;
                    }
                }
            }
        }

        $product = Product::create([
            'category_id' => $validated['category_id'],
            'store_id' => $store->id,
            'name' => $validated['name'],
            'brand' => $store->name,
            'badge' => $store->badge ?? 'Official',
            'slug' => $slug,
            'description' => $validated['description'],
            'price' => (int) $validated['price'],
            'discount_price' => $validated['discount_price'] ? (int) $validated['discount_price'] : null,
            'weight_grams' => (int) $validated['weight_grams'],
            'stock' => (int) $validated['stock'],
            'rating' => 5.0,
            'sold_count' => 0,
            'image_path' => $imagePath,
            'gallery_images' => ! empty($galleryImages) ? array_values(array_unique($galleryImages)) : null,
            'is_available' => $request->has('is_available'),
            'is_featured' => false,
        ]);

        if (! empty($validated['variants'])) {
            foreach ($validated['variants'] as $index => $variantData) {
                $variantImagePath = null;
                if ($request->hasFile("variants.{$index}.image")) {
                    $variantImagePath = $request->file("variants.{$index}.image")->store('variants', 'public');
                }

                ProductVariant::create([
                    'product_id' => $product->id,
                    'name' => $variantData['name'],
                    'price' => isset($variantData['price']) && $variantData['price'] !== '' ? (int) $variantData['price'] : null,
                    'stock' => isset($variantData['stock']) && $variantData['stock'] !== '' ? (int) $variantData['stock'] : 0,
                    'image_path' => $variantImagePath,
                ]);
            }
        }

        return redirect()->route('seller.products.index')
            ->with('success', 'Produk "'.$validated['name'].'" berhasil ditambahkan ke etalase toko!');
    }

    /**
     * Show form to edit a product.
     */
    public function editProduct(Product $product): View
    {
        $store = auth()->user()->store;
        abort_unless($product->store_id === $store->id || auth()->user()->isAdmin(), 403, 'Akses ditolak.');

        $categories = Category::all();

        return view('seller.products.edit', compact('product', 'categories'));
    }

    /**
     * Update an existing product.
     */
    public function updateProduct(Request $request, Product $product): RedirectResponse
    {
        $store = auth()->user()->store;
        abort_unless($product->store_id === $store->id || auth()->user()->isAdmin(), 403, 'Akses ditolak.');

        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'min:3', 'max:150'],
            'description' => ['required', 'string', 'min:10'],
            'price' => ['required', 'numeric', 'min:1000'],
            'discount_price' => ['nullable', 'numeric', 'lt:price'],
            'stock' => ['required', 'integer', 'min:0'],
            'weight_grams' => ['required', 'integer', 'min:1'],
            'is_available' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['nullable', 'image', 'max:2048'],
            'existing_gallery_images' => ['nullable', 'array'],
            'variants' => ['nullable', 'array'],
            'variants.*.id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'variants.*.name' => ['required_with:variants', 'string', 'max:100'],
            'variants.*.price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.stock' => ['nullable', 'integer', 'min:0'],
            'variants.*.image' => ['nullable', 'image', 'max:2048'],
        ]);

        // Retained gallery images from existing list
        $retainedGallery = $request->input('existing_gallery_images', []);
        if (! is_array($retainedGallery)) {
            $retainedGallery = [];
        }

        // Delete any old gallery images removed by user
        $currentGallery = is_array($product->gallery_images) ? $product->gallery_images : [];
        foreach ($currentGallery as $oldPath) {
            if ($oldPath && ! in_array($oldPath, $retainedGallery, true) && $oldPath !== $product->image_path) {
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
        }

        $galleryImages = array_values(array_filter($retainedGallery));

        // Handle main product image replacement
        $imagePath = $product->image_path;
        if ($request->hasFile('image')) {
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $request->file('image')->store('products', 'public');
            if (! in_array($imagePath, $galleryImages, true)) {
                array_unshift($galleryImages, $imagePath);
            }
        }

        // Handle additional multiple gallery photos
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store('products', 'public');
                    if (! $imagePath) {
                        $imagePath = $path;
                    }
                    $galleryImages[] = $path;
                }
            }
        }

        if (! $imagePath && ! empty($galleryImages)) {
            $imagePath = $galleryImages[0];
        }

        $product->update([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'description' => $validated['description'],
            'price' => (int) $validated['price'],
            'discount_price' => $validated['discount_price'] ? (int) $validated['discount_price'] : null,
            'weight_grams' => (int) $validated['weight_grams'],
            'stock' => (int) $validated['stock'],
            'image_path' => $imagePath,
            'gallery_images' => ! empty($galleryImages) ? array_values(array_unique($galleryImages)) : null,
            'is_available' => $request->has('is_available'),
        ]);

        // Sync variants
        $incomingIds = collect($validated['variants'] ?? [])
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->all();

        // Delete variants not in the incoming list
        $product->variants()->whereNotIn('id', $incomingIds)->each(function (ProductVariant $variant): void {
            if ($variant->image_path && Storage::disk('public')->exists($variant->image_path)) {
                Storage::disk('public')->delete($variant->image_path);
            }
            $variant->delete();
        });

        foreach ($validated['variants'] ?? [] as $index => $variantData) {
            $variantImagePath = null;
            $existingVariant = ! empty($variantData['id']) ? ProductVariant::find((int) $variantData['id']) : null;

            if ($request->hasFile("variants.{$index}.image")) {
                if ($existingVariant?->image_path && Storage::disk('public')->exists($existingVariant->image_path)) {
                    Storage::disk('public')->delete($existingVariant->image_path);
                }
                $variantImagePath = $request->file("variants.{$index}.image")->store('variants', 'public');
            } else {
                $variantImagePath = $existingVariant?->image_path;
            }

            $variantAttributes = [
                'product_id' => $product->id,
                'name' => $variantData['name'],
                'price' => isset($variantData['price']) && $variantData['price'] !== '' ? (int) $variantData['price'] : null,
                'stock' => isset($variantData['stock']) && $variantData['stock'] !== '' ? (int) $variantData['stock'] : 0,
                'image_path' => $variantImagePath,
            ];

            if ($existingVariant) {
                $existingVariant->update($variantAttributes);
            } else {
                ProductVariant::create($variantAttributes);
            }
        }

        return redirect()->route('seller.products.index')
            ->with('success', 'Informasi produk berhasil diperbarui!');
    }

    /**
     * Delete a product.
     */
    public function destroyProduct(Product $product): RedirectResponse
    {
        $store = auth()->user()->store;
        abort_unless($product->store_id === $store->id || auth()->user()->isAdmin(), 403, 'Akses ditolak.');

        $name = $product->name;
        $product->delete();

        return redirect()->route('seller.products.index')
            ->with('success', 'Produk "'.$name.'" berhasil dihapus dari toko.');
    }

    /**
     * Display seller orders list with status filtering and search.
     */
    public function orders(Request $request): View
    {
        Order::autoCancelExpiredRequests();
        Order::autoCompleteDeliveredOrders();

        $store = $request->user()->store;
        $status = $request->input('status', 'all');
        $search = trim((string) $request->input('q', ''));

        $baseOrdersQuery = Order::whereHas('items.product', fn ($q) => $q->where('store_id', $store->id));

        // Tab counters for store orders
        $countAll = (clone $baseOrdersQuery)->count();
        $countPending = (clone $baseOrdersQuery)->whereIn('status', ['pending', 'confirmed'])->count();
        $countProcessing = (clone $baseOrdersQuery)->where('status', 'processing')->count();
        $countShipped = (clone $baseOrdersQuery)->where('status', 'shipped')->count();
        $countDelivered = (clone $baseOrdersQuery)->where('status', 'delivered')->count();
        $countCompleted = (clone $baseOrdersQuery)->where('status', 'completed')->count();
        $countCancelled = (clone $baseOrdersQuery)->whereIn('status', ['cancelled', 'canceled'])->count();
        $countCancellationRequests = (clone $baseOrdersQuery)->where('cancellation_status', 'requested')->count();
        $countReturnRequests = (clone $baseOrdersQuery)->where('return_status', 'requested')->count();

        $orders = (clone $baseOrdersQuery)
            ->with([
                'items' => fn ($q) => $q->whereHas('product', fn ($p) => $p->where('store_id', $store->id))->with('product.variants'),
                'user',
            ])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('order_code', 'ILIKE', "%{$search}%")
                        ->orWhere('customer_name', 'ILIKE', "%{$search}%")
                        ->orWhere('customer_phone', 'ILIKE', "%{$search}%");
                });
            })
            ->when($status !== 'all', function ($q) use ($status) {
                if ($status === 'pending') {
                    $q->whereIn('status', ['pending', 'confirmed']);
                } elseif ($status === 'processing') {
                    $q->where('status', 'processing');
                } elseif ($status === 'shipped') {
                    $q->where('status', 'shipped');
                } elseif ($status === 'delivered') {
                    $q->where('status', 'delivered');
                } elseif ($status === 'completed') {
                    $q->where('status', 'completed');
                } elseif ($status === 'cancelled') {
                    $q->whereIn('status', ['cancelled', 'canceled']);
                } elseif ($status === 'cancellation_requests') {
                    $q->where('cancellation_status', 'requested');
                } elseif ($status === 'return_requests') {
                    $q->where('return_status', 'requested');
                } elseif ($status === 'returned') {
                    $q->where(fn ($sub) => $sub->where('status', 'returned')->orWhere('return_status', 'approved'));
                }
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('seller.orders', compact(
            'store',
            'orders',
            'status',
            'search',
            'countAll',
            'countPending',
            'countProcessing',
            'countShipped',
            'countDelivered',
            'countCompleted',
            'countCancelled',
            'countCancellationRequests',
            'countReturnRequests'
        ));
    }

    /**
     * Update order status by seller.
     */
    public function updateOrderStatus(Request $request, Order $order): RedirectResponse
    {
        $store = $request->user()->store;

        // Verify that this order contains products from seller's store
        $hasStoreProduct = $order->items()->whereHas('product', fn ($q) => $q->where('store_id', $store->id))->exists();
        if (! $hasStoreProduct) {
            abort(403, 'Anda tidak memiliki hak untuk memproses pesanan ini.');
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:processing,shipped,delivered,completed,cancelled'],
            'shipping_courier' => ['nullable', 'string', 'max:50'],
            'tracking_number' => ['nullable', 'string', 'max:100'],
            'cancellation_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $newStatus = $validated['status'];

        // Prevent shipping while cancellation request is pending
        if ($order->cancellation_status === 'requested' && $newStatus === 'shipped') {
            return back()->with('error', 'Pesanan ini sedang dalam pengajuan pembatalan oleh pembeli. Harap setujui atau tolak pembatalan terlebih dahulu sebelum mengirim barang.');
        }

        // Prevent modifying order status while return request is pending
        if ($order->return_status === 'requested' && in_array($newStatus, ['delivered', 'completed'])) {
            return back()->with('error', 'Pesanan ini sedang dalam pengajuan pengembalian barang oleh pembeli. Harap tanggapi pengembalian terlebih dahulu.');
        }

        // Prevent cancelling an order that is already shipped, delivered, or completed
        if (in_array($order->status, ['shipped', 'delivered', 'completed']) && $newStatus === 'cancelled') {
            return back()->with('error', 'Pesanan yang sudah dalam pengiriman, sudah sampai, atau telah selesai tidak dapat dibatalkan lagi.');
        }

        $updateData = ['status' => $newStatus];

        if ($newStatus === 'shipped') {
            if (! empty($validated['shipping_courier'])) {
                $updateData['shipping_courier'] = $validated['shipping_courier'];
            }
            if (! empty($validated['tracking_number'])) {
                $updateData['tracking_number'] = $validated['tracking_number'];
            }
        }

        if ($newStatus === 'delivered') {
            $updateData['delivered_at'] = now();
        }

        if ($newStatus === 'completed') {
            $updateData['payment_status'] = 'paid';
            $updateData['completed_at'] = now();
        }

        if ($newStatus === 'cancelled') {
            // Restore stock if seller cancels directly
            foreach ($order->items as $item) {
                if ($item->product) {
                    $item->product->increment('stock', $item->quantity);
                    $item->product->decrement('sold_count', min($item->quantity, $item->product->sold_count));

                    if ($item->product->relationLoaded('variants') || $item->product->variants()->exists()) {
                        foreach ($item->product->variants as $variant) {
                            if (str_contains($item->product_name, $variant->name)) {
                                $variant->increment('stock', $item->quantity);
                                break;
                            }
                        }
                    }
                }
            }

            if (! empty($validated['cancellation_reason'])) {
                $existingNotes = $order->customer_notes ? $order->customer_notes."\n" : '';
                $updateData['customer_notes'] = $existingNotes.'[Catatan Pembatalan Toko: '.$validated['cancellation_reason'].']';
            }
        }

        $order->update($updateData);

        $statusLabels = [
            'processing' => 'Sedang Dikemas / Diproses',
            'shipped' => 'Dalam Pengiriman (Dikirim)',
            'delivered' => 'Sampai di Tujuan (Menunggu Konfirmasi Pembeli)',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];

        $label = $statusLabels[$newStatus] ?? $newStatus;

        AuditLogger::order("Status Pesanan Diubah ke {$label}", $order, $request->user());

        return back()->with('success', "Status pesanan #{$order->order_code} berhasil diperbarui menjadi: {$label}!");
    }

    /**
     * Respond to buyer cancellation request by seller (approve or reject).
     */
    public function respondCancellation(Request $request, Order $order): RedirectResponse
    {
        $store = $request->user()->store;

        $hasStoreProduct = $order->items()->whereHas('product', fn ($q) => $q->where('store_id', $store->id))->exists();
        if (! $hasStoreProduct) {
            abort(403, 'Anda tidak memiliki hak untuk merespons pembatalan pesanan ini.');
        }

        if ($order->cancellation_status !== 'requested') {
            return back()->with('error', 'Pesanan ini tidak memiliki pengajuan pembatalan yang aktif.');
        }

        if (in_array($order->status, ['shipped', 'completed'])) {
            return back()->with('error', 'Pesanan sudah dalam pengiriman atau selesai dan tidak dapat dibatalkan.');
        }

        $validated = $request->validate([
            'action' => ['required', 'string', 'in:approve,reject'],
            'response_note' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validated['action'] === 'approve') {
            DB::transaction(function () use ($order, $validated) {
                $order->loadMissing('items.product.variants');

                // Restore stock for products & variants
                foreach ($order->items as $item) {
                    if ($item->product) {
                        $item->product->increment('stock', $item->quantity);
                        $item->product->decrement('sold_count', min($item->quantity, $item->product->sold_count));

                        if ($item->product->relationLoaded('variants') || $item->product->variants()->exists()) {
                            foreach ($item->product->variants as $variant) {
                                if (str_contains($item->product_name, $variant->name)) {
                                    $variant->increment('stock', $item->quantity);
                                    break;
                                }
                            }
                        }
                    }
                }

                $order->update([
                    'status' => 'cancelled',
                    'cancellation_status' => 'approved',
                    'cancellation_responded_at' => now(),
                    'cancellation_response_note' => $validated['response_note'] ?: 'Disetujui oleh penjual.',
                ]);
            });

            AuditLogger::order('Penjual Menyetujui Pembatalan Pesanan', $order, $request->user());

            return back()->with('success', "Pengajuan pembatalan pesanan #{$order->order_code} telah DISETUJUI. Pesanan dibatalkan dan stok produk telah dikembalikan.");
        }

        // Action: reject
        $order->update([
            'cancellation_status' => 'rejected',
            'cancellation_responded_at' => now(),
            'cancellation_response_note' => $validated['response_note'] ?: 'Ditolak oleh penjual (pesanan tetap diproses).',
        ]);

        AuditLogger::order('Penjual Menolak Pembatalan Pesanan', $order, $request->user());

        return back()->with('success', "Pengajuan pembatalan pesanan #{$order->order_code} telah DITOLAK. Anda sekarang dapat melanjutkan proses pengemasan dan pengiriman barang.");
    }

    /**
     * Respond to buyer return / refund request by seller (approve or reject).
     */
    public function respondReturn(Request $request, Order $order): RedirectResponse
    {
        $store = $request->user()->store;

        $hasStoreProduct = $order->items()->whereHas('product', fn ($q) => $q->where('store_id', $store->id))->exists();
        if (! $hasStoreProduct) {
            abort(403, 'Anda tidak memiliki hak untuk merespons pengembalian pesanan ini.');
        }

        if ($order->return_status !== 'requested') {
            return back()->with('error', 'Pesanan ini tidak memiliki pengajuan pengembalian yang sedang aktif.');
        }

        $validated = $request->validate([
            'action' => ['required', 'string', 'in:approve,reject'],
            'response_note' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validated['action'] === 'approve') {
            DB::transaction(function () use ($order, $validated) {
                $order->loadMissing('items.product.variants');

                // Restore stock for products & variants
                foreach ($order->items as $item) {
                    if ($item->product) {
                        $item->product->increment('stock', $item->quantity);
                        $item->product->decrement('sold_count', min($item->quantity, $item->product->sold_count));

                        if ($item->product->relationLoaded('variants') || $item->product->variants()->exists()) {
                            foreach ($item->product->variants as $variant) {
                                if (str_contains($item->product_name, $variant->name)) {
                                    $variant->increment('stock', $item->quantity);
                                    break;
                                }
                            }
                        }
                    }
                }

                $order->update([
                    'status' => 'returned',
                    'return_status' => 'approved',
                    'return_responded_at' => now(),
                    'return_response_note' => $validated['response_note'] ?: 'Pengajuan pengembalian disetujui oleh penjual.',
                ]);
            });

            AuditLogger::order('Penjual Menyetujui Pengembalian Barang', $order, $request->user());

            return back()->with('success', "Pengajuan pengembalian barang pesanan #{$order->order_code} telah DISETUJUI. Stok produk telah dikembalikan ke etalase toko.");
        }

        // Action: reject
        $order->update([
            'return_status' => 'rejected',
            'return_responded_at' => now(),
            'return_response_note' => $validated['response_note'] ?: 'Pengajuan pengembalian ditolak oleh penjual.',
        ]);

        AuditLogger::order('Penjual Menolak Pengembalian Barang', $order, $request->user());

        return back()->with('success', "Pengajuan pengembalian barang pesanan #{$order->order_code} telah DITOLAK.");
    }

    /**
     * Show store profile settings.
     */
    public function settings(): View
    {
        $store = auth()->user()->store;

        return view('seller.settings', compact('store'));
    }

    /**
     * Update store settings.
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $store = auth()->user()->store;

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:30'],
            'description' => ['nullable', 'string', 'max:1000'],
            'address_detail' => ['nullable', 'string', 'max:500'],
            'advantages' => ['nullable', 'string', 'max:2000'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:3072'],
            'remove_logo' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('remove_logo')) {
            if ($store->logo && Storage::disk('public')->exists($store->logo)) {
                Storage::disk('public')->delete($store->logo);
            }
            $validated['logo'] = null;
        } elseif ($request->hasFile('logo')) {
            if ($store->logo && Storage::disk('public')->exists($store->logo)) {
                Storage::disk('public')->delete($store->logo);
            }
            $validated['logo'] = $request->file('logo')->store('stores/logos', 'public');
        }

        unset($validated['remove_logo']);

        $store->update($validated);

        return redirect()->route('seller.settings')
            ->with('success', 'Pengaturan dan foto profil toko berhasil diperbarui!');
    }

    /**
     * Check pending/new orders and return notification payload for seller.
     */
    public function notificationsCheck(Request $request): JsonResponse
    {
        $store = $request->user()?->store;
        if (! $store) {
            return response()->json([
                'pending_orders_count' => 0,
                'latest_order' => null,
            ]);
        }

        Order::autoCancelExpiredRequests();
        Order::autoCompleteDeliveredOrders();

        $baseOrdersQuery = Order::whereHas('items.product', fn ($q) => $q->where('store_id', $store->id));

        // Count orders that need seller's action
        $actionRequiredCount = (clone $baseOrdersQuery)
            ->where(function ($q) {
                $q->whereIn('status', ['pending', 'processing'])
                    ->orWhere('cancellation_status', 'requested')
                    ->orWhere('return_status', 'requested');
            })
            ->count();

        // Get latest incoming order
        $latestOrder = (clone $baseOrdersQuery)
            ->whereIn('status', ['pending', 'processing'])
            ->latest('id')
            ->first();

        return response()->json([
            'pending_orders_count' => $actionRequiredCount,
            'latest_order' => $latestOrder ? [
                'id' => $latestOrder->id,
                'order_code' => $latestOrder->order_code,
                'customer_name' => $latestOrder->customer_name,
                'grand_total' => $latestOrder->formatted_grand_total,
                'status' => $latestOrder->status,
                'status_label' => $latestOrder->status_label,
                'created_at' => $latestOrder->created_at->diffForHumans(),
                'url' => route('seller.orders'),
            ] : null,
        ]);
    }
}
