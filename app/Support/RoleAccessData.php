<?php

namespace App\Support;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleAccessData
{
    /**
     * @return array{roles: array<int, array<string, mixed>>, pagination: array<string, int>}
     */
    public function roles(Request $request): array
    {
        $queryText = trim((string) $request->query('q', ''));
        $page = max(1, (int) $request->query('page', 1));
        $perPage = max(10, min(100, (int) $request->query('per_page', 20)));

        if (! AccessControl::tablesReady()) {
            return [
                'roles' => [],
                'pagination' => [
                    'page' => 1,
                    'per_page' => $perPage,
                    'total' => 0,
                    'last_page' => 1,
                ],
            ];
        }

        $query = DB::table('roles')
            ->orderByDesc('is_system')
            ->orderBy('name');

        if ($queryText !== '') {
            $like = '%'.$queryText.'%';
            $query->where(function (Builder $builder) use ($like): void {
                $builder
                    ->where('roles.name', 'like', $like)
                    ->orWhere('roles.slug', 'like', $like)
                    ->orWhere('roles.description', 'like', $like)
                    ->orWhereExists(function (Builder $permissionQuery) use ($like): void {
                        $permissionQuery
                            ->selectRaw('1')
                            ->from('role_permission as rp')
                            ->join('permissions as p', 'rp.permission_id', '=', 'p.id')
                            ->whereColumn('rp.role_id', 'roles.id')
                            ->where(function (Builder $permissionFilter) use ($like): void {
                                $permissionFilter
                                    ->where('p.name', 'like', $like)
                                    ->orWhere('p.slug', 'like', $like)
                                    ->orWhere('p.group', 'like', $like);
                            });
                    });
            });
        }

        $total = (clone $query)->count();
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min($page, $lastPage);
        $roles = $query
            ->forPage($page, $perPage)
            ->get(['id', 'name', 'slug', 'description', 'is_system', 'created_at', 'updated_at']);
        $roleIds = $roles->pluck('id')->map(static fn ($id): int => (int) $id)->all();

        $permissionMap = [];
        if ($roleIds !== []) {
            $permissionRows = DB::table('role_permission as rp')
                ->join('permissions as p', 'rp.permission_id', '=', 'p.id')
                ->whereIn('rp.role_id', $roleIds)
                ->get(['rp.role_id', 'p.id', 'p.slug']);

            foreach ($permissionRows as $row) {
                $roleId = (int) ($row->role_id ?? 0);
                $permissionMap[$roleId] ??= ['ids' => [], 'slugs' => []];
                $permissionMap[$roleId]['ids'][] = (int) ($row->id ?? 0);
                $permissionMap[$roleId]['slugs'][] = (string) ($row->slug ?? '');
            }
        }

        $userCounts = $roleIds === []
            ? collect()
            : DB::table('user_role')
                ->select('role_id', DB::raw('count(*) as total'))
                ->whereIn('role_id', $roleIds)
                ->groupBy('role_id')
                ->pluck('total', 'role_id');

        return [
            'roles' => $roles
                ->map(function ($role) use ($permissionMap, $userCounts): array {
                    $roleId = (int) $role->id;
                    $mapped = $permissionMap[$roleId] ?? ['ids' => [], 'slugs' => []];
                    $slug = (string) $role->slug;

                    return [
                        'id' => $roleId,
                        'name' => (string) $role->name,
                        'slug' => $slug,
                        'description' => (string) ($role->description ?? ''),
                        'is_system' => (bool) ($role->is_system ?? false),
                        'is_locked' => $slug === 'super-admin',
                        'permission_ids' => array_values(array_unique($mapped['ids'])),
                        'permission_slugs' => array_values(array_filter(array_unique($mapped['slugs']))),
                        'user_count' => (int) ($userCounts[$roleId] ?? 0),
                        'created_at' => $role->created_at,
                        'updated_at' => $role->updated_at,
                    ];
                })
                ->values()
                ->all(),
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => $lastPage,
            ],
        ];
    }

    /**
     * @return array{permissions: array<int, array<string, mixed>>, permission_groups: array<int, array<string, mixed>>}
     */
    public function permissions(): array
    {
        if (! AccessControl::tablesReady()) {
            return [
                'permissions' => [],
                'permission_groups' => [],
            ];
        }

        $permissions = DB::table('permissions')
            ->orderBy('group')
            ->orderBy('name')
            ->get(['id', 'slug', 'name', 'group'])
            ->map(static fn ($permission): array => [
                'id' => (int) $permission->id,
                'slug' => (string) $permission->slug,
                'name' => (string) $permission->name,
                'group' => (string) ($permission->group ?? 'Lainnya'),
            ])
            ->values();

        return [
            'permissions' => $permissions->all(),
            'permission_groups' => $permissions
                ->groupBy('group')
                ->map(static fn ($items, string $group): array => [
                    'group' => $group,
                    'permissions' => $items->values()->all(),
                ])
                ->values()
                ->all(),
        ];
    }
}
