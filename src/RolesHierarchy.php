<?php

namespace dbeurive\Rbac;
use dbeurive\Tree\Tree;
use dbeurive\Tree\Node;

/**
 * Class RolesHierarchy
 *
 * This class represent a hierarchy of roles. The hierarchy is represented by a tree.
 *
 * @package dbeurive\acl
 */

class RolesHierarchy
{
    /**
     * @var Tree Tree of roles.
     */
    private $__tree;
    /**
     * @var Node Current role.
     */
    private $__currentRole;
    /**
     * @var array Roles' index.
     */
    private $__index = [];

    /**
     * Create a hierarchy of roles.
     * @param string $inSuperAdmin Role's name.
     * @throws \Exception
     */
    public function __construct($inSuperAdmin) {
        if (! is_string($inSuperAdmin)) {
            throw new \Exception("The given name is not a string!");
        }

        $this->__tree = new Tree($inSuperAdmin);
        $this->__currentRole = $this->__index[$inSuperAdmin] = $this->__tree->getRoot();
    }

    /**
     * Add a role to the level below the current level. The current level becomes the newly added sub level.
     * @param string $inRole Name of the sub role to add.
     * @return $this
     * @throws \Exception
     */
    public function addSubRole($inRole) {
        if (! is_string($inRole)) {
            throw new \Exception("Given rank is not a string!");
        }
        if (array_key_exists($inRole, $this->__index)) {
            throw new \Exception("Duplicated role detected ($inRole)!");
        }

        $this->__currentRole = $this->__index[$inRole] = $this->__currentRole->addChild($inRole);
        return $this;
    }

    /**
     * Go back one level up from the current role.
     * @return $this
     */
    public function up() {
        $this->__currentRole = $this->__currentRole->end();
        return $this;
    }

    /**
     * Compare two roles.
     * @param string $inRole First role to compare.
     * @param string $inOtherRole Second role to compare.
     * @return int The method return one of the following values:
     *         -1: ($inRole < $inOtherRole)  $inRole subsumes permissions owned by $inOtherRole.
     *         0:  ($inRole == $inOtherRole) $inRole and $inOtherRole are identical.
     *         +1: ($inRole > $inOtherRole)  $inOtherRole subsumes permissions owned by $inRole.
     * @throws \Exception
     */
    public function cmp($inRole, $inOtherRole) {
        if (! array_key_exists($inRole, $this->__index)) {
            throw new \Exception("Unknown role ($inRole)!");
        }
        if (! array_key_exists($inOtherRole, $this->__index)) {
            throw new \Exception("Unknown role ($inOtherRole)!");
        }

        /** @var Node $rank */
        $rank = $this->__index[$inRole];
        /** @var Node $required */
        $required = $this->__index[$inOtherRole];

        if ($rank === $required) {
            return 0;
        }
        return $rank->isAscendantOf($required) ? 1 : -1;
    }

    /**
     * Test if a given role ($inRole) can access resources managed by another role ($inOtherRole).
     * @param string $inRole Given role that need to access the resources.
     * @param string $inOtherRole The role that manages the resources.
     * @return bool If the role $inRole can access the resources managed by the other role ($inOtherRole), then the method
     *         returns the value true. Otherwise it returns the value false.
     * @throws \Exception
     */
    public function canAccessResource($inRole, $inOtherRole) {
        return $this->cmp($inRole, $inOtherRole) >= 0;
    }

    /**
     * Export the roles' hierarchy into DOT graph, so it can be rendered by GraphViz.
     * @return string The method returns the DOT graph (dot -Tgif -Ograph file.dot).
     */
    public function toDot() {
        return $this->__tree->toDot();
    }
}