<script module lang="ts">
    export const layout = {
        title: 'Lengkapi Data Travel',
        description: 'Isi data travel Anda untuk melanjutkan.',
    };
</script>

<script lang="ts">
    import { Form, page } from '@inertiajs/svelte';
    import { BusFront } from 'lucide-svelte';
    import AppHead from '@/components/AppHead.svelte';
    import InputError from '@/components/InputError.svelte';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Spinner } from '@/components/ui/spinner';

    let { user_name = '', user_email = '' }: { user_name?: string; user_email?: string } = $props();

    const formAction = '/onboarding';
</script>

<AppHead title="Lengkapi Data" />

<Form
    action={formAction}
    method="post"
    class="flex flex-col gap-6"
>
    {#snippet children({ errors, processing })}
        <div class="text-center mb-4">
            <BusFront class="h-10 w-10 text-primary mx-auto mb-2" />
            <h2 class="text-lg font-semibold">Lengkapi Data Travel</h2>
            <p class="text-sm text-muted-foreground mt-1">
                Akun Google: {user_email}<br />
                Isi data di bawah untuk memulai.
            </p>
        </div>

        <div class="grid gap-6">
            <div class="grid gap-2">
                <Label for="travel_name">Nama Travel / Pool <span class="text-destructive">*</span></Label>
                <Input id="travel_name" type="text" required name="travel_name" placeholder="Contoh: Mandiri Trans" />
                <p class="text-xs text-muted-foreground">Nama usaha travel Anda.</p>
                <InputError message={errors.travel_name} />
            </div>

            <div class="grid gap-2">
                <Label for="phone">Nomor WhatsApp <span class="text-destructive">*</span></Label>
                <Input id="phone" type="tel" required name="phone" placeholder="0852xxxx" />
                <p class="text-xs text-muted-foreground">Untuk konfirmasi dan reminder.</p>
                <InputError message={errors.phone} />
            </div>

            <div class="grid gap-2">
                <Label for="route">Rute Utama <span class="text-destructive">*</span></Label>
                <Input id="route" type="text" required name="route" placeholder="Contoh: Pinrang - Makassar" />
                <p class="text-xs text-muted-foreground">Rute utama operasional travel Anda.</p>
                <InputError message={errors.route} />
            </div>

            <Button type="submit" class="w-full" disabled={processing}>
                {#if processing}<Spinner />{/if}
                Lanjut ke Dashboard
            </Button>
        </div>
    {/snippet}
</Form>
