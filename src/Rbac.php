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

    public function addUser(string $name): User
    {
        if (!$this->users->has($name)) {
            $this->users->add($name);
        }
        return $this->users->get($name);
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
}
