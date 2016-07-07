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
     * @var bool This flag indicates whether the hierarchy has been fully created or not.
     */
    private $__init = false;

    /**
     * Create a hierarchy of roles.
     * @param array $inHierarchy The hierarchy of roles.
     * @throws \Exception
     */
    public function __construct($inHierarchyOrTopRole) {
        if (is_array($inHierarchyOrTopRole)) {
            $this->__tree = Tree::fromArray($inHierarchyOrTopRole, null, 'role', 'access');
            $this->__currentRole = $this->__tree->getRoot();
            $this->__index = $this->__tree->index(function ($x) { return $x; }, true);
            $this->__init = true;
        } elseif (is_string($inHierarchyOrTopRole)) {
            $this->__tree = new Tree($inHierarchyOrTopRole);
            $this->__currentRole = $this->__index[$inHierarchyOrTopRole] = $this->__tree->getRoot();
        } else {
            throw new \Exception("Invalid value for hierarchy' specification. Valid values are strings or arrays.");
        }
    }

    /**
     * Add a role to the level below the current level. The current level becomes the newly added sub level.
     * @param string $inRole Name of the sub role to add.
     * @return $this
     * @throws \Exception
     */
    public function addSubRole($inRole) {
        if ($this->__init) {
            throw new \Exception("The hierarchy of roles has already been created!");
        }

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
     * Compare the positions of two roles within the hierarchy.
     * @param string $inRole First role to compare.
     * @param string $inOtherRole Second role to compare.
     * @return int The method return one of the following values:
     *         -1: ($inRole < $inOtherRole)  $inRole is "below" $inOtherRole.
     *         0:  ($inRole == $inOtherRole) $inRole and $inOtherRole are identical.
     *         +1: ($inRole > $inOtherRole)  $inOtherRole is "above" $inRole.
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