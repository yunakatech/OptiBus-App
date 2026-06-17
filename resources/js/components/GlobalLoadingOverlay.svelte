<script lang="ts">
    import { Sparkles } from 'lucide-svelte';
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
            class="h-full w-1/2 origin-left animate-[optibus-nav-bar_1.05s_ease-in-out_infinite] rounded-r-full bg-linear-to-r from-cyan-400 via-sky-500 to-emerald-300 shadow-[0_0_24px_rgba(14,165,233,0.65)]"
        ></div>
    </div>
    <span class="sr-only" role="status" aria-live="polite" aria-busy="true">
        {navigationEntry.message || 'Memuat halaman...'}
    </span>
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
                        <span>OptiBus Processing</span>
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
