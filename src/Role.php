<?php

namespace Light\Rbac;

use Exception;

class Role
{
    private Rbac $rbac;
    public  $name;

    public $parents = [];
    public $children = [];
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

    public function can(string $permission)
    {
        if (in_array("*", $this->permissions)) {
            return true;
        }

        if (in_array($permission, $this->permissions)) {
            return true;
        }

        //split $action into resource and action
        $parts = explode(":", $permission, 2);

        if (in_array($parts[0] . ":*", $this->permissions)) {
            return true;
        }

        foreach ($this->getChild() as $child) {
            if ($child->can($permission)) {
                return true;
            }
        }

        return false;
    }

    public function addPermission(string $permission)
    {
        $this->permissions[] = $permission;
        $this->permissions = array_unique($this->permissions);
    }

    public function getPermissions(bool $children = true)
    {
        $permissions = $this->permissions;

        if ($children) {
            foreach ($this->getChild() as $child) {
                foreach ($child->getPermissions() as $permission) {
                    $permissions[] = $permission;
                }
            }
        }

        return array_values(array_unique($permissions));
    }

    public function addChild(Role|string $child)
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
        $this->rbac->getRole($child)->addParent($this);
    }


    public function addParent(Role|string $parent)
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
        $this->rbac->getRole($parent)->addChild($this);
    }

    function getParents()
    {
        $parents = [];
        foreach ($this->parents as $parent) {
            $parents[] = $this->rbac->getRole($parent);
        }
        return $parents;
    }

    function getChild()
    {
        $children = [];
        foreach ($this->children as $child) {
            $children[] = $this->rbac->getRole($child);
        }
        return $children;
    }

    function hasAncestor(string $role): bool
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

    function hasDescendant(string $role): bool
    {
        if (in_array($role, $this->children)) {
            return true;
        }

        foreach ($this->getChild() as $child) {
            if ($child->hasDescendant($role)) {
                return true;
            }
        }

        return false;
    }

    function removeChild(Role|string $child)
    {
        if ($child instanceof Role) {
            $child = $child->getName();
        }

        $key = array_search($child, $this->children);
        if ($key !== false) {
            unset($this->children[$key]);
        }

        if ($c = $this->rbac->getRole($child)) {
            $c->removeParent($this);
        }
    }

    function removeParent(Role|string $parent)
    {
        if ($parent instanceof Role) {
            $parent = $parent->getName();
        }

        $key = array_search($parent, $this->parents);
        if ($key !== false) {
            unset($this->parents[$key]);
        }

        if ($c = $this->rbac->getRole($parent)) {
            $c->removeChild($this);
        }
    }


    function __debugInfo()
    {
        return [
            'name' => $this->name,
            'parents' => $this->parents,
            'children' => $this->children,
            'permissions' => $this->getPermissions(false)
        ];
    }
}
