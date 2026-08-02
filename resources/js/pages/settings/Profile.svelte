<script module lang="ts">
    import { edit } from '@/routes/profile';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Profile settings',
                href: edit(),
            },
        ],
    };
</script>

<script lang="ts">
    import { Form, page } from '@inertiajs/svelte';
    import { onDestroy } from 'svelte';
    import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
    import AppHead from '@/components/AppHead.svelte';
    import DeleteUser from '@/components/DeleteUser.svelte';
    import Heading from '@/components/Heading.svelte';
    import InputError from '@/components/InputError.svelte';
    import {
        Avatar,
        AvatarFallback,
        AvatarImage,
    } from '@/components/ui/avatar';
    import TextLink from '@/components/TextLink.svelte';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { resolveAvatarUrl } from '@/lib/avatar';
    import { getInitials } from '@/lib/initials';
    import { send } from '@/routes/verification';

    let {
        mustVerifyEmail,
        status = '',
    }: {
        mustVerifyEmail: boolean;
        status?: string;
    } = $props();

    const user = $derived(page.props.auth.user);
    const avatarUrl = $derived(resolveAvatarUrl(user.avatar, user.name));
    let avatarPreviewUrl = $state('');
    let avatarPreviewObjectUrl = '';

    const clearAvatarPreview = () => {
        if (avatarPreviewObjectUrl) {
            URL.revokeObjectURL(avatarPreviewObjectUrl);
            avatarPreviewObjectUrl = '';
        }

        avatarPreviewUrl = '';
    };

    const handleAvatarChange = (event: Event) => {
        clearAvatarPreview();

        const input = event.currentTarget as HTMLInputElement | null;
        const file = input?.files?.[0];

        if (!file) {
            return;
        }

        avatarPreviewObjectUrl = URL.createObjectURL(file);
        avatarPreviewUrl = avatarPreviewObjectUrl;
    };

    onDestroy(() => {
        clearAvatarPreview();
    });
</script>

<AppHead title="Profile settings" />

<h1 class="sr-only">Profile settings</h1>

<div class="flex flex-col space-y-6">
    <Heading
        variant="small"
        title="Profile information"
        description="Update your name and email address"
    />

    <Form
        {...ProfileController.update.form()}
        class="space-y-6"
        options={{ preserveScroll: true }}
    >
        {#snippet children({ errors, processing })}
            <div class="flex items-start gap-4 rounded-2xl border border-border/70 bg-muted/20 p-4">
                <Avatar class="h-20 w-20 rounded-2xl">
                    <AvatarImage src={avatarPreviewUrl || avatarUrl} alt={user.name} />
                    <AvatarFallback class="rounded-2xl bg-slate-200 text-slate-800 dark:bg-slate-200 dark:text-slate-800">
                        {getInitials(user.name)}
                    </AvatarFallback>
                </Avatar>

                <div class="grid flex-1 gap-2">
                    <div>
                        <Label for="avatar">Profile picture</Label>
                        <Input
                            id="avatar"
                            type="file"
                            name="avatar"
                            accept="image/png,image/jpeg,image/webp,image/gif"
                            class="mt-1 block w-full"
                            onchange={handleAvatarChange}
                        />
                    </div>
                    <p class="text-xs text-muted-foreground">
                        PNG, JPG, WEBP, atau GIF. Maks 2 MB. Foto lama akan
                        otomatis diganti saat Anda mengunggah yang baru.
                    </p>
                    <InputError class="mt-1" message={errors.avatar} />
                </div>
            </div>

            <div class="grid gap-2">
                <Label for="name">Name</Label>
                <Input
                    id="name"
                    name="name"
                    class="mt-1 block w-full"
                    value={user.name}
                    required
                    autocomplete="name"
                    placeholder="Full name"
                />
                <InputError class="mt-2" message={errors.name} />
            </div>

            <div class="grid gap-2">
                <Label for="email">Email address</Label>
                <Input
                    id="email"
                    type="email"
                    name="email"
                    class="mt-1 block w-full"
                    value={user.email}
                    required
                    autocomplete="username"
                    placeholder="Email address"
                />
                <InputError class="mt-2" message={errors.email} />
            </div>

            {#if mustVerifyEmail && !user.email_verified_at}
                <div>
                    <p class="-mt-4 text-sm text-muted-foreground">
                        Your email address is unverified.
                        <TextLink href={send()} as="button">
                            Click here to resend the verification email.
                        </TextLink>
                    </p>

                    {#if status === 'verification-link-sent'}
                        <div class="mt-2 text-sm font-medium text-green-600">
                            A new verification link has been sent to your email
                            address.
                        </div>
                    {:else if status === 'verification-link-failed'}
                        <div class="mt-2 text-sm font-medium text-destructive">
                            We could not send the verification email right now.
                            Please try again in a moment.
                        </div>
                    {/if}
                </div>
            {/if}

            <div class="flex items-center gap-4">
                <Button
                    type="submit"
                    disabled={processing}
                    data-test="update-profile-button">Save</Button
                >
            </div>
        {/snippet}
    </Form>
</div>

<DeleteUser />
