<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class RoleTest extends TestCase
{
    public function testAddRole(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $role = $rbac->addRole("admin");
        $this->assertEquals("admin", $role->getName());
    }

    // BEGIN: Additional test cases
    public function testAddRoleWithEmptyName(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $this->expectException(Exception::class);
        $rbac->addRole("");
    }

    public function testAddRoleWithSpecialCharacters(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $role = $rbac->addRole("!@#$%^&*()");
        $this->assertEquals("!@#$%^&*()", $role->getName());
    }
    // END: Additional test cases

    public function testRemoveRole(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $role = $rbac->addRole("admin");
        $rbac->removeRole("admin");
        $this->assertNull($rbac->getRole("admin"));
    }

    public function testHierarchyRole(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $admin = $rbac->addRole("admin");
        $admin->addChild("editor");

        $rbac->getRole("editor")->addPermission("post", "create");

        $this->assertTrue($admin->can("post", "create"));
    }

    public function testPermissions(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $role = $rbac->addRole("admin");
        $role->addPermission("post", "create");
        $role->addPermission("post", "read");
        $role->addPermission("post", "update");
        $role->addPermission("post", "delete");
        $this->assertEquals(["post:create", "post:read", "post:update", "post:delete"], $role->getPermissions());
        $this->assertTrue($role->can("post", "create"));
    }

    public function testAstriskPermissions(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $role = $rbac->addRole("admin");
        $role->addPermission("*"); // all permissions
        $this->assertTrue($role->can("post", "create"));

        $editor = $rbac->addRole("editor");
        $editor->addPermission("post");

        $this->assertTrue($editor->can("post", "create"));
    }
}
