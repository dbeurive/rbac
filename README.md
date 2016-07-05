# Description

This package contains a basic implementation of a role-based access control (RBAC) mechanism.

Please note that role-based access control (RBAC) differs from an access control list (ACL).

In general terms:

* An access control list (ACL) specifies which users are granted access to resources, as well as what operations are allowed on given resources.
  For instance, if a resource has an ACL that contains (Alice: read,write; Bob: read), this would give Alice permission to read and write the file and Bob to only read it.
* Role-based access control (RBAC) is an approach to restricting system access to authorized users.
  In role based access control, the role hierarchy defines an inheritance relationship among roles.
  Higher-level roles subsume permissions owned by sub-roles.

Please note that, because Higher-level roles subsume permissions owned by sub-roles, role-based access control is unsuitable to manage individuals' ownerships over resources.

# Synopsis

```php
// Specify the hierarchy of roles.

$hierarchy = new RolesHierarchy("super-admin");
$hierarchy->addSubRole("admin")
            ->addSubRole("admin-bouygues")
              ->addSubRole("user-bouygues")
              ->up()
            ->up()
            ->addSubRole("admin-orange")
              ->addSubRole("user-orange")
              ->up()
            ->up()
          ->up()
            ->addSubRole("other-admin")->up()
          ->up();

// Test a given role.

if ($hierarchy->cmp("super-admin", "admin") > 0) {
    // "super-user" can access resources managed by "admin".
}
```

![Example](https://github.com/dbeurive/rbac/blob/master/doc/example.gif)

# API overview

## construct($inToLevelRole)

Construct a new hierarchy of roles. The argument <code>$inToLevelRole</code> represents the name of the
role a the top of the hierarchy.

## addSubRole($inRole)

Add a role to the level below the current level. The current level becomes the newly added sub level.

## up()

Go back one level up from the current role.

## cmp($inRole, $inOtherRole)

Test if a given role (<code>$inRole</code>) can access resources managed by another role (<code>$inOtherRole</code>).

* if <code>$inRole</code> subsumes permissions owned by <code>$inOtherRole</code>, then the method
returns the value +1.
* If <code>$inRole</code> and <code>$inOtherRole</code>, then the method returns the value 0.
* if <code>$inOtherRole</code> subsumes permissions owned by <code>$inRole</code>, then the method
returns the value -1.

## toDot()

Generate the [GraphViz](http://www.graphviz.org) representation of the hierarchy of roles.

The method returns a string that represents the DOT representation of the tree.

Assuming that you store this string in the file "<code>tree.dot</code>", then you can get the graphical
representation of the tree with the following command: <code>dot -Tgif -Ograph tree.dot</code>

