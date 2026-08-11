<script lang="ts">
    import {
        Avatar,
        AvatarFallback,
        AvatarImage,
    } from '@/components/ui/avatar';
    import { Button } from '@/components/ui/button';
    import UserRound from 'lucide-svelte/icons/user-round';
    import {
        DropdownMenu,
        DropdownMenuContent,
        DropdownMenuTrigger,
    } from '@/components/ui/dropdown-menu';
    import UserMenuContent from '@/components/UserMenuContent.svelte';
    import { resolveAvatarUrl } from '@/lib/avatar';
    import type { User } from '@/types';

    let {
        user = null,
        triggerClass = '',
        contentClass = 'w-56',
    }: {
        user?: User | null;
        triggerClass?: string;
        contentClass?: string;
    } = $props();

    const avatarUrl = $derived(resolveAvatarUrl(user?.avatar, user?.name));
</script>

{#if user}
    <DropdownMenu>
        <DropdownMenuTrigger asChild>
            {#snippet children(props)}
                <Button
                    variant="ghost"
                    size="icon"
                    class={`relative size-10 w-auto rounded-full p-1 focus-visible:ring-2 focus-visible:ring-primary ${triggerClass}`}
                    onclick={props.onclick}
                    aria-expanded={props['aria-expanded']}
                    data-state={props['data-state']}
                    aria-label="Buka menu profil"
                >
                    <Avatar class="size-8 overflow-hidden rounded-full">
                        <AvatarImage
                            src={avatarUrl}
                            alt={user.name ?? 'User avatar'}
                        />
                        <AvatarFallback
                            class="rounded-full bg-slate-200 text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                        >
                            <UserRound class="size-4" aria-hidden="true" />
                            <span class="sr-only">Foto profil belum tersedia</span>
                        </AvatarFallback>
                    </Avatar>
                </Button>
            {/snippet}
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" class={contentClass}>
            <UserMenuContent {user} />
        </DropdownMenuContent>
    </DropdownMenu>
{/if}
