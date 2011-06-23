<?php
ini_set('include_path',
        ini_get('include_path')
        .PATH_SEPARATOR
        .dirname(__FILE__).'/../../lib');

require_once('SQL/Maker/Select.php');


class StatementTest extends PHPUnit_Framework_TestCase {

    public function testPrefixQuoteCharNameSepSimple() {
        $stmt = $this->ns(array('quote_char' => '`', 'name_sep' => '.'));
        $stmt->addSelect('*');
        $stmt->addFrom('foo');
        $this->assertEquals("SELECT *\nFROM `foo`", $stmt->asSql());
    }

    public function testPrefixQuoteCharNameSepSQL_CALC_FOUND_ROWS() {
        $stmt = $this->ns(array('quote_char' => '`', 'name_sep' => '.'));
        $stmt->prefix = 'SELECT SQL_CALC_FOUND_ROWS ';
        $stmt->addSelect('*');
        $stmt->addFrom('foo');
        $this->assertEquals("SELECT SQL_CALC_FOUND_ROWS *\nFROM `foo`", $stmt->asSql());
    }

    public function testPrefixQuoteCharNameSepNewlineSimple() {
        $stmt = $this->ns(array('quote_char' => '`', 'name_sep' => '.', 'new_line' => ' '));
        $stmt->addSelect('*');
        $stmt->addFrom('foo');
        $this->assertEquals("SELECT * FROM `foo`", $stmt->asSql());
    }

    public function testPrefixQuoteCharNameSepNewlineSQL_CALC_FOUND_ROWS() {
        $stmt = $this->ns(array('quote_char' => '`', 'name_sep' => '.', 'new_line' => ' '));
        $stmt->prefix = 'SELECT SQL_CALC_FOUND_ROWS ';
        $stmt->addSelect('*');
        $stmt->addFrom('foo');
        $this->assertEquals("SELECT SQL_CALC_FOUND_ROWS * FROM `foo`", $stmt->asSql());
    }


    public function testFromQuoteCharNameSepSingle() {
        $stmt = $this->ns(array('quote_char' => '`', 'name_sep' => '.'));
        $stmt->addFrom('foo');
        $this->assertEquals('FROM `foo`', $stmt->asSql());
    }

    public function testFromQuoteCharNameSepMulti() {
        $stmt = $this->ns(array('quote_char' => '`', 'name_sep' => '.'));
        $stmt->addFrom( 'foo' );
        $stmt->addFrom( 'bar' );
        $this->assertEquals("FROM `foo`, `bar`", $stmt->asSql());
    }

    public function testFromQuoteCharNameSepMultiPlusAlias() {
        $stmt = $this->ns(array('quote_char' => '`', 'name_sep' => '.'));
        $stmt->addFrom('foo', 'f');
        $stmt->addFrom('bar', 'b');
        $this->assertEquals("FROM `foo` `f`, `bar` `b`", $stmt->asSql());
    }


    public function testJoinQuoteCharNameSepInnerJoin() {
        $stmt = $this->ns(array('quote_char' => '`', 'name_sep' => '.'));
        $stmt->addJoin(
                       'foo',
                       array(
                             'type' => 'inner',
                             'table' => 'baz',
                             )
                       );
        $this->assertEquals("FROM `foo` INNER JOIN `baz`", $stmt->asSql());
    }

    public function testJoinQuoteCharNameSepInnerJoinWithCondition() {
        $stmt = $this->ns(array('quote_char' => '`', 'name_sep' => '.'));
        $stmt->addJoin(
                       'foo',
                       array(
                             'type'      => 'inner',
                             'table'     => 'baz',
                             'condition' => 'foo.baz_id = baz.baz_id',
                             )
                       );
        $this->assertEquals("FROM `foo` INNER JOIN `baz` ON foo.baz_id = baz.baz_id", $stmt->asSql());
    }

    public function testJoinQuoteCharNameSepFromAndInnerJoinWithCondition() {
        $stmt = $this->ns(array('quote_char' => '`', 'name_sep' => '.'));
        $stmt->addFrom( 'bar' );
        $stmt->addJoin(
                       'foo',
                       array(
                             'type'      => 'inner',
                             'table'     => 'baz',
                             'condition' => 'foo.baz_id = baz.baz_id'
                             )
                       );
        $this->assertEquals("FROM `foo` INNER JOIN `baz` ON foo.baz_id = baz.baz_id, `bar`", $stmt->asSql());
    }

    public function testJoinQuoteCharNameSepTestCaseForBugFoundWhereAddJoinIsCalledTwice() {
        $stmt = $this->ns(array('quote_char' => '`', 'name_sep' => '.'));
        $stmt->addJoin(
                       'foo',
                       array(
                             'type'      => 'inner',
                             'table'     => 'baz',
                             'alias'     => 'b1',
                             'condition' => 'foo.baz_id = b1.baz_id AND b1.quux_id = 1'
                             )
                       );
        $stmt->addJoin(
                       'foo',
                       array(
                             'type'      => 'left',
                             'table'     => 'baz',
                             'alias'     => 'b2',
                             'condition' => 'foo.baz_id = b2.baz_id AND b2.quux_id = 2'
                             )
                       );

        $this->assertEquals("FROM `foo` INNER JOIN `baz` `b1` ON foo.baz_id = b1.baz_id AND b1.quux_id = 1 LEFT JOIN `baz` `b2` ON foo.baz_id = b2.baz_id AND b2.quux_id = 2", $stmt->asSql());
    }

    public function testJoinQuoteCharNameSepTestCaseAddingAnotherTableOntoTheWholeMess() {
        $stmt = $this->ns(array('quote_char' => '`', 'name_sep' => '.'));
        $stmt->addJoin(
                       'foo',
                       array(
                             'type'      => 'inner',
                             'table'     => 'baz',
                             'alias'     => 'b1',
                             'condition' => 'foo.baz_id = b1.baz_id AND b1.quux_id = 1'
                             )
                       );
        $stmt->addJoin(
                       'foo',
                       array(
                             'type'      => 'left',
                             'table'     => 'baz',
                             'alias'     => 'b2',
                             'condition' => 'foo.baz_id = b2.baz_id AND b2.quux_id = 2'
                             )
                       );
        $stmt->addJoin(
                       'quux',
                       array(
                             'type'      => 'inner',
                             'table'     => 'foo',
                             'alias'     => 'f1',
                             'condition'  => 'f1.quux_id = quux.q_id'
                             )
                       );
        $this->assertEquals("FROM `foo` INNER JOIN `baz` `b1` ON foo.baz_id = b1.baz_id AND b1.quux_id = 1 LEFT JOIN `baz` `b2` ON foo.baz_id = b2.baz_id AND b2.quux_id = 2 INNER JOIN `foo` `f1` ON f1.quux_id = quux.q_id", $stmt->asSql());
    }


    public function testGroupByQuoteCharNameSepSingleBareGroupBy() {
        $stmt = $this->ns(array('quote_char' => '`', 'name_sep' => '.'));
        $stmt->addFrom( 'foo' );
        $stmt->addGroupBy('baz');
        $this->assertEquals("FROM `foo`\nGROUP BY `baz`", $stmt->asSql());
    }

    public function testGroupByQuoteCharNameSepSingleGroupByWithDesc() {
        $stmt = $this->ns(array('quote_char' => '`', 'name_sep' => '.'));
        $stmt->addFrom('foo');
        $stmt->addGroupBy('baz', 'DESC');
        $this->assertEquals("FROM `foo`\nGROUP BY `baz` DESC", $stmt->asSql());
    }

    public function testGroupByQuoteCharNameSepMultipleGroupBy() {
        $stmt = $this->ns(array('quote_char' => '`', 'name_sep' => '.'));
        $stmt->addFrom( 'foo' );
        $stmt->addGroupBy('baz');
        $stmt->addGroupBy('quux');
        $this->assertEquals("FROM `foo`\nGROUP BY `baz`, `quux`", $stmt->asSql());
    }

    public function testGroupByQuoteCharNameSepMultipleGroupByWithDesc() {
        $stmt = $this->ns(array('quote_char' => '`', 'name_sep' => '.'));
        $stmt->addFrom('foo');
        $stmt->addGroupBy('baz', 'DESC');
        $stmt->addGroupBy('quux', 'DESC');
        $this->assertEquals("FROM `foo`\nGROUP BY `baz` DESC, `quux` DESC", $stmt->asSql());
    }


    public function testGroupByQuoteCharNameSepNewLineSingleBareGroupBy() {
        $stmt = $this->ns(array('quote_char' => '', 'name_sep' => '.', 'new_line' => ' '));
        $stmt->addFrom( 'foo' );
        $stmt->addGroupBy('baz');
        $this->assertEquals("FROM foo GROUP BY baz", $stmt->asSql());
    }

    public function testGroupByQuoteCharNameSepNewLineSingleGroupByWithDesc() {
        $stmt = $this->ns(array('quote_char' => '', 'name_sep' => '.', 'new_line' => ' '));
        $stmt->addFrom('foo');
        $stmt->addGroupBy('baz', 'DESC');
        $this->assertEquals("FROM foo GROUP BY baz DESC", $stmt->asSql());
    }

    public function testGroupByQuoteCharNameSepNewLineMultipleGroupBy() {
        $stmt = $this->ns(array('quote_char' => '', 'name_sep' => '.', 'new_line' => ' '));
        $stmt->addFrom( 'foo' );
        $stmt->addGroupBy('baz');
        $stmt->addGroupBy('quux');
        $this->assertEquals("FROM foo GROUP BY baz, quux", $stmt->asSql());
    }

    public function testGroupByQuoteCharNameSepNewLineMultipleGroupByWithDesc() {
        $stmt = $this->ns(array('quote_char' => '', 'name_sep' => '.', 'new_line' => ' '));
        $stmt->addFrom('foo');
        $stmt->addGroupBy('baz', 'DESC');
        $stmt->addGroupBy('quux', 'DESC');
        $this->assertEquals("FROM foo GROUP BY baz DESC, quux DESC", $stmt->asSql());
    }


    public function testOrderByQuoteCharNameSepSingleOrderBy() {
        $stmt = $this->ns(array('quote_char' => '`', 'name_sep' => '.'));
        $stmt->addFrom( 'foo' );
        $stmt->addOrderBy('baz', 'DESC');
        $this->assertEquals("FROM `foo`\nORDER BY `baz` DESC", $stmt->asSql());
    }

    public function testOrderByQuoteCharNameSepMultipleOrderBy() {
        $stmt = $this->ns(array('quote_char' => '`', 'name_sep' => '.'));
        $stmt->addFrom( 'foo' );
        $stmt->addOrderBy('baz', 'DESC');
        $stmt->addOrderBy('quux', 'ASC');
        $this->assertEquals("FROM `foo`\nORDER BY `baz` DESC, `quux` ASC", $stmt->asSql());
    }

    public function testOrderByQuoteCharNameSepScalarRef() {
        $stmt = $this->ns(array('quote_char' => '`', 'name_sep' => '.'));
        $stmt->addFrom( 'foo' );

        // Not scalar ref, using array ref in PHP
        $stmt->addOrderBy(array('baz DESC'));

        $this->assertEquals("FROM `foo`\nORDER BY baz DESC", $stmt->asSql());
    }

    public function testOrderByQuoteCharNameSepNewLineSingleOrderBy() {
        $stmt = $this->ns(array('quote_char' => '', 'name_sep' => '.', 'new_line' => ' '));
        $stmt->addFrom( 'foo' );
        $stmt->addOrderBy('baz', 'DESC');
        $this->assertEquals("FROM foo ORDER BY baz DESC", $stmt->asSql());
    }

    public function testOrderByQuoteCharNameSepNewLineMultipleOrderBy() {
        $stmt = $this->ns(array('quote_char' => '', 'name_sep' => '.', 'new_line' => ' '));
        $stmt->addFrom( 'foo' );
        $stmt->addOrderBy('baz', 'DESC');
        $stmt->addOrderBy('quux', 'ASC');
        $this->assertEquals("FROM foo ORDER BY baz DESC, quux ASC", $stmt->asSql());
    }

    public function testOrderByQuoteCharNameSepNewLineScalarRef() {
        $stmt = $this->ns(array('quote_char' => '', 'name_sep' => '.', 'new_line' => ' '));
        $stmt->addFrom( 'foo' );

        // Not scalar ref, using array ref in PHP
        $stmt->addOrderBy(array('baz DESC'));

        $this->assertEquals("FROM foo ORDER BY baz DESC", $stmt->asSql());
    }

    // GROUP BY + ORDER BY
    public function testGroupByOrderByQuoteCharNameSepGroupByWithOrderBy() {
        $stmt = $this->ns(array('quote_char' => '`', 'name_sep' => '.'));
        $stmt->addFrom( 'foo' );
        $stmt->addGroupBy('quux');
        $stmt->addOrderBy('baz', 'DESC');
        $this->assertEquals("FROM `foo`\nGROUP BY `quux`\nORDER BY `baz` DESC", $stmt->asSql());
    }

    public function testGroupByOrderByQuoteCharNameSepNewLineGroupByWithOrderBy() {
        $stmt = $this->ns(array('quote_char' => '', 'name_sep' => '.', 'new_line' => ' '));
        $stmt->addFrom( 'foo' );
        $stmt->addGroupBy('quux');
        $stmt->addOrderBy('baz', 'DESC');
        $this->assertEquals("FROM foo GROUP BY quux ORDER BY baz DESC", $stmt->asSql());
    }


    // LIMIT OFFSET
    public function testLimitOffsetQuoteCharNameSepBogusLimitCausesAsSqlAssertion() {
        $stmt = $this->ns(array('quote_char' => '`', 'name_sep' => '.'));
        $stmt->addFrom('foo');
        $stmt->limit(5);
        $this->assertEquals("FROM `foo`\nLIMIT 5", $stmt->asSql());
        $stmt->offset(10);
        $this->assertEquals("FROM `foo`\nLIMIT 5 OFFSET 10", $stmt->asSql());
        $stmt->limit("  15g"); // Non-numerics should cause an error
        try {
            $sql = $stmt->asSql();
        }
        catch (Exception $e) {
            $this->assertRegExp('/Non-numerics/', $e->getMessage());
        }
    }

    public function testLimitOffsetQuoteCharNameSepNewLineBogusLimitCausesAsSqlAssertion() {
        $stmt = $this->ns(array('quote_char' => '', 'name_sep' => '.', 'new_line' => ' '));
        $stmt->addFrom('foo');
        $stmt->limit(5);
        $this->assertEquals("FROM foo LIMIT 5", $stmt->asSql());
        $stmt->offset(10);
        $this->assertEquals("FROM foo LIMIT 5 OFFSET 10", $stmt->asSql());
        $stmt->limit("  15g"); // Non-numerics should cause an error
        try {
            $sql = $stmt->asSql();
        }
        catch (Exception $e) {
            $this->assertRegExp('/Non-numerics/', $e->getMessage());
        }
    }


    // WHERE
    public function testWhereQuoteCharNameSepSingleEquals() {
        $stmt = $this->ns(array('quote_char' => '`', 'name_sep' => '.'));
        $stmt->addWhere('foo', 'bar');
        $this->assertEquals("WHERE (`foo` = ?)\n", $stmt->asSqlWhere());
        $this->assertEquals(1, count($stmt->bind()));
        $b = $stmt->bind();
        $this->assertEquals('bar', $b[0]);
    }

    public function testWhereQuoteCharNameSepSingleEqualsMultiValuesIsINStatement() {
        $stmt = $this->ns(array('quote_char' => '`', 'name_sep' => '.'));
        $stmt->addWhere('foo', array('bar', 'baz'));
        $this->assertEquals("WHERE (`foo` IN (?, ?))\n", $stmt->asSqlWhere());

        $b = $stmt->bind();
        $this->assertEquals(2, count($b));
        $this->assertEquals('bar', $b[0]);
        $this->assertEquals('baz', $b[1]);
    }

    public function testWhereQuoteCharNameSepNewConditionSingleEqualsMultiValuesIsINStatement() {
        $stmt = $this->ns(array('quote_char' => '`', 'name_sep' => '.'));
        $cond = $stmt->newCondition();
        $cond->add('foo', array('bar', 'baz'));
        $stmt->setWhere($cond);
        $this->assertEquals("WHERE (`foo` IN (?, ?))\n", $stmt->asSqlWhere());

        $b = $stmt->bind();
        $this->assertEquals(2, count($b));
        $this->assertEquals('bar', $b[0]);
        $this->assertEquals('baz', $b[1]);
    }


    public function testWhereQuoteCharNameSepNewLineSingleEquals() {
        $stmt = $this->ns(array('quote_char' => '', 'name_sep' => '.', 'new_line' => ' '));
        $stmt->addWhere('foo', 'bar');
        $this->assertEquals("WHERE (foo = ?) ", $stmt->asSqlWhere());
        $this->assertEquals(1, count($stmt->bind()));
        $b = $stmt->bind();
        $this->assertEquals('bar', $b[0]);
    }

    public function testWhereQuoteCharNameSepNewLineSingleEqualsMultiValuesIsINStatement() {
        $stmt = $this->ns(array('quote_char' => '', 'name_sep' => '.', 'new_line' => ' '));
        $stmt->addWhere('foo', array('bar', 'baz'));
        $this->assertEquals("WHERE (foo IN (?, ?)) ", $stmt->asSqlWhere());

        $b = $stmt->bind();
        $this->assertEquals(2, count($b));
        $this->assertEquals('bar', $b[0]);
        $this->assertEquals('baz', $b[1]);
    }

    public function testWhereQuoteCharNameSepNewLineNewConditionSingleEqualsMultiValuesIsINStatement() {
        $stmt = $this->ns(array('quote_char' => '', 'name_sep' => '.', 'new_line' => ' '));
        $cond = $stmt->newCondition();
        $cond->add('foo', array('bar', 'baz'));
        $stmt->setWhere($cond);
        $this->assertEquals("WHERE (foo IN (?, ?)) ", $stmt->asSqlWhere());

        $b = $stmt->bind();
        $this->assertEquals(2, count($b));
        $this->assertEquals('bar', $b[0]);
        $this->assertEquals('baz', $b[1]);
    }


    public function ns($args) {
        return new SQL_Maker_Select($args);
    }

}
