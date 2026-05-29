<script module lang="ts">
    import { edit as editAppearance } from '@/routes/appearance';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Appearance settings',
                href: editAppearance(),
            },
        ],
    };
</script>

<script lang="ts">
    import AppearanceTabs from '@/components/AppearanceTabs.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import DensityTabs from '@/components/DensityTabs.svelte';
    import Heading from '@/components/Heading.svelte';
    import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
</script>

<AppHead title="Appearance settings" />

<h1 class="sr-only">Appearance settings</h1>

<div class="space-y-6">
    <Heading
        variant="small"
        title="Appearance settings"
        description="Update your account's appearance settings"
    />
    <Card>
        <CardHeader>
            <CardTitle>Theme mode</CardTitle>
            <CardDescription>Choose light, dark, or follow your system setting.</CardDescription>
        </CardHeader>
        <CardContent>
            <AppearanceTabs />
        </CardContent>
    </Card>
    <Card>
        <CardHeader>
            <CardTitle>Desktop density</CardTitle>
            <CardDescription>Set UI spacing to compact or comfortable for desktop view.</CardDescription>
        </CardHeader>
        <CardContent>
            <DensityTabs />
        </CardContent>
    </Card>
</div>
