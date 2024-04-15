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
}
