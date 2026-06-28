
<script module lang="ts">
    export const layout = {
        title: 'Lengkapi Data Travel',
        description:
            'Isi data travel untuk menyiapkan akun dan konteks operasional Anda.',
    };
</script>

<script lang="ts">
    import { Form } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import InputError from '@/components/InputError.svelte';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Spinner } from '@/components/ui/spinner';

    let { user_email = '' }: { user_email?: string } = $props();

    const formAction = '/onboarding';
</script>

<AppHead title="Lengkapi Data Travel" />

<Form
    action={formAction}
    method="post"
    class="flex flex-col gap-6"
>
    {#snippet children({ errors, processing })}
        <div class="text-center mb-4">
            <h2 class="text-lg font-semibold">Lengkapi Data Travel</h2>
            <p class="text-sm text-muted-foreground mt-1">
                Akun Google: {user_email}
            </p>
            <p class="text-xs text-muted-foreground mt-0.5">Isi data di bawah untuk memulai.</p>
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
                <Label>Rute Utama <span class="text-destructive">*</span></Label>
                <div class="grid grid-cols-2 gap-3">
                    <div class="grid gap-1.5">
                        <Label for="origin" class="text-xs text-muted-foreground">Dari</Label>
                        <Input id="origin" type="text" required name="origin" placeholder="Contoh: Pinrang" />
                        <InputError message={errors.origin} />
                    </div>
                    <div class="grid gap-1.5">
                        <Label for="destination" class="text-xs text-muted-foreground">Tujuan</Label>
                        <Input id="destination" type="text" required name="destination" placeholder="Contoh: Makassar" />
                        <InputError message={errors.destination} />
                    </div>
                </div>
                <p class="text-xs text-muted-foreground">Rute utama operasional travel Anda. Akan otomatis terdaftar di Rute Induk dan termapping ke Pool.</p>
            </div>

            <Button type="submit" class="w-full" disabled={processing}>
                {#if processing}<Spinner />{/if}
                Lanjut ke Dashboard
            </Button>
        </div>
    {/snippet}
</Form>
