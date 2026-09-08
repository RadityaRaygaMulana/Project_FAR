@if($products->count() > 0)
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3 sm:gap-4">
        @foreach($products as $item)
            <a href="{{ route('product.detail', $item->slug) }}" 
               class="bg-white rounded-2xl border border-[#EAE1D7] overflow-hidden hover:border-[#6B4226]/50 hover:shadow-md transition duration-200 flex flex-col justify-between group cursor-pointer">
                <!-- Thumbnail -->
                <div class="relative aspect-square bg-[#FAF8F5] overflow-hidden flex items-center justify-center">
                    @if($item->product_image_url)
                        <img src="{{ $item->product_image_url }}" alt="{{ $item->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                    @else
                        <span class="text-3xl sm:text-4xl">
                            {{ $item->category->icon ?? '🛍️' }}
                        </span>
                    @endif

                    <!-- Penawaran Spesial Tag -->
                    <div class="absolute top-2 left-2">
                        <span class="px-1.5 py-0.5 rounded bg-amber-600 text-white text-[9px] font-black tracking-tight shadow-xs flex items-center gap-0.5">
                            <span>🏷️</span>
                            <span>Spesial</span>
                        </span>
                    </div>

                    <!-- Discount Badge (If Any) -->
                    @if($item->hasDiscount())
                        <div class="absolute top-2 right-2">
                            <span class="px-1.5 py-0.5 rounded bg-rose-600 text-white text-[9px] font-black tracking-tight shadow-xs">
                                -{{ $item->discount_percentage }}%
                            </span>
                        </div>
                    @endif
                </div>

                <!-- Info Body -->
                <div class="p-2.5 space-y-1.5 flex-1 flex flex-col justify-between">
                    <div class="space-y-1">
                        <!-- Store Name & Badge -->
                        <div class="flex items-center gap-1 text-[10px] text-[#8A7C70] truncate">
                            <span class="truncate">{{ $item->store->name ?? $item->brand ?? 'NusantaraMart' }}</span>
                            @if($item->badge)
                                <span class="px-1 py-0.2 rounded text-[8px] font-bold bg-[#FAF4ED] text-[#6B4226] border border-[#E8DED3] shrink-0">
                                    {{ $item->badge }}
                                </span>
                            @endif
                        </div>

                        <!-- Product Title -->
                        <h4 class="font-bold text-xs text-[#2D241E] group-hover:text-[#6B4226] line-clamp-2 transition leading-snug" title="{{ $item->name }}">
                            {{ $item->name }}
                        </h4>

                        <!-- Price -->
                        <div class="pt-0.5">
                            <div class="text-xs sm:text-sm font-black text-[#6B4226]">
                                {{ $item->formatted_effective_price }}
                            </div>
                            @if($item->hasDiscount())
                                <div class="text-[10px] text-[#9E9084] line-through">
                                    {{ $item->formatted_price }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Origin City & Rating -->
                    <div class="flex items-center justify-between text-[10px] text-[#8A7C70] pt-1.5 border-t border-[#F2EAE0]">
                        <span class="text-amber-600 font-bold">★ {{ number_format($item->rating ?? 5.0, 1) }}</span>
                        <span class="text-[#8A7C70] truncate text-[9px]" title="{{ $item->origin_address }}">📍 {{ $item->origin_city }}</span>
                    </div>
                </div>
            </a>
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
        <span class="text-4xl block">🏷️</span>
        <div class="space-y-1 max-w-sm mx-auto">
            <h4 class="text-sm font-bold text-[#2D241E]">
                Belum ada produk penawaran spesial yang cocok
            </h4>
            <p class="text-xs text-[#8A7C70] leading-relaxed">
                Silakan coba ubah pilihan filter atau pilih kategori lain untuk melihat produk lainnya.
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
