<?php

use dbeurive\Rbac\RolesHierarchy;


class RolesHierarchyTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var RolesHierarchy Hierarchy of roles built from an array.
     */
    private $__hierarchyArray;
    /**
     * @var RolesHierarchy Hierarchy of roles built from the builder.
     */
    private $__hierarchyBuilder;
    private $__hierarchies = [];


    protected function setUp() {

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


        $this->__hierarchyBuilder = new RolesHierarchy("super-admin");
        $this->__hierarchyBuilder
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

        $this->__hierarchies = array($this->__hierarchyArray, $this->__hierarchyBuilder);
   }

    public function testCmpFromArray() {
        foreach ($this->__hierarchies as $_hierarchy) {
            $this->assertEquals(1, $_hierarchy->cmp("super-admin", "admin"));
            $this->assertEquals(-1, $_hierarchy->cmp("admin", "super-admin"));

            $this->assertEquals(1, $_hierarchy->cmp("super-admin", "admin-bouygues"));
            $this->assertEquals(1, $_hierarchy->cmp("super-admin", "user-bouygues"));
            $this->assertEquals(1, $_hierarchy->cmp("super-admin", "admin-orange"));
            $this->assertEquals(1, $_hierarchy->cmp("super-admin", "user-orange"));

            $this->assertEquals(-1, $_hierarchy->cmp("admin-bouygues", "super-admin"));
            $this->assertEquals(-1, $_hierarchy->cmp("user-bouygues", "super-admin"));
            $this->assertEquals(-1, $_hierarchy->cmp("admin-orange", "super-admin"));
            $this->assertEquals(-1, $_hierarchy->cmp("user-orange", "super-admin"));

            $this->assertEquals(1, $_hierarchy->cmp("admin-bouygues", "user-bouygues"));
            $this->assertEquals(1, $_hierarchy->cmp("admin-orange", "user-orange"));

            $this->assertEquals(-1, $_hierarchy->cmp("user-bouygues", "admin-bouygues"));
            $this->assertEquals(-1, $_hierarchy->cmp("user-orange", "admin-bouygues"));

            $this->assertEquals(0, $_hierarchy->cmp("super-admin", "super-admin"));
            $this->assertEquals(0, $_hierarchy->cmp("admin", "admin"));
            $this->assertEquals(0, $_hierarchy->cmp("admin-bouygues", "admin-bouygues"));
            $this->assertEquals(0, $_hierarchy->cmp("admin-orange", "admin-orange"));
            $this->assertEquals(0, $_hierarchy->cmp("user-bouygues", "user-bouygues"));
            $this->assertEquals(0, $_hierarchy->cmp("user-orange", "user-orange"));
        }
    }


    public function testCanAccessResourceFromArray() {
        foreach ($this->__hierarchies as $_hierarchy) {
            $this->assertTrue($_hierarchy->canAccessResource("super-admin", "admin"));
            $this->assertTrue($_hierarchy->canAccessResource("super-admin", "admin-bouygues"));
            $this->assertTrue($_hierarchy->canAccessResource("super-admin", "user-bouygues"));
            $this->assertTrue($_hierarchy->canAccessResource("super-admin", "admin-orange"));
            $this->assertTrue($_hierarchy->canAccessResource("super-admin", "user-orange"));
            $this->assertTrue($_hierarchy->canAccessResource("admin-bouygues", "user-bouygues"));
            $this->assertTrue($_hierarchy->canAccessResource("admin-orange", "user-orange"));

            $this->assertFalse($_hierarchy->canAccessResource("admin", "super-admin"));
            $this->assertFalse($_hierarchy->canAccessResource("admin-bouygues", "super-admin"));
            $this->assertFalse($_hierarchy->canAccessResource("user-bouygues", "super-admin"));
            $this->assertFalse($_hierarchy->canAccessResource("admin-orange", "super-admin"));
            $this->assertFalse($_hierarchy->canAccessResource("user-orange", "super-admin"));
            $this->assertFalse($_hierarchy->canAccessResource("user-bouygues", "admin-bouygues"));
            $this->assertFalse($_hierarchy->canAccessResource("user-orange", "admin-bouygues"));
        }
    }

    public function testToDot() {

        $index = 1;
        foreach ($this->__hierarchies as $_hierarchy) {
            $dot = $this->__hierarchyArray->toDot();
            $fd = fopen(__DIR__ . DIRECTORY_SEPARATOR . "testToDot${index}.dot", "w");
            fwrite($fd, $dot);
            fclose($fd);
            print "\nYou can generate the GraphViz representation by typing: dot -Tgif -Ograph testToDot${index}.dot\n";
            $index += 1;
        }
    }


}