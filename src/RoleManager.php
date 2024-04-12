<?php

namespace Light\Rbac;

class RoleManager
{
    private Rbac $rbac;
    private $roles = [];
    public function __construct(Rbac $rbac)
    {
        $this->rbac = $rbac;
    }

    public function remove(string $name)
    {
        unset($this->roles[$name]);
        foreach ($this->roles as $role) {
            $role->removeChild($name);
        }

        foreach ($this->roles as $role) {
            $role->removeParent($name);
        }
    }

    public function add(string $name)
    {
        //empty name
        if ($name == "") {
            throw new \Exception("Role name cannot be empty");
        }

        $this->roles[$name] = new Role($this->rbac, $name);
    }

    public function has(string $name)
    {
        return isset($this->roles[$name]);
    }

    public function get(string $name): ?Role
    {
        if ($this->has($name)) {
            return $this->roles[$name];
        }
        return null;
    }

    /**
     * @return Role[]
     */
    public function all()
    {
        return array_values($this->roles);
    }
}
