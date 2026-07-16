<script lang="ts">
    import { Link, page } from '@inertiajs/svelte';
    import type { Snippet } from 'svelte';
    import AppLogo from '@/components/AppLogo.svelte';
    import NavMain from '@/components/NavMain.svelte';
    import {
        Sidebar,
        SidebarContent,
        SidebarHeader,
        SidebarMenu,
        SidebarMenuButton,
        SidebarMenuItem,
    } from '@/components/ui/sidebar';
    import { getVisibleNavSections } from '@/lib/navigation';
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
    const visibleSections = $derived(getVisibleNavSections(page.props.auth));
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
                            prefetch
                            cacheFor={30000}
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
</Sidebar>
{@render children?.()}
