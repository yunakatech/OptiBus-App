<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'Role & Hak Akses',
                href: '/admin-ops/roles',
            },
        ],
    };
</script>

<script lang="ts">
    import { Link, page, router } from '@inertiajs/svelte';
    import {
        ArrowLeft,
        CheckCircle2,
        LockKeyhole,
        Pencil,
        Plus,
        RefreshCw,
        Search,
        ShieldCheck,
        Trash2,
        Users,
    } from 'lucide-svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import {
        Card,
        CardContent,
        CardHeader,
        CardTitle,
    } from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import { LoadingButton } from '@/components/ui/loading-button';
    import { confirmAndRun, runWithFeedback } from '@/lib/action-feedback';
    import { cn } from '@/lib/utils';
    import { dashboard } from '@/routes';

    type Permission = {
        id: number;
        slug: string;
        name: string;
        group: string;
    };

    type PermissionGroup = {
        group: string;
        permissions: Permission[];
    };

    type RoleRow = {
        id: number;
        name: string;
        slug: string;
        description: string;
        is_system: boolean;
        is_locked: boolean;
        permission_ids: number[];
        permission_slugs: string[];
        user_count: number;
        created_at: string | null;
        updated_at: string | null;
    };

    type RoleForm = {
        id: number;
        name: string;
        slug: string;
        description: string;
        permission_ids: number[];
    };
    type Pagination = {
        page: number;
        per_page: number;
        total: number;
        last_page: number;
    };
    type RoleDataPayload = {
        roles?: RoleRow[];
        pagination?: Pagination;
    };
    type RolePermissionsPayload = {
        permissions?: Permission[];
        permission_groups?: PermissionGroup[];
    };

    let {
        roleQuery = '',
        roleData = null,
        rolePermissions = null,
    }: {
        roleQuery?: string;
        roleData?: RoleDataPayload | null;
        rolePermissions?: RolePermissionsPayload | null;
    } = $props();

    let roles = $state<RoleRow[]>([]);
    let permissions = $state<Permission[]>([]);
    let permissionGroups = $state<PermissionGroup[]>([]);
    let form = $state<RoleForm>({
        id: 0,
        name: '',
        slug: '',
        description: '',
        permission_ids: [],
    });
    let search = $state('');
    let roleQueryHydrated = $state(false);
    let roleMeta = $state<Pagination>({
        page: 1,
        per_page: 20,
        total: 0,
        last_page: 1,
    });
    let loading = $state(true);
    let saving = $state(false);
    let deletingId = $state(0);
    let message = $state('');
    let error = $state('');
    let roleView = $state<'data' | 'form'>('data');

    const isSuperAdmin = $derived(
        Boolean(page.props.auth?.user?.is_super_admin),
    );
    const isEditing = $derived(form.id > 0);
    const isSuperAdminRole = $derived(form.slug === 'super-admin');
    const allPermissionIds = $derived(permissions.map((item) => item.id));
    const selectedCount = $derived(form.permission_ids.length);
    const csrfToken = () => {
        if (typeof document === 'undefined') {
            return '';
        }

        return (
            document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
                ?.content ?? ''
        );
    };

    const xsrfTokenFromCookie = () => {
        if (typeof document === 'undefined') {
            return '';
        }

        const part = document.cookie
            .split('; ')
            .find((row) => row.startsWith('XSRF-TOKEN='));

        if (!part) {
            return '';
        }

        try {
            return decodeURIComponent(part.split('=')[1] ?? '');
        } catch {
            return '';
        }
    };

    const refreshCsrfToken = async () => {
        if (typeof window === 'undefined') {
            return false;
        }

        try {
            const response = await fetch(window.location.href, {
                method: 'GET',
                credentials: 'same-origin',
                cache: 'no-store',
                headers: {
                    Accept: 'text/html',
                },
            });
            const html = await response.text();
            const match = html.match(
                /<meta\s+name=["']csrf-token["']\s+content=["']([^"']+)["']/i,
            );

            if (!match?.[1]) {
                return false;
            }

            let node = document.querySelector(
                'meta[name="csrf-token"]',
            ) as HTMLMetaElement | null;

            if (!node) {
                node = document.createElement('meta');
                node.name = 'csrf-token';
                document.head.appendChild(node);
            }

            node.content = match[1];

            return true;
        } catch {
            return false;
        }
    };

    const sendApiRequest = async (
        method: 'GET' | 'POST' | 'DELETE',
        url: string,
        body?: Record<string, unknown>,
    ) => {
        const token = csrfToken() || xsrfTokenFromCookie();
        const isDelete = method === 'DELETE';
        const requestMethod = isDelete ? 'POST' : method;
        const payload =
            method === 'GET'
                ? body
                : {
                      ...(body ?? {}),
                      ...(isDelete ? { _method: 'DELETE' } : {}),
                      _token: token,
                  };

        return fetch(url, {
            method: requestMethod,
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'X-XSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: payload ? JSON.stringify(payload) : undefined,
        });
    };

    const api = async (
        method: 'GET' | 'POST' | 'DELETE',
        url: string,
        body?: Record<string, unknown>,
    ) => {
        let response = await sendApiRequest(method, url, body);

        if (
            response.status === 419 &&
            method !== 'GET' &&
            (await refreshCsrfToken())
        ) {
            response = await sendApiRequest(method, url, body);
        }

        const json = await response.json().catch(() => ({}));
        const firstValidationError = (() => {
            const errors = json?.errors;

            if (!errors || typeof errors !== 'object') {
                return '';
            }

            for (const value of Object.values(errors)) {
                if (Array.isArray(value) && value.length > 0) {
                    return String(value[0] ?? '').trim();
                }
            }

            return '';
        })();

        if (!response.ok || json.success === false) {
            throw new Error(
                json.error ||
                    json.message ||
                    firstValidationError ||
                    `Request gagal (${response.status})`,
            );
        }

        return json;
    };

    const reloadRolesWithInertia = (targetPage = roleMeta.page) => {
        if (typeof window === 'undefined') {
            return;
        }

        loading = true;
        message = '';
        error = '';

        router.get(
            window.location.pathname,
            {
                page: targetPage,
                per_page: roleMeta.per_page,
                ...(search.trim() !== '' ? { q: search.trim() } : {}),
            },
            {
                only: ['roleData'],
                preserveState: true,
                preserveScroll: true,
                replace: true,
                onError: () => {
                    error = 'Gagal memuat role.';
                },
                onFinish: () => {
                    loading = false;
                },
            },
        );
    };

    const scrollToRoleSection = (id: string) => {
        if (typeof window === 'undefined') {
            return;
        }

        window.requestAnimationFrame(() => {
            document
                .getElementById(id)
                ?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    };

    const resetForm = () => {
        form = {
            id: 0,
            name: '',
            slug: '',
            description: '',
            permission_ids: [],
        };
        message = '';
        error = '';
    };

    const openCreateRole = () => {
        resetForm();
        roleView = 'form';
        scrollToRoleSection('role-editor');
    };

    const showRoleData = () => {
        resetForm();
        roleView = 'data';
        scrollToRoleSection('role-data');
    };

    const editRole = (role: RoleRow, scrollIntoForm = true) => {
        form = {
            id: role.id,
            name: role.name,
            slug: role.slug,
            description: role.description,
            permission_ids:
                role.slug === 'super-admin'
                    ? [...allPermissionIds]
                    : [...role.permission_ids],
        };
        message = '';
        error = '';
        roleView = 'form';

        if (scrollIntoForm) {
            scrollToRoleSection('role-editor');
        }
    };

    const hasPermissionId = (permissionId: number) =>
        form.permission_ids.includes(permissionId);

    const togglePermission = (permissionId: number, checked: boolean) => {
        if (isSuperAdminRole) {
            return;
        }

        form = {
            ...form,
            permission_ids: checked
                ? Array.from(new Set([...form.permission_ids, permissionId]))
                : form.permission_ids.filter((id) => id !== permissionId),
        };
    };

    const groupPermissionIds = (group: PermissionGroup) =>
        group.permissions.map((permission) => permission.id);

    const groupAllSelected = (group: PermissionGroup) => {
        const ids = groupPermissionIds(group);

        return ids.length > 0 && ids.every((id) => hasPermissionId(id));
    };

    const groupSomeSelected = (group: PermissionGroup) => {
        const ids = groupPermissionIds(group);

        return ids.some((id) => hasPermissionId(id));
    };

    const toggleGroup = (group: PermissionGroup, checked: boolean) => {
        if (isSuperAdminRole) {
            return;
        }

        const ids = groupPermissionIds(group);
        form = {
            ...form,
            permission_ids: checked
                ? Array.from(new Set([...form.permission_ids, ...ids]))
                : form.permission_ids.filter((id) => !ids.includes(id)),
        };
    };

    const selectAll = () => {
        if (isSuperAdminRole) {
            return;
        }

        form = { ...form, permission_ids: [...allPermissionIds] };
    };

    const clearAll = () => {
        if (isSuperAdminRole) {
            return;
        }

        form = { ...form, permission_ids: [] };
    };

    const saveRole = async (event: SubmitEvent) => {
        event.preventDefault();
        saving = true;
        message = '';
        error = '';

        try {
            await runWithFeedback(
                async () => {
                    await api('POST', '/api/admin/roles', {
                        id: form.id || undefined,
                        name: form.name,
                        slug: form.slug,
                        description: form.description,
                        permission_ids: isSuperAdminRole
                            ? allPermissionIds
                            : form.permission_ids,
                    });
                },
                {
                    loadingMessage: isEditing
                        ? 'Memperbarui role...'
                        : 'Menyimpan role baru...',
                    successMessage: isEditing
                        ? 'Role berhasil diperbarui.'
                        : 'Role berhasil dibuat.',
                    errorMessage: 'Gagal menyimpan role.',
                },
            );
            message = isEditing ? 'Role updated.' : 'Role created.';
            resetForm();
            roleView = 'data';
            reloadRolesWithInertia(1);
        } catch (e) {
            error = e instanceof Error ? e.message : 'Gagal menyimpan role.';
        } finally {
            saving = false;
        }
    };

    const deleteRole = async (role: RoleRow) => {
        deletingId = role.id;
        message = '';
        error = '';

        try {
            const result = await confirmAndRun(
                `Hapus role "${role.name}"? Role yang sudah dipakai user tidak dapat dihapus.`,
                async () => api('DELETE', `/api/admin/roles/${role.id}`),
                {
                    loadingMessage: 'Menghapus role...',
                    successMessage: 'Role berhasil dihapus.',
                    errorMessage: 'Gagal menghapus role.',
                },
            );

            if (result) {
                if (form.id === role.id) {
                    resetForm();
                }

                reloadRolesWithInertia(roleMeta.page);
            }
        } catch (e) {
            error = e instanceof Error ? e.message : 'Gagal menghapus role.';
        } finally {
            deletingId = 0;
        }
    };

    $effect(() => {
        if (isSuperAdminRole && allPermissionIds.length > 0) {
            form = { ...form, permission_ids: [...allPermissionIds] };
        }
    });

    $effect(() => {
        if (!roleQueryHydrated) {
            search = roleQuery;
            roleQueryHydrated = true;
        }
    });

    $effect(() => {
        const payload = roleData;
        if (!payload) {
            return;
        }

        roles = Array.isArray(payload.roles) ? payload.roles : [];
        roleMeta = payload.pagination ?? roleMeta;

        if (form.id > 0) {
            const updatedRole = roles.find((role) => role.id === form.id);
            if (updatedRole) {
                editRole(updatedRole, false);
            }
        }

        loading = false;
    });

    $effect(() => {
        const payload = rolePermissions;
        if (!payload) {
            return;
        }

        permissions = Array.isArray(payload.permissions)
            ? payload.permissions
            : [];
        permissionGroups = Array.isArray(payload.permission_groups)
            ? payload.permission_groups
            : [];
    });
</script>

<AppHead title="Role & Hak Akses" />

<div
    class="min-h-full bg-[radial-gradient(circle_at_top_left,rgba(14,165,233,0.10),transparent_32rem),linear-gradient(180deg,hsl(var(--background)),hsl(var(--muted))/0.45)] px-3 py-4 md:px-6 md:py-6"
>
    <div class="mx-auto w-full max-w-7xl space-y-5">
        <section
            class="overflow-hidden rounded-lg border border-cyan-200/50 bg-card/95 shadow-sm shadow-cyan-950/5 dark:border-cyan-900/40 dark:bg-card/90"
        >
            <div
                class="grid gap-4 bg-[linear-gradient(135deg,rgba(8,145,178,0.14),rgba(15,23,42,0.02))] p-4 md:grid-cols-[1.2fr_0.8fr] md:p-6"
            >
                <div class="space-y-3">
                    <Badge
                        variant="outline"
                        class="border-cyan-300/70 bg-cyan-50/70 text-cyan-800 dark:border-cyan-800 dark:bg-cyan-950/40 dark:text-cyan-100"
                    >
                        <ShieldCheck class="size-3.5" />
                        Super Admin Console
                    </Badge>
                    <div class="space-y-1">
                        <h1
                            class="text-2xl font-black tracking-tight text-foreground md:text-3xl"
                        >
                            Role & Hak Akses
                        </h1>
                        <p class="max-w-2xl text-sm text-muted-foreground">
                            Buat role baru, atur permission per menu, dan jaga
                            akses operasional OptiBus tetap rapi per tim.
                        </p>
                    </div>
                </div>
                <div
                    class="grid grid-cols-3 gap-2 rounded-lg border border-border/70 bg-background/70 p-3 text-center backdrop-blur"
                >
                    <div>
                        <p class="text-xl font-black text-foreground">
                            {roleMeta.total}
                        </p>
                        <p
                            class="text-[10px] uppercase tracking-wide text-muted-foreground"
                        >
                            Role
                        </p>
                    </div>
                    <div>
                        <p class="text-xl font-black text-foreground">
                            {permissions.length}
                        </p>
                        <p
                            class="text-[10px] uppercase tracking-wide text-muted-foreground"
                        >
                            Permission
                        </p>
                    </div>
                    <div>
                        <p class="text-xl font-black text-foreground">
                            {permissionGroups.length}
                        </p>
                        <p
                            class="text-[10px] uppercase tracking-wide text-muted-foreground"
                        >
                            Grup
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {#if !isSuperAdmin}
            <Card class="border-destructive/30">
                <CardContent class="space-y-3 p-5">
                    <div class="flex items-start gap-3">
                        <LockKeyhole class="mt-1 size-5 text-destructive" />
                        <div class="space-y-1">
                            <h2 class="font-bold text-foreground">
                                Akses dibatasi untuk Super Admin
                            </h2>
                            <p class="text-sm text-muted-foreground">
                                Halaman ini hanya bisa digunakan oleh akun
                                super-admin.
                            </p>
                        </div>
                    </div>
                    <Button asChild variant="outline">
                        {#snippet children(props)}
                            <Link {...props} href={dashboard()}
                                >Kembali ke Dashboard</Link
                            >
                        {/snippet}
                    </Button>
                </CardContent>
            </Card>
        {:else}
            <div class="space-y-5">
                <div
                    class="flex flex-col gap-3 rounded-lg border border-border/70 bg-card/95 p-3 shadow-sm md:flex-row md:items-center md:justify-between md:p-4"
                >
                    <div class="space-y-1">
                        <p class="text-sm font-black text-foreground">
                            {roleView === 'data'
                                ? 'Data Role'
                                : isEditing
                                  ? 'Form Edit Role'
                                  : 'Form Tambah Role'}
                        </p>
                        <p class="text-xs text-muted-foreground">
                            {roleView === 'data'
                                ? 'Pilih role untuk diedit, atau buat role baru dari tombol tambah.'
                                : 'Isi detail role dan permission tanpa keluar dari menu Role & Hak Akses.'}
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <Button
                            type="button"
                            variant={roleView === 'data'
                                ? 'default'
                                : 'outline'}
                            onclick={showRoleData}
                        >
                            Data Role
                        </Button>
                        <Button
                            type="button"
                            variant={roleView === 'form' && !isEditing
                                ? 'default'
                                : 'outline'}
                            onclick={openCreateRole}
                        >
                            <Plus class="size-4" />
                            Tambah Role
                        </Button>
                    </div>
                </div>

                {#if roleView === 'data'}
                    <aside id="role-data" class="space-y-3">
                        <Card
                            class="overflow-hidden border-border/80 bg-card/95"
                        >
                            <CardHeader class="space-y-3 p-4">
                                <div
                                    class="flex items-center justify-between gap-3"
                                >
                                    <CardTitle class="text-base">
                                        Daftar Role
                                    </CardTitle>
                                    <div class="flex shrink-0 gap-2">
                                        <Button
                                            type="button"
                                            size="sm"
                                            onclick={openCreateRole}
                                        >
                                            <Plus class="size-4" />
                                            Tambah Role
                                        </Button>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            onclick={() =>
                                                reloadRolesWithInertia(
                                                    roleMeta.page,
                                                )}
                                            disabled={loading}
                                        >
                                            <RefreshCw
                                                class={cn(
                                                    'size-4',
                                                    loading && 'animate-spin',
                                                )}
                                            />
                                        </Button>
                                    </div>
                                </div>
                                <form
                                    class="grid gap-2 sm:grid-cols-[minmax(0,1fr)_auto]"
                                    onsubmit={(event) => {
                                        event.preventDefault();
                                        reloadRolesWithInertia(1);
                                    }}
                                >
                                    <label class="relative block">
                                        <Search
                                            class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground"
                                        />
                                        <Input
                                            bind:value={search}
                                            placeholder="Cari role atau permission"
                                            class="pl-9"
                                        />
                                    </label>
                                    <Button type="submit" disabled={loading}>
                                        Cari
                                    </Button>
                                </form>
                            </CardHeader>
                            <CardContent class="space-y-3 p-4 pt-0">
                                {#if error}
                                    <div
                                        class="rounded-lg border border-destructive/30 bg-destructive/10 px-3 py-2 text-sm text-destructive"
                                    >
                                        {error}
                                    </div>
                                {/if}
                                {#if loading}
                                    <div
                                        class="rounded-lg border border-dashed p-4 text-sm text-muted-foreground"
                                    >
                                        Memuat role...
                                    </div>
                                {:else if roles.length === 0}
                                    <div
                                        class="rounded-lg border border-dashed p-4 text-sm text-muted-foreground"
                                    >
                                        Role tidak ditemukan.
                                    </div>
                                {:else}
                                    {#each roles as role (role.id)}
                                        <article
                                            class={cn(
                                                'rounded-lg border bg-background/80 p-3 transition-all hover:border-cyan-300/70 hover:shadow-sm',
                                                form.id === role.id &&
                                                    'border-cyan-400 bg-cyan-50/60 dark:bg-cyan-950/25',
                                            )}
                                        >
                                            <div
                                                class="flex items-start justify-between gap-3"
                                            >
                                                <button
                                                    type="button"
                                                    class="min-w-0 flex-1 text-left"
                                                    onclick={() =>
                                                        editRole(role)}
                                                >
                                                    <div
                                                        class="flex flex-wrap items-center gap-2"
                                                    >
                                                        <h3
                                                            class="truncate text-sm font-black text-foreground"
                                                        >
                                                            {role.name}
                                                        </h3>
                                                        {#if role.is_system}
                                                            <Badge
                                                                variant="secondary"
                                                                class="text-[10px]"
                                                            >
                                                                Sistem
                                                            </Badge>
                                                        {/if}
                                                        {#if role.is_locked}
                                                            <LockKeyhole
                                                                class="size-3.5 text-cyan-700 dark:text-cyan-300"
                                                            />
                                                        {/if}
                                                    </div>
                                                    <p
                                                        class="mt-1 truncate text-xs text-muted-foreground"
                                                    >
                                                        {role.slug}
                                                    </p>
                                                </button>
                                                <div
                                                    class="flex shrink-0 gap-1"
                                                >
                                                    <Button
                                                        type="button"
                                                        variant="outline"
                                                        size="sm"
                                                        class="h-8 w-8 p-0"
                                                        onclick={() =>
                                                            editRole(role)}
                                                    >
                                                        <Pencil
                                                            class="size-3.5"
                                                        />
                                                    </Button>
                                                    <Button
                                                        type="button"
                                                        variant="outline"
                                                        size="sm"
                                                        class="h-8 w-8 p-0 text-destructive hover:text-destructive"
                                                        disabled={role.is_system ||
                                                            deletingId ===
                                                                role.id}
                                                        onclick={() =>
                                                            void deleteRole(
                                                                role,
                                                            )}
                                                    >
                                                        <Trash2
                                                            class="size-3.5"
                                                        />
                                                    </Button>
                                                </div>
                                            </div>
                                            <div
                                                class="mt-3 flex flex-wrap items-center gap-2 text-[11px] text-muted-foreground"
                                            >
                                                <span
                                                    class="inline-flex items-center gap-1 rounded-full bg-muted px-2 py-1"
                                                >
                                                    <CheckCircle2
                                                        class="size-3.5 text-cyan-700 dark:text-cyan-300"
                                                    />
                                                    {role.permission_ids.length}
                                                    akses
                                                </span>
                                                <span
                                                    class="inline-flex items-center gap-1 rounded-full bg-muted px-2 py-1"
                                                >
                                                    <Users class="size-3.5" />
                                                    {role.user_count} user
                                                </span>
                                            </div>
                                        </article>
                                    {/each}
                                    <div
                                        class="flex flex-wrap items-center justify-between gap-2 border-t border-border/70 pt-3"
                                    >
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            Total {roleMeta.total} role
                                        </p>
                                        <div class="flex items-center gap-2">
                                            <Button
                                                type="button"
                                                size="sm"
                                                variant="outline"
                                                disabled={roleMeta.page <= 1 ||
                                                    loading}
                                                onclick={() =>
                                                    reloadRolesWithInertia(
                                                        roleMeta.page - 1,
                                                    )}
                                            >
                                                Prev
                                            </Button>
                                            <span
                                                class="rounded-full border border-border/70 bg-background px-3 py-1 text-xs text-muted-foreground"
                                            >
                                                {roleMeta.page} / {roleMeta.last_page}
                                            </span>
                                            <Button
                                                type="button"
                                                size="sm"
                                                variant="outline"
                                                disabled={roleMeta.page >=
                                                    roleMeta.last_page ||
                                                    loading}
                                                onclick={() =>
                                                    reloadRolesWithInertia(
                                                        roleMeta.page + 1,
                                                    )}
                                            >
                                                Next
                                            </Button>
                                        </div>
                                    </div>
                                {/if}
                            </CardContent>
                        </Card>
                    </aside>
                {:else}
                    <section id="role-editor" class="space-y-4">
                        <Card class="border-border/80 bg-card/95">
                            <CardHeader class="gap-3 p-4 md:p-5">
                                <div
                                    class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between"
                                >
                                    <div>
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            class="-ml-2 mb-2 h-8 px-2 text-muted-foreground"
                                            onclick={showRoleData}
                                        >
                                            <ArrowLeft class="size-4" />
                                            Kembali ke Data
                                        </Button>
                                        <CardTitle class="text-lg">
                                            {isEditing
                                                ? 'Edit Role'
                                                : 'Tambah Role Baru'}
                                        </CardTitle>
                                        <p
                                            class="mt-1 text-sm text-muted-foreground"
                                        >
                                            Pilih permission yang boleh
                                            digunakan role ini. Permission
                                            super-admin dikunci penuh.
                                        </p>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        <Button
                                            type="button"
                                            variant="outline"
                                            onclick={openCreateRole}
                                        >
                                            <Plus class="size-4" />
                                            Tambah Role
                                        </Button>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            onclick={selectAll}
                                            disabled={isSuperAdminRole}
                                        >
                                            Pilih Semua
                                        </Button>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            onclick={clearAll}
                                            disabled={isSuperAdminRole}
                                        >
                                            Kosongkan
                                        </Button>
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent class="p-4 pt-0 md:p-5 md:pt-0">
                                <form class="space-y-5" onsubmit={saveRole}>
                                    <div
                                        class="grid gap-3 rounded-lg border border-border/80 bg-background/70 p-3 md:grid-cols-2"
                                    >
                                        <label class="space-y-1.5">
                                            <span
                                                class="text-xs font-bold uppercase tracking-wide text-muted-foreground"
                                            >
                                                Nama Role
                                            </span>
                                            <Input
                                                bind:value={form.name}
                                                required
                                                maxlength="120"
                                                placeholder="Contoh: Operator Pool Baru"
                                            />
                                        </label>
                                        <label class="space-y-1.5">
                                            <span
                                                class="text-xs font-bold uppercase tracking-wide text-muted-foreground"
                                            >
                                                Slug
                                            </span>
                                            <Input
                                                bind:value={form.slug}
                                                maxlength="80"
                                                disabled={isEditing}
                                                placeholder="otomatis dari nama role"
                                            />
                                        </label>
                                        <label
                                            class="space-y-1.5 md:col-span-2"
                                        >
                                            <span
                                                class="text-xs font-bold uppercase tracking-wide text-muted-foreground"
                                            >
                                                Deskripsi
                                            </span>
                                            <textarea
                                                bind:value={form.description}
                                                rows="3"
                                                maxlength="1000"
                                                class="min-h-20 w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm outline-none transition focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                                placeholder="Ringkas tujuan role ini agar mudah dipahami admin."
                                            ></textarea>
                                        </label>
                                    </div>

                                    <div
                                        class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-cyan-200/60 bg-cyan-50/60 px-3 py-2 text-sm dark:border-cyan-900/50 dark:bg-cyan-950/20"
                                    >
                                        <div class="flex items-center gap-2">
                                            <ShieldCheck
                                                class="size-4 text-cyan-700 dark:text-cyan-300"
                                            />
                                            <span
                                                class="font-bold text-foreground"
                                            >
                                                {selectedCount} dari
                                                {permissions.length} permission
                                            </span>
                                        </div>
                                        {#if isSuperAdminRole}
                                            <Badge
                                                variant="outline"
                                                class="border-cyan-300 text-cyan-800 dark:border-cyan-800 dark:text-cyan-100"
                                            >
                                                Super Admin selalu akses penuh
                                            </Badge>
                                        {/if}
                                    </div>

                                    <div class="grid gap-3 xl:grid-cols-2">
                                        {#each permissionGroups as group (group.group)}
                                            <article
                                                class="overflow-hidden rounded-lg border border-border/80 bg-background/75"
                                            >
                                                <header
                                                    class="flex items-center justify-between gap-3 border-b border-border/70 bg-muted/40 px-3 py-2"
                                                >
                                                    <div>
                                                        <h3
                                                            class="text-sm font-black text-foreground"
                                                        >
                                                            {group.group}
                                                        </h3>
                                                        <p
                                                            class="text-[11px] text-muted-foreground"
                                                        >
                                                            {group.permissions
                                                                .length}
                                                            permission
                                                        </p>
                                                    </div>
                                                    <button
                                                        type="button"
                                                        class={cn(
                                                            'rounded-full border px-3 py-1 text-[11px] font-bold transition',
                                                            groupAllSelected(
                                                                group,
                                                            )
                                                                ? 'border-cyan-300 bg-cyan-100 text-cyan-900 dark:border-cyan-700 dark:bg-cyan-950 dark:text-cyan-100'
                                                                : groupSomeSelected(
                                                                        group,
                                                                    )
                                                                  ? 'border-amber-300 bg-amber-50 text-amber-800 dark:border-amber-700 dark:bg-amber-950/40 dark:text-amber-100'
                                                                  : 'border-border bg-card text-muted-foreground',
                                                        )}
                                                        disabled={isSuperAdminRole}
                                                        onclick={() =>
                                                            toggleGroup(
                                                                group,
                                                                !groupAllSelected(
                                                                    group,
                                                                ),
                                                            )}
                                                    >
                                                        {groupAllSelected(group)
                                                            ? 'Semua aktif'
                                                            : groupSomeSelected(
                                                                    group,
                                                                )
                                                              ? 'Sebagian'
                                                              : 'Pilih grup'}
                                                    </button>
                                                </header>
                                                <div class="grid gap-2 p-3">
                                                    {#each group.permissions as permission (permission.id)}
                                                        <label
                                                            class="flex cursor-pointer items-start gap-3 rounded-xl border border-transparent px-2 py-2 transition hover:border-cyan-200 hover:bg-cyan-50/50 dark:hover:border-cyan-900 dark:hover:bg-cyan-950/20"
                                                        >
                                                            <input
                                                                type="checkbox"
                                                                checked={hasPermissionId(
                                                                    permission.id,
                                                                )}
                                                                disabled={isSuperAdminRole}
                                                                onchange={(
                                                                    event,
                                                                ) =>
                                                                    togglePermission(
                                                                        permission.id,
                                                                        (
                                                                            event.currentTarget as HTMLInputElement
                                                                        )
                                                                            .checked,
                                                                    )}
                                                                class="mt-0.5 size-4 shrink-0 rounded-lg border border-input accent-cyan-700 disabled:cursor-not-allowed disabled:opacity-60"
                                                            />
                                                            <span
                                                                class="min-w-0"
                                                            >
                                                                <span
                                                                    class="block text-sm font-semibold text-foreground"
                                                                >
                                                                    {permission.name}
                                                                </span>
                                                                <span
                                                                    class="block truncate text-[11px] text-muted-foreground"
                                                                >
                                                                    {permission.slug}
                                                                </span>
                                                            </span>
                                                        </label>
                                                    {/each}
                                                </div>
                                            </article>
                                        {/each}
                                    </div>

                                    {#if error}
                                        <div
                                            class="rounded-lg border border-destructive/30 bg-destructive/10 px-3 py-2 text-sm text-destructive"
                                        >
                                            {error}
                                        </div>
                                    {/if}

                                    {#if message}
                                        <div
                                            class="rounded-lg border border-emerald-300/50 bg-emerald-50 px-3 py-2 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/30 dark:text-emerald-100"
                                        >
                                            {message}
                                        </div>
                                    {/if}

                                    <div
                                        class="flex flex-col-reverse gap-2 border-t border-border/70 pt-4 sm:flex-row sm:justify-end"
                                    >
                                        <Button
                                            type="button"
                                            variant="outline"
                                            onclick={showRoleData}
                                        >
                                            Kembali ke Data
                                        </Button>
                                        <LoadingButton
                                            type="submit"
                                            loading={saving}
                                            loadingText="Menyimpan..."
                                            disabled={form.name.trim() === ''}
                                        >
                                            Simpan Role
                                        </LoadingButton>
                                    </div>
                                </form>
                            </CardContent>
                        </Card>
                    </section>
                {/if}
            </div>
        {/if}
    </div>
</div>
