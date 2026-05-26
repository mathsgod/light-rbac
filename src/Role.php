<?php

namespace Light\Rbac;

use Exception;

class Role
{
    private Rbac $rbac;
    public readonly string $name;

    public array $parents = [];
    public array $children = [];
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

    public function can(string $permission): bool
    {
        $permission_seprator  = $this->rbac->getPermissionSeparator();

        if (in_array("*", $this->permissions)) {
            return true;
        }

        if (in_array($permission, $this->permissions)) {
            return true;
        }

        //split $permission by :
        $parts = explode($permission_seprator, $permission);

        while (count($parts) > 1) {
            array_pop($parts);
            if (in_array(implode($permission_seprator, $parts) . $permission_seprator . "*", $this->permissions)) {
                return true;
            }
        }


        foreach ($this->getChildren() as $child) {
            if ($child->can($permission)) {
                return true;
            }
        }

        return false;
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions);
    }

    public function addPermission(string $permission): void
    {
        $this->permissions[] = $permission;
        $this->permissions = array_unique($this->permissions);
    }

    /** @return string[] */
    public function getPermissions(bool $children = true): array
    {
        $permissions = $this->permissions;

        if ($children) {
            foreach ($this->getChildren() as $child) {
                foreach ($child->getPermissions() as $permission) {
                    $permissions[] = $permission;
                }
            }
        }

        return array_values(array_unique($permissions));
    }

    public function addChild(Role|string $child): void
    {
        if ($child instanceof Role) {
            $child = $child->getName();
        }

        if ($this->hasAncestor($child)) {
            throw new Exception("Cyclic role inheritance detected: {$this->name} -> {$child}");
        }

        if (in_array($child, $this->children)) {
            return;
        }

        if (!$this->rbac->hasRole($child)) {
            //auto add role if not exists
            $this->rbac->addRole($child);
        }

        $this->children[] = $child;
        $this->rbac->getRole($child)?->addParent($this);
    }


    public function addParent(Role|string $parent): void
    {
        if ($parent instanceof Role) {
            $parent = $parent->getName();
        }

        if (in_array($parent, $this->parents)) {
            return;
        }

        if ($this->hasDescendant($parent)) {
            throw new Exception("Cyclic role inheritance detected: {$parent} -> {$this->name}");
        }

        if (!$this->rbac->hasRole($parent)) {
            //auto add role if not exists
            $this->rbac->addRole($parent);
        }

        $this->parents[] = $parent;
        $this->rbac->getRole($parent)?->addChild($this);
    }

    /** @return Role[] */
    public function getParents(): array
    {
        $parents = [];
        foreach ($this->parents as $parent) {
            $parents[] = $this->rbac->getRole($parent);
        }
        return $parents;
    }

    /** @return Role[] */
    public function getChildren(): array
    {
        $children = [];
        foreach ($this->children as $child) {
            $children[] = $this->rbac->getRole($child);
        }
        return $children;
    }

    public function hasAncestor(string $role): bool
    {
        if (in_array($role, $this->parents)) {
            return true;
        }

        foreach ($this->getParents() as $parent) {
            if ($parent->hasAncestor($role)) {
                return true;
            }
        }

        return false;
    }

    public function hasDescendant(string $role): bool
    {
        if (in_array($role, $this->children)) {
            return true;
        }

        foreach ($this->getChildren() as $child) {
            if ($child->hasDescendant($role)) {
                return true;
            }
        }

        return false;
    }

    public function removeChild(Role|string $child): void
    {
        if ($child instanceof Role) {
            $child = $child->getName();
        }

        $key = array_search($child, $this->children);
        if ($key !== false) {
            unset($this->children[$key]);
            if ($c = $this->rbac->getRole($child)) {
                $c->removeParent($this);
            }
        }
    }

    public function removeParent(Role|string $parent): void
    {
        if ($parent instanceof Role) {
            $parent = $parent->getName();
        }

        $key = array_search($parent, $this->parents);
        if ($key !== false) {
            unset($this->parents[$key]);
            if ($c = $this->rbac->getRole($parent)) {
                $c->removeChild($this);
            }
        }
    }

    public function __debugInfo(): array
    {
        return [
            'name' => $this->name,
            'parents' => $this->parents,
            'children' => $this->children,
            'permissions' => $this->getPermissions(false)
        ];
    }
}
