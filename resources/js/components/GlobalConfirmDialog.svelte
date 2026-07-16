<script lang="ts">
    import { AlertTriangle } from 'lucide-svelte';
    import { Button } from '@/components/ui/button';
    import { confirmAccept, confirmCancel, confirmState } from '@/lib/confirm';

    const destructiveClass = 'border-rose-500/20 bg-rose-500/10 text-rose-200';
</script>

{#if $confirmState.open}
    <div
        class="fixed inset-0 z-[121] flex items-center justify-center bg-slate-950/55 p-4 backdrop-blur-[3px]"
    >
        <button
            type="button"
            class="absolute inset-0"
            aria-label="Tutup konfirmasi"
            onclick={confirmCancel}
        ></button>
        <div
            class="relative w-full max-w-md overflow-hidden rounded-lg border border-white/12 bg-slate-950/96 p-5 text-white shadow-[0_28px_80px_rgba(15,23,42,0.48)]"
        >
            <div
                class="absolute inset-x-0 top-0 h-px bg-linear-to-r from-transparent via-cyan-300/70 to-transparent"
            ></div>
            <div class="flex items-start gap-3">
                <div
                    class={`flex h-11 w-11 shrink-0 items-center justify-center rounded-lg border border-white/10 ${$confirmState.variant === 'destructive' ? destructiveClass : 'bg-white/7 text-cyan-100'}`}
                >
                    <AlertTriangle class="h-5 w-5" />
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-white">
                        {$confirmState.title}
                    </p>
                    <p class="mt-2 text-sm leading-relaxed text-slate-300">
                        {$confirmState.message}
                    </p>
                </div>
            </div>

            <div class="mt-5 flex flex-wrap justify-end gap-2">
                <Button
                    type="button"
                    variant="outline"
                    class="rounded-xl border-white/12 bg-white/6 text-slate-100 hover:bg-white/12 hover:text-white"
                    onclick={confirmCancel}
                >
                    {$confirmState.cancelText}
                </Button>
                <Button
                    type="button"
                    variant={$confirmState.variant === 'destructive'
                        ? 'destructive'
                        : 'default'}
                    class="rounded-xl"
                    onclick={confirmAccept}
                >
                    {$confirmState.confirmText}
                </Button>
            </div>
        </div>
    </div>
{/if}
