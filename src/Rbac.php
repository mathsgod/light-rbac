<?php

namespace Light\Rbac;

class Rbac
{
    protected RoleManager $roles;
    protected UserManager $users;
    protected string $permission_separator = ':';

    public function __construct()
    {
        $this->roles = new RoleManager($this);
        $this->users = new UserManager($this);
    }

    public function setPermissionSeparator(string $separator): void
    {
        $this->permission_separator = $separator;
    }

    public function getPermissionSeparator(): string
    {
        return $this->permission_separator;
    }

    public function addUser(string $name, array $roles = []): User
    {
        if (!$this->users->has($name)) {
            $this->users->add($name);
        }
        $user = $this->users->get($name) ?? throw new \LogicException("User '{$name}' not found after add.");
        foreach ($roles as $role) {
            $user->addRole($role);
        }
        return $user;
    }

    public function addRole(string $name): Role
    {
        if (!$this->roles->has($name)) {
            $this->roles->add($name);
        }
        return $this->roles->get($name) ?? throw new \LogicException("Role '{$name}' not found after add.");
    }

    public function getRole(string $name): ?Role
    {
        return $this->roles->get($name);
    }

    /** @return Role[] */
    public function getRoles(): array
    {
        return $this->roles->all();
    }

    public function removeRole(string $name): void
    {
        $this->roles->remove($name);
    }

    public function hasRole(string $name): bool
    {
        return $this->roles->has($name);
    }

    public function hasUser(string $name): bool
    {
        return $this->users->has($name);
    }

    /** @return User[] */
    public function getUsers(): array
    {
        return $this->users->all();
    }

    public function removeUser(string $name): void
    {
        $this->users->remove($name);
    }

    public function getUser(string $name): ?User
    {
        return $this->users->get($name);
    }

    /** @return string[] */
    public function getPermissions(): array
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
