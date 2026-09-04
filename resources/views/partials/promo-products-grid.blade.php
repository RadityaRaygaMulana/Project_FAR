@if($products->count() > 0)
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-5">
        @foreach($products as $item)
            @php
                $discountPct = $item->discount_percentage;
                $soldCount = $item->sold_count ?? 0;
                $stock = $item->stock ?? 10;
                $totalStock = max(1, $soldCount + $stock);
                $soldPercentage = min(96, max(12, (int) round(($soldCount / $totalStock) * 100)));
            @endphp

            <div class="bg-white rounded-2xl border border-[#EAE1D7] overflow-hidden hover:border-[#6B4226]/40 hover:shadow-md transition duration-200 flex flex-col justify-between group">
                <!-- Thumbnail -->
                <div class="relative aspect-square bg-[#FAF8F5] overflow-hidden flex items-center justify-center">
                    @if($item->product_image_url)
                        <img src="{{ $item->product_image_url }}" alt="{{ $item->name }}" class="w-full h-full object-cover group-hover:scale-102 transition duration-300">
                    @else
                        <span class="text-4xl sm:text-5xl">
                            {{ $item->category->icon ?? '🛍️' }}
                        </span>
                    @endif

                    <!-- Discount Badge -->
                    <div class="absolute top-2.5 left-2.5">
                        <span class="px-2 py-0.5 rounded-md bg-rose-600 text-white text-[10px] sm:text-xs font-black tracking-tight shadow-xs">
                            -{{ $discountPct }}%
                        </span>
                    </div>
                </div>

                <!-- Info & CTA -->
                <div class="p-3.5 space-y-2.5 flex-1 flex flex-col justify-between">
                    <div class="space-y-1">
                        <div class="flex items-center gap-1.5 text-[11px] text-[#8A7C70] truncate">
                            <span>{{ $item->store->name ?? $item->brand ?? 'NusantaraMart' }}</span>
                            @if($item->badge)
                                <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-[#FAF4ED] text-[#6B4226] border border-[#E8DED3]">
                                    {{ $item->badge }}
                                </span>
                            @endif
                        </div>

                        <a href="{{ route('product.detail', $item->slug) }}" 
                           class="font-bold text-xs sm:text-sm text-[#2D241E] hover:text-[#6B4226] line-clamp-2 transition leading-snug block" 
                           title="{{ $item->name }}">
                            {{ $item->name }}
                        </a>

                        <div class="pt-0.5">
                            <div class="text-sm sm:text-base font-black text-[#6B4226]">
                                {{ $item->formatted_effective_price }}
                            </div>
                            <div class="text-[11px] text-[#9E9084] line-through">
                                {{ $item->formatted_price }}
                            </div>
                        </div>
                    </div>

                    <!-- Clean stock meter -->
                    <div class="space-y-1 pt-1">
                        <div class="flex items-center justify-between text-[10px] text-[#8A7C70]">
                            <span>Terjual {{ $soldCount }}</span>
                            <span class="font-medium text-[#6B4226]">Sisa {{ $item->stock }}</span>
                        </div>
                        <div class="w-full h-1.5 bg-[#FAF4ED] rounded-full overflow-hidden border border-[#EAE1D7]">
                            <div class="h-full bg-amber-500 rounded-full" style="width: {{ $soldPercentage }}%"></div>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="pt-1">
                        <button type="button" 
                                @click="addToCartDirectly({{ json_encode([
                                    'id' => $item->id,
                                    'name' => $item->name,
                                    'price' => $item->effective_price,
                                    'image_url' => $item->product_image_url,
                                    'stock' => $item->stock,
                                ]) }})"
                                class="w-full py-2 px-3 bg-[#6B4226] hover:bg-[#54321B] active:scale-[0.98] text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 cursor-pointer shadow-xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                            <span>+ Keranjang</span>
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Pagination Links -->
    @if($products->hasPages())
        <div class="pt-6 flex justify-center" @click="handlePaginationClick($event)">
            {{ $products->links() }}
        </div>
    @endif
@else
    <!-- Clean Minimalist Empty State -->
    <div class="p-10 text-center rounded-2xl bg-white border border-[#EAE1D7] space-y-3">
        <span class="text-4xl block">🔍</span>
        <div class="space-y-1 max-w-sm mx-auto">
            <h4 class="text-sm font-bold text-[#2D241E]">
                Tidak ada produk promo yang cocok
            </h4>
            <p class="text-xs text-[#8A7C70] leading-relaxed">
                Silakan coba pilih filter diskon atau kategori lain untuk melihat penawaran flash sale lainnya.
            </p>
        </div>
        <div>
            <button type="button" 
                    @click="resetAllFilters()"
                    class="px-4 py-2 rounded-xl bg-[#FAF8F5] hover:bg-[#FAF4ED] text-[#6B4226] border border-[#EAE1D7] text-xs font-bold transition cursor-pointer">
                Reset Filter
            </button>
        </div>
    </div>
@endif
