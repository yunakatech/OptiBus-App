<script lang="ts">
    import MessageCircle from 'lucide-svelte/icons/message-circle';
    import { getSupportWhatsappHref } from '@/lib/support';

    const supportHref = getSupportWhatsappHref();
</script>

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
