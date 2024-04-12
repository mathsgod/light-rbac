<?php

use Light\Rbac\Rbac;

require_once 'vendor/autoload.php';

$rbac = new Rbac;
$rbac = new Light\Rbac\Rbac;
$users = $rbac->addRole("users");
$users->addPermission("post:1:*");

print_R($users->can("post:edit"));


