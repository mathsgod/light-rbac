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
        $role->addPermission("user.create");
        $role->addPermission("user.read");
        $role->addPermission("user.update");
        $role->addPermission("user.delete");

        $rbac->addUser("admin", ["administrators"]);

        $user = $rbac->getUser("admin");
        $this->assertTrue($user->is("administrators"));

        $this->assertTrue($user->can("user.create"));
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

        $rbac->getRole("editors")->addPermission("post.create");

        $rbac->addUser("admin", ["administrators"]);

        $user = $rbac->getUser("admin");
        $this->assertTrue($user->can("post.create"));
    }
}
