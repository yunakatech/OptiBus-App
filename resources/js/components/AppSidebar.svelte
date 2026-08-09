<script lang="ts">
    import { Link, page } from '@inertiajs/svelte';
    import type { Snippet } from 'svelte';
    import MessageCircle from 'lucide-svelte/icons/message-circle';
    import AppLogo from '@/components/AppLogo.svelte';
    import NavMain from '@/components/NavMain.svelte';
    import {
        Sidebar,
        SidebarContent,
        SidebarFooter,
        SidebarHeader,
        SidebarMenu,
        SidebarMenuButton,
        SidebarMenuItem,
    } from '@/components/ui/sidebar';
    import {
        getVisibleNavSections,
        shouldPrefetchNavigationHref,
    } from '@/lib/navigation';
    import { getSupportWhatsappHref } from '@/lib/support';
    import { toUrl } from '@/lib/utils';
    import { dashboard } from '@/routes';

    let {
        children,
    }: {
        children?: Snippet;
    } = $props();

    const billingLocked = $derived(
        Boolean(page.props.auth?.billing_access?.locked),
    );
    const homeHref = $derived(
        billingLocked ? '/subscription' : toUrl(dashboard()),
    );
    const canPrefetchHome = $derived(shouldPrefetchNavigationHref(homeHref));
    const supportHref = $derived(getSupportWhatsappHref(page.url || '/'));
    const visibleSections = $derived.by(() =>
        getVisibleNavSections(page.props.auth)
            .map((section) => ({
                ...section,
                items: section.items.filter(
                    (item) => !item.hideInDesktopSidebar,
                ),
            }))
            .filter((section) => section.items.length > 0),
    );
</script>

<Sidebar collapsible="icon" variant="inset">
    <SidebarHeader class="border-b border-sidebar-border/70">
        <SidebarMenu>
            <SidebarMenuItem>
                <SidebarMenuButton
                    size="lg"
                    asChild
                    class="rounded-lg hover:bg-sidebar-accent/70"
                >
                    {#snippet children(props)}
                        <Link
                            {...props}
                            href={homeHref}
                            class={`${props.class} justify-center`}
                            prefetch={canPrefetchHome || undefined}
                            cacheFor={canPrefetchHome ? 30000 : undefined}
                        >
                            <AppLogo />
                        </Link>
                    {/snippet}
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarHeader>

    <SidebarContent
        class="gap-2 px-1.5 py-2 group-data-[collapsible=icon]:!overflow-visible"
    >
        <NavMain label="Navigasi" sections={visibleSections} />
    </SidebarContent>
    <SidebarFooter class="border-t border-sidebar-border/70 px-1.5 py-2">
        <SidebarMenu>
            <SidebarMenuItem>
                <SidebarMenuButton
                    asChild
                    tooltip="Bantuan WhatsApp"
                    class="text-neutral-600 hover:text-neutral-800 dark:text-neutral-300 dark:hover:text-neutral-100"
                >
                    {#snippet children(props)}
                        <a
                            {...props}
                            href={supportHref}
                            target="_blank"
                            rel="noopener noreferrer"
                            aria-label="Hubungi bantuan OptiBus melalui WhatsApp"
                        >
                            <MessageCircle class="size-4 shrink-0" />
                            <span>Bantuan WhatsApp</span>
                        </a>
                    {/snippet}
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarFooter>
</Sidebar>
{@render children?.()}
