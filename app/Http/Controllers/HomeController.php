<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\ProductVariant;
use App\Models\Store;
use App\Models\StoreFollower;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the snack catalog landing page.
     */
    public function index(Request $request): View
    {
        $categories = Category::active()->withCount(['products' => function ($query) {
            $query->available();
        }])->get();

        $featuredProducts = Product::available()
            ->featured()
            ->with('category')
            ->take(6)
            ->get();

        $flashSaleProducts = Product::available()
            ->whereNotNull('discount_price')
            ->with('category')
            ->orderBy('sold_count', 'desc')
            ->take(4)
            ->get();

        $selectedCategories = (array) $request->input('categories', $request->input('category', []));
        $selectedCategories = array_filter(is_array($selectedCategories) ? $selectedCategories : [$selectedCategories]);

        $selectedBadges = (array) $request->input('badges', $request->input('badge', []));
        $selectedBadges = array_filter(is_array($selectedBadges) ? $selectedBadges : [$selectedBadges]);

        $products = Product::available()
            ->with('category')
            ->when(! empty($selectedCategories), function ($query) use ($selectedCategories) {
                $query->whereHas('category', function ($q) use ($selectedCategories) {
                    $q->whereIn('slug', $selectedCategories);
                });
            })
            ->when(! empty($selectedBadges), function ($query) use ($selectedBadges) {
                $query->whereIn('badge', $selectedBadges);
            })
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->input('q').'%';
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'ILIKE', $term)
                        ->orWhere('brand', 'ILIKE', $term)
                        ->orWhere('description', 'ILIKE', $term);
                });
            })
            ->when($request->filled('min_price'), function ($query) use ($request) {
                $query->where('price', '>=', (int) $request->input('min_price'));
            })
            ->when($request->filled('max_price'), function ($query) use ($request) {
                $query->where('price', '<=', (int) $request->input('max_price'));
            })
            ->when($request->input('sort') === 'cheapest', function ($query) {
                $query->orderBy('price', 'asc');
            })
            ->when($request->input('sort') === 'highest_price', function ($query) {
                $query->orderBy('price', 'desc');
            })
            ->when($request->input('sort') === 'highest_discount', function ($query) {
                $query->whereNotNull('discount_price')->orderByRaw('(price - discount_price) DESC');
            })
            ->when($request->input('sort') === 'newest', function ($query) {
                $query->orderBy('id', 'desc');
            }, function ($query) {
                // Default: Most popular / best rating
                $query->orderBy('sold_count', 'desc')->orderBy('rating', 'desc');
            })
            ->get();

        return view('home', [
            'categories' => $categories,
            'featuredProducts' => $featuredProducts,
            'flashSaleProducts' => $flashSaleProducts,
            'products' => $products,
            'selectedCategory' => $request->input('category', 'all'),
            'selectedCategories' => $selectedCategories,
            'selectedBadge' => $request->input('badge', 'all'),
            'selectedBadges' => $selectedBadges,
            'selectedSort' => $request->input('sort', 'popular'),
            'searchQuery' => $request->input('q', ''),
        ]);
    }

    /**
     * Display the dedicated search discovery & input page.
     */
    public function search(Request $request): View
    {
        $categories = Category::active()->withCount(['products' => function ($query) {
            $query->available();
        }])->get();

        $recommendedProducts = Product::available()
            ->with('category')
            ->orderBy('sold_count', 'desc')
            ->orderBy('rating', 'desc')
            ->take(12)
            ->get();

        $trendingKeywords = [
            'TWS Wireless',
            'Smartwatch Waterproof',
            'Sneakers Kasual',
            'Kopi Arabika Gayo',
            'Sunscreen SPF 50',
            'Serum Niacinamide',
            'Mechanical Keyboard',
            'Air Humidifier',
            'Obeng Set',
            'Buku Journal',
        ];

        return view('search', [
            'categories' => $categories,
            'recommendedProducts' => $recommendedProducts,
            'trendingKeywords' => $trendingKeywords,
            'searchQuery' => (string) $request->input('q', ''),
        ]);
    }

    /**
     * Display filtered search results with active marketplace state.
     */
    public function searchResults(Request $request): View|RedirectResponse
    {
        if ($request->input('badge') === 'Official' && ! $request->filled('q') && ! $request->filled('category')) {
            return redirect()->route('official.brand');
        }

        if ($request->input('sort') === 'popular' && ! $request->filled('q') && ! $request->filled('category') && ! $request->filled('badge')) {
            return redirect()->route('products.trending');
        }

        $categories = Category::active()->withCount(['products' => function ($query) {
            $query->available();
        }])->get();

        $query = Product::available()->with('category');

        if ($request->filled('q')) {
            $term = '%'.$request->input('q').'%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'ILIKE', $term)
                    ->orWhere('brand', 'ILIKE', $term)
                    ->orWhere('description', 'ILIKE', $term);
            });
        }

        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->input('category'));
            });
        }

        if ($request->filled('badge')) {
            $query->where('badge', $request->input('badge'));
        }

        if ($request->filled('promo')) {
            if ($request->input('promo') === 'discount') {
                $query->whereNotNull('discount_price');
            } elseif ($request->input('promo') === 'free_shipping') {
                $query->where('price', '>=', 50000);
            }
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', (int) $request->input('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', (int) $request->input('max_price'));
        }

        if ($request->filled('rating')) {
            $query->where('rating', '>=', (float) $request->input('rating'));
        }

        switch ($request->input('sort')) {
            case 'cheapest':
                $query->orderBy('price', 'asc');
                break;
            case 'priciest':
                $query->orderBy('price', 'desc');
                break;
            case 'highest_discount':
                $query->whereNotNull('discount_price')->orderByRaw('(price - discount_price) DESC');
                break;
            case 'newest':
                $query->orderBy('id', 'desc');
                break;
            default:
                $query->orderBy('sold_count', 'desc')->orderBy('rating', 'desc');
                break;
        }

        $products = $query->get();

        $recommendedProducts = Product::available()
            ->with('category')
            ->orderBy('rating', 'desc')
            ->orderBy('sold_count', 'desc')
            ->take(6)
            ->get();

        $trendingKeywords = [
            'TWS Wireless',
            'Smartwatch Waterproof',
            'Sneakers Kasual',
            'Kopi Arabika Gayo',
            'Sunscreen SPF 50',
            'Serum Niacinamide',
            'Mechanical Keyboard',
            'Air Humidifier',
        ];

        $matchingStore = null;
        if ($request->filled('q')) {
            $matchedProduct = Product::where('brand', 'ILIKE', '%'.$request->input('q').'%')->first();
            if ($matchedProduct) {
                $matchingStore = [
                    'name' => 'Official Store '.$matchedProduct->brand,
                    'brand' => $matchedProduct->brand,
                    'badge' => $matchedProduct->badge ?? 'Mall',
                    'rating' => 4.9,
                    'chat_response' => '99% (Hitungan Menit)',
                    'products_count' => Product::where('brand', $matchedProduct->brand)->count() + 18,
                    'followers' => '128.5rb Pengikut',
                ];
            }
        }

        return view('search-results', [
            'products' => $products,
            'categories' => $categories,
            'recommendedProducts' => $recommendedProducts,
            'trendingKeywords' => $trendingKeywords,
            'matchingStore' => $matchingStore,
            'searchQuery' => $request->input('q', ''),
            'selectedCategory' => $request->input('category', ''),
            'selectedLocation' => $request->input('location', ''),
            'selectedBadge' => $request->input('badge', ''),
            'selectedPayment' => $request->input('payment', ''),
            'selectedShipping' => $request->input('shipping', ''),
            'selectedPromo' => $request->input('promo', ''),
            'selectedRating' => $request->input('rating', ''),
            'selectedSort' => $request->input('sort', 'popular'),
            'minPrice' => $request->input('min_price', ''),
            'maxPrice' => $request->input('max_price', ''),
        ]);
    }

    /**
     * Display the Official Brand / Official Store showcase portal.
     */
    public function officialBrand(Request $request): View
    {
        $categories = Category::active()->withCount(['products' => function ($query) {
            $query->available();
        }])->get();

        $officialStores = Store::where('status', 'approved')
            ->withCount(['products' => fn ($q) => $q->available()])
            ->orderByDesc('rating')
            ->get();

        $selectedCategory = $request->input('category');
        $sort = $request->input('sort', 'popular');

        $query = Product::available()
            ->with(['category', 'store'])
            ->where(function ($q) {
                $q->where('badge', 'Official')
                    ->orWhere('badge', 'Mall')
                    ->orWhereHas('store', fn ($sq) => $sq->where('status', 'approved'));
            });

        if ($selectedCategory) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $selectedCategory));
        }

        switch ($sort) {
            case 'cheapest':
                $query->orderBy('price', 'asc');
                break;
            case 'priciest':
                $query->orderBy('price', 'desc');
                break;
            case 'newest':
                $query->latest();
                break;
            default:
                $query->orderBy('sold_count', 'desc')->orderBy('rating', 'desc');
                break;
        }

        $products = $query->paginate(16)->withQueryString();

        return view('official-brand', compact('categories', 'officialStores', 'products', 'selectedCategory', 'sort'));
    }

    /**
     * Display the Trending Products showcase page.
     */
    public function trendingProducts(Request $request): View
    {
        $categories = Category::active()->withCount(['products' => function ($query) {
            $query->available();
        }])->get();

        $selectedCategory = $request->input('category');
        $sort = $request->input('sort', 'popular');

        // Top 3 Hall of Fame / Trending Champions
        $topTrending = Product::available()
            ->with(['category', 'store'])
            ->orderByDesc('sold_count')
            ->orderByDesc('rating')
            ->take(3)
            ->get();

        $query = Product::available()
            ->with(['category', 'store']);

        if ($selectedCategory) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $selectedCategory));
        }

        switch ($sort) {
            case 'rating':
                $query->orderByDesc('rating')->orderByDesc('sold_count');
                break;
            case 'cheapest':
                $query->orderBy('price', 'asc');
                break;
            case 'priciest':
                $query->orderBy('price', 'desc');
                break;
            case 'newest':
                $query->latest();
                break;
            default:
                $query->orderByDesc('sold_count')->orderByDesc('rating');
                break;
        }

        $products = $query->paginate(16)->withQueryString();

        return view('trending', compact('categories', 'topTrending', 'products', 'selectedCategory', 'sort'));
    }

    /**
     * Display the Limited Promo & Flash Sale portal.
     */
    public function limitedPromo(Request $request): View|JsonResponse
    {
        $categories = Category::active()->withCount(['products' => function ($query) {
            $query->available();
        }])->get();

        $selectedCategory = $request->input('category');
        $minDiscount = (int) $request->input('min_discount', 0);
        $maxPrice = (int) $request->input('max_price', 0);
        $stockFilter = $request->input('stock_filter');
        $sort = $request->input('sort', 'highest_discount');

        $query = Product::available()
            ->with(['category', 'store'])
            ->whereNotNull('discount_price')
            ->whereRaw('discount_price < price');

        if ($selectedCategory) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $selectedCategory));
        }

        if ($minDiscount > 0) {
            $query->whereRaw('(price - discount_price) * 100 >= ? * price', [$minDiscount]);
        }

        if ($maxPrice > 0) {
            $query->where('discount_price', '<=', $maxPrice);
        }

        if ($stockFilter === 'limited') {
            $query->where('stock', '<=', 25);
        }

        switch ($sort) {
            case 'cheapest':
                $query->orderBy('discount_price', 'asc');
                break;
            case 'priciest':
                $query->orderBy('discount_price', 'desc');
                break;
            case 'rating':
                $query->orderByDesc('rating')->orderByDesc('sold_count');
                break;
            case 'popular':
                $query->orderByDesc('sold_count')->orderByDesc('rating');
                break;
            case 'newest':
                $query->latest();
                break;
            case 'highest_discount':
            default:
                $query->orderByRaw('(price - discount_price) * 1.0 / price DESC');
                break;
        }

        $products = $query->paginate(16)->withQueryString();

        // Flash Sale Sessions definition (Dynamic based on current time)
        $currentHour = (int) now()->format('H');
        $flashSessions = [
            [
                'id' => 'session_1',
                'time' => '00:00 - 12:00',
                'title' => 'Sesi Pagi',
                'status' => $currentHour < 12 ? 'active' : 'ended',
                'label' => $currentHour < 12 ? '🔥 Sedang Berlangsung' : 'Selesai',
                'ends_at' => now()->startOfDay()->addHours(12)->toIso8601String(),
            ],
            [
                'id' => 'session_2',
                'time' => '12:00 - 18:00',
                'title' => 'Sesi Siang & Sore',
                'status' => ($currentHour >= 12 && $currentHour < 18) ? 'active' : ($currentHour >= 18 ? 'ended' : 'upcoming'),
                'label' => ($currentHour >= 12 && $currentHour < 18) ? '🔥 Sedang Berlangsung' : ($currentHour >= 18 ? 'Selesai' : '⏰ Segera Hadir'),
                'ends_at' => now()->startOfDay()->addHours(18)->toIso8601String(),
            ],
            [
                'id' => 'session_3',
                'time' => '18:00 - 24:00',
                'title' => 'Sesi Malam Kilat',
                'status' => $currentHour >= 18 ? 'active' : 'upcoming',
                'label' => $currentHour >= 18 ? '🔥 Sedang Berlangsung' : '⏰ Segera Hadir',
                'ends_at' => now()->endOfDay()->toIso8601String(),
            ],
        ];

        // Active session target time for countdown
        $activeSession = collect($flashSessions)->firstWhere('status', 'active') ?? $flashSessions[1];

        // Flash sale statistics
        $totalDiscountedCount = Product::available()
            ->whereNotNull('discount_price')
            ->whereRaw('discount_price < price')
            ->count();

        $maxDiscountPercentage = (int) (Product::available()
            ->whereNotNull('discount_price')
            ->whereRaw('discount_price < price')
            ->selectRaw('MAX(ROUND(((price - discount_price) * 100.0) / price)) as max_pct')
            ->value('max_pct') ?? 60);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html' => view('partials.promo-products-grid', compact('products'))->render(),
                'total' => $products->total(),
            ]);
        }

        return view('promo-limited', compact(
            'categories',
            'products',
            'selectedCategory',
            'minDiscount',
            'maxPrice',
            'stockFilter',
            'sort',
            'flashSessions',
            'activeSession',
            'totalDiscountedCount',
            'maxDiscountPercentage'
        ));
    }

    /**
     * Display the Daily Essentials (Groceries & Healthcare) showcase portal.
     * Exclusively displays Food & Beverage and Health/Vitamins categories.
     */
    public function dailyEssentials(Request $request): View|JsonResponse
    {
        $allowedCategories = Category::active()
            ->where(function ($q) {
                $q->where('slug', 'makanan-minuman')
                    ->orWhere('slug', 'LIKE', 'kesehatan%');
            })
            ->withCount(['products' => fn ($q) => $q->available()])
            ->get();

        $allowedCategoryIds = $allowedCategories->pluck('id')->toArray();

        $selectedCategory = $request->input('category');
        $onlyDiscount = $request->boolean('only_discount');
        $maxPrice = (int) $request->input('max_price', 0);
        $selectedBadge = $request->input('badge');
        $sort = $request->input('sort', 'popular');

        $query = Product::available()
            ->with(['category', 'store'])
            ->whereIn('category_id', $allowedCategoryIds);

        if ($selectedCategory) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $selectedCategory));
        }

        if ($onlyDiscount) {
            $query->whereNotNull('discount_price')->whereRaw('discount_price < price');
        }

        if ($maxPrice > 0) {
            $query->whereRaw('COALESCE(discount_price, price) <= ?', [$maxPrice]);
        }

        if ($selectedBadge) {
            $query->where('badge', $selectedBadge);
        }

        switch ($sort) {
            case 'cheapest':
                $query->orderByRaw('COALESCE(discount_price, price) ASC');
                break;
            case 'priciest':
                $query->orderByRaw('COALESCE(discount_price, price) DESC');
                break;
            case 'rating':
                $query->orderByDesc('rating')->orderByDesc('sold_count');
                break;
            case 'highest_discount':
                $query->whereNotNull('discount_price')->orderByRaw('(price - discount_price) * 1.0 / price DESC');
                break;
            case 'newest':
                $query->latest();
                break;
            case 'popular':
            default:
                $query->orderByDesc('sold_count')->orderByDesc('rating');
                break;
        }

        $products = $query->paginate(16)->withQueryString();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html' => view('partials.essentials-products-grid', compact('products'))->render(),
                'total' => $products->total(),
            ]);
        }

        return view('kebutuhan-pokok', compact(
            'allowedCategories',
            'products',
            'selectedCategory',
            'onlyDiscount',
            'maxPrice',
            'selectedBadge',
            'sort'
        ));
    }

    /**
     * Display the comprehensive Top Up & Digital Bills (PPOB) portal.
     */
    public function topUpBills(Request $request): View
    {
        $activeTab = $request->input('tab', 'pulsa');

        return view('topup-bills', compact('activeTab'));
    }

    /**
     * Process digital top-up or bill payment checkout.
     */
    public function processTopUp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'service_type' => ['required', 'string'],
            'customer_number' => ['required', 'string', 'min:4', 'max:50'],
            'provider' => ['required', 'string', 'max:100'],
            'product_name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:1000'],
            'admin_fee' => ['nullable', 'numeric'],
            'payment_method' => ['required', 'string'],
        ]);

        $user = Auth::user();
        $adminFee = (int) ($validated['admin_fee'] ?? 1500);
        $totalPay = (int) $validated['amount'] + $adminFee;
        $trxCode = 'ORD-PPOB-'.strtoupper(bin2hex(random_bytes(3)));

        $token = ($validated['service_type'] === 'pln_token')
            ? implode('-', str_split(str_pad((string) mt_rand(10000000, 99999999).mt_rand(10000000, 99999999), 16, '0', STR_PAD_LEFT), 4))
            : null;

        $notes = $token
            ? "Token PLN: {$token} | No. Meter: {$validated['customer_number']}"
            : "Layanan Digital {$validated['provider']} untuk No. {$validated['customer_number']}";

        // Save real Order and OrderItem record to database so it shows in Pesanan Saya / Orders
        $order = DB::transaction(function () use ($user, $trxCode, $validated, $adminFee, $totalPay, $notes) {
            $createdOrder = Order::create([
                'user_id' => $user?->id,
                'order_code' => $trxCode,
                'customer_name' => $user?->name ?? 'Pelanggan NusantaraMart',
                'customer_phone' => $validated['customer_number'],
                'customer_address' => "Layanan Digital PPOB ({$validated['provider']} - {$validated['service_type']})",
                'customer_notes' => $notes,
                'payment_method' => $validated['payment_method'],
                'total_amount' => (int) $validated['amount'],
                'shipping_cost' => $adminFee,
                'grand_total' => $totalPay,
                'status' => 'completed',
                'payment_status' => 'paid',
            ]);

            OrderItem::create([
                'order_id' => $createdOrder->id,
                'product_id' => null,
                'product_name' => "{$validated['product_name']} ({$validated['customer_number']})",
                'quantity' => 1,
                'unit_price' => (int) $validated['amount'],
                'subtotal' => (int) $validated['amount'],
            ]);

            return $createdOrder;
        });

        if ($user) {
            AuditLogger::digital('topup_payment', "Pembayaran {$validated['service_type']} {$validated['product_name']} untuk {$validated['customer_number']}", $user);
            AuditLogger::order('topup_completed', $order, $user);
        }

        return redirect()->route('topup.bills')
            ->with('topup_success', [
                'trx_code' => $trxCode,
                'service' => $validated['product_name'],
                'provider' => $validated['provider'],
                'target' => $validated['customer_number'],
                'total' => $totalPay,
                'method' => strtoupper(str_replace('_', ' ', $validated['payment_method'])),
                'token' => $token,
                'time' => now()->format('d M Y, H:i').' WIB',
            ]);
    }

    /**
     * Get real-time live search autocomplete suggestions.
     */
    public function searchSuggestions(Request $request): JsonResponse
    {
        $q = trim((string) $request->input('q', ''));
        if (mb_strlen($q) === 0) {
            return response()->json([
                'query' => '',
                'store_query' => null,
                'suggestions' => [],
            ]);
        }

        $term = '%'.$q.'%';
        $lowerQ = mb_strtolower($q);

        // Fetch matching products and categories
        $products = Product::available()
            ->where(function ($query) use ($term) {
                $query->where('name', 'ILIKE', $term)
                    ->orWhere('brand', 'ILIKE', $term)
                    ->orWhere('description', 'ILIKE', $term);
            })
            ->take(20)
            ->get(['name', 'brand']);

        $categories = Category::active()
            ->where('name', 'ILIKE', $term)
            ->take(4)
            ->pluck('name');

        $suggestions = collect();

        foreach ($categories as $cat) {
            $suggestions->push($cat);
        }

        foreach ($products as $p) {
            $suggestions->push($p->name);
            if ($p->brand && stripos($p->brand, $q) !== false) {
                $suggestions->push($p->brand);
            }

            $words = explode(' ', $p->name);
            $wordCount = count($words);
            for ($i = 0; $i < $wordCount; $i++) {
                if (stripos($words[$i], $q) === 0) {
                    $slice2 = implode(' ', array_slice($words, $i, 2));
                    $slice3 = implode(' ', array_slice($words, $i, 3));
                    if (mb_strlen($slice2) > 0) {
                        $suggestions->push($slice2);
                    }
                    if (mb_strlen($slice3) > 0) {
                        $suggestions->push($slice3);
                    }
                }
            }
        }

        $unique = $suggestions
            ->filter(fn ($s) => mb_strlen(trim((string) $s)) > 0)
            ->map(fn ($s) => trim((string) $s))
            ->unique(fn ($s) => mb_strtolower((string) $s))
            ->sortBy(function ($s) use ($lowerQ) {
                $pos = mb_stripos((string) $s, $lowerQ);

                return $pos === 0 ? 0 : ($pos !== false ? 1 : 2);
            })
            ->values()
            ->take(8)
            ->all();

        return response()->json([
            'query' => $q,
            'store_query' => 'Cari Toko "'.$q.'"',
            'suggestions' => $unique,
        ]);
    }

    /**
     * Store a newly created order.
     */
    public function checkout(Request $request): RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Silakan masuk (login) terlebih dahulu untuk menyelesaikan pesanan kamu. 🍿');
        }

        $user = Auth::user();

        if (! $request->filled('customer_name') && $user?->recipient_name) {
            $request->merge(['customer_name' => $user->recipient_name]);
        }
        if (! $request->filled('customer_phone') && $user?->recipient_phone) {
            $request->merge(['customer_phone' => $user->recipient_phone]);
        }
        if (! $request->filled('customer_address') && ($user?->formatted_address ?: $user?->default_address)) {
            $request->merge(['customer_address' => $user->formatted_address ?: $user->default_address]);
        }
        if (! $request->filled('customer_notes') && $user?->map_notes) {
            $request->merge(['customer_notes' => $user->map_notes]);
        }

        if (! $user?->hasCompleteAddress() && ! $request->filled('customer_address')) {
            return redirect()->route('settings', ['view' => 'address', 'return_to' => 'checkout'])
                ->with('error', 'Silakan lengkapi alamat pengiriman kamu terlebih dahulu di menu Pengaturan.');
        }

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:20'],
            'customer_address' => ['required', 'string', 'max:1000'],
            'customer_notes' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['nullable', 'string', 'in:qris,bca_va,mandiri_va,cod'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'items.*.variant_id' => ['nullable'],
            'items.*.variant_name' => ['nullable', 'string', 'max:100'],
        ]);

        $order = DB::transaction(function () use ($validated) {
            $totalAmount = 0;
            $itemsData = [];

            foreach ($validated['items'] as $itemInput) {
                $product = Product::findOrFail($itemInput['product_id']);
                $unitPrice = $product->effective_price;
                $quantity = (int) $itemInput['quantity'];
                $productName = $product->name;

                if (! empty($itemInput['variant_id'])) {
                    $variant = ProductVariant::find((int) $itemInput['variant_id']);
                    if ($variant && $variant->product_id === $product->id) {
                        if ($variant->price) {
                            $unitPrice = (int) $variant->price;
                        }
                        $productName .= ' ('.$variant->name.')';
                        if ($variant->stock > 0) {
                            $variant->decrement('stock', min($quantity, $variant->stock));
                        }
                    }
                } elseif (! empty($itemInput['variant_name'])) {
                    $productName .= ' ('.$itemInput['variant_name'].')';
                }

                $subtotal = $unitPrice * $quantity;
                $totalAmount += $subtotal;

                $itemsData[] = [
                    'product_id' => $product->id,
                    'product_name' => $productName,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ];

                // Reduce stock & increment sold count
                $product->decrement('stock', $quantity);
                $product->increment('sold_count', $quantity);
            }

            $shippingCost = 15000; // Flat standard shipping
            if ($totalAmount >= 100000) {
                $shippingCost = 0; // Free shipping promo >= 100rb!
            }

            // Coupon code validation & calculation
            $discountAmount = 0;
            $couponCode = strtoupper(trim($validated['coupon_code'] ?? ''));

            if ($couponCode === 'SNACKSERU') {
                $discountAmount = 10000;
            } elseif ($couponCode === 'HEMAT20') {
                $discountAmount = (int) min(25000, round($totalAmount * 0.20));
            } elseif ($couponCode === 'GRATISONGKIR') {
                $shippingCost = 0;
            }

            $grandTotal = max(0, $totalAmount + $shippingCost - $discountAmount);

            $paymentMethod = strtolower($validated['payment_method'] ?? 'qris');
            $isPaidImmediately = ($paymentMethod === 'qris');
            $paymentStatus = $isPaidImmediately ? 'paid' : 'unpaid';

            $order = Order::create([
                'user_id' => Auth::id(),
                'order_code' => Order::generateOrderCode(),
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_address' => $validated['customer_address'],
                'customer_notes' => $validated['customer_notes'] ?? null,
                'payment_method' => $paymentMethod,
                'coupon_code' => ! empty($couponCode) ? $couponCode : null,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'shipping_cost' => $shippingCost,
                'grand_total' => $grandTotal,
                'status' => $isPaidImmediately ? 'processing' : 'pending',
                'payment_status' => $paymentStatus,
            ]);

            foreach ($itemsData as $item) {
                $order->items()->create($item);
            }

            return $order;
        });

        AuditLogger::order('Transaksi Checkout Berhasil', $order);

        $paymentMethod = strtolower($validated['payment_method'] ?? 'qris');
        $successMessage = ($paymentMethod === 'qris')
            ? 'Pesanan kamu berhasil dibuat dan pembayaran QRIS telah lunas terverifikasi!'
            : 'Pesanan kamu berhasil dibuat dengan metode Bayar di Tempat (COD)! Silakan siapkan pembayaran saat kurir mengantar barang.';

        return redirect()->route('my.orders')
            ->with('success', $successMessage)
            ->with('just_ordered', true);
    }

    /**
     * Display order invoice / detail page.
     */
    public function orderDetail(string $order_code): View
    {
        Order::autoCancelExpiredRequests();
        Order::autoCompleteDeliveredOrders();

        $order = Order::with(['items.product.variants', 'reviews'])->where('order_code', $order_code)->firstOrFail();

        return view('order-detail', [
            'order' => $order,
        ]);
    }

    /**
     * Display dedicated shopping cart page.
     */
    public function cart(): View
    {
        return view('cart');
    }

    /**
     * Display dedicated checkout & payment page.
     */
    public function showCheckout(): View
    {
        return view('checkout');
    }

    /**
     * Display list of current authenticated user orders.
     */
    public function myOrders(): View
    {
        Order::autoCancelExpiredRequests();
        Order::autoCompleteDeliveredOrders();

        $orders = Order::where('user_id', Auth::id())
            ->with(['items.product.variants', 'reviews'])
            ->latest()
            ->get();

        return view('my-orders', [
            'orders' => $orders,
        ]);
    }

    /**
     * Request order cancellation by the buyer.
     */
    public function requestOrderCancellation(Request $request, Order $order): RedirectResponse
    {
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Kamu tidak memiliki akses untuk membatalkan pesanan ini.');
        }

        if (! $order->canBeCancelledByBuyer()) {
            return back()->with('error', 'Pesanan ini sudah tidak dapat dibatalkan karena produk sudah dalam pengiriman kurir. Pembatalan hanya dapat diajukan saat pesanan masih dalam status dikemas.');
        }

        $reason = $request->input('cancellation_reason') ?: $request->input('reason');
        if (! $reason || strlen(trim($reason)) < 3) {
            return back()->with('error', 'Silakan pilih atau masukkan alasan pembatalan minimal 3 karakter.');
        }

        $order->update([
            'cancellation_status' => 'requested',
            'cancellation_reason' => trim($reason),
            'cancellation_requested_at' => now(),
        ]);

        AuditLogger::order('Pembeli Mengajukan Pembatalan Pesanan', $order, Auth::user());

        return back()->with('success', "Pengajuan pembatalan untuk pesanan #{$order->order_code} berhasil dikirim ke penjual! Jika penjual tidak merespons dalam 3 hari, pesanan akan otomatis dibatalkan sistem.");
    }

    /**
     * Confirm order completion by the buyer (Pesanan Selesai).
     */
    public function confirmOrderCompletion(Order $order): RedirectResponse
    {
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Kamu tidak memiliki akses untuk menyelesaikan pesanan ini.');
        }

        if ($order->status === 'completed') {
            return back()->with('info', 'Pesanan ini sudah berstatus selesai.');
        }

        if (! $order->canBeConfirmedCompletedByBuyer()) {
            return back()->with('error', 'Pesanan belum dapat dikonfirmasi selesai karena belum sampai di tujuan atau masih terdapat pengajuan pengembalian barang yang aktif.');
        }

        $order->update([
            'status' => 'completed',
            'payment_status' => 'paid',
            'completed_at' => now(),
        ]);

        AuditLogger::order('Pembeli Mengonfirmasi Pesanan Selesai', $order, Auth::user());

        return back()->with('success', "Pesanan #{$order->order_code} telah berhasil diselesaikan! Terima kasih sudah berbelanja di NusantaraMart.");
    }

    /**
     * Request return / refund of order items by the buyer.
     */
    public function requestOrderReturn(Request $request, Order $order): RedirectResponse
    {
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Kamu tidak memiliki akses untuk mengajukan pengembalian pada pesanan ini.');
        }

        if ($order->status === 'completed') {
            return back()->with('error', 'Pesanan yang sudah selesai tidak dapat diajukan pengembalian barang lagi.');
        }

        if (! $order->canBeReturnedByBuyer()) {
            return back()->with('error', 'Pengajuan pengembalian barang hanya dapat dilakukan ketika pesanan telah tiba di tujuan (sampai) dan belum diselesaikan.');
        }

        $validated = $request->validate([
            'return_reason' => ['required', 'string', 'max:100'],
            'return_description' => ['required', 'string', 'min:5', 'max:1000'],
            'return_proof_image' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ], [
            'return_reason.required' => 'Pilih salah satu alasan pengembalian barang.',
            'return_description.required' => 'Tuliskan deskripsi kendala barang secara lengkap.',
            'return_description.min' => 'Deskripsi kendala minimal 5 karakter.',
            'return_proof_image.image' => 'Bukti pengembalian harus berupa file foto atau gambar.',
            'return_proof_image.max' => 'Ukuran foto bukti maksimal 5MB.',
        ]);

        $proofPath = null;
        if ($request->hasFile('return_proof_image')) {
            $proofPath = $request->file('return_proof_image')->store('returns', 'public');
        }

        $order->update([
            'return_status' => 'requested',
            'return_reason' => $validated['return_reason'],
            'return_description' => $validated['return_description'],
            'return_proof_image' => $proofPath,
            'return_requested_at' => now(),
        ]);

        AuditLogger::order('Pembeli Mengajukan Pengembalian Barang (Komplain/Refund)', $order, Auth::user());

        return back()->with('success', "Pengajuan pengembalian barang untuk pesanan #{$order->order_code} berhasil dikirim ke penjual! Penjual akan meninjau komplain Anda.");
    }

    /**
     * Display the comprehensive product detail page (Shopee/Tokopedia style PDP).
     */
    public function productDetail(Product $product): View
    {
        $product->load(['category', 'store']);

        $store = $product->store;
        if (! $store && $product->store_id) {
            $store = Store::find($product->store_id);
        }
        if (! $store && $product->brand) {
            $store = Store::where('name', $product->brand)->orWhere('slug', Str::slug($product->brand))->first();
        }

        if ($store) {
            $storeName = $store->name;
            $storeCity = $store->city ?: 'Kota Bandung';
            $storeLocation = $store->location;
            $storeRating = (float) ($store->rating ?: 5.0);
            $storeRatingCount = $store->rating_count;
            $storeSoldTotal = $store->total_sold;
            $storeProductsCount = $store->products()->available()->count();
            if ($storeProductsCount === 0) {
                $storeProductsCount = 1;
            }
            $storeChatPerformance = $store->chat_performance;
            $storeLastActive = $store->user?->updated_at ? 'Aktif '.$store->user->updated_at->diffForHumans() : 'Aktif baru saja';
            $storeBadge = $store->badge ?: $product->badge;
        } else {
            $storeName = $product->brand ?: 'NusantaraMart Official Store';
            $storeCity = 'Kota Bandung';
            $storeLocation = 'Kota Bandung, Jawa Barat';
            $storeRating = (float) ($product->rating ?: 5.0);
            $storeSoldTotal = (int) $product->sold_count;
            $storeRatingCount = (int) OrderItem::where('product_name', 'LIKE', '%'.$product->name.'%')->count();
            $storeProductsCount = Product::available()
                ->when($product->brand, fn ($q) => $q->where('brand', $product->brand))
                ->count();
            if ($storeProductsCount === 0) {
                $storeProductsCount = 1;
            }
            $storeChatPerformance = [
                'rate' => '100%',
                'speed' => 'Hitungan Menit',
                'full_label' => '100% (Hitungan Menit)',
            ];
            $storeLastActive = 'Aktif hari ini';
            $storeBadge = $product->badge ?: 'Official';
        }

        // Real Product Reviews & Rating Summary
        $reviews = $product->reviews()->with('user')->latest()->get();
        $reviewsCount = $reviews->count();
        $averageRating = $reviewsCount > 0 ? round($reviews->avg('rating'), 1) : (float) ($product->rating ?: 5.0);

        $ratingDistribution = [
            5 => $reviews->where('rating', 5)->count(),
            4 => $reviews->where('rating', 4)->count(),
            3 => $reviews->where('rating', 3)->count(),
            2 => $reviews->where('rating', 2)->count(),
            1 => $reviews->where('rating', 1)->count(),
        ];

        // Real Store Follower Stats
        $isFollowingStore = false;
        $storeFollowersCount = 0;
        if ($store) {
            $storeFollowersCount = $store->followers()->count();
            if (Auth::check()) {
                $isFollowingStore = $store->isFollowedBy(Auth::user());
            }
        }

        $relatedProducts = Product::available()
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->take(6)
            ->get();

        if ($relatedProducts->count() < 6) {
            $filler = Product::available()
                ->where('id', '!=', $product->id)
                ->whereNotIn('id', $relatedProducts->pluck('id'))
                ->take(6 - $relatedProducts->count())
                ->get();
            $relatedProducts = $relatedProducts->merge($filler);
        }

        $storeAdvantages = $store?->advantages_list ?? [
            'Produk 100% Original langsung dari distributor & produsen terverifikasi.',
            'Pengemasan aman menggunakan kardus tebal & lapisan bubble wrap tanpa biaya tambahan.',
            'Pengiriman cepat setiap hari kerja ke seluruh pelosok wilayah Indonesia.',
            'Layanan pelanggan aktif dan tanggap siap membantu jika ada kendala pesanan.',
        ];

        return view('products.show', compact(
            'product',
            'relatedProducts',
            'store',
            'storeName',
            'storeCity',
            'storeLocation',
            'storeRating',
            'storeRatingCount',
            'storeSoldTotal',
            'storeProductsCount',
            'storeChatPerformance',
            'storeLastActive',
            'storeBadge',
            'storeAdvantages',
            'reviews',
            'reviewsCount',
            'averageRating',
            'ratingDistribution',
            'isFollowingStore',
            'storeFollowersCount'
        ));
    }

    /**
     * Display the seller / store profile and product catalog page (Shopee/Tokopedia style Store).
     */
    public function storeShow(Request $request, string $store): View
    {
        $storeName = urldecode($store);

        $storeModel = Store::where('name', $storeName)->orWhere('slug', Str::slug($storeName))->first();

        $productsQuery = Product::available();

        if ($storeModel) {
            $productsQuery->where('store_id', $storeModel->id);
        } elseif ($storeName === 'NusantaraMart Official Mall' || empty($storeName)) {
            $productsQuery->where(function ($q) {
                $q->whereNull('brand')->orWhere('brand', '')->orWhere('brand', 'NusantaraMart Official Mall');
            });
            if ($productsQuery->count() === 0) {
                $productsQuery = Product::available();
            }
        } else {
            $hasExact = Product::available()->where('brand', 'ILIKE', $storeName)->exists();
            if ($hasExact) {
                $productsQuery->where('brand', 'ILIKE', $storeName);
            } else {
                $productsQuery->where(function ($q) use ($storeName) {
                    $q->where('brand', 'ILIKE', '%'.$storeName.'%')
                        ->orWhere('name', 'ILIKE', '%'.$storeName.'%');
                });
            }
        }

        $sort = $request->input('sort', 'popular');
        if ($sort === 'cheapest') {
            $productsQuery->orderByRaw('COALESCE(discount_price, price) ASC');
        } elseif ($sort === 'expensive') {
            $productsQuery->orderByRaw('COALESCE(discount_price, price) DESC');
        } elseif ($sort === 'newest') {
            $productsQuery->latest();
        } else {
            $productsQuery->orderBy('sold_count', 'desc');
        }

        $products = $productsQuery->get();

        $isFollowingStore = false;
        $storeFollowersCount = 0;

        if ($storeModel) {
            $storeFollowersCount = $storeModel->followers()->count();
            if (Auth::check()) {
                $isFollowingStore = $storeModel->isFollowedBy(Auth::user());
            }

            $storeInfo = [
                'name' => $storeModel->name,
                'logo_url' => $storeModel->logo_url,
                'initials' => $storeModel->initials,
                'slug' => $storeModel->slug,
                'rating' => number_format($storeModel->rating ?: 5.0, 1),
                'chat_response' => $storeModel->chat_performance['full_label'],
                'joined_since' => $storeModel->created_at->diffForHumans(),
                'location' => $storeModel->location,
                'products_count' => $products->count(),
                'followers_count' => $storeFollowersCount,
                'is_following' => $isFollowingStore,
                'followers' => $storeFollowersCount >= 1000 ? round($storeFollowersCount / 1000, 1).'rb Pengikut' : "{$storeFollowersCount} Pengikut",
                'is_official' => strtolower($storeModel->badge) === 'official' || strtolower($storeModel->badge) === 'mall',
            ];
        } else {
            $storeInfo = [
                'name' => $storeName,
                'logo_url' => null,
                'initials' => 'NM',
                'slug' => Str::slug($storeName),
                'rating' => '5.0',
                'chat_response' => '100% (Hitungan Menit)',
                'joined_since' => 'Official Store',
                'location' => 'Kota Bandung, Jawa Barat',
                'products_count' => $products->count(),
                'followers_count' => 0,
                'is_following' => false,
                'followers' => 'Official Partner',
                'is_official' => true,
            ];
        }

        return view('store.show', compact('storeInfo', 'products', 'sort', 'storeModel', 'isFollowingStore', 'storeFollowersCount'));
    }

    /**
     * Toggle follow/unfollow for a store.
     */
    public function toggleFollowStore(Request $request, Store $store): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['error' => 'Silakan masuk terlebih dahulu untuk mengikuti toko ini.'], 401);
        }

        if ($store->user_id === $user->id) {
            return response()->json([
                'error' => 'Kamu tidak dapat mengikuti toko milikmu sendiri.',
            ], 422);
        }

        $follower = StoreFollower::where('store_id', $store->id)
            ->where('user_id', $user->id)
            ->first();

        if ($follower) {
            $follower->delete();
            $isFollowing = false;
            $message = "Berhenti mengikuti toko {$store->name}.";
        } else {
            StoreFollower::create([
                'store_id' => $store->id,
                'user_id' => $user->id,
            ]);
            $isFollowing = true;
            $message = "Sekarang kamu mengikuti toko {$store->name}! 🎉";
        }

        $count = $store->followers()->count();

        return response()->json([
            'is_following' => $isFollowing,
            'followers_count' => $count,
            'formatted_followers' => $count >= 1000 ? round($count / 1000, 1).'rb' : (string) $count,
            'message' => $message,
        ]);
    }

    /**
     * Submit a customer review and rating for a product.
     */
    public function storeReview(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'review' => ['nullable', 'string', 'max:1000'],
            'variant_name' => ['nullable', 'string', 'max:100'],
            'order_id' => ['nullable', 'integer', 'exists:orders,id'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:3072'],
        ]);

        $user = Auth::user();

        $photoPaths = [];
        if ($request->hasFile('photo')) {
            $photoPaths[] = $request->file('photo')->store('reviews', 'public');
        }

        $review = ProductReview::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'order_id' => $validated['order_id'] ?? null,
            'rating' => $validated['rating'],
            'review' => $validated['review'] ?? null,
            'variant_name' => $validated['variant_name'] ?? null,
            'photos' => ! empty($photoPaths) ? $photoPaths : null,
        ]);

        // Recalculate product aggregate rating
        $avgRating = $product->reviews()->avg('rating');
        if ($avgRating) {
            $product->update(['rating' => round($avgRating, 1)]);
        }

        // Also update store rating if linked
        if ($product->store_id) {
            $avgStoreRating = Product::where('store_id', $product->store_id)
                ->join('product_reviews', 'products.id', '=', 'product_reviews.product_id')
                ->avg('product_reviews.rating');
            if ($avgStoreRating) {
                $product->store()->update(['rating' => round($avgStoreRating, 1)]);
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Terima kasih atas penilaian kamu! Ulasan berhasil dikirim. ⭐',
                'review' => $review->load('user'),
            ]);
        }

        return back()->with('success', 'Terima kasih! Penilaian dan ulasan kamu berhasil ditambahkan. ⭐');
    }
}
