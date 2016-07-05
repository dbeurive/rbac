<?php

use dbeurive\Rbac\RolesHierarchy;


class RolesHierarchyTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var RolesHierarchy Hierarchy of roles.
     */
    private $__hierarchy;

    protected function setUp() {

        $this->__hierarchy = new RolesHierarchy("super-admin");
        $this->__hierarchy->addSubRole("admin")
                                ->addSubRole("admin-bouygues")
                                    ->addSubRole("user-bouygues")->up()
                                ->up()
                                ->addSubRole("admin-orange")
                                    ->addSubRole("user-orange")->up()
                                ->up()
                            ->up()
                                ->addSubRole("other-admin")->up()
                            ->up();
    }

    public function testCmp() {

        $this->assertEquals(1,  $this->__hierarchy->cmp("super-admin", "admin"));
        $this->assertEquals(-1, $this->__hierarchy->cmp("admin", "super-admin"));

        $this->assertEquals(1, $this->__hierarchy->cmp("super-admin", "admin-bouygues"));
        $this->assertEquals(1, $this->__hierarchy->cmp("super-admin", "user-bouygues"));
        $this->assertEquals(1, $this->__hierarchy->cmp("super-admin", "admin-orange"));
        $this->assertEquals(1, $this->__hierarchy->cmp("super-admin", "user-orange"));

        $this->assertEquals(-1, $this->__hierarchy->cmp("admin-bouygues", "super-admin"));
        $this->assertEquals(-1, $this->__hierarchy->cmp("user-bouygues",  "super-admin"));
        $this->assertEquals(-1, $this->__hierarchy->cmp("admin-orange",   "super-admin"));
        $this->assertEquals(-1, $this->__hierarchy->cmp("user-orange",    "super-admin"));

        $this->assertEquals(1, $this->__hierarchy->cmp("admin-bouygues", "user-bouygues"));
        $this->assertEquals(1, $this->__hierarchy->cmp("admin-orange",   "user-orange"));

        $this->assertEquals(-1, $this->__hierarchy->cmp("user-bouygues", "admin-bouygues"));
        $this->assertEquals(-1, $this->__hierarchy->cmp("user-orange",   "admin-bouygues"));

        $this->assertEquals(0, $this->__hierarchy->cmp("super-admin", "super-admin"));
        $this->assertEquals(0, $this->__hierarchy->cmp("admin", "admin"));
        $this->assertEquals(0, $this->__hierarchy->cmp("admin-bouygues", "admin-bouygues"));
        $this->assertEquals(0, $this->__hierarchy->cmp("admin-orange", "admin-orange"));
        $this->assertEquals(0, $this->__hierarchy->cmp("user-bouygues", "user-bouygues"));
        $this->assertEquals(0, $this->__hierarchy->cmp("user-orange", "user-orange"));


        $this->assertTrue($this->__hierarchy->canAccessResource("super-admin", "admin"));
        $this->assertTrue($this->__hierarchy->canAccessResource("super-admin", "admin-bouygues"));
        $this->assertTrue($this->__hierarchy->canAccessResource("super-admin", "user-bouygues"));
        $this->assertTrue($this->__hierarchy->canAccessResource("super-admin", "admin-orange"));
        $this->assertTrue($this->__hierarchy->canAccessResource("super-admin", "user-orange"));
        $this->assertTrue($this->__hierarchy->canAccessResource("admin-bouygues", "user-bouygues"));
        $this->assertTrue($this->__hierarchy->canAccessResource("admin-orange",   "user-orange"));

        $this->assertFalse($this->__hierarchy->canAccessResource("admin", "super-admin"));
        $this->assertFalse($this->__hierarchy->canAccessResource("admin-bouygues", "super-admin"));
        $this->assertFalse($this->__hierarchy->canAccessResource("user-bouygues",  "super-admin"));
        $this->assertFalse($this->__hierarchy->canAccessResource("admin-orange",   "super-admin"));
        $this->assertFalse($this->__hierarchy->canAccessResource("user-orange",    "super-admin"));
        $this->assertFalse($this->__hierarchy->canAccessResource("user-bouygues", "admin-bouygues"));
        $this->assertFalse($this->__hierarchy->canAccessResource("user-orange",   "admin-bouygues"));
    }

    public function testToDot() {

        $dot = $this->__hierarchy->toDot();
        $fd = fopen(__DIR__ . DIRECTORY_SEPARATOR . "testToDot.dot", "w");
        fwrite($fd, $dot);
        fclose($fd);
        print "\nYou can generate the GraphViz representation by typing: dot -Tgif -Ograph testToDot.dot\n";
    }


}