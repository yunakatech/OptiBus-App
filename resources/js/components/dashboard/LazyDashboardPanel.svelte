<script lang="ts">
    import { onMount  } from 'svelte';
import type {Component} from 'svelte';
    import { Button } from '@/components/ui/button';

    let {
        loader,
        panelProps,
        label,
    }: {
        loader: () => Promise<{ default: Component<any> }>;
        panelProps: Record<string, unknown>;
        label: string;
    } = $props();
    let target: HTMLDivElement;
    let Panel = $state<Component<any> | null>(null);
    let failed = $state(false);
    let busy = false;
    let disposed = false;
    async function load() {
        if (busy || disposed) {
return;
}

        busy = true;
        failed = false;

        try {
            const module = await loader();

            if (!disposed) {
Panel = module.default;
}
        } catch {
            if (!disposed) {
failed = true;
}
        } finally {
            busy = false;
        }
    }
    onMount(() => {
        if (!('IntersectionObserver' in window)) {
void load();
}

        const observer =
            'IntersectionObserver' in window
                ? new IntersectionObserver(
                      (entries) => {
                          if (entries.some((entry) => entry.isIntersecting)) {
                              observer?.disconnect();
                              void load();
                          }
                      },
                      { rootMargin: '160px' },
                  )
                : null;
        observer?.observe(target);

        return () => {
            disposed = true;
            observer?.disconnect();
        };
    });
</script>

<div bind:this={target} class="min-w-0">
    {#if Panel}<Panel {...panelProps} />
    {:else if failed}
        <div class="rounded-lg border p-4" role="alert">
            <p class="text-sm">{label} gagal dimuat.</p>
            <Button class="mt-3" variant="outline" onclick={() => void load()}
                >Coba lagi</Button
            >
        </div>
    {:else}
        <div
            class="min-h-48 rounded-lg border p-4"
            role="status"
            aria-label={`Memuat ${label}`}
        >
            <div
                class="h-32 animate-pulse rounded bg-muted motion-reduce:animate-none"
            ></div>
        </div>
    {/if}
</div>
