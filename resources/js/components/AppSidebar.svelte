<script lang="ts">
    import { Link, page } from '@inertiajs/svelte';
    import type { Snippet } from 'svelte';
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
    const supportHref = getSupportWhatsappHref();
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
                    class="bg-[#25D366] font-semibold text-[#102C1B] hover:bg-[#20bd5b] hover:text-[#102C1B]"
                >
                    {#snippet children(props)}
                        <a
                            {...props}
                            href={supportHref}
                            target="_blank"
                            rel="noopener noreferrer"
                            aria-label="Hubungi bantuan OptiBus melalui WhatsApp"
                        >
                            <svg aria-hidden="true" viewBox="0 0 24 24" class="size-4 shrink-0 fill-current">
                                <path d="M20.52 3.48A11.87 11.87 0 0 0 12.06 0C5.47 0 .11 5.36.11 11.95c0 2.11.55 4.17 1.6 5.98L0 24l6.22-1.63a11.9 11.9 0 0 0 5.83 1.48h.01c6.59 0 11.95-5.36 11.95-11.95 0-3.19-1.24-6.19-3.49-8.42ZM12.06 21.8a9.9 9.9 0 0 1-5.04-1.38l-.36-.21-3.69.97.99-3.6-.23-.37a9.85 9.85 0 0 1-1.51-5.26c0-5.44 4.43-9.87 9.88-9.87 2.63 0 5.1 1.03 6.96 2.89a9.8 9.8 0 0 1 2.89 6.98c0 5.44-4.43 9.85-9.89 9.85Zm5.42-7.39c-.3-.15-1.77-.87-2.04-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.95 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.47-.89-.79-1.49-1.77-1.67-2.07-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.62-.92-2.22-.24-.58-.48-.5-.67-.51h-.57c-.2 0-.52.07-.8.37-.27.3-1.05 1.02-1.05 2.49s1.07 2.89 1.22 3.09c.15.2 2.1 3.21 5.08 4.5.71.31 1.27.49 1.7.63.71.23 1.36.2 1.87.12.57-.08 1.77-.72 2.02-1.42.25-.7.25-1.3.17-1.42-.07-.13-.27-.2-.57-.35Z" />
                            </svg>
                            <span>Bantuan WhatsApp</span>
                        </a>
                    {/snippet}
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarFooter>
</Sidebar>
{@render children?.()}
