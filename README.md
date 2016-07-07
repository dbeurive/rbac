# Description

This package contains a basic implementation of a role-based access control (RBAC) mechanism.

Please note that role-based access control (RBAC) differs from an access control list (ACL).

In general terms:

* An access control list (ACL) specifies which users are granted access to resources, as well as what operations are allowed on given resources.
  For instance, if a resource has an ACL that contains (Alice: read,write; Bob: read), this would give Alice permission to read and write the file and Bob to only read it.
* Role-based access control (RBAC) is an approach to restricting system access to authorized users.
  In role based access control, the role hierarchy defines an inheritance relationship among roles.
  Higher-level roles subsume permissions owned by sub-roles.

> Please note that, because Higher-level roles subsume permissions owned by sub-roles, role-based access control is **unsuitable** to manage individuals'
> ownerships over resources.

# Installation

Form the command line:

    composer require dbeurive/rbac
    
Or, from within your file `composer.json`:

    "require": {
        "dbeurive/rbac": "*"
    }

# Synopsis

```php
use dbeurive\Rbac\RolesHierarchy;

// Specify the hierarchy of roles using the builder

$hierarchy = new RolesHierarchy("super-admin");
$hierarchy
    ->addSubRole("admin")
        ->addSubRole("admin-bouygues")
            ->addSubRole("user-bouygues")
            ->up()
        ->up()
        ->addSubRole("admin-orange")
            ->addSubRole("user-orange")
            ->up()
        ->up()
    ->up()
    ->addSubRole("other-admin");

// Test a given role.

if ($hierarchy->canAccessResource("super-admin", "admin")) {
    // "super-user" can access resources managed by "admin".
}
```

Please note that you can also specify the hierarchy through an array:

```php
$hierarchy = array(
     'role'   => 'super-admin',
     'access' => array(
         array(
             'role'   => 'admin',
             'access' =>  array(
                 array(
                     'role'   => 'admin-bouygues',
                     'access' => array(
                         array(
                             'role'   => 'user-bouygues',
                             'access' => array()
                         )
                     )
                 )
             )
         ),
         array(
             'role'   => 'admin-orange',
             'access' => array(
                 array(
                     'role'   => 'user-orange',
                     'access' => array()
                 )
             )
         ),
         array(
             'role'   => 'other-admin',
             'access' => array()
         )
     )
);

$this->__hierarchyArray = new RolesHierarchy($hierarchy); 
```

> NOTE: the key `access` ALWAYS points to an **array of arrays**.

Below, the graphical representation of the tree.

![Example](https://github.com/dbeurive/rbac/blob/master/doc/example.gif)

# API overview

## construct($inHierarchyOrTopRole)

Construct a new hierarchy of roles.

The argument `$inHierarchyOrTopRole` may be a string or an array.

* If `$inHierarchyOrTopRole` is a string: it represents the name of the role a the top of the hierarchy.
* If `$inHierarchyOrTopRole` is an array: it represents the entire hierarchy.

## addSubRole($inRole)

Add a role to the level below the current level. The current level becomes the newly added sub level.

## up()

Go back one level up from the current role.

## canAccessResource($inRole, $inOtherRole)

Test if a given role (`$inRole`) can access resources managed by another role (`$inOtherRole`).

If the role `$inRole` can access the resources managed by the other role (`$inOtherRole`), then the method
returns the value `true`. Otherwise it returns the value `false`.

## cmp($inRole, $inOtherRole)

Compare the positions of two roles within the hierarchy.

* if `$inRole` is "above" `$inOtherRole`, then the method returns the value +1.
* If `$inRole` and `$inOtherRole` are identical, then the method returns the value 0.
* if `$inOtherRole` is "below" `$inRole`, then the method returns the value -1.

## toDot()

Generate the [GraphViz](http://www.graphviz.org) representation of the hierarchy of roles.

The method returns a string that represents the DOT representation of the tree.

Assuming that you store this string in the file `tree.dot`, then you can get the graphical
representation of the tree with the following command:

    dot -Tgif -Ograph tree.dot

