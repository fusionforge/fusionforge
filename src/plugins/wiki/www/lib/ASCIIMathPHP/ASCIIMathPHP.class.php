<?php

/****
 * ASCIIMathPHP and associated classes:
 * -- XMLNode
 * -- MathMLNode extends XMLNode
 *
 * ASCIIMathPHP Version 2.1, 30 December 2013, (c) Zefling (http://ikilote.net / zefling@ikilote.net)
 *
 * These classes are a PHP port of ASCIIMath
 * Version 1.3 Feb 19 2004, (c) Peter Jipsen http://www.chapman.edu/~jipsen
 *
 * ASCIIMathPHP Version 1.11, 26 April 2006, (c) Kee-Lin Steven Chan (kc56@cornell.edu)
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License (at http://www.gnu.org/copyleft/gpl.html)
 * for more details.
 *
 * ChangeLog
 *
 * Ver 2.1
 * -- correct PHP5 object version of ASCIIMathPHP, MathMLNode, XMLNode
 * -- fixed bug of empty list in XMLNode (dumpXML & _dumpAttr)
 * 
 * Ver 2.0
 * -- PHP5 only version of ASCIIMathPHP
 *
 * Ver 1.12.1
 * -- Included the missing setCurrExpr() method
 *
 * Ver 1.12
 * -- Added changes that David Lippman <DLippman@pierce.ctc.edu> made to bring ASCIIMathPHP up to
 * ASCIIMath 1.4.7 public functionality.
 * -- Added parseIntExpr, for intermediate expression parsing rule, allowing x^2/x^3 to render as (x^2)/(x^3)
 * -- Added quotes as another way of designating text; "hello" is equivalent to text(hello)
 * -- Added FUNC designator to allow sin, cos, etc to act as public functions, so sin(x)/x renders as {sin(x)}/x
 *
 * Ver 1.11
 * -- Fixed bug that stopped script execution for incomplete expressions
 * -- Changed the algorithm for parsing expressions so that it matches the longest string possible (greedy)
 *
 * Ver 1.10
 * -- Added definition support
 * -- Added stackrel support
 * -- Added a bunch of different symbols etc. >>, << and definitions like dx, dy, dz etc.
 *
 * Ver 1.02
 * -- Fixed bug with mbox and text
 * -- Fixed spacing bug with mbox and text
 *
 * Ver 1.01
 * -- Fixed Bug that did not parse symbols greater than a single character
 * correctly when appearing at end of expression.
 *
 ***/

class XMLNode
{
	// Private variables
	protected $_id;
	protected $_name;
	protected $_content;
	protected $_mt_elem_flg;
	protected $_attr_arr;
	protected $_child_arr;
	protected $_nmspc;
	protected $_nmspc_alias;
	protected $_parent_id;
	protected $_parent_node;

	public function __construct($id = null)
	{
		$this->_id = isset($id) ? $id : md5(uniqid(rand(),1));
		$this->_name = '';
		$this->_content = '';
		$this->_mt_elem_flg = false;
		$this->_attr_arr = array();
		$this->_child_arr = array();
		$this->_nmspc = '';
		$this->_nmspc_alias = '';
		$this->_parent_id = false;
		$this->_parent_node = null;
	}

	public function addChild(&$node)
	{
		$this->_child_arr[$node->getId()] = $node;
		$node->setParentId($this->_id);
		$node->setParentNode($this);
	}

	public function addChildArr(&$node_arr)
	{
		$key_arr = array_keys($node_arr);
		$num_key = count($key_arr);

		for ($i = 0; $i < $num_key; $i++) {
			$node = $node_arr[$key_arr[$i]];
			$this->addChild($node);
		}
	}

	public function insertChildBefore($idx,&$node)
	{
		$key_arr = array_keys($this->_child_arr);
		$num_key = count($key_arr);
		$tmp_arr = array();

		for ($i = 0;$i < $num_key;$i++) {
			if ($i == $idx) {
				$tmp_arr[$node->getId()] = $node;
			}
			$tmp_arr[$key_arr[$i]] = $this->_child_arr[$key_arr[$i]];
		}
		$this->_child_arr = $tmp_arr;
	}

	public function insertChildAfter($idx,&$node)
	{
		$key_arr = array_keys($this->_child_arr);
		$num_key = count($key_arr);
		$tmp_arr = array();

		for ($i = 0;$i < $num_key;$i++) {
			$tmp_arr[$key_arr[$i]] = $this->_child_arr[$key_arr[$i]];
			if ($i == $idx) {
				$tmp_arr[$node->getId()] = $node;
			}
		}
		$this->_child_arr = $tmp_arr;
	}

	public function setId($id)
	{
		$this->_id = $id;
	}

	public function setName($name)
	{
		$this->_name = $name;
	}

	public function setNamepace($nmspc)
	{
		$this->_nmspc = $nmspc;
	}

	public function setNamespaceAlias($nmspc_alias)
	{
		$this->_nmspc_alias = $nmspc_alias;
	}

	public function setContent($content)
	{
		$this->_content = $content;
	}

	public function setEmptyElem($mt_elem_flg)
	{
		$this->_mt_elem_flg = $mt_elem_flg;
	}

	public function setAttr($attr_nm,$attr_val)
	{
		$this->_attr_arr[$attr_nm] = $attr_val;
	}

	public function setAttrArr($attr_arr)
	{
		$this->_attr_arr = $attr_arr;
	}

	public function setParentId($id)
	{
		$this->_parent_id = $id;
	}

	public function setParentNode(&$node)
	{
		$this->_parent_node = $node;
	}

	public function getId()
	{
		return($this->_id);
	}

	public function getName()
	{
		return($this->_name);
	}

	public function getNamespace()
	{
		return($this->_nmspc);
	}

	public function getNamespaceAlias()
	{
		return($this->_nmspc_alias);
	}

	public function getContent()
	{
		return($this->_content);
	}

	public function getAttr($attr_nm)
	{
		if (isset($this->_attr_arr[$attr_nm])) {
			return($this->_attr_arr[$attr_nm]);
		} else {
			return(null);
		}
	}

	public function getAttrArr()
	{
		return($this->_attr_arr);
	}

	public function getParentId()
	{
		return($this->_parent_id);
	}

	public function getParentNode()
	{
		return($this->_parent_node);
	}

	public function getChild($id)
	{
		if (isset($this->_child_arr[$id])) {
			return($this->_child_arr[$id]);
		} else {
			return(false);
		}
	}

	public function getFirstChild()
	{
		$id_arr = array_keys($this->_child_arr);
		$num_child = count($id_arr);

		if ($num_child > 0) {
			return($this->_child_arr[$id_arr[0]]);
		} else {
			return(false);
		}
	}

	public function getLastChild()
	{
		$id_arr = array_keys($this->_child_arr);
		$num_child = count($id_arr);

		if ($num_child > 0) {
			return($this->_child_arr[$id_arr[$num_child - 1]]);
		} else {
			return(false);
		}
	}

	public function getChildByIdx($idx)
	{
		$id_arr = array_keys($this->_child_arr);

		if (isset($this->_child_arr[$id_arr[$idx]])) {
			return($this->_child_arr[$id_arr[$idx]]);
		} else {
			return(false);
		}
	}

	public function getNumChild()
	{
		return(count($this->_child_arr));
	}

	public function removeChild($id)
	{
		unset($this->_child_arr[$id]);
	}

	public function removeChildByIdx($idx)
	{
		$key_arr = array_keys($this->_child_arr);
		unset($this->_child_arr[$key_arr[$idx]]);
	}

	public function removeFirstChild()
	{
		$key_arr = array_keys($this->_child_arr);
		unset($this->_child_arr[$key_arr[0]]);
	}

	public function removeLastChild()
	{
		$key_arr = array_keys($this->_child_arr);
		unset($this->_child_arr[$key_arr[count($key_arr)-1]]);
	}

	public function dumpXML($indent_str = "\t") 
	{
		$attr_txt = $this->_dumpAttr();
		$name = $this->_dumpName();
		$xmlns = $this->_dumpXmlns();
		$lvl = $this->_getCurrentLevel();
		$indent = str_pad('',$lvl,$indent_str);
		
		
		if ($this->_mt_elem_flg) {
			$tag = "$indent<$name$xmlns$attr_txt />";
			return($tag);
		} else {
			$num_child = 0;
			if (!empty($this->_child_arr)) {
				$key_arr = array_keys($this->_child_arr);
				
				$num_child = count($key_arr);
			}
	
			$tag = "$indent<$name$xmlns$attr_txt>$this->_content";

			for ($i = 0;$i < $num_child; $i++) {
				$node = $this->_child_arr[$key_arr[$i]];
				
				//print_r($node);
				
				$child_txt = $node->dumpXML($indent_str);
				$tag .= "\n$child_txt";
			}

			$tag .= ($num_child > 0 ? "\n$indent</$name>" : "</$name>");
			return($tag);
		}
	}
	
	public function _dumpAttr()
	{
		$attr_txt = '';
		if (!empty($this->_attr_arr)) {
			$id_arr = array_keys($this->_attr_arr);
			$id_arr_cnt = count($id_arr);
	
			for($i = 0;$i < $id_arr_cnt;$i++) {
				$key = $id_arr[$i];
				$attr_txt .= " $key=\"{$this->_attr_arr[$key]}\"";
			}
		}
		return($attr_txt);
	}

	public function _dumpName()
	{
		$alias = $this->getNamespaceAlias();
		if ($alias == '') {
			return($this->getName());
		} else {
			return("$alias:" . $this->getName());
		}
	}

	public function _dumpXmlns()
	{
		$nmspc = $this->getNamespace();
		$alias = $this->getNamespaceAlias();

		if ($nmspc != '') {
			if ($alias == '') {
				return(" xmlns=\"" . $nmspc . "\"");
			} else {
				return(" xmlns:$alias=\"" . $nmspc . "\"");
			}
		} else {
			return('');
		}
	}

	public function _getCurrentLevel()
	{
		if ($this->_parent_id === false) {
			return 0 ;
		} else {
			$node = $this->getParentNode();
			if ($node != null) {
				$lvl = $node->_getCurrentLevel();
				$lvl++;
				return($lvl);
			} else {
				return 0 ;
			}
		}
	}
}

class MathMLNode extends XMLNode
{
	public function __construct($id = null)
	{
		parent::__construct($id);
	}

	public function removeBrackets()
	{
		if ($this->_name == 'mrow') {
			if ($c_node_0 = $this->getFirstChild()) {
				$c_node_0->isLeftBracket() ? $this->removeFirstChild() : 0;
			}

			if ($c_node_0 = $this->getLastChild()) {
				$c_node_0->isRightBracket() ? $this->removeLastChild() : 0;
			}
		}
	}

	public function isLeftBracket()
	{
		switch ($this->_content) {
			case '{':
			case '[':
			case '(':
				return(true);
				break;
		}
		return(false);
	}

	public function isRightBracket()
	{
		switch ($this->_content) {
			case '}':
			case ']':
			case ')':
				return(true);
				break;
		}
		return(false);
	}
}

class ASCIIMathPHP
{
	protected $_expr;
	protected $_curr_expr;
	protected $_prev_expr;
	protected $_symbol_arr;
	protected $_node_arr;
	protected $_node_cntr;

	public function __construct($symbol_arr, $expr = null) {
		$this->_symbol_arr = $symbol_arr;
		if (isset($expr)) {
			$this->setExpr($expr);
		}
	}

	/**
	 * Returns an empty node (containing a non-breaking space) 26-Apr-2006
	 *
	 * Used when an expression is incomplete
	 *
	 * @return object
	 *
	 * @access private
	 */
	public function emptyNode()
	{
		$tmp_node = $this->createNode();
		$tmp_node->setName('mn');
		$tmp_node->setContent('&#' . hexdec('200B') . ';');
		return $tmp_node;
	}

	public function pushExpr($prefix) // 2005-06-11 wes
	{
		$this->_curr_expr = $prefix . $this->_curr_expr;
	}

	public function setExpr($expr)
	{
		$this->_expr = $expr;
		$this->_curr_expr = $expr;
		$this->_prev_expr = $expr;

		$this->_node_arr = array();
		$this->_node_cntr = 0;
	}

	public function genMathML($attr_arr = null)
	{
		// <math> node
		$node_0 = $this->createNode();
		$node_0->setName('math');
		$node_0->setNamepace('http://www.w3.org/1998/Math/MathML');

		// <mstyle> node
		if (isset($attr_arr)) {
			$node_1 = $this->createNode();
			$node_1->setName('mstyle');
			$node_1->setAttrArr($attr_arr);

			$node_arr = $this->parseExpr();

			$node_1->addChildArr($node_arr);
			$node_0->addChild($node_1);
		} else {
			$node_arr = $this->parseExpr();
			$node_0->addChildArr($node_arr);
		}

		return true;
	}

	/*
	public function  mergeNodeArr(&$node_arr_0,&$node_arr_1)
	{
		$key_arr_0 = array_keys($node_arr_0);
		$key_arr_1 = array_keys($node_arr_1);

		$num_key_0 = count($key_arr_0);
		$num_key_1 = count($key_arr_1);

		$merge_arr = array();

		for ($i = 0;$i < $num_key_0;$i++) {
			$merge_arr[$key_arr_0[$i]] = $node_arr_0[$key_arr_0[$i]];
		}

		for ($j = 0;$j < $num_key_1;$i++) {
			$merge_arr[$key_arr_1[$i]] = $node_arr_1[$key_arr_1[$i]];
		}

		return($merge_arr);
	}
	*/

	//Broken out of parseExpr Sept 7, 2006 David Lippman for
	//ASCIIMathML 1.4.7 compatibility
	public function parseIntExpr()
	{
		$sym_0 = $this->getSymbol();
		$node_0 = $this->parseSmplExpr();
		$sym = $this->getSymbol();

		if (isset($sym['infix']) && $sym['input'] != '/') {
			$this->chopExpr($sym['symlen']);
			$node_1 = $this->parseSmplExpr();

			if ($node_1 === false) { //show box in place of missing argument
				$node_1 = $this->emptyNode();//??
			} else {
				$node_1->removeBrackets();
			}

			// If 'sub' -- subscript
			if ($sym['input'] == '_') {

				$sym_1 = $this->getSymbol();

				// If 'sup' -- superscript
				if ($sym_1['input'] == '^') {
					$this->chopExpr($sym_1['symlen']);
					$node_2 = $this->parseSmplExpr();
					$node_2->removeBrackets();

					$node_3 = $this->createNode();
					$node_3->setName(isset($sym_0['underover']) ? 'munderover' : 'msubsup');
					$node_3->addChild($node_0);
					$node_3->addChild($node_1);
					$node_3->addChild($node_2);

					$node_4 = $this->createNode();
					$node_4->setName('mrow');
					$node_4->addChild($node_3);

					return $node_4;
				} else {
					$node_2 = $this->createNode();
					$node_2->setName(isset($sym_0['underover']) ? 'munder' : 'msub');
					$node_2->addChild($node_0);
					$node_2->addChild($node_1);

					return $node_2;
				}
			} else {
				$node_2 = $this->createNode();
				$node_2->setName($sym['tag']);
				$node_2->addChild($node_0);
				$node_2->addChild($node_1);

				return($node_2);
			}
		} elseif ($node_0 !== false) {
			return($node_0);
		} else {
			return $this->emptyNode();
		}

	}

	public function parseExpr()
	{
		// Child/Fragment array
		$node_arr = array();

		// Deal whole expressions like 'ax + by + c = 0' etc.
		do {
			$sym_0 = $this->getSymbol();
			$node_0 = $this->parseIntExpr();
			$sym = $this->getSymbol();
			// var_dump($sym);

			if (isset($sym['infix']) && $sym['input'] == '/') {
				$this->chopExpr($sym['symlen']);
				$node_1 = $this->parseIntExpr();

				if ($node_1 === false) { //should show box in place of missing argument
					$node_1 = $this->emptyNode();
					continue;
				}

				$node_1->removeBrackets();

				// If 'div' -- divide
				$node_0->removeBrackets();
				$node_2 = $this->createNode();
				$node_2->setName($sym['tag']);
				$node_2->addChild($node_0);
				$node_2->addChild($node_1);
				$node_arr[$node_2->getId()] = $node_2;

			} elseif ($node_0 !== false) {
				$node_arr[$node_0->getId()] = $node_0;
			}
		} while (!isset($sym['right_bracket']) && $sym !== false && $sym['output'] != '');

		//var_dump($sym);
		// Possibly to deal with matrices
		if (isset($sym['right_bracket'])) {
			$node_cnt = count($node_arr);
			$key_node_arr = array_keys($node_arr);

			if ($node_cnt > 1) {
				$node_5 = $node_arr[$key_node_arr[$node_cnt-1]];
				$node_6 = $node_arr[$key_node_arr[$node_cnt-2]];
			} else {
				$node_5 = false;
				$node_6 = false;
			}

			// Dealing with matrices
			if ($node_5 !== false && $node_6 !== false &&
				$node_cnt > 1 &&
				$node_5->getName() == 'mrow' &&
				$node_6->getName() == 'mo' &&
				$node_6->getContent() == ',') {

				// Checking if Node 5 has a LastChild
				if ($node_7 = $node_5->getLastChild()) {
					$node_7_cntnt = $node_7->getContent();
				} else {
					$node_7_cntnt = false;
				}

				// If there is a right bracket
				if ($node_7 !== false && ($node_7_cntnt == ']' || $node_7_cntnt == ')')) {

					// Checking if Node 5 has a firstChild
					if ($node_8 = $node_5->getFirstChild()) {
						$node_8_cntnt = $node_8->getContent();
					} else {
						$node_8_cntnt = false;
					}

					// If there is a matching left bracket
					if ($node_8 !== false &&
						(($node_8_cntnt == '(' && $node_7_cntnt == ')' && $sym['output'] != '}') ||
						($node_8_cntnt == '[' && $node_7_cntnt == ']'))) {

						$is_mtrx_flg = true;
						$comma_pos_arr = array();

						$i = 0;

						while ($i < $node_cnt && $is_mtrx_flg) {
							$tmp_node = $node_arr[$key_node_arr[$i]];

							if($tmp_node_first = $tmp_node->getFirstChild()) {
								$tnfc = $tmp_node_first->getContent();
							} else {
								$tnfc = false;
							}

							if($tmp_node_last = $tmp_node->getLastChild()) {
								$tnlc = $tmp_node_last->getContent();
							} else {
								$tnlc = false;
							}

							if (isset($key_node_arr[$i+1])) {
								$next_tmp_node = $node_arr[$key_node_arr[$i+1]];
								$ntnn = $next_tmp_node->getName();
								$ntnc = $next_tmp_node->getContent();
							} else {
								$ntnn = false;
								$ntnc = false;
							}

							// Checking each node in node array for matrix criteria
							if ($is_mtrx_flg) {
								$is_mtrx_flg = $tmp_node->getName() == 'mrow' &&
									($i == $node_cnt-1 || $ntnn == 'mo' && $ntnc == ',') &&
									$tnfc == $node_8_cntnt && $tnlc == $node_7_cntnt;
							}

							if ($is_mtrx_flg) {
								for ($j = 0;$j < $tmp_node->getNumChild();$j++) {
									$tmp_c_node = $tmp_node->getChildByIdx($j);

									if ($tmp_c_node->getContent() == ',') {
										$comma_pos_arr[$i][] = $j;
									}
								}
							}

							if ($is_mtrx_flg && $i > 1) {

								$cnt_cpan = isset($comma_pos_arr[$i]) ? count($comma_pos_arr[$i]) : null;
								$cnt_cpap = isset($comma_pos_arr[$i-2]) ? count($comma_pos_arr[$i-2]) : null;
								$is_mtrx_flg = $cnt_cpan == $cnt_cpap;
							}

							$i += 2;
						}

						// If the node passes the matrix tests
						if ($is_mtrx_flg) {
							$tab_node_arr = array();

							for ($i = 0;$i < $node_cnt;$i += 2) {
								$tmp_key_node_arr = array_keys($node_arr);
								if (!($tmp_node = $node_arr[$tmp_key_node_arr[0]])) {
									break;
								}
								$num_child = $tmp_node->getNumChild();
								$k = 0;

								$tmp_node->removeFirstChild();

								$row_node_arr = array();
								$row_frag_node_arr = array();

								for ($j = 1;$j < ($num_child-1);$j++) {
									if (isset($comma_pos_arr[$i][$k]) &&
										$j == $comma_pos_arr[$i][$k]) {

										$tmp_node->removeFirstChild();

										$tmp_c_node = $this->createNode();
										$tmp_c_node->setName('mtd');
										$tmp_c_node->addChildArr($row_frag_node_arr);
										$row_frag_node_arr = array();

										$row_node_arr[$tmp_c_node->getId()] = $tmp_c_node;

										$k++;
									} else {

										if ($tmp_c_node = $tmp_node->getFirstChild()) {
											$row_frag_node_arr[$tmp_c_node->getId()] = $tmp_c_node;
											$tmp_node->removeFirstChild();
										}
									}
								}

								$tmp_c_node = $this->createNode();
								$tmp_c_node->setName('mtd');
								$tmp_c_node->addChildArr($row_frag_node_arr);

								$row_node_arr[$tmp_c_node->getId()] = $tmp_c_node;

								if (count($node_arr) > 2) {
									$tmp_key_node_arr = array_keys($node_arr);
									unset($node_arr[$tmp_key_node_arr[0]]);
									unset($node_arr[$tmp_key_node_arr[1]]);
								}

								$tmp_c_node = $this->createNode();
								$tmp_c_node->setName('mtr');
								$tmp_c_node->addChildArr($row_node_arr);

								$tab_node_arr[$tmp_c_node->getId()] = $tmp_c_node;
							}

							$tmp_c_node = $this->createNode();
							$tmp_c_node->setName('mtable');
							$tmp_c_node->addChildArr($tab_node_arr);

							if (isset($sym['invisible'])) {
								$tmp_c_node->setAttr('columnalign','left');
							}

							$key_node_arr = array_keys($node_arr);
							$tmp_c_node->setId($key_node_arr[0]);

							$node_arr[$tmp_c_node->getId()] = $tmp_c_node;
						}
					}
				}
			}

			$this->chopExpr($sym['symlen']);
			if (!isset($sym['invisible'])) {
				$node_7 = $this->createNode();
				$node_7->setName('mo');
				$node_7->setContent($sym['output']);
				$node_arr[$node_7->getId()] = $node_7;
			}
		}

		return($node_arr);
	}

	public function parseSmplExpr()
	{
		$sym = $this->getSymbol();

		if (!$sym || isset($sym['right_bracket'])) //return false;
			return $this->emptyNode();

		$this->chopExpr($sym['symlen']);

		// 2005-06-11 wes: add definition type support
		if(isset($sym['definition'])) {
			$this->pushExpr($sym['output']);
			$sym = $this->getSymbol();
			$this->chopExpr($sym['symlen']);
		}

		if (isset($sym['left_bracket'])) {
			$node_arr = $this->parseExpr();

			if (isset($sym['invisible'])) {
				$node_0 = $this->createNode();
				$node_0->setName('mrow');
				$node_0->addChildArr($node_arr);

				return($node_0);
			} else {
				$node_0 = $this->createNode();
				$node_0->setName('mo');
				$node_0->setContent($sym['output']);

				$node_1 = $this->createNode();
				$node_1->setName('mrow');
				$node_1->addChild($node_0);
				$node_1->addChildArr($node_arr);

				return($node_1);
			}
		} elseif (isset($sym['unary'])) {

			if ($sym['input'] == 'sqrt') {
				$node_0 = $this->parseSmplExpr();
				$node_0->removeBrackets();

				$node_1 = $this->createNode();
				$node_1->setName($sym['tag']);
				$node_1->addChild($node_0);

				return($node_1);
			} elseif (isset($sym['func'])) { //added 2006-9-7 David Lippman
				$expr = ltrim($this->getCurrExpr());
				$st = $expr[0];
				$node_0 = $this->parseSmplExpr();
				//$node_0->removeBrackets();
				if ($st=='^' || $st == '_' || $st=='/' || $st=='|' || $st==',') {
					$node_1 = $this->createNode();
					$node_1->setName($sym['tag']);
					$node_1->setContent($sym['output']);
					$this->setCurrExpr($expr);
					return($node_1);
				} else {
					$node_1 = $this->createNode();
					$node_1->setName('mrow');
					$node_2 = $this->createNode();
					$node_2->setName($sym['tag']);
					$node_2->setContent($sym['output']);
					$node_1->addChild($node_2);
					$node_1->addChild($node_0);
					return($node_1);
				}
			} elseif ($sym['input'] == 'text' || $sym['input'] == 'mbox' || $sym['input'] == '"') {
				$expr = ltrim($this->getCurrExpr());
				if ($sym['input']=='"') {
					$end_brckt = '"';
					$txt = substr($expr,0,strpos($expr,$end_brckt));
				} else {
					switch($expr[0]) {
						case '(':
							$end_brckt = ')';
							break;
						case '[':
							$end_brckt = ']';
							break;
						case '{':
							$end_brckt = '}';
							break;
						default:
							$end_brckt = chr(11); // A character that will never be matched.
							break;
					}
					$txt = substr($expr,1,strpos($expr,$end_brckt)-1);
				}

				//$txt = substr($expr,1,strpos($expr,$end_brckt)-1);
				$len = strlen($txt);

				$node_0 = $this->createNode();
				$node_0->setName('mrow');

				if ($len > 0) {
					if ($txt[0] == " ") {
						$node_1 = $this->createNode();
						$node_1->setName('mspace');
						$node_1->setAttr('width','1ex');

						$node_0->addChild($node_1);
					}

					$node_3 = $this->createNode();
					$node_3->setName($sym['tag']);
					$node_3->setContent(trim($txt));

					$node_0->addChild($node_3);

					if ($len > 1 && $txt[$len-1] == " ") {
						$node_2 = $this->createNode();
						$node_2->setName('mspace');
						$node_2->setAttr('width','1ex');

						$node_0->addChild($node_2);
					}

					$this->chopExpr($len+2);
				}
				return($node_0);

			} elseif (isset($sym['acc'])) {
				$node_0 = $this->parseSmplExpr();
				$node_0->removeBrackets();

				$node_1 = $this->createNode();
				$node_1->setName($sym['tag']);
				$node_1->addChild($node_0);

				$node_2 = $this->createNode();
				$node_2->setName('mo');
				$node_2->setContent($sym['output']);

				$node_1->addChild($node_2);
				return($node_1);
			} else {
				// Font change commands -- to complete
			}
		} elseif (isset($sym['binary'])) {
			$node_arr = array();

			$node_0 = $this->parseSmplExpr();
			$node_0->removeBrackets();

			$node_1 = $this->parseSmplExpr();
			$node_1->removeBrackets();

			/* 2005-06-05 wes: added stackrel */
			if ($sym['input'] == 'root' || $sym['input'] == 'stackrel') {
				$node_arr[$node_1->getId()] = $node_1;
				$node_arr[$node_0->getId()] = $node_0;
			} elseif ($sym['input'] == 'frac') {
				$node_arr[$node_0->getId()] = $node_0;
				$node_arr[$node_1->getId()] = $node_1;
			}

			$node_2 = $this->createNode();
			$node_2->setName($sym['tag']);
			$node_2->addChildArr($node_arr);

			return($node_2);
		} elseif (isset($sym['infix'])) {
			$node_0 = $this->createNode();
			$node_0->setName('mo');
			$node_0->setContent($sym['output']);

			return($node_0);
		} elseif (isset($sym['space'])) {
			$node_0 = $this->createNode();
			$node_0->setName('mrow');

			$node_1 = $this->createNode();
			$node_1->setName('mspace');
			$node_1->setAttr('width',$sym['space']);

			$node_2 = $this->createNode();
			$node_2->setName($sym['tag']);
			$node_2->setContent($sym['output']);

			$node_3 = $this->createNode();
			$node_3->setName('mspace');
			$node_3->setAttr('width',$sym['space']);

			$node_0->addChild($node_1);
			$node_0->addChild($node_2);
			$node_0->addChild($node_3);

			return($node_0);
		} else {

			// A constant
			$node_0 = $this->createNode();
			$node_0->setName($sym['tag']);
			$node_0->setContent($sym['output']);
			return($node_0);
		}

		// Return an empty node
		return $this->emptyNode();
	}

	public function getMathML()
	{
		$root = $this->_node_arr[0];
		return($root->dumpXML());
	}

	public function getCurrExpr()
	{
		return($this->_curr_expr);
	}

	public function setCurrExpr($str)
	{
		$this->_curr_expr = $str;
	}

	public function getExpr()
	{
		return($this->_expr);
	}

	public function getPrevExpr()
	{
		return($this->_prev_expr);
	}

	public function  createNode()
	{
		$node = new MathMLNode($this->_node_cntr);
		// $node->setNamespaceAlias('m');
		$this->_node_arr[$this->_node_cntr] = $node;
		$this->_node_cntr++;
		return($node);
	}

	/**
	 * Gets the largest symbol in the expression (greedy). Changed from non-greedy 26-Apr-2006
	 *
	 * @parameter boolean[optional] Chop original string?
	 *
	 * @return mixed
	 *
	 * @access private
	 */
	public function getSymbol($chop_flg = false)
	{
		// Implemented a reverse symbol matcher.
		// Instead of going front to back, it goes back to front. Steven 26-Apr-2006
		$chr_cnt = strlen($this->_curr_expr);

		if ($chr_cnt == 0) return false;

		for ($i = $chr_cnt; $i > 0; $i--) {
			$sym_0 = substr($this->_curr_expr,0,$i);

			// Reading string for numeric values
			if (is_numeric($sym_0)) {

				if ($chop_flg) $this->chopExpr($i);
				return array('input'=>$sym_0, 'tag'=>'mn', 'output'=>$sym_0, 'symlen'=>$i);

			} elseif (isset($this->_symbol_arr[$sym_0])) {

				if ($chop_flg) $this->chopExpr($i);
				$sym_arr = $this->_symbol_arr[$sym_0];
				$sym_arr['symlen'] = $i;
				return $sym_arr;
			}
		}

		// Reading string for alphabetic constants and the minus sign
		$char = $this->_curr_expr[0];
		$len_left = $chop_flg ? $this->chopExpr(1) : strlen($this->_curr_expr)-1;

		// Deals with expressions of length 1
		if ($len_left == 0 && isset($this->_symbol_arr[$char])) {
			$sym_arr = $this->_symbol_arr[$char];
			$sym_arr['symlen'] = 1;
			return $sym_arr;
		} else {
			$tag = preg_match('/[a-z]/i',$char) ? 'mi' : 'mo';
			return array('input'=>$char, 'tag'=>$tag, 'output'=>$char, 'symlen'=>1);
		}
	}

	public function chopExpr($strlen)
	{
		$this->_prev_expr = $this->_curr_expr;

		if ($strlen == strlen($this->_curr_expr)) {
			$this->_curr_expr = '';
			return(0);
		} else {
			$this->_curr_expr = ltrim(substr($this->_curr_expr,$strlen));
			return(strlen($this->_curr_expr));
		}
	}
}
