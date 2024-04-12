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