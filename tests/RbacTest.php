<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class RbacTest extends TestCase
{


    public function testAddRole(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $role = $rbac->addRole("admin");
        $this->assertEquals("admin", $role->getName());
    }

    public function testGetPermissions()
    {
        $rbac = new Light\Rbac\Rbac;
        $rbac->setPermissionSeparator(':');
        $role = $rbac->addRole("admin");
        $role->addPermission("post:create");
        $role->addPermission("post:read");
        $role->addPermission("post:update");
        $role->addPermission("post:delete");

        $this->assertEquals(["post:create", "post:read", "post:update", "post:delete"], $rbac->getPermissions());
    }

    public function testGetRole(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $rbac->addRole("admin");
        $this->assertInstanceOf(Light\Rbac\Role::class, $rbac->getRole("admin"));
        $this->assertNull($rbac->getRole("nonexistent"));
    }

    public function testGetRoles(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $rbac->addRole("admin");
        $rbac->addRole("editor");
        $this->assertCount(2, $rbac->getRoles());
    }

    public function testHasRole(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $rbac->addRole("admin");
        $this->assertTrue($rbac->hasRole("admin"));
        $this->assertFalse($rbac->hasRole("editor"));
    }

    public function testRemoveRoleUpdatesLookup(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $rbac->addRole("admin");
        $rbac->removeRole("admin");
        $this->assertFalse($rbac->hasRole("admin"));
        $this->assertCount(0, $rbac->getRoles());
    }

    public function testGetUser(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $rbac->addUser("alice");
        $this->assertInstanceOf(Light\Rbac\User::class, $rbac->getUser("alice"));
        $this->assertNull($rbac->getUser("nonexistent"));
    }

    public function testGetUsers(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $rbac->addUser("alice");
        $rbac->addUser("bob");
        $this->assertCount(2, $rbac->getUsers());
    }

    public function testHasUser(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $rbac->addUser("alice");
        $this->assertTrue($rbac->hasUser("alice"));
        $this->assertFalse($rbac->hasUser("bob"));
    }

    public function testRemoveUserUpdatesLookup(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $rbac->addUser("alice");
        $rbac->removeUser("alice");
        $this->assertFalse($rbac->hasUser("alice"));
        $this->assertCount(0, $rbac->getUsers());
    }

    public function testGetPermissionSeparator(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $this->assertEquals(".", $rbac->getPermissionSeparator());
        $rbac->setPermissionSeparator(":");
        $this->assertEquals(":", $rbac->getPermissionSeparator());
    }

    public function testGetPermissionsIncludesUserDirectPermissions(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $user = $rbac->addUser("alice");
        $user->addPermission("post:read");
        $this->assertContains("post:read", $rbac->getPermissions());
    }

    public function testGetPermissionsDeduplicates(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $rbac->setPermissionSeparator(':');
        $role = $rbac->addRole("admin");
        $role->addPermission("post:read");
        $user = $rbac->addUser("alice", ["admin"]);
        $user->addPermission("post:read");

        $permissions = $rbac->getPermissions();
        $this->assertCount(1, array_keys($permissions, "post:read"));
    }
}
