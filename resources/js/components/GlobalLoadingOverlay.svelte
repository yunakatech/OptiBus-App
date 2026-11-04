<script lang="ts">
    import { Spinner } from '@/components/ui/spinner';
    import { globalLoading } from '@/lib/loading';

    const loadingState = globalLoading;
    const navigationEntry = $derived(
        $loadingState.entries.find((entry) => entry.scope === 'navigation') ??
            null,
    );
</script>

{#if navigationEntry}
    <div
        class="pointer-events-none fixed inset-x-0 top-0 z-[130] h-1 overflow-hidden bg-cyan-950/10"
        aria-hidden="true"
    >
        <div
            class="h-full w-1/2 origin-left rounded-r-full bg-primary motion-safe:animate-[optibus-nav-bar_1.05s_ease-in-out_infinite]"
        ></div>
    </div>
    <span class="sr-only" role="status" aria-live="polite" aria-busy="true">
        {navigationEntry.message || 'Memuat halaman...'}
    </span>
{/if}

{#if $loadingState.active && $loadingState.blocking}
    <div
        class="pointer-events-auto fixed inset-0 z-[120] flex items-center justify-center bg-slate-950/48 p-4 backdrop-blur-[2px]"
        role="status"
        aria-live="polite"
        aria-busy="true"
    >
        <div
            class="flex w-full max-w-sm items-center gap-3 rounded-lg border bg-background p-4 text-foreground shadow-lg"
        >
            <Spinner class="h-5 w-5 shrink-0 text-primary" />
            <p class="text-sm">
                {$loadingState.message || 'Memproses permintaan…'}
            </p>
        </div>
    </div>
{/if}

<style>
    @keyframes -global-optibus-nav-bar {
        0% {
            transform: translateX(-120%) scaleX(0.45);
        }

        42% {
            transform: translateX(35%) scaleX(0.9);
        }

        100% {
            transform: translateX(230%) scaleX(0.55);
        }
    }
</style>
