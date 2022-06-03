<span x-data="{ open: false }" x-init="
    @this.on('{{ $attributes['event'] }}', () => {
        if (open === false) setTimeout(() => { open = false }, 3500);
        open = true;
    })
" x-show.transition.out.duration.1000ms="open" style="display: none;" class="inline-flex {{ $attributes['color'] }}">{{ $slot }}</span>