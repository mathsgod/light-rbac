<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function testAddUser(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $user = $rbac->addUser("admin");
        $this->assertEquals("admin", $user->getName());
    }

    // BEGIN: Additional test cases
    public function testAddUserWithEmptyName(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $this->expectException(Exception::class);
        $rbac->addUser("");
    }

    public function testAddUserWithSpecialCharacters(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $user = $rbac->addUser("!@#$%^&*()");
        $this->assertEquals("!@#$%^&*()", $user->getName());
    }
    // END: Additional test cases

    public function testRemoveUser(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $user = $rbac->addUser("admin");
        $rbac->removeUser("admin");
        $this->assertNull($rbac->getUser("admin"));
    }

    public function testAddRoleWithPermissions(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $role = $rbac->addRole("administrators");
        $role->addPermission("user:create");
        $role->addPermission("user:read");
        $role->addPermission("user:update");
        $role->addPermission("user:delete");

        $rbac->addUser("admin", ["administrators"]);

        $user = $rbac->getUser("admin");
        $this->assertTrue($user->is("administrators"));

        $this->assertTrue($user->can("user:create"));
    }

    public function testHasRole(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $role = $rbac->addRole("administrators");
        $rbac->addUser("admin", ["administrators"]);

        $user = $rbac->getUser("admin");
        $this->assertTrue($user->hasRole("administrators"));
    }

    public function testIsRole(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $role = $rbac->addRole("administrators");
        $role->addChild("editors");

        $rbac->addUser("admin", ["administrators"]);

        $user = $rbac->getUser("admin");
        $this->assertTrue($user->is("administrators"));
        $this->assertTrue($user->is("editors"));
    }

    public function testHierarchyRolePermissions(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $admin = $rbac->addRole("administrators");
        $admin->addChild("editors");

        $rbac->getRole("editors")->addPermission("post:create");

        $rbac->addUser("admin", ["administrators"]);

        $user = $rbac->getUser("admin");
        $this->assertTrue($user->can("post:create"));
    }

    public function testRemoveRole(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $rbac->addRole("admin");
        $user = $rbac->addUser("alice", ["admin"]);

        $user->removeRole("admin");

        $this->assertFalse($user->hasRole("admin"));
    }

    public function testAddRoleAsObject(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $role = $rbac->addRole("admin");
        $user = $rbac->addUser("alice");

        $user->addRole($role);

        $this->assertTrue($user->hasRole("admin"));
    }

    public function testDuplicateRoleNotDuplicated(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $rbac->addRole("admin");
        $user = $rbac->addUser("alice");
        $user->addRole("admin");
        $user->addRole("admin");

        $this->assertCount(1, $user->roles);
    }

    public function testGetRoles(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $rbac->addRole("admin");
        $rbac->addRole("editor");
        $user = $rbac->addUser("alice", ["admin", "editor"]);

        $roles = $user->getRoles();
        $this->assertCount(2, $roles);
        $this->assertInstanceOf(Light\Rbac\Role::class, $roles[0]);
    }

    public function testUserDirectPermission(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $user = $rbac->addUser("alice");
        $user->addPermission("post:read");

        $this->assertTrue($user->can("post:read"));
        $this->assertFalse($user->can("post:write"));
    }

    public function testUserCanWithWildcard(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $user = $rbac->addUser("alice");
        $user->addPermission("*");

        $this->assertTrue($user->can("post:read"));
        $this->assertTrue($user->can("anything"));
    }

    public function testUserCanWithResourceWildcard(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $user = $rbac->addUser("alice");
        $user->addPermission("post:*");

        $this->assertTrue($user->can("post:read"));
        $this->assertTrue($user->can("post:write"));
        $this->assertFalse($user->can("user:read"));
    }

    public function testGetPermissionsIncludesRolePermissions(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $role = $rbac->addRole("admin");
        $role->addPermission("post:read");
        $user = $rbac->addUser("alice", ["admin"]);
        $user->addPermission("user:read");

        $permissions = $user->getPermissions(true);
        $this->assertContains("post:read", $permissions);
        $this->assertContains("user:read", $permissions);
    }

    public function testGetPermissionsWithoutRoles(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $role = $rbac->addRole("admin");
        $role->addPermission("post:read");
        $user = $rbac->addUser("alice", ["admin"]);
        $user->addPermission("user:read");

        $permissions = $user->getPermissions(false);
        $this->assertNotContains("post:read", $permissions);
        $this->assertContains("user:read", $permissions);
    }
}
