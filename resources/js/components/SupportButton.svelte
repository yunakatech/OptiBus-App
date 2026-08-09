<script lang="ts">
    import MessageCircle from 'lucide-svelte/icons/message-circle';
    import { page } from '@inertiajs/svelte';
    import {
        Tooltip,
        TooltipContent,
        TooltipTrigger,
    } from '@/components/ui/tooltip';
    import { getSupportWhatsappHref } from '@/lib/support';

    let {
        variant = 'floating',
    }: {
        variant?: 'floating' | 'compact';
    } = $props();

    const supportHref = $derived(getSupportWhatsappHref(page.url || '/'));
</script>

{#if variant === 'compact'}
    <a
        href={supportHref}
        target="_blank"
        rel="noopener noreferrer"
        class="inline-flex items-center gap-2 rounded-full border border-[#b9d8d0] bg-white/85 px-4 py-2 text-sm font-medium text-[#17655f] shadow-sm transition hover:-translate-y-0.5 hover:bg-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#1c8278] focus-visible:ring-offset-2"
        aria-label="Hubungi bantuan OptiBus melalui WhatsApp"
    >
        <MessageCircle class="size-4" />
        <span>Bantuan WhatsApp</span>
    </a>
{:else}
    <div
        class="fixed bottom-[calc(5.75rem+env(safe-area-inset-bottom))] right-4 z-40 md:bottom-5 md:right-5"
    >
        <Tooltip>
            <TooltipTrigger>
                {#snippet child({ props })}
                    <a
                        {...props}
                        href={supportHref}
                        target="_blank"
                        rel="noopener noreferrer"
                        class="group flex size-12 items-center justify-center rounded-full bg-[#168c7c] text-white shadow-[0_14px_30px_-12px_rgba(13,91,82,0.75)] ring-4 ring-white/80 transition duration-200 hover:-translate-y-1 hover:bg-[#117568] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#168c7c] focus-visible:ring-offset-2 dark:ring-slate-950/80"
                        aria-label="Hubungi bantuan OptiBus melalui WhatsApp"
                        title="Bantuan WhatsApp"
                    >
                        <MessageCircle
                            class="size-5 transition-transform group-hover:scale-110"
                        />
                    </a>
                {/snippet}
            </TooltipTrigger>
            <TooltipContent side="left">Bantuan WhatsApp</TooltipContent>
        </Tooltip>
    </div>
{/if}
