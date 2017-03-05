<?php
/**
 * Artifact Expression
*
* Copyright 2017 Stéphane-Eymeric Bredthauer - TrivialDev
* http://fusionforge.org/
*
* This file is part of FusionForge. FusionForge is free software;
* you can redistribute it and/or modify it under the terms of the
* GNU General Public License as published by the Free Software
* Foundation; either version 2 of the Licence, or (at your option)
* any later version.
*
* FusionForge is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License along
* with FusionForge; if not, write to the Free Software Foundation, Inc.,
* 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

require_once $gfcommon.'include/FFError.class.php';
require_once $gfwww.'include/expression.php';

class ArtifactExpression extends FFError {
	
	private $expression;
	private $arithmeticOperators = array (
											array('=','Assignment','a = b'),
											array('+','Addition','a + b'),
											array('-','Subtraction','a - b'),
											array('-','Unary minus','-a'),
											array('*','Multiplication','a * b'),
											array('/','Division','a / b'),
											array('%','Modulo (integer remainder)','a % b'),
											array('^','Power','a ^ b')
									);
	private $comparisonOperators = array (
											array('==','Equal to','a == b'),
											array('!=','Not equal to','a != b'),
											array('>','Greater than','a > b'),
											array('<','Less than','a < b'),
											array('>=','Greater than or equal to','a >= b'),
											array('<=','Less than or equal to','a <= b'),
											array('=~','Regex match','a =~ regex')
									);
	private $logicalOperators = array(
										array('!','Logical negation (NOT)','!a'),
										array('&&','Logical AND','a && b'),
										array('||','Logical OR','a || b')
			);

	private $otherOperators = array(
									array('?:','Conditional operator','a ? b : c')
	);
	
	private $functionsDescription = array();
	
	public function __construct()
	{
		$this->functionsDescription = array('in_array'=>_('Test if a value is in an (json) array'));
		
		$this->expression = new Expression;
		$this->expression->suppress_errors = true;
		$this->expression->fb = array();
		$this->expression->functions ['in_array'] = 'expr_in_array';
	}
	
	public function evaluate($expression) {
		$return = null;
		$this->clearError();
		$lines = preg_split('/;\s*\R/',$expression);
		foreach ($lines as $line) {
			$line = preg_replace('/\R|\s/',' ', $line);
			if (!preg_match('/^\s*#.*/',$line)) {
				$return = $this->expression->evaluate($line);
				if ($this->expression->last_error) {
					$this->setError($this->expression->last_error);
				}
			}
		}
		return $return;
	}
	
	public function getVariables() {
		return $this->expression->vars();
	}
	
	public function getFunctions () {
		$builtInFunctions = $this->expression->fb;
		$customFunctions = array_keys($this->expression->functions);
		return array_merge($builtInFunctions, $customFunctions);
	}
	
	public function getUserDefineFunctions () {
		return array_keys($this->expression->f);
	}
	
	public function getOperators() {
		return array(
						array(_('Arithmetic operators'), $this->arithmeticOperators),
						array(_('Comparison operators'), $this->comparisonOperators),
						array(_('Logical operators'), $this->logicalOperators),
						array(_('Other operators'), $this->otherOperators)
					);
	}
	
	public function getFunctionDescription($function) {
		return $this->functionsDescription[$function];
	}
}
function expr_in_array($value, $jsonArray) {
	$array = json_decode($jsonArray, true);
	return in_array($value, $array);	
}