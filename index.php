<?php

use Light\Rbac\Rbac;

require_once 'vendor/autoload.php';

$rbac = new Rbac;
$everyone = $rbac->addRole("everyone");
$everyone->addPermission("user", "view");

$admin = $rbac->addRole("administrators");
$admin->addPermission("user", "create");
$admin->addPermission("user", "update");

$admin->addChild("everyone");

$user = $rbac->addUser("admin")->addRole("administrators");;


    /* 
print_R($admin->getPermissions()) */;
