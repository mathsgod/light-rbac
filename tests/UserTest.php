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
        $role->addPermission("user", "create");
        $role->addPermission("user", "read");
        $role->addPermission("user", "update");
        $role->addPermission("user", "delete");

        $rbac->addUser("admin", ["administrators"]);

        $user = $rbac->getUser("admin");
        $this->assertTrue($user->is("administrators"));

        $this->assertTrue($user->can("user", "create"));
    }
}
