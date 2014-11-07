<?php

namespace Sitecake;

class UtilsTest extends \PHPUnit_Framework_TestCase {

	/*
	static function setUpBeforeClass() {
		setlocale(LC_ALL, 'en_US.UTF8');
		static::mockStaticClass('\\sitecake\\util');
	}
	*/

	function test_rpath() {
		define('SC_ROOT', '/some/path');
		$this->assertEquals('and/relative/path', 
			Utils::rpath('/some/path/and/relative/path'));
		$this->assertEquals('/some/other/path',
			Utils::rpath('/some/other/path'));		
	}
	
	function test_map() {
		$this->assertEquals(array(1, 4, 9), 
			Utils::map(function($val) { return $val*$val; }, array(1, 2, 3)));
	}
	
	function test_map2() {
		$this->assertEquals(array(1, 0, 3),
			Utils::map(function($val1, $val2) {
					return $val1*$val2;
				}, array(1, 2, 3), array(1, 0, 1)));
	}
	
	function test_array_map_prop() {
		$this->assertEquals(array('a', 'b', 'c'), Utils::array_map_prop(
			array(array('prop' => 'a'), array('prop' => 'b'), 
				array('prop' => 'c')), 'prop'));
	}
	
	function test_array_filter_prop() {
		$this->assertEquals(
			array(
				0 => array('prop' => 'a'), 
				2 => array('prop' => 'a')
			), 
			Utils::array_filter_prop(
				array(
					0 => array('prop' => 'a'), 
					1 => array('prop' => 'b'), 
					2 => array('prop' => 'a'), 
					3 => array('nop' => 'a')
				), 'prop', 'a'));
	}
	
	function test_array_find_prop() {
		$this->assertEquals(array('p'=>2, 's'=>3), Utils::array_find_prop(
			array(array('p'=>2, 's'=>3), array('p'=>3)), 'p', 2));
		$this->assertEquals(null, Utils::array_find_prop(
			array(
				array('p'=>2, 's'=>3), 
				array('p'=>3), 
				array('s'=>4)), 'p', 4));
	}
	
	function test_array_diff() {
		$this->assertEquals(array(), Utils::array_diff(array(), array()));
		$this->assertEquals(array(), Utils::array_diff(array(1, 2, 3), array(2, 1, 3)));
		$this->assertEquals(array(1), Utils::array_diff(array(1, 2, 3, 4), array(3, 4, 2)));
	}
	
	function test_str_endswith() {
		$this->assertTrue(Utils::str_endswith('.html', 'index.html'));
		$this->assertFalse(Utils::str_endswith('.html', 'index.htm'));
		$this->assertTrue(Utils::str_endswith('.html', '.html'));
		$this->assertFalse(Utils::str_endswith('.html', 'html'));
	}

	function test_slug() {
		$this->assertEquals('àáäâãåāăąćĉċčçďđèéëêēĕėęěĝğġģìíïîĩīĭįıĵĥħķĺļľŀłńñņňŉòóöôõøōŏőŕŗřśŝşšţťŧùúüûũūŭůűųŵýÿŷźżž', 
			Utils::slug('àáäâãåāăąćĉċčçďđèéëêēĕėęěĝğġģìíïîĩīĭįıĵĥħķĺļľŀłńñņňŉòóöôõøōŏőŕŗřśŝşšţťŧùúüûũūŭůűųŵýÿŷźżž'));
		$this->assertEquals('my-piñata', Utils::slug('my// piñata!'));
	}
	
}