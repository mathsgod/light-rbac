<?php

namespace Light\Rbac;

class UserManager
{
    protected Rbac $rbac;
    protected array $users = [];

    public function __construct(Rbac $rbac)
    {
        $this->rbac = $rbac;
    }

    public function add(string $name): void
    {
        if ($name == "") {
            throw new \Exception("User name cannot be empty");
        }

        $this->users[$name] = new User($this->rbac, $name);
    }

    public function has(string $name): bool
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

    /** @return User[] */
    public function all(): array
    {
        return array_values($this->users);
    }

    public function remove(string $name): void
    {
        unset($this->users[$name]);
    }
}
