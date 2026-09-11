{{-- Plain pagination links. Laravel's bundled views assume Tailwind or
     Bootstrap; this dashboard uses neither. --}}
@if ($paginator->hasPages())
  <nav class="pager__in" role="navigation" aria-label="Pagination">
    @if ($paginator->onFirstPage())
      <span class="pager__btn is-off">Newer</span>
    @else
      <button type="button" class="pager__btn" wire:click="previousPage" wire:loading.attr="disabled">Newer</button>
    @endif

    <span class="pager__at">Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</span>

    @if ($paginator->hasMorePages())
      <button type="button" class="pager__btn" wire:click="nextPage" wire:loading.attr="disabled">Older</button>
    @else
      <span class="pager__btn is-off">Older</span>
    @endif
  </nav>
@endif
