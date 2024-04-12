<?php

use Light\Rbac\Rbac;

require_once 'vendor/autoload.php';

$rbac = new Rbac;
$rbac = new Light\Rbac\Rbac;
$admin = $rbac->addRole("admin");
$admin->addChild("editor");

$rbac->getRole("editor")->addPermission("post", "create");



print_R($admin->can("post", "create"));

