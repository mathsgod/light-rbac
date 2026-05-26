# Light RBAC

Light RBAC is a simple and lightweight Role-Based Access Control (RBAC) library for PHP.

## Requirements

- PHP >= 8.3

## Installation

```bash
composer require mathsgod/light-rbac
```

## Quick Start

```php
$rbac = new \Light\Rbac\Rbac();

$role = $rbac->addRole('admin');
$role->addPermission('post:read');
$role->addPermission('post:write');

$user = $rbac->addUser('John Doe', ['admin']);

if ($user->can('post:read')) {
    echo 'John Doe can read posts.';
}
```

---

## Permissions

Permissions are strings in the format `resource:action`, e.g. `post:read`, `post:write`, `post:delete`.

The default separator is `:`. You can change it with `setPermissionSeparator()`.

```php
$role = $rbac->addRole('admin');
$role->addPermission('post:read');
$role->addPermission('post:write');
```

### Wildcard Permissions

Use `*` to grant all permissions:

```php
$role->addPermission('*');

$role->can('post:read');   // true
$role->can('anything');    // true
```

Use `resource:*` to grant all actions on a specific resource:

```php
$role->addPermission('post:*');

$role->can('post:read');   // true
$role->can('post:delete'); // true
$role->can('user:read');   // false
```

---

## Roles

### Adding and Retrieving Roles

```php
$rbac->addRole('admin');
$rbac->addRole('editor');

$rbac->getRole('admin');    // returns Role object
$rbac->getRoles();          // returns all Role objects
$rbac->hasRole('admin');    // true
$rbac->removeRole('admin');
```

### Hierarchical Roles

A parent role inherits all permissions from its child roles.

```php
$admin = $rbac->addRole('admin');
$admin->addChild('editor');

$rbac->getRole('editor')->addPermission('post:read');

$admin->can('post:read'); // true — inherited from child
```

Multi-level hierarchies are supported:

```php
$admin->addChild('editor');
$rbac->getRole('editor')->addChild('viewer');
$rbac->getRole('viewer')->addPermission('post:read');

$admin->can('post:read'); // true
```

Cyclic inheritance is detected and throws an `Exception`:

```php
$admin->addChild('editor');
$rbac->getRole('editor')->addChild('admin'); // throws Exception
```

---

## Users

### Adding and Retrieving Users

```php
$user = $rbac->addUser('John Doe', ['admin', 'editor']);

$rbac->getUser('John Doe');  // returns User object
$rbac->getUsers();           // returns all User objects
$rbac->hasUser('John Doe'); // true
$rbac->removeUser('John Doe');
```

### Checking Permissions

```php
$user->can('post:read');  // checks via assigned roles
```

Users can also have direct permissions independent of roles:

```php
$user->addPermission('report:view');
$user->can('report:view'); // true
```

### Checking Roles

```php
$user->hasRole('admin');  // true if directly assigned
$user->is('admin');       // true if directly assigned OR inherited via hierarchy
```

```php
$admin = $rbac->addRole('admin');
$admin->addChild('editor');

$user = $rbac->addUser('John Doe', ['admin']);
$user->is('editor'); // true — inherited via hierarchy
```

### Fluent Interface

`addRole()` and `removeRole()` on `User` return `static` for chaining:

```php
$user->addRole('admin')->addRole('editor');
$user->removeRole('editor');
```

---

## Custom Permission Separator

The default separator is `:`. You can change it if needed:

```php
$rbac->setPermissionSeparator('/');

$role->addPermission('post/*');
$role->can('post/read'); // true
```

