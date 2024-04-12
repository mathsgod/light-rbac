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

/*     public function testAddRoleWithPermissions(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $role = $rbac->addRole("admin", ["create", "read", "update", "delete"]);
        $this->assertEquals(["create", "read", "update", "delete"], $role->getPermissions());
    } */
}
