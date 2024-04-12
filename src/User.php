<?php

namespace Light\Rbac;

use Exception;

class User
{
    private Rbac $rbac;
    public  $name;

    public $roles = [];
    public $permissions = [];

    public function __construct(Rbac $rbac, string $name)
    {
        $this->rbac = $rbac;
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getRoles()
    {
        $roles = [];
        foreach ($this->roles as $role) {
            $roles[] = $this->rbac->getRole($role);
        }
        return $roles;
    }

    public function addPermission(string $obj, string $action)
    {
        $this->permissions[$obj][$action] = true;
    }

    public function is(string $role)
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

    public function addRole(Role|string $role)
    {
        if ($role instanceof Role) {
            $role = $role->getName();
        }

        if (in_array($role, $this->roles)) {
            return $this;
        }

        $this->roles[] = $role;
        return $this;
    }

    public function removeRole(Role|string $role)
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

    public function can(string $obj, string $action)
    {
        if (isset($this->permissions[$obj]['*']) && $this->permissions[$obj]['*']) {
            return true;
        }

        if (isset($this->permissions[$obj][$action]) && $this->permissions[$obj][$action]) {
            return true;
        }

        foreach ($this->roles as $role) {
            $role = $this->rbac->getRole($role);
            if ($role->can($obj, $action)) {
                return true;
            }
        }
        return false;
    }

    public function getPermissions(bool $includeRoles = true)
    {
        $permissions = [];
        foreach ($this->permissions as $obj => $actions) {
            foreach ($actions as $action => $true) {
                $permissions[] = "$obj:$action";
            }
        }

        if ($includeRoles) {
            foreach ($this->getRoles() as $role) {
                foreach ($role->getPermissions() as $permission) {
                    $permissions[] = $permission;
                }
            }
        }

        return $permissions;
    }

    public function __debugInfo()
    {
        return [
            'name' => $this->name,
            'roles' => $this->roles,
            "permissions" => $this->getPermissions(false),
        ];
    }
}
