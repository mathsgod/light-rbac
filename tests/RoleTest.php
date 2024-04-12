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
        $role = $rbac->addRole("");
        $this->assertEquals("", $role->getName());
    }

    public function testAddRoleWithSpecialCharacters(): void
    {
        $rbac = new Light\Rbac\Rbac;
        $role = $rbac->addRole("!@#$%^&*()");
        $this->assertEquals("!@#$%^&*()", $role->getName());
    }
    // END: Additional test cases
}
