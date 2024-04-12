<?php

namespace Light\Rbac;

class UserManager
{
    protected Rbac $rbac;
    protected $users = [];

    public function __construct(Rbac $rbac)
    {
        $this->rbac = $rbac;
    }

    public function add(string $name)
    {
        $this->users[$name] = new User($this->rbac, $name);
    }

    public function has(string $name)
    {
        return isset($this->users[$name]);
    }

    public function get(string $name): ?User
    {
        if ($this->has($name)) {
            return $this->users[$name];
        }
        return null;
    }

    /**
     * @return User[]
     */
    public function all()
    {
        return array_values($this->users);
    }

    public function remove(string $name)
    {
        unset($this->users[$name]);
    }
}
