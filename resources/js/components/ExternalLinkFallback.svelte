<script lang="ts">
    import { overlay } from '@/lib/mobile-overlay';
    import { externalFallback } from '@/lib/webview';
</script>

{#if $externalFallback}
    <div
        class="mobile-dialog-viewport fixed inset-0 z-[150] flex items-center justify-center bg-black/50 p-4"
    >
        <section
            use:overlay={{
                close: () => externalFallback.set(null),
                label: 'Buka tautan',
            }}
            role="dialog"
            aria-modal="true"
            class="mobile-dialog-panel w-full max-w-md space-y-4 rounded-2xl border bg-background p-5 text-foreground shadow-xl"
        >
            <h2 class="text-lg font-semibold">Tautan belum terbuka</h2>
            <p>
                Perangkat membatasi jendela baru. Buka tautan di halaman ini
                atau salin alamatnya.
            </p>
            <input
                class="w-full rounded-lg border p-3"
                aria-label="Alamat tautan"
                readonly
                value={$externalFallback}
                onclick={(event) => event.currentTarget.select()}
            />
            <div class="flex flex-wrap gap-3">
                <a
                    class="inline-flex min-h-12 items-center rounded-lg bg-primary px-4 text-primary-foreground"
                    href={$externalFallback}
                    onclick={() => externalFallback.set(null)}
                    >Buka di halaman ini</a
                >
                <button
                    class="min-h-12 rounded-lg border px-4"
                    onclick={() => externalFallback.set(null)}>Tutup</button
                >
            </div>
        </section>
    </div>
{/if}
