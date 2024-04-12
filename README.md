# Light RBAC

Light RBAC is a simple Role-Based Access Control (RBAC) system implemented in PHP.

## Class: Rbac

The `Rbac` class is the main class of the system. It manages roles and users.

### Properties

- `$roles`: An instance of `RoleManager` that manages all roles in the system.
- `$users`: An instance of `UserManager` that manages all users in the system.

### Methods

- `addUser(string $name, array $roles = []): User`: Adds a user with the given name and roles to the system. If the user already exists, it adds the roles to the existing user.
- `addRole(string $name)`: Adds a role with the given name to the system. If the role already exists, it returns the existing role.
- `getRole(string $name)`: Returns the role with the given name.

## Usage

First, create an instance of the `Rbac` class. Then, use the `addUser` and `addRole` methods to add users and roles to the system. Use the `getRole` method to retrieve a role by its name.

```php
$rbac = new \Light\Rbac\Rbac();
$rbac->addRole('admin');
$rbac->addUser('John Doe', ['admin']);
```


### Permissions

Permissions can be assigned to roles. A permission is a string that represents a certain action or resource. For example, `post:read`, `post:write`, `post:delete`, etc.

```php
$role = $rbac->addRole('admin');
$role->addPermission('post:read');
$role->addPermission('post:write');
```

### Checking Permissions

To check if a user has a certain permission, use the `can` method of the `User` class.

```php
$user = $rbac->addUser('John Doe', ['admin']);
if ($user->can('post:read')) {
    echo 'John Doe can read posts.';
}
```

### Checking Roles

To check if a user has a certain role, use the `hasRole` method of the `User` class.

```php
$user = $rbac->addUser('John Doe', ['admin']);
if ($user->hasRole('admin')) {
    echo 'John Doe is an admin.';
}
```

### Hierarchical Roles

```php
$admin = $rbac->addRole('admin');
$admin->addChild('editor');

$rbac->getRole('editor')->addPermission('post:read');

if($admin->can('post:read')) {
    echo 'Admin can read posts.';
}


```

