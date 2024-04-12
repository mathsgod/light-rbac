<?php

namespace Light\Rbac;

class Rbac
{
    public $roles = null;
    public $users = null;

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


}
