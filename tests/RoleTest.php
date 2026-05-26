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

        $rbac->getRole("editor")->addPermission("post:create");

        $this->assertTrue($admin->can("post:create"));
    }

    public function testPermissions(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $role = $rbac->addRole("admin");
        $role->addPermission("post:create");
        $role->addPermission("post:read");
        $role->addPermission("post:update");
        $role->addPermission("post:delete");
        $this->assertEquals(["post:create", "post:read", "post:update", "post:delete"], $role->getPermissions());
        $this->assertTrue($role->can("post:create"));
    }

    public function testRemoveChild(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $admin = $rbac->addRole("admin");
        $admin->addChild("editor");

        $admin->removeChild("editor");

        $this->assertEmpty($admin->children);
        $this->assertEmpty($rbac->getRole("editor")->parents);
    }

    public function testRemoveParent(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $admin = $rbac->addRole("admin");
        $admin->addChild("editor");

        $rbac->getRole("editor")->removeParent("admin");

        $this->assertEmpty($admin->children);
        $this->assertEmpty($rbac->getRole("editor")->parents);
    }

    public function testHasPermission(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $role = $rbac->addRole("admin");
        $role->addPermission("post:read");
        $this->assertTrue($role->hasPermission("post:read"));
        $this->assertFalse($role->hasPermission("post:write"));
    }

    public function testDuplicatePermissionNotDuplicated(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $role = $rbac->addRole("admin");
        $role->addPermission("post:read");
        $role->addPermission("post:read");
        $this->assertCount(1, $role->getPermissions(false));
    }

    public function testGetPermissionsWithoutChildren(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $admin = $rbac->addRole("admin");
        $admin->addChild("editor");
        $admin->addPermission("user:manage");
        $rbac->getRole("editor")->addPermission("post:read");

        $this->assertEquals(["user:manage"], $admin->getPermissions(false));
        $this->assertContains("post:read", $admin->getPermissions(true));
    }

    public function testAddChildAsObject(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $admin = $rbac->addRole("admin");
        $editor = $rbac->addRole("editor");
        $editor->addPermission("post:read");

        $admin->addChild($editor);

        $this->assertTrue($admin->can("post:read"));
    }

    public function testAddParent(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $admin = $rbac->addRole("admin");
        $admin->addPermission("user:manage");
        $editor = $rbac->addRole("editor");

        $editor->addParent("admin");

        $this->assertContains("admin", $editor->parents);
        $this->assertContains("editor", $admin->children);
    }

    public function testAddParentAsObject(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $admin = $rbac->addRole("admin");
        $editor = $rbac->addRole("editor");
        $editor->addPermission("post:read");

        $editor->addParent($admin);

        // parent (admin) inherits child (editor) permissions
        $this->assertTrue($admin->can("post:read"));
    }

    public function testGetChildren(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $admin = $rbac->addRole("admin");
        $admin->addChild("editor");

        $children = $admin->getChildren();
        $this->assertCount(1, $children);
        $this->assertInstanceOf(Light\Rbac\Role::class, $children[0]);
        $this->assertEquals("editor", $children[0]->getName());
    }

    public function testGetParents(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $admin = $rbac->addRole("admin");
        $admin->addChild("editor");

        $parents = $rbac->getRole("editor")->getParents();
        $this->assertCount(1, $parents);
        $this->assertEquals("admin", $parents[0]->getName());
    }

    public function testHasDescendant(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $admin = $rbac->addRole("admin");
        $admin->addChild("editor");
        $rbac->getRole("editor")->addChild("viewer");

        $this->assertTrue($admin->hasDescendant("editor"));
        $this->assertTrue($admin->hasDescendant("viewer"));
        $this->assertFalse($admin->hasDescendant("nonexistent"));
    }

    public function testHasAncestor(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $admin = $rbac->addRole("admin");
        $admin->addChild("editor");
        $rbac->getRole("editor")->addChild("viewer");

        $viewer = $rbac->getRole("viewer");
        $this->assertTrue($viewer->hasAncestor("editor"));
        $this->assertTrue($viewer->hasAncestor("admin"));
        $this->assertFalse($viewer->hasAncestor("nonexistent"));
    }

    public function testCyclicDetectionOnAddChild(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $admin = $rbac->addRole("admin");
        $admin->addChild("editor");

        $this->expectException(Exception::class);
        $rbac->getRole("editor")->addChild("admin");
    }

    public function testCyclicDetectionOnAddParent(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $admin = $rbac->addRole("admin");
        $admin->addChild("editor");

        $this->expectException(Exception::class);
        $admin->addParent("editor");
    }

    public function testAutoCreateRoleOnAddChild(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $admin = $rbac->addRole("admin");
        $admin->addChild("editor");

        $this->assertTrue($rbac->hasRole("editor"));
    }

    public function testDuplicateChildNotDuplicated(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $admin = $rbac->addRole("admin");
        $admin->addChild("editor");
        $admin->addChild("editor");

        $this->assertCount(1, $admin->children);
    }

    public function testMultiLevelHierarchyCan(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $admin = $rbac->addRole("admin");
        $admin->addChild("editor");
        $rbac->getRole("editor")->addChild("viewer");
        $rbac->getRole("viewer")->addPermission("post:read");

        $this->assertTrue($admin->can("post:read"));
    }

    public function testRemoveRoleCleanupChildren(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $admin = $rbac->addRole("admin");
        $admin->addChild("editor");

        $rbac->removeRole("editor");

        $this->assertEmpty($admin->children);
    }

    public function testAstriskPermissions(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $role = $rbac->addRole("admin");
        $role->addPermission("*"); // all permissions
        $this->assertTrue($role->can("postcreate"));

        $editor = $rbac->addRole("editor");
        $editor->addPermission("post:*");

        $this->assertTrue($editor->can("post:create"));
    }
}
