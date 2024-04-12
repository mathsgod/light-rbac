<?php

namespace Light\Rbac;

class Rbac
{
    protected $roles = null;
    protected $users = null;

    public function __construct()
    {
        $this->roles = new RoleManager($this);
        $this->users = new UserManager($this);
    }

    public function addUser(string $name, array $roles = []): User
    {
        if (!$this->users->has($name)) {
            $this->users->add($name);
        }
        $user = $this->users->get($name);
        foreach ($roles as $role) {
            $user->addRole($role);
        }
        return $user;
    }

    public function addRole(string $name)
    {
        if (!$this->roles->has($name)) {
            $this->roles->add($name);
        }
        return $this->roles->get($name);
    }

    public function getRole(string $name)
    {
        return $this->roles->get($name);
    }

    public function getRoles()
    {
        return $this->roles->all();
    }

    public function removeRole(string $name)
    {
        $this->roles->remove($name);
    }

    public function hasRole(string $name)
    {
        return $this->roles->has($name);
    }

    public function hasUser(string $name)
    {
        return $this->users->has($name);
    }

    public function getUsers()
    {
        return $this->users->all();
    }

    public function removeUser(string $name)
    {
        $this->users->remove($name);
    }

    public function getUser(string $name)
    {
        return $this->users->get($name);
    }

    public function getPermissions()
    {
        $permissions = [];
        foreach ($this->roles->all() as $role) {
            foreach ($role->getPermissions() as $permission) {
                $permissions[] = $permission;
            }
        }

        foreach ($this->users->all() as $user) {
            foreach ($user->getPermissions() as $permission) {
                $permissions[] = $permission;
            }
        }

        return array_values(array_unique($permissions));
    }
}
