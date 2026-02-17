@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-center">
        <span class="inline-flex overflow-hidden rounded-xl border border-slate-200 bg-white">
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" aria-label="@lang('pagination.previous')" class="inline-flex items-center bg-brandRed/10 px-3 py-2 text-sm font-semibold text-brandRed cursor-not-allowed">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M12.707 15.707a1 1 0 01-1.414 0l-5-5a1 1 0 010-1.414l5-5a1 1 0 011.414 1.414L8.414 10l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
                    </svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')" class="inline-flex items-center px-3 py-2 text-sm font-semibold text-brandBlueDark hover:bg-brandBlue/10 focus:z-10 focus:outline-none focus:ring-2 focus:ring-brandBlue/20">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M12.707 15.707a1 1 0 01-1.414 0l-5-5a1 1 0 010-1.414l5-5a1 1 0 011.414 1.414L8.414 10l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
                    </svg>
                </a>
            @endif

            @php($lastPage = $paginator->lastPage())
            @php($currentPage = $paginator->currentPage())

            @if ($lastPage <= 7)
                @for ($page = 1; $page <= $lastPage; $page++)
                    @if ($page === $currentPage)
                        <span aria-current="page" class="inline-flex items-center border-l border-slate-200 bg-brandBlue/10 px-4 py-2 text-sm font-semibold text-brandBlueDark">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $paginator->url($page) }}" aria-label="@lang('Go to page :page', ['page' => $page])" class="inline-flex items-center border-l border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-brandBlueDark hover:bg-brandBlue/10 focus:z-10 focus:outline-none focus:ring-2 focus:ring-brandBlue/20">
                            {{ $page }}
                        </a>
                    @endif
                @endfor
            @else
                @if ($currentPage === 1)
                    <span aria-current="page" class="inline-flex items-center border-l border-slate-200 bg-brandBlue/10 px-4 py-2 text-sm font-semibold text-brandBlueDark">
                        1
                    </span>
                @else
                    <a href="{{ $paginator->url(1) }}" aria-label="@lang('Go to page :page', ['page' => 1])" class="inline-flex items-center border-l border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-brandBlueDark hover:bg-brandBlue/10 focus:z-10 focus:outline-none focus:ring-2 focus:ring-brandBlue/20">
                        1
                    </a>
                @endif

                @if ($currentPage > 4)
                    <span aria-disabled="true" class="inline-flex items-center border-l border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-400">
                        …
                    </span>
                @endif

                @for ($page = max(2, $currentPage - 1); $page <= min($lastPage - 1, $currentPage + 1); $page++)
                    @if ($page === $currentPage)
                        <span aria-current="page" class="inline-flex items-center border-l border-slate-200 bg-brandBlue/10 px-4 py-2 text-sm font-semibold text-brandBlueDark">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $paginator->url($page) }}" aria-label="@lang('Go to page :page', ['page' => $page])" class="inline-flex items-center border-l border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-brandBlueDark hover:bg-brandBlue/10 focus:z-10 focus:outline-none focus:ring-2 focus:ring-brandBlue/20">
                            {{ $page }}
                        </a>
                    @endif
                @endfor

                @if ($currentPage < $lastPage - 3)
                    <span aria-disabled="true" class="inline-flex items-center border-l border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-400">
                        …
                    </span>
                @endif

                @if ($lastPage === $currentPage)
                    <span aria-current="page" class="inline-flex items-center border-l border-slate-200 bg-brandBlue/10 px-4 py-2 text-sm font-semibold text-brandBlueDark">
                        {{ $lastPage }}
                    </span>
                @else
                    <a href="{{ $paginator->url($lastPage) }}" aria-label="@lang('Go to page :page', ['page' => $lastPage])" class="inline-flex items-center border-l border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-brandBlueDark hover:bg-brandBlue/10 focus:z-10 focus:outline-none focus:ring-2 focus:ring-brandBlue/20">
                        {{ $lastPage }}
                    </a>
                @endif
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')" class="inline-flex items-center border-l border-slate-200 px-3 py-2 text-sm font-semibold text-brandBlueDark hover:bg-brandBlue/10 focus:z-10 focus:outline-none focus:ring-2 focus:ring-brandBlue/20">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L11.586 9 7.293 4.707a1 1 0 011.414-1.414l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                </a>
            @else
                <span aria-disabled="true" aria-label="@lang('pagination.next')" class="inline-flex items-center border-l border-slate-200 bg-brandRed/10 px-3 py-2 text-sm font-semibold text-brandRed cursor-not-allowed">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L11.586 9 7.293 4.707a1 1 0 011.414-1.414l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                </span>
            @endif
        </span>
    </nav>
@endif
