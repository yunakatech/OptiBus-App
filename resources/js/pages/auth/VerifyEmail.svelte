<script module lang="ts">
    export const layout = {
        title: 'Verifikasi Email',
        description:
            'Silakan verifikasi alamat email Anda melalui link yang kami kirimkan.',
    };
</script>

<script lang="ts">
    import { Form } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import TextLink from '@/components/TextLink.svelte';
    import { Button } from '@/components/ui/button';
    import { Spinner } from '@/components/ui/spinner';
    import { logout } from '@/routes';
    import { send } from '@/routes/verification';

    let {
        status = '',
    }: {
        status?: string;
    } = $props();
</script>

<AppHead title="Verifikasi Email" />

{#if status === 'verification-link-sent'}
    <div class="mb-4 text-center text-sm font-medium text-green-600">
        Link verifikasi baru sudah dikirim ke alamat email Anda.
    </div>
{:else if status === 'verification-link-failed'}
    <div class="mb-4 text-center text-sm font-medium text-destructive">
        Email verifikasi belum bisa dikirim saat ini. Silakan coba lagi sebentar
        lagi.
    </div>
{/if}

<Form {...send.form()} class="space-y-6 text-center">
    {#snippet children({ processing })}
        <Button type="submit" disabled={processing} variant="secondary">
            {#if processing}<Spinner />{/if}
            Kirim ulang email verifikasi
        </Button>

        <TextLink href={logout()} as="button" class="mx-auto block text-sm">
            Keluar
        </TextLink>
    {/snippet}
</Form>
