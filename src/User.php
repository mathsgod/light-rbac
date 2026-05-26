<?php

namespace Light\Rbac;

use Exception;

class User
{
    private Rbac $rbac;
    public readonly string $name;

    public array $roles = [];
    public array $permissions = [];

    public function __construct(Rbac $rbac, string $name)
    {
        $this->rbac = $rbac;
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /** @return Role[] */
    public function getRoles(): array
    {
        $roles = [];
        foreach ($this->roles as $role) {
            if ($role = $this->rbac->getRole($role)) {
                $roles[] = $role;
            }
        }
        return $roles;
    }

    public function addPermission(string $action): void
    {
        $this->permissions[] = $action;
        $this->permissions = array_unique($this->permissions);
    }

    public function is(string $role): bool
    {
        if (in_array($role, $this->roles)) {
            return true;
        }

        foreach ($this->getRoles() as $r) {
            if ($r->hasDescendant($role)) {
                return true;
            }
        }
        return false;
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles);
    }

    public function addRole(Role|string $role): static
    {
        if ($role instanceof Role) {
            $role = $role->getName();
        }

        if (in_array($role, $this->roles)) {
            return $this;
        }

        if (!$this->rbac->hasRole($role)) {
            //auto create role
            $this->rbac->addRole($role);
        }

        $this->roles[] = $role;
        return $this;
    }

    public function removeRole(Role|string $role): static
    {
        if ($role instanceof Role) {
            $role = $role->getName();
        }

        $key = array_search($role, $this->roles);
        if ($key !== false) {
            unset($this->roles[$key]);
        }
        return $this;
    }

    public function can(string $permission): bool
    {
        $permission_seprator  = $this->rbac->getPermissionSeparator();
        if (in_array("*", $this->permissions)) {
            return true;
        }

        if (in_array($permission, $this->permissions)) {
            return true;
        }

        //split $permission by $permission_seprator
        $parts = explode($permission_seprator, $permission);

        while (count($parts) > 1) {
            array_pop($parts);
            if (in_array(implode($permission_seprator, $parts) . $permission_seprator . "*", $this->permissions)) {
                return true;
            }
        }

        foreach ($this->roles as $role) {
            $role = $this->rbac->getRole($role);
            if ($role->can($permission)) {
                return true;
            }
        }
        return false;
    }

    /** @return string[] */
    public function getPermissions(bool $includeRoles = true): array
    {
        $permissions = $this->permissions;

        if ($includeRoles) {
            foreach ($this->getRoles() as $role) {
                foreach ($role->getPermissions() as $permission) {
                    $permissions[] = $permission;
                }
            }
        }

        return array_values(array_unique($permissions));
    }

    public function __debugInfo(): array
    {
        return [
            'name' => $this->name,
            'roles' => $this->roles,
            "permissions" => $this->getPermissions(false),
        ];
    }
}
