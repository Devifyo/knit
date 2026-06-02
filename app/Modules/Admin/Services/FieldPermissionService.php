<?php

declare(strict_types=1);

namespace App\Modules\Admin\Services;

use App\Models\FieldPermission;
use App\Models\User;

/**
 * Resolves field-level permissions for a user. A FieldPermission row restricts
 * a role's view/edit access to a specific field on an entity; with no row the
 * default is "allowed". When a user has multiple roles, the most permissive
 * wins. Owners always have full field access.
 */
class FieldPermissionService
{
    public function canView(User $user, string $entity, string $field): bool
    {
        return $this->resolve($user, $entity, $field, 'can_view');
    }

    public function canEdit(User $user, string $entity, string $field): bool
    {
        return $this->resolve($user, $entity, $field, 'can_edit');
    }

    /**
     * Strip fields the user may not view from a payload.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function filterViewable(User $user, string $entity, array $data): array
    {
        return array_filter(
            $data,
            fn (string $field) => $this->canView($user, $entity, $field),
            ARRAY_FILTER_USE_KEY,
        );
    }

    protected function resolve(User $user, string $entity, string $field, string $column): bool
    {
        if ($user->isOwner()) {
            return true;
        }

        $roles = $user->getRoleNames()->all();

        if ($roles === []) {
            return true;
        }

        // TenantScope keeps this within the current workspace.
        $rows = FieldPermission::query()
            ->where('entity', $entity)
            ->where('field_key', $field)
            ->whereIn('role', $roles)
            ->get();

        if ($rows->isEmpty()) {
            return true; // no restriction defined
        }

        return $rows->contains(fn (FieldPermission $p) => (bool) $p->{$column});
    }
}
