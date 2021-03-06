<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2009

 See text/en/licence.txt for full licencing information.

*/

/**
 * @license		http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright	ocProducts Ltd
 * @package		core_validation
 */

/**
 * Standard code module initialisation function.
 */
function init__js_parse()
{
	// In precendence order. Note REFERENCE==BW_AND (it gets converted, for clarity). Ditto QUESTION==UNARY_IF
	global $OPS;
	$OPS=array('QUESTION','UNARY_IF','BOOLEAN_OR','BOOLEAN_AND','BW_OR','BW_XOR','OBJECT_OPERATOR','BW_AND','IS_EQUAL','IS_NOT_EQUAL','IS_IDENTICAL','IS_NOT_IDENTICAL','IS_SMALLER','IS_SMALLER_OR_EQUAL','IS_GREATER','IS_GREATER_OR_EQUAL','SL','SR','ZSR','ADD','SUBTRACT','MULTIPLY','DIVIDE','REMAINDER');
}

/**
 * Return parse info for parse type.
 *
 * @return ?map				Parse info (NULL: error)
 */
function js_parse()
{
	global $JS_PARSE_POSITION;
	$JS_PARSE_POSITION=0;

	$structure=_js_parse_js();

	return $structure;
}

/**
 * Return parse info for parse type.
 *
 * @return ?map			Parse info (NULL: error)
 */
function _js_parse_js()
{
// Choice{"FUNCTION" "IDENTIFIER "BRACKET_OPEN" comma_parameters "BRACKET_CLOSE" command | command}*

	$next=parser_peek();
	$program=array();
	$program['functions']=array();
	$program['main']=array();
	while (!is_null($next))
	{
		switch ($next)
		{
			case 'FUNCTION':
				$_function=_js_parse_function_dec();
				if (is_null($_function)) return NULL;
				foreach ($program['functions'] as $_)
				{
					if ($_['name']==$_function['name']) js_log_warning('PARSER','Duplicated function \''.$_function['name'].'\'');
				}
				//log_special('defined',$_function['name']);
				$program['functions'][]=$_function;

				// Sometimes happens when people get confused by =function() and function blah() {};
				$next_2=parser_peek();
				if ($next_2=='COMMAND_TERMINATE') parser_next();
				break;

			default:
				$command=_js_parse_command();
				if (is_null($command)) return NULL;
				$program['main']=array_merge($program['main'],$command);
				break;
		}

		$next=parser_peek();
	}
	return $program;
}

/**
 * Return parse info for parse type.
 *
 * @return ?list			Parse info (NULL: error)
 */
function _js_parse_command()
{
// Choice{"CURLY_OPEN" command* "CURLY_CLOSE" | command_actual "COMMAND_TERMINATE"*}

	$next=parser_peek();
	$command=array();
	switch ($next)
	{
		case 'CURLY_OPEN':
			parser_next();
			$next_2=parser_peek();
			while (true)
			{
				switch ($next_2)
				{
					case 'CURLY_CLOSE':
						parser_next();
						break 2;

					default:
						$command2=_js_parse_command();
						if (is_null($command2)) return NULL;
						$command=array_merge($command,$command2);
						break;
				}
				$next_2=parser_peek();
			}
			break;

		default:
			$new_command=_js_parse_command_actual();
			if (is_null($new_command)) return NULL;

			// This is now a bit weird. Not all commands end with a COMMAND_TERMINATE, and those are actually for the commands to know their finished (and the ones requiring would have complained if they were missing). Therefore we now just skip any semicolons. There can be more than one, it's valid, albeit crazy.
			$command[]=$new_command;
			$next_2=parser_peek();
			while ($next_2=='COMMAND_TERMINATE')
			{
				parser_next();
				$next_2=parser_peek();
			}

			break;
	}
	return $command;
}

/**
 * Return parse info for parse type.
 *
 * @return ?list			Parse info (NULL: error)
 */
function _js_parse_command_actual()
{
// Choice{"VAR" comma_variables | "FUNCTION" | variable "DEC" | variable "INC" | variable assignment_operator expression | function "BRACKET_OPEN" comma_expressions "BRACKET_CLOSE" | "IF" expression command if_rest? | "FINALLY" identifier "CURLY_OPEN" command "CURLY_CLOSE" | "CATCH" identifier "CURLY_OPEN" command "CURLY_CLOSE" | "TRY" "CURLY_OPEN" command "CURLY_CLOSE" | "SWITCH" expression "CURLY_OPEN" cases "CURLY_CLOSE" | "FOR" "BRACKET_OPEN" identifier "OF" expression "BRACKET_CLOSE" command | "FOR" "BRACKET_OPEN" command expression command "BRACKET_CLOSE" command | "DO" command "WHILE" "BRACKET_OPEN" expression "BRACKET_CLOSE" | "WHILE" "BRACKET_OPEN" expression "BRACKET_CLOSE" command | "RETURN" | "CONTINUE" | "BREAK" | "RETURN" expression | "THROW" expression | "DELETE" identifier}

	$next=parser_peek(true);
	switch ($next[0])
	{
		case 'VAR':
			parser_next();
			$t=_js_parse_comma_parameters();
			if (is_null($t)) return NULL;
			$command=array('VAR',$t,$GLOBALS['JS_PARSE_POSITION']);
			break;

		case 'DELETE':
			parser_next();
			$variable=_js_parse_variable();
			if (is_null($variable)) return NULL;
			$command=array('DELETE',$variable,$GLOBALS['JS_PARSE_POSITION']);
			break;

		case 'FUNCTION':
			$command=array('INNER_FUNCTION',_js_parse_function_dec(),$GLOBALS['JS_PARSE_POSITION']);
			break;

		case 'IF':
			parser_next();
			$c_pos=$GLOBALS['JS_PARSE_POSITION'];
			if (is_null(parser_expect('BRACKET_OPEN'))) return NULL;
			$expression=_js_parse_expression();
			if (is_null($expression)) return NULL;
			if (is_null(parser_expect('BRACKET_CLOSE'))) return NULL;
			$command=_js_parse_command();
			if (is_null($command)) return NULL;

			$next_2=parser_peek();
			if ($next_2=='ELSE')
			{
				$if_rest=_js_parse_if_rest();
				if (is_null($if_rest)) return NULL;
				$command=array('IF_ELSE',$expression,$command,$if_rest,$c_pos);
			} else
			{
				$command=array('IF',$expression,$command,$c_pos);
			}
			break;

		case 'TRY':
			parser_next();
			$c_pos=$GLOBALS['JS_PARSE_POSITION'];
			$command=_js_parse_command();
			if (is_null($command)) return NULL;
			$command=array('TRY',$command,$c_pos);
			break;

		case 'WITH':
			parser_next();
			$c_pos=$GLOBALS['JS_PARSE_POSITION'];
			if (is_null(parser_expect('BRACKET_OPEN'))) return NULL;
			$var=_js_parse_variable();
			if (is_null($var)) $exp=NULL;
			if (is_null(parser_expect('BRACKET_CLOSE'))) return NULL;
			$command=_js_parse_command();
			if (is_null($command)) return NULL;
			$command=array('WITH',$var,$command,$c_pos);
			break;

		case 'CATCH':
			parser_next();
			$c_pos=$GLOBALS['JS_PARSE_POSITION'];
			if (is_null(parser_expect('BRACKET_OPEN'))) return NULL;
			$target=parser_expect('IDENTIFIER');
			if (is_null($target)) return NULL;
			if (is_null(parser_expect('BRACKET_CLOSE'))) return NULL;
			$command=_js_parse_command();
			if (is_null($command)) return NULL;
			$command=array('CATCH',$target,$command,$c_pos);
			break;

		case 'FINALLY':
			parser_next();
			$c_pos=$GLOBALS['JS_PARSE_POSITION'];
			$command=_js_parse_command();
			if (is_null($command)) return NULL;
			$command=array('FINALLY',$command,$c_pos);
			break;

		case 'SWITCH':
			parser_next();
			$c_pos=$GLOBALS['JS_PARSE_POSITION'];
			$expression=_js_parse_expression();
			if (is_null($expression)) return NULL;
			if (is_null(parser_expect('CURLY_OPEN'))) return NULL;
			$cases=_js_parse_cases();
			if (is_null($cases)) return NULL;
			if (is_null(parser_expect('CURLY_CLOSE'))) return NULL;
			$command=array('SWITCH',$expression,$cases,$c_pos);
			break;

		case 'FOR':
			parser_next();
			$c_pos=$GLOBALS['JS_PARSE_POSITION'];
			if (is_null(parser_expect('BRACKET_OPEN'))) return NULL;
			$_test=parser_peek_dist(1);
			$_test2=parser_peek_dist(2);
			if (($_test2=='OF') || ($_test2=='IN') || ($_test=='OF') || ($_test=='IN'))
			{
				$test=parser_peek();
				if ($test=='VAR') parser_next();

				$c_pos=$GLOBALS['JS_PARSE_POSITION'];
				$variable=_js_parse_variable();
				if (is_null($variable)) return NULL;
				if (is_null(parser_expect((($_test=='OF') || ($_test=='IN'))?$_test:$_test2))) return NULL;
				$expression=_js_parse_expression();
				if (is_null($expression)) return NULL;
				if (is_null(parser_expect('BRACKET_CLOSE'))) return NULL;
				$loop_command=_js_parse_command();
				if (is_null($loop_command)) return NULL;
				$command=array('FOREACH_list',$expression,$variable,$loop_command,$c_pos);
			} else
			{
				$next_2=parser_peek();
				if ($next_2=='COMMAND_TERMINATE')
				{
					$init_command=NULL;
				} else
				{
					$init_command=_js_parse_command_actual();
					if (is_null($init_command)) return NULL;
				}
				if (is_null(parser_expect('COMMAND_TERMINATE'))) return NULL;
				$control_expression=_js_parse_expression();
				if (is_null($control_expression)) return NULL;
				if (is_null(parser_expect('COMMAND_TERMINATE'))) return NULL;
				$next_2=parser_peek();
				if ($next_2=='BRACKET_CLOSE')
				{
					$control_command=NULL;
				} else
				{
					$control_command=_js_parse_command_actual();
					if (is_null($control_command)) return NULL;
				}
				if (is_null(parser_expect('BRACKET_CLOSE'))) return NULL;
				$next_2=parser_peek();
				if ($next_2=='COMMAND_TERMINATE')
				{
					$loop_command=NULL;
				} else
				{
					$loop_command=_js_parse_command();
					if (is_null($loop_command)) return NULL;
				}
				$command=array('FOR',$init_command,$control_expression,$control_command,$loop_command,$c_pos);
			}
			break;

		case 'DO':
			parser_next();
			$c_pos=$GLOBALS['JS_PARSE_POSITION'];
			$loop_command=_js_parse_command();
			if (is_null($loop_command)) return NULL;
			if (is_null(parser_expect('WHILE'))) return NULL;
			if (is_null(parser_expect('BRACKET_OPEN'))) return NULL;
			$control_expression=_js_parse_expression();
			if (is_null($control_expression)) return NULL;
			if (is_null(parser_expect('BRACKET_CLOSE'))) return NULL;
			$command=array('DO',$control_expression,$loop_command,$c_pos);
			break;

		case 'WHILE':
			parser_next();
			$c_pos=$GLOBALS['JS_PARSE_POSITION'];
			if (is_null(parser_expect('BRACKET_OPEN'))) return NULL;
			$control_expression=_js_parse_expression();
			if (is_null($control_expression)) return NULL;
			if (is_null(parser_expect('BRACKET_CLOSE'))) return NULL;
			$loop_command=_js_parse_command();
			if (is_null($loop_command)) return NULL;
			$command=array('WHILE',$control_expression,$loop_command,$c_pos);
			break;

		case 'RETURN':
			parser_next();
			$next_2=parser_peek();
			switch ($next_2)
			{
				case 'COMMAND_TERMINATE':
					$command=array('RETURN',array('SOLO',array('LITERAL',array('Null')),$GLOBALS['JS_PARSE_POSITION']),$GLOBALS['JS_PARSE_POSITION']);
					break;

				default:
					$expression=_js_parse_expression();
					if (is_null($expression)) return NULL;
					$command=array('RETURN',$expression,$GLOBALS['JS_PARSE_POSITION']);
			}
			break;

		case 'THROW':
			parser_next();
			$expression=_js_parse_expression();
			if (is_null($expression)) return NULL;
			$command=array('THROW',$expression,$GLOBALS['JS_PARSE_POSITION']);
			break;

		case 'CONTINUE':
			parser_next();
			$command=array('CONTINUE',array('SOLO',array('LITERAL',array('NUMBER',1)),$GLOBALS['JS_PARSE_POSITION']),$GLOBALS['JS_PARSE_POSITION']);
			break;

		case 'BREAK':
			parser_next();
			$command=array('BREAK',array('SOLO',array('LITERAL',array('NUMBER',1)),$GLOBALS['JS_PARSE_POSITION']),$GLOBALS['JS_PARSE_POSITION']);
			break;

		default:
			$command=_js_parse_expression();
	}
	return $command;
}

/**
 * Return parse info for parse type.
 *
 * @return ?list			Parse info (NULL: error)
 */
function _js_parse_if_rest()
{
// Choice{else command | elseif expression command if_rest?}

	$next=parser_peek();
	switch ($next)
	{
		case 'ELSE':
			parser_next();
			$command=_js_parse_command();
			if (is_null($command)) return NULL;
			$if_rest=$command;
			break;

		default:
			js_parser_error('Expected <if_rest> but got '.$next);
			return NULL;
	}
	return $if_rest;
}

/**
 * Return parse info for parse type.
 *
 * @return ?list			Parse info (NULL: error)
 */
function _js_parse_cases()
{
// Choice{"CASE" expression "COLON" command* | "DEFAULT" "COLON" command*}*

	$next=parser_peek();
	$cases=array();
	while (($next=='CASE') || ($next=='DEFAULT'))
	{
		switch ($next)
		{
			case 'CASE':
				parser_next();
				$expression=_js_parse_expression();
				if (is_null($expression)) return NULL;
				if (is_null(parser_expect('COLON'))) return NULL;
				$next_2=parser_peek();
				$commands=array();
				while (($next_2!='CURLY_CLOSE') && ($next_2!='CASE') && ($next_2!='DEFAULT'))
				{
					$command=_js_parse_command();
					if (is_null($command)) return NULL;
					$commands=array_merge($commands,$command);
					$next_2=parser_peek();
				}
				$cases[]=array($expression,$commands);
				break;

			case 'DEFAULT':
				parser_next();
				if (is_null(parser_expect('COLON'))) return NULL;
				$next_2=parser_peek();
				$commands=array();
				while (($next_2!='CURLY_CLOSE') && ($next_2!='CASE'))
				{
					$command=_js_parse_command();
					if (is_null($command)) return NULL;
					$commands+=$command;
					$next_2=parser_peek();
				}
				$cases[]=array(NULL,$commands);
				break;

			default:
				js_parser_error('Expected <cases> but got '.$next);
				return NULL;
		}

		$next=parser_peek();
	}

	return $cases;
}

/**
 * Return parse info for parse type.
 *
 * @param  boolean		Whether this is an anonymous function
 * @return ?list			Parse info (NULL: error)
 */
function _js_parse_function_dec($anonymous=false)
{
	$function=array();
	$function['offset']=$GLOBALS['JS_PARSE_POSITION'];
	if (is_null(parser_expect('FUNCTION'))) return NULL;
	if (!$anonymous)
	{
		$function['name']=parser_expect('IDENTIFIER');
		if (is_null($function['name'])) return NULL;
	}
	if (is_null(parser_expect('BRACKET_OPEN'))) return NULL;
	$function['parameters']=_js_parse_comma_parameters(false);
	if (is_null($function['parameters'])) return NULL;
	if (is_null(parser_expect('BRACKET_CLOSE'))) return NULL;
	$function['code']=_js_parse_command();
	if (is_null($function['code'])) return NULL;

	return $function;
}

/**
 * Return parse info for parse type.
 *
 * @return ?list			Parse info (NULL: error)
 */
function _js_parse_expression()
{
// Choice{expression_inner | expression_inner binary_operation expression_inner | expression_inner QUESTION expression_inner COLON expression_inner}

	global $OPS;

	$expression=_js_parse_expression_inner();
	if (is_null($expression)) return NULL;
	$op_list=array($expression);

	$next=parser_peek();
	while (in_array($next,$OPS))
	{
		parser_next();
		if ($next=='QUESTION')
		{
			$expression_2=_js_parse_expression();
			if (is_null($expression_2)) return NULL;
			if (is_null(parser_expect('COLON'))) return NULL;
			$expression_3=_js_parse_expression();
			if (is_null($expression_3)) return NULL;
			$op_list[]='UNARY_IF';
			$op_list[]=array($expression_2,$expression_3);
		} else
		{
			$expression_2=_js_parse_expression_inner();
			if (is_null($expression_2)) return NULL;
			$op_list[]=$next;
			$op_list[]=$expression_2;
		}
		$next=parser_peek();
	}

	$op_tree=precedence_sort($op_list);
	return $op_tree;
}

/**
 * Sort an unordered structure of operations into a precedence tree.
 *
 * @param  list			Ops in
 * @return list			Ops out
 */
function precedence_sort($op_list)
{
	if (count($op_list)==1)
	{
		return $op_list[0];
	}

	if (count($op_list)==2)
	{
		$_e_pos=$op_list[0][count($op_list[0])-1];
		$new=array($op_list[1],$op_list[0],$op_list[2],$_e_pos);
		return $new;
	}

	global $OPS;

	foreach ($OPS as $op_try)
	{
		foreach ($op_list as $JS_PARSE_POSITION=>$op)
		{
			if ($JS_PARSE_POSITION%2==0) continue;
			if ($op==$op_try)
			{
				$left=array_slice($op_list,0,$JS_PARSE_POSITION);
				//$right=array_slice($op_list,$JS_PARSE_POSITION+1);	// RoadSend
				$right=array();
				foreach ($op_list as $i=>$bit)
				{
					if ($i>$JS_PARSE_POSITION) $right[]=$bit;
				}
				$_e_pos=$left[count($left)-1][count($left[count($left)-1])-1];
				$_left=precedence_sort($left);
				$_right=precedence_sort($right);
				return array($op,$_left,$_right,$_e_pos);
			}
		}
	}

	// Should never get here
	echo '!';
	print_r($op_list);
//	print_r(debug_backtrace());
}

/**
 * Return parse info for parse type.
 *
 * @return ?list			Parse info (NULL: error)
 */
function _js_parse_expression_inner()
{
// Choice{"BW_NOT" expression | "BOOLEAN_NOT" expression | "TYPEOF" "IDENTIFIER" | "IDENTIFIER" "INSTANCEOF" "IDENTIFIER" | SUBTRACT expression | literal | variable | variable "BRACKET_OPEN" comma_parameters "BRACKET_CLOSE" | "IDENTIFIER" | "IDENTIFIER" "BRACKET_OPEN" comma_parameters "BRACKET_CLOSE" | "NEW" "IDENTIFIER" "BRACKET_OPEN" comma_expressions "BRACKET_CLOSE" | "NEW" "IDENTIFIER" | "ARRAY" "BRACKET_OPEN" create_array "BRACKET_CLOSE" | "BRACKET_OPEN" expression "BRACKET_CLOSE" | "BRACKET_OPEN" assignment "BRACKET_CLOSE"}

	$next=parser_peek();
	if (in_array($next,array('number_literal','string_literal','true','false','null','undefined','NaN','infinity'))) // little trick
	{
		$next='*literal';
	}
	switch ($next)
	{
		case 'DEC':
			parser_next();
			$variable=_js_parse_variable();
			if (is_null($variable)) return NULL;
			$expression=array('PRE_DEC',$variable,$GLOBALS['JS_PARSE_POSITION']);
			break;

		case 'INC':
			parser_next();
			$variable=_js_parse_variable();
			if (is_null($variable)) return NULL;
			$expression=array('PRE_INC',$variable,$GLOBALS['JS_PARSE_POSITION']);
			break;

		case 'BOOLEAN_NOT':
			parser_next();
			$_expression=_js_parse_expression_inner();
			if (is_null($_expression)) return NULL;
			$expression=array('BOOLEAN_NOT',$_expression,$GLOBALS['JS_PARSE_POSITION']);
			break;

		case 'BW_NOT':
			parser_next();
			$_expression=_js_parse_expression_inner();
			if (is_null($_expression)) return NULL;
			$expression=array('BW_NOT',$_expression,$GLOBALS['JS_PARSE_POSITION']);
			break;

		case 'TYPEOF':
			parser_next();
			$_expression=_js_parse_expression_inner();
			if (is_null($_expression)) return NULL;
			$expression=array('TYPEOF',$_expression,$GLOBALS['JS_PARSE_POSITION']);
			break;

		case 'SUBTRACT':
			parser_next();
			$_expression=_js_parse_expression_inner();
			if (is_null($_expression)) return NULL;
			$expression=array('NEGATE',$_expression,$GLOBALS['JS_PARSE_POSITION']);
			break;

		case '*literal':
			$literal=_js_parse_literal();
			if (is_null($literal)) return NULL;
			$expression=array('LITERAL',$literal,$GLOBALS['JS_PARSE_POSITION']);
			break;

		case 'IDENTIFIER':
			$variable=_js_parse_variable();
			if (is_null($variable)) return NULL;
			$expression=_js_parse_identify_chain($variable);
			break;

		case 'FUNCTION':
			$function=_js_parse_function_dec(true);
			if (is_null($function)) return NULL;
			$expression=array('NEW_OBJECT_FUNCTION',$function,$GLOBALS['JS_PARSE_POSITION']);
			break;

		case 'NEW':
			parser_next();
			$identifier=parser_next(true);
			if ($identifier[0]!='IDENTIFIER')
			{
				js_parser_error('Expected IDENTIFIER but got '.$identifier[0]);
				return NULL;
			}
			$next_2=parser_peek();
			if ($next_2=='BRACKET_OPEN')
			{
				parser_next();
				$expressions=_js_parse_comma_expressions();
				if (is_null($expressions)) return NULL;
				if (is_null(parser_expect('BRACKET_CLOSE'))) return NULL;
				$expression=array('NEW_OBJECT',$identifier[1],$expressions,$GLOBALS['JS_PARSE_POSITION']);
			} else
			{
				$expression=array('NEW_OBJECT',$identifier[1],array(),$GLOBALS['JS_PARSE_POSITION']);
			}
			break;

		case 'EXTRACT_OPEN':
			parser_next();
			$expressions=_js_parse_comma_expressions(true);
			if (is_null($expressions)) return NULL;
			$expression=array('NEW_OBJECT','Array',$expressions,$GLOBALS['JS_PARSE_POSITION']);
			if (is_null(parser_expect('EXTRACT_CLOSE'))) return NULL;
			break;

		case 'CURLY_OPEN':
			parser_next();
			$expressions=_js_parse_comma_parameters(true,'COLON');
			if (is_null($expressions)) return NULL;
			$expression=array('NEW_OBJECT','!Object',$expressions,$GLOBALS['JS_PARSE_POSITION']); // This is a hack. There could be no general-purpose expressions constructor for an object. The member names aren't even stored. But as it's dynamic, we couldn't check this anyway. So we just store a new "unknown" object and check the expressions as isolated (which shoving them in a constructor function will do).
			if (is_null(parser_expect('CURLY_CLOSE'))) return NULL;
			break;

		case 'BRACKET_OPEN':
			parser_next();

			// Look ahead to see if this is an embedded assignment or a cast
			$next_2=parser_peek_dist(0);
			$next_3=parser_peek_dist(1);
			if ($next_3=='EQUAL') // Not really necessary, but makes a bit cleaner (plus legacy from more restrained PHP version)
			{
				$target=_js_parse_variable();
				if (is_null($target)) return NULL;
				if (is_null(parser_expect('EQUAL'))) return NULL;
				$_expression=_js_parse_expression();
				if (is_null($_expression)) return NULL;
				$expression=array('ASSIGNMENT','EQUAL',$target,$_expression,$GLOBALS['JS_PARSE_POSITION']);
				if (is_null(parser_expect('BRACKET_CLOSE'))) return NULL;
			} else
			{
				$_expression=_js_parse_expression();
				if (is_null($_expression)) return NULL;
				if (is_null(parser_expect('BRACKET_CLOSE'))) return NULL;
				$test=parser_peek();
				if ($test=='BRACKET_OPEN')
				{
					$variable=array('VARIABLE',$_expression,array(),$GLOBALS['JS_PARSE_POSITION']);
					parser_next();
					$parameters=_js_parse_comma_expressions();
					if (is_null($parameters)) return NULL;
					if (is_null(parser_expect('BRACKET_CLOSE'))) return NULL;
					$expression=array('CALL',$variable,$parameters,$GLOBALS['JS_PARSE_POSITION']);
				} else
				{
					$expression=array('BRACKETED',$_expression,$GLOBALS['JS_PARSE_POSITION']);
				}
			}
			break;

		default:
			js_parser_error('Invalid expression '.$next);
			return NULL;
	}
	return $expression;
}

/**
 * Return parse info for parse type.
 *
 * @param  list			The variable
 * @return ?list			Parse info (NULL: error)
 */
function _js_parse_identify_chain($variable)
{
	$next_2=parser_peek();
	switch ($next_2)
	{
		case 'DEC':
			parser_next();
			$expression=array('DEC',$variable,$GLOBALS['JS_PARSE_POSITION']);
			break;

		case 'INC':
			parser_next();
			$expression=array('INC',$variable,$GLOBALS['JS_PARSE_POSITION']);
			break;

		case 'BRACKET_OPEN': // Is it an inline direct function call, or possibly a longer chain
			parser_next();
			$parameters=_js_parse_comma_expressions();
			if (is_null($parameters)) return NULL;
			if (is_null(parser_expect('BRACKET_CLOSE'))) return NULL;
			$expression=array('CALL',$variable,$parameters,$GLOBALS['JS_PARSE_POSITION']);
			//log_special('functions',$next[1].'/'.count($parameters));

			// Now, it is possible we are actually part of a larger variable
			$next_2=parser_peek();
			if ($next_2=='EXTRACT_OPEN') // If this is a "." then we will handle that as an operator
			{
				$extra=_js_parse_variable_actual();
				$expression=array('VARIABLE',$expression,$extra,$GLOBALS['JS_PARSE_POSITION']);
			}

			// Extension?
			$next_2=parser_peek();
			if (in_array($next_2,array('BRACKET_OPEN','DEC','INC','INSTANCEOF','EQUAL','CONCAT_EQUAL','DIV_EQUAL','MUL_EQUAL','MINUS_EQUAL','PLUS_EQUAL','SL_EQUAL','SR_EQUAL','ZSR_EQUAL','BW_AND_EQUAL','BW_OR_EQUAL')))
				return _js_parse_identify_chain(array('VARIABLE',$expression,array(),$GLOBALS['JS_PARSE_POSITION']));
			break;

		case 'INSTANCEOF':
			parser_next();
			$identifier=parser_expect('IDENTIFIER');
			if (is_null($identifier)) return NULL;
			$expression=array('INSTANCEOF',$variable,$identifier,$GLOBALS['JS_PARSE_POSITION']);
			break;

		default:
			if (in_array($next_2,array('EQUAL','CONCAT_EQUAL','DIV_EQUAL','MUL_EQUAL','MINUS_EQUAL','PLUS_EQUAL','SL_EQUAL','SR_EQUAL','ZSR_EQUAL','BW_AND_EQUAL','BW_OR_EQUAL')))
			{
				$assignment=_js_parse_assignment_operator();
				if (is_null($assignment)) return NULL;
				$_expression=_js_parse_expression();
				if (is_null($_expression)) return NULL;
				$expression=array('ASSIGNMENT',$assignment,$variable,$_expression,$GLOBALS['JS_PARSE_POSITION']);
			} else
			{
				$expression=$variable;
			}
	}

	return $expression;
}

/**
 * Return parse info for parse type.
 *
 * @return ?list			Parse info (NULL: error)
 */
function _js_parse_variable()
{
// Choice{"IDENTIFIER" | "IDENTIFIER" "OBJECT_OPERATOR" "IDENTIFIER" | "IDENTIFIER" "EXTRACT_OPEN" expression "EXTRACT_CLOSE"}

	$variable=parser_next(true);
	if ($variable[0]!='IDENTIFIER')
	{
		js_parser_error('Expected IDENTIFIER but got '.$variable[0]);
		return NULL;
	}

	$variable_actual=_js_parse_variable_actual();
	if (is_null($variable_actual)) return NULL;

	return array('VARIABLE',$variable[1],$variable_actual,$GLOBALS['JS_PARSE_POSITION']);
}

/**
 * Return parse info for parse type.
 *
 * @return ?list			Parse info (NULL: error)
 */
function _js_parse_variable_actual()
{
	$next=parser_peek();
	switch ($next)
	{
		case 'OBJECT_OPERATOR':
			parser_next();
			$next_2=parser_peek(true);
			if ($next_2[0]!='IDENTIFIER')
			{
				js_parser_error('Expected variable/identifier to be dereferenced from object variable but got '.$next_2);
				return NULL;
			}
			if (is_null(parser_expect('IDENTIFIER'))) return NULL;
//			if ($next_2[0]=='IDENTIFIER')
//			{
				$dereference=array('VARIABLE',$next_2[1],array(),$GLOBALS['JS_PARSE_POSITION']);
//			} else
//			{
//				$dereference=_js_parse_variable();
//			}
			$tunnel=array();
			$next_3=parser_peek();
			$next_4=parser_peek_dist(1);
			if ((($next_3=='EXTRACT_OPEN') && ($next_4!='EXTRACT_CLOSE')) || ($next_3=='CURLY_OPEN') || ($next_3=='OBJECT_OPERATOR'))
			{
				$tunnel=_js_parse_variable_actual();
				if (is_null($tunnel)) return NULL;
			}

			$variable=array('OBJECT_OPERATOR',$dereference,$tunnel,$GLOBALS['JS_PARSE_POSITION']);
			break;

		case 'EXTRACT_OPEN':
			$next_t=parser_peek_dist(1);
			if ($next_t=='EXTRACT_CLOSE')
			{
				$variable=array();
				break;
			}
			parser_next();
			$next_2=parser_peek(true);
			$expression=_js_parse_expression();
			if (is_null($expression)) return NULL;
			if (is_null(parser_expect('EXTRACT_CLOSE'))) return NULL;
			$tunnel=array();
			$next_3=parser_peek();
			$next_4=parser_peek_dist(1);
			if ((($next_3=='EXTRACT_OPEN') && ($next_4!='EXTRACT_CLOSE')) || ($next_3=='OBJECT_OPERATOR'))
			{
				$tunnel=_js_parse_variable_actual();
				if (is_null($tunnel)) return NULL;
			}
			$variable=array('ARRAY_AT',$expression,$tunnel,$GLOBALS['JS_PARSE_POSITION']);
			break;

		default:
			$variable=array();
			break;
	}

	return $variable;
}

/**
 * Return parse info for parse type.
 *
 * @return ?list			Parse info (NULL: error)
 */
function _js_parse_assignment_operator()
{
// Choice{"EQUAL" | "CONCAT_EQUAL" | "DIV_EQUAL" | "MUL_EQUAL" | "MINUS_EQUAL" | "PLUS_EQUAL" | "SL_EQUAL" | "SR_EQUAL" | "ZSR_EQUAL" | "BW_AND_EQUAL" | "BW_OR_EQUAL"}

	$next=parser_next();
	if (!in_array($next,array('EQUAL','CONCAT_EQUAL','DIV_EQUAL','MUL_EQUAL','MINUS_EQUAL','PLUS_EQUAL','SL_EQUAL','SR_EQUAL','ZSR_EQUAL','BW_AND_EQUAL','BW_OR_EQUAL')))
	{
		js_parser_error('Expected assignment operator but got '.$next);
		return NULL;
	}
	return $next;
}

/**
 * Return parse info for parse type.
 *
 * @return ?list			Parse info (NULL: error)
 */
function _js_parse_literal()
{
// Choice{"SUBTRACT" literal | "number_literal" | "string_literal" | "true" | "false" | "null" | "undefined" | "infinity" | "NaN" | "IDENTIFIER"}

	$next=parser_peek();
	switch ($next)
	{
		case 'SUBTRACT':
			parser_next();
			$_literal=_js_parse_literal();
			if (is_null($_literal)) return NULL;
			$literal=array('NEGATE',$_literal,$GLOBALS['JS_PARSE_POSITION']);
			break;

		case 'number_literal':
			$_literal=parser_next(true);
			$literal=array('Number',$_literal[1],$GLOBALS['JS_PARSE_POSITION']);
			break;

		case 'string_literal':
			$_literal=parser_next(true);
			$literal=array('String',$_literal[1],$GLOBALS['JS_PARSE_POSITION']);
			break;

		case 'true':
			parser_next();
			$literal=array('Boolean',true,$GLOBALS['JS_PARSE_POSITION']);
			break;

		case 'false':
			parser_next();
			$literal=array('Boolean',false,$GLOBALS['JS_PARSE_POSITION']);
			break;

		case 'null':
			parser_next();
			$literal=array('Null',$GLOBALS['JS_PARSE_POSITION']);
			break;

		case 'undefined':
			parser_next();
			$literal=array('Undefined',$GLOBALS['JS_PARSE_POSITION']);
			break;

		case 'NaN':
			parser_next();
			$literal=array('Number',$GLOBALS['JS_PARSE_POSITION']);
			break;

		case 'infinity':
			parser_next();
			$literal=array('Number',$GLOBALS['JS_PARSE_POSITION']);
			break;

		default:
			js_parser_error('Expected <literal> but got '.$next);
			return NULL;
	}
	return $literal;
}

/**
 * Return parse info for parse type.
 *
 * @param  boolean		Whether to allow blanks in the list
 * @return ?list			Parse info (NULL: error)
 */
function _js_parse_comma_expressions($allow_blanks=false)
{
// Choice{expression "COMMA" comma_expressions | expression}

	$expressions=array();

	$next=parser_peek();
	if (($next=='BRACKET_CLOSE') || ($next=='COMMAND_TERMINATE') || ($next=='EXTRACT_CLOSE')) return array();

	$next_2='';
	do
	{
		$next_2=parser_peek();
		if (($next_2=='COMMA') || ($next_2=='EXTRACT_CLOSE') || ($next_2=='BRACKET_CLOSE') || ($next_2=='COMMAND_TERMINATE'))
		{
			$expression=array('LITERAL',array('Undefined',$GLOBALS['JS_PARSE_POSITION']),$GLOBALS['JS_PARSE_POSITION']);
		} else
		{
			$expression=_js_parse_expression();
			if (is_null($expression)) return NULL;
		}
		$expressions[]=$expression;

		$next_2=parser_peek();
		if ($next_2=='COMMA') parser_next();
	}
	while ($next_2=='COMMA');

	return $expressions;
}

/**
 * Return parse info for parse type.
 *
 * @param  boolean		Whether to allow blanks in the list
 * @return ?list			Parse info (NULL: error)
 */
function _js_parse_comma_variables($allow_blanks=false)
{
// Choice{"variable" "COMMA" comma_variables | "variable"}

	$variables=array();

	$next=parser_peek();
	if (($next=='BRACKET_CLOSE') || ($next=='COMMAND_TERMINATE')) return $variables;

	$next_2='';
	do
	{
		$next_2=parser_peek();
		while (($allow_blanks) && (($next_2=='COMMA') || ($next_2=='BRACKET_CLOSE')))
		{
			if ($next_2=='COMMA') // ,,
			{
				parser_next();
				$variables[]=array('VARIABLE','_',array());
			}
			elseif ($next_2=='BRACKET_CLOSE') // ,,
			{
				$variables[]=array('VARIABLE','_',array());
				return $variables;
			}

			$next_2=parser_peek();
		}

		$variable=_js_parse_variable();
		if (is_null($variable)) return NULL;
		$variables[]=$variable;

		$next_2=parser_peek();
		if ($next_2=='COMMA') parser_next();
	}
	while ($next_2=='COMMA');

	return $variables;
}

/**
 * Return parse info for parse type.
 *
 * @param  boolean		Whether to allow expressions in this
 * @param  string			The token that sits as the 'separator' between name and value
 * @return ?list			Parse info (NULL: error)
 */
function _js_parse_comma_parameters($allow_expressions=true,$separator='EQUAL')
{
// Choice{parameter | parameter "COMMA" comma_parameters}?

	$parameters=array();

	$next=parser_peek();
	if (($next=='BRACKET_CLOSE') || ($next=='COMMAND_TERMINATE') || ($next=='CURLY_CLOSE')) return $parameters;

	$next_2='';
	do
	{
		$parameter=_js_parse_parameter($allow_expressions,$separator);
		if (is_null($parameter)) return NULL;
		$parameters[]=$parameter;

		$next_2=parser_peek();
		if ($next_2=='COMMA') parser_next();
	}
	while ($next_2=='COMMA');

	return $parameters;
}

/**
 * Return parse info for parse type.
 *
 * @param  boolean		Whether to allow expressions in this
 * @param  string			The token that sits as the 'separator' between name and value
 * @return ?list			Parse info (NULL: error)
 */
function _js_parse_parameter($allow_expressions=true,$separator='EQUAL')
{
// Choice{"REFERENCE" "variable" | "variable" | "variable" "EQUAL" literal}

	$next=parser_next(true);
	if (($next[0]=='IDENTIFIER') || ((substr($next[0],-8)=='_literal') && ($separator=='COLON')))
	{
		$parameter=array('CALL_BY_VALUE',$next[1],NULL,$GLOBALS['JS_PARSE_POSITION']);
		if ($allow_expressions)
		{
			$next_2=parser_peek();
			if ($next_2==$separator)
			{
				parser_next();
				$value=_js_parse_expression();
				if (is_null($value)) return NULL;
				$parameter[2]=$value;
			}
		}
	} else
	{
		js_parser_error('Expected <parameter> but got '.$next[0]);
		return NULL;
	}
	return $parameter;
}

/**
 * Expect a token during parsing. Give error if not found. Else give token parameters.
 *
 * @param  string			The token we want
 * @return ?mixed			The token parameters (NULL: error)
 */
function parser_expect($token)
{
	global $JS_LEX_TOKENS,$JS_PARSE_POSITION;
	if (!isset($JS_LEX_TOKENS[$JS_PARSE_POSITION]))
	{
		js_parser_error('Ran out of input when expecting '.$token);
		return NULL;
	}
	$next=$JS_LEX_TOKENS[$JS_PARSE_POSITION];
	if ($next[0]=='comment') return parser_expect($token);
	$JS_PARSE_POSITION++;
	if ($next[0]!=$token)
	{
		js_parser_error('Expected '.$token.' but got '.$next[0]);
		return NULL;
	}
	return $next[1];
}

/**
 * Peek to find the next token.
 *
 * @param  boolean		Whether we want all the token parameters (as opposed to just the first)
 * @return ?mixed			All the token parameters, or just the first (NULL: error)
 */
function parser_peek($all=false)
{
	global $JS_LEX_TOKENS,$JS_PARSE_POSITION;
	if (!isset($JS_LEX_TOKENS[$JS_PARSE_POSITION])) return NULL;
	if ($JS_LEX_TOKENS[$JS_PARSE_POSITION][0]=='comment')
	{
		$JS_PARSE_POSITION++;
		return parser_peek($all);
	}
	if ($all) return $JS_LEX_TOKENS[$JS_PARSE_POSITION];
	return $JS_LEX_TOKENS[$JS_PARSE_POSITION][0];
}

/**
 * Peek to find the next token after a distance.
 *
 * @param  integer		The distance
 * @param  ?integer		Whether to start looking from (NULL: current position in parse)
 * @return ?mixed			The first token parameter (NULL: error)
 */
function parser_peek_dist($d,$p=NULL)
{
	global $JS_LEX_TOKENS,$JS_PARSE_POSITION;
	if (is_null($p)) $p=$JS_PARSE_POSITION;
	while ($d!=0)
	{
		if (!isset($JS_LEX_TOKENS[$p])) return NULL;
		if ($JS_LEX_TOKENS[$p][0]=='comment')
		{
			return parser_peek_dist($d,$p+1);
		}
		$p++;
		$d--;
	}
	if (!isset($JS_LEX_TOKENS[$p])) return NULL;
	return $JS_LEX_TOKENS[$p][0];
}

/**
 * Find the next token and move on.
 *
 * @param  boolean		Whether we want all the token parameters (as opposed to just the first)
 * @return ?mixed			All the token parameters, or just the first (NULL: error)
 */
function parser_next($all=false)
{
	global $JS_LEX_TOKENS,$JS_PARSE_POSITION;
	if (!isset($JS_LEX_TOKENS[$JS_PARSE_POSITION])) return NULL;
	$next=$JS_LEX_TOKENS[$JS_PARSE_POSITION];
	$JS_PARSE_POSITION++;
	if ($next[0]=='comment') return parser_next($all);
	if ($all) return $next;
	return $next[0];
}

/**
 * Give a parse error.
 *
 * @param  string			The error
 * @return ?boolean		Always NULL (NULL: exit)
 */
function js_parser_error($message)
{
	global $JS_LEX_TOKENS,$JS_PARSE_POSITION;
	list($pos,$line,$_,$i)=js_pos_to_line_details($JS_PARSE_POSITION);
	return js_die_error('PARSER',$pos,$line,$message,$i);
}


