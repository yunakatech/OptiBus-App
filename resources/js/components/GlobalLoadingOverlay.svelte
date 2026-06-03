<script lang="ts">
    import { Sparkles } from 'lucide-svelte';
    import { Spinner } from '@/components/ui/spinner';
    import { Skeleton } from '@/components/ui/skeleton';
    import { globalLoading } from '@/lib/loading';

    const loadingState = globalLoading;
    const skeletonRows = Array.from({ length: 6 });
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
            class="h-full w-1/2 origin-left animate-[qbus-nav-bar_1.05s_ease-in-out_infinite] rounded-r-full bg-linear-to-r from-cyan-400 via-sky-500 to-emerald-300 shadow-[0_0_24px_rgba(14,165,233,0.65)]"
        ></div>
    </div>

    <div
        class="pointer-events-none fixed inset-x-3 top-[calc(4.4rem+env(safe-area-inset-top))] z-[70] md:top-20"
        role="status"
        aria-live="polite"
        aria-busy="true"
    >
        <div
            class="mx-auto w-full max-w-5xl overflow-hidden rounded-[24px] border border-border/70 bg-background/88 shadow-[0_22px_70px_-42px_rgba(15,23,42,0.55)] ring-1 ring-white/15 backdrop-blur-xl dark:bg-slate-950/78"
        >
            <span class="sr-only">{navigationEntry.message || 'Memuat halaman...'}</span>
            <div class="border-b border-border/60 bg-muted/20 px-4 py-3">
                <div class="flex items-center justify-between gap-4">
                    <div class="min-w-0 flex-1 space-y-2">
                        <Skeleton class="h-3 w-24 rounded-full bg-cyan-500/18" />
                        <Skeleton class="h-4 w-full max-w-sm rounded-full" />
                    </div>
                    <Skeleton class="hidden h-8 w-24 rounded-full md:block" />
                </div>
            </div>
            <div class="space-y-2 p-3">
                <div class="grid grid-cols-[1.2fr_0.9fr_0.8fr] gap-3 px-2 md:grid-cols-[1.3fr_0.9fr_0.8fr_0.7fr_0.5fr]">
                    <Skeleton class="h-3 rounded-full" />
                    <Skeleton class="h-3 rounded-full" />
                    <Skeleton class="h-3 rounded-full" />
                    <Skeleton class="hidden h-3 rounded-full md:block" />
                    <Skeleton class="hidden h-3 rounded-full md:block" />
                </div>
                {#each skeletonRows as _, index}
                    <div
                        class="grid grid-cols-[1.2fr_0.9fr_0.8fr] gap-3 rounded-2xl border border-border/45 bg-card/62 px-3 py-3 md:grid-cols-[1.3fr_0.9fr_0.8fr_0.7fr_0.5fr]"
                        style={`animation: qbus-skeleton-rise 420ms ease-out both; animation-delay: ${index * 45}ms;`}
                    >
                        <Skeleton class="h-4 rounded-full" />
                        <Skeleton class="h-4 rounded-full" />
                        <Skeleton class="h-4 rounded-full" />
                        <Skeleton class="hidden h-4 rounded-full md:block" />
                        <Skeleton class="hidden h-4 rounded-full md:block" />
                    </div>
                {/each}
            </div>
        </div>
    </div>
{/if}

{#if $loadingState.active && $loadingState.blocking}
    <div class="pointer-events-auto fixed inset-0 z-[120] flex items-center justify-center bg-slate-950/48 p-4 backdrop-blur-[2px]" role="status" aria-live="polite" aria-busy="true">
        <div class="w-full max-w-xs overflow-hidden rounded-[28px] border border-white/15 bg-slate-950/92 p-5 text-white shadow-[0_32px_80px_rgba(15,23,42,0.45)]">
            <div class="absolute inset-x-0 top-0 h-px bg-linear-to-r from-transparent via-cyan-300/70 to-transparent"></div>
            <div class="flex items-center gap-3">
                <div class="relative flex h-12 w-12 items-center justify-center rounded-2xl border border-white/10 bg-white/8">
                    <div class="absolute inset-1 rounded-[14px] bg-linear-to-br from-cyan-400/18 via-sky-400/8 to-transparent"></div>
                    <Spinner class="relative h-6 w-6 text-cyan-200" />
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 text-[11px] font-semibold tracking-[0.14em] text-cyan-100/80 uppercase">
                        <Sparkles class="h-3.5 w-3.5" />
                        <span>Qbus Processing</span>
                    </div>
                    <p class="mt-1 text-sm font-semibold text-white">Mohon tunggu sebentar</p>
                    <p class="mt-1 text-xs leading-relaxed text-slate-300">
                        {$loadingState.message || 'Sistem sedang memproses permintaan Anda.'}
                    </p>
                </div>
            </div>
        </div>
    </div>
{/if}

<style>
    @keyframes -global-qbus-nav-bar {
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

    @keyframes -global-qbus-skeleton-rise {
        from {
            opacity: 0;
            transform: translateY(8px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
