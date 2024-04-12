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
            if ($role = $this->rbac->getRole($role)) {
                $roles[] = $role;
            }
        }
        return $roles;
    }

    public function addPermission(string $action)
    {
        $this->permissions[] = $action;
        $this->permissions = array_unique($this->permissions);
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

    public function hasRole(string $role)
    {
        return in_array($role, $this->roles);
    }

    public function addRole(Role|string $role)
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

    public function can(string $permission)
    {
        if (in_array("*", $this->permissions)) {
            return true;
        }

        if (in_array($permission, $this->permissions)) {
            return true;
        }

        //split $permission by :
        $parts = explode(":", $permission);

        while (count($parts) > 1) {
            array_pop($parts);
            if (in_array(implode(":", $parts) . ":*", $this->permissions)) {
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

    public function getPermissions(bool $includeRoles = true)
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

    public function __debugInfo()
    {
        return [
            'name' => $this->name,
            'roles' => $this->roles,
            "permissions" => $this->getPermissions(false),
        ];
    }
}
