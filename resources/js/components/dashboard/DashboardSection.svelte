<script lang="ts">
    import { Deferred, router } from '@inertiajs/svelte';
    import type { Snippet } from 'svelte';
    import { Button } from '@/components/ui/button';

    let {
        data,
        label,
        children,
    }: { data: string | string[]; label: string; children: Snippet } = $props();
    let timedOut = $state(false);
    let retrying = $state(false);
    $effect(() => {
        const timer = setTimeout(() => {
            timedOut = true;
        }, 15000);
        return () => clearTimeout(timer);
    });
    function retry() {
        retrying = true;
        router.reload({
            only: Array.isArray(data) ? data : [data],
            onFinish: () => {
                retrying = false;
            },
        });
    }
</script>

<Deferred {data}>
    {#snippet fallback()}
        <div
            class="min-h-32 rounded-lg border border-border p-4"
            role="status"
            aria-live="polite"
        >
            <p class="text-sm text-muted-foreground">
                {timedOut
                    ? `${label} belum berhasil dimuat.`
                    : `Memuat ${label.toLowerCase()}…`}
            </p>
            {#if timedOut}<Button
                    class="mt-3"
                    variant="outline"
                    disabled={retrying}
                    onclick={retry}>Coba lagi</Button
                >
            {:else}<div
                    class="mt-4 h-16 animate-pulse rounded bg-muted motion-reduce:animate-none"
                ></div>{/if}
        </div>
    {/snippet}
    {#snippet rescue()}
        <div class="rounded-lg border border-border p-4" role="alert">
            <p class="text-sm">{label} gagal dimuat.</p>
            <Button
                class="mt-3"
                variant="outline"
                disabled={retrying}
                onclick={retry}>{retrying ? 'Memuat…' : 'Coba lagi'}</Button
            >
        </div>
    {/snippet}
    {@render children()}
</Deferred>
