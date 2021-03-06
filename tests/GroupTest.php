<?php

namespace atk4\report\tests;


/**
 * Tests basic create, update and delete operatiotns
 */
class GroupTest extends \atk4\schema\PHPUnit_SchemaTestCase
{

    private $init_db = 
        [
            'client' => [
                ['name' => 'Vinny'],
                ['name' => 'Zoe'],
            ],
            'invoice' => [
                ['client_id'=>1, 'name'=>'chair purchase', 'amount'=>4],
                ['client_id'=>1, 'name'=>'table purchase', 'amount'=>15],
                ['client_id'=>2, 'name'=>'chair purchase', 'amount'=>4],
            ],
            'payment' => [
                ['client_id'=>1, 'name'=>'prepay', 'amount'=>10],
                ['client_id'=>2, 'name'=>'full pay', 'amount'=>4],
            ],
        ];

    protected $g;

    function setUp() {
        parent::setUp();
        $m1 = new Invoice($this->db);
        $m1->getRef('client_id')->addTitle();
        $this->g = new \atk4\report\GroupModel($m1);
        $this->g->addField('client');
    }

    public function testGroupSelect()
    {
        $g = $this->g;

        $g->groupBy(['client_id'], ['c'=>'count(*)']);

        $this->assertEquals(
            [
                ['client'=>'Vinny','client_id'=>1, 'c'=>2],
                ['client'=>'Zoe','client_id'=>2, 'c'=>1],
            ],
            $g->export()
        );
    }

    public function testGroupSelect2()
    {
        $g = $this->g;

        $g->groupBy(['client_id'], [
            //'s'=>'sum([amount])',
            'amount'=>'sum([])',
        ]);

        $this->assertEquals(
            [
                ['client'=>'Vinny','client_id'=>1, 'amount'=>19],
                ['client'=>'Zoe','client_id'=>2, 'amount'=>4],
            ],
            $g->export()
        );
    }

    public function testGroupSelect3()
    {
        $g = $this->g;

        $g->groupBy(['client_id'], [
            's'=>'sum([amount])',
            'amount'=>'sum([])',
        ]);

        $this->assertEquals(
            [
                ['client'=>'Vinny','client_id'=>1, 'amount'=>19, 's'=>19],
                ['client'=>'Zoe','client_id'=>2, 'amount'=>4, 's'=>4],
            ],
            $g->export()
        );
    }

    public function testGroupSelectExpr()
    {
        $g = $this->g;

        $g->groupBy(['client_id'], [
            's'=>'sum([amount])',
            'amount'=>'sum([])',
        ]);

        $g->addExpression('double', '[s]+[amount]');

        $this->assertEquals(
            [
                ['client'=>'Vinny','client_id'=>1, 'amount'=>19, 's'=>19, 'double'=>38],
                ['client'=>'Zoe','client_id'=>2, 'amount'=>4, 's'=>4, 'double'=>8],
            ],
            $g->export()
        );
    }

    public function testGroupSelectCondition()
    {
        $g = $this->g;
        $g->master_model->addCondition('name', 'chair purchase');

        $g->groupBy(['client_id'], [
            's'=>'sum([amount])',
            'amount'=>'sum([])',
        ]);

        $g->addExpression('double', '[s]+[amount]');

        $this->assertEquals(
            [
                ['client'=>'Vinny','client_id'=>1, 'amount'=>4, 's'=>4, 'double'=>8],
                ['client'=>'Zoe','client_id'=>2, 'amount'=>4, 's'=>4, 'double'=>8],
            ],
            $g->export()
        );
    }

    public function testGroupSelectCondition2()
    {
        $g = $this->g;

        $g->groupBy(['client_id'], [
            's'=>'sum([amount])',
            'amount'=>'sum([])',
        ]);

        $g->addExpression('double', '[s]+[amount]');
        $g->addCondition('double', '>', 10);

        $this->assertEquals(
            [
                ['client'=>'Vinny','client_id'=>1, 'amount'=>19, 's'=>19, 'double'=>38],
            ],
            $g->export()
        );
    }

    public function testGroupSelectCondition3()
    {
        $g = $this->g;

        $g->groupBy(['client_id'], [
            's'=>'sum([amount])',
            'amount'=>'sum([])',
        ]);

        $g->addExpression('double', '[s]+[amount]');
        $g->addCondition('double', 38);

        $this->assertEquals(
            [
                ['client'=>'Vinny','client_id'=>1, 'amount'=>19, 's'=>19, 'double'=>38],
            ],
            $g->export()
        );
    }

    public function testGroupSelectCondition4()
    {
        $g = $this->g;

        $g->groupBy(['client_id'], [
            's'=>'sum([amount])',
            'amount'=>'sum([])',
        ]);

        $g->addExpression('double', '[s]+[amount]');
        $g->addCondition('client_id', 2);

        $this->assertEquals(
            [
                ['client'=>'Zoe','client_id'=>2, 'amount'=>4, 's'=>4, 'double'=>8],
            ],
            $g->export()
        );
    }

    public function testGroupLimit()
    {
        $g = $this->g;

        $g->groupBy(['client_id'], [
            'amount'=>'sum([])',
        ]);
        $g->setLimit(1);

        $this->assertEquals(
            [
                ['client'=>'Vinny','client_id'=>1, 'amount'=>19],
            ],
            $g->export()
        );
    }

    public function testGroupLimit2()
    {
        $g = $this->g;

        $g->groupBy(['client_id'], [
            'amount'=>'sum([])',
        ]);
        $g->setLimit(1, 1);

        $this->assertEquals(
            [
                ['client'=>'Zoe','client_id'=>2, 'amount'=>4],
            ],
            $g->export()
        );
    }
}
