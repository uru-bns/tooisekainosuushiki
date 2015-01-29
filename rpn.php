<?php

function calculate($formula, $operators_order_by_priority)
{
  return calculate_rpn(to_rpn($formula, $operators_order_by_priority));
}

function calculate_rpn($rpn)
{
  $operand_stack = [];

  foreach($rpn as $piece){
    if (is_numeric($piece)) {
      array_push($operand_stack, $piece);
    }
    else {
      $accumulator = array_pop($operand_stack);
      array_push(
        $operand_stack,
        (int)eval(sprintf('return %s %s %s;', array_pop($operand_stack), $piece, $accumulator))
      );
    }
  }

  return array_pop($operand_stack);
}

function to_rpn($formula, $operators_order_by_priority)
{
  $operator_stack = [];
  $rpn = [];

  foreach (split_formula($formula, $operators_order_by_priority) as $piece) {
    if (is_numeric($piece)) {
      $rpn[] = $piece;
      continue;
    }

    $the_priority = array_search($piece, $operators_order_by_priority);

    while (!empty($operator_stack)) {
      $last_operator = end($operator_stack);

      $last_operator_priority = array_search($last_operator, $operators_order_by_priority);
      if ($last_operator_priority < $the_priority) {
        break;
      }

      $rpn[] = array_pop($operator_stack);
    }

    array_push($operator_stack, $piece);
  }

  while (!empty($operator_stack)) {
    $rpn[] = array_pop($operator_stack);
  }

  return $rpn;
}

function split_formula($formula, $operators)
{
  $pattern = make_split_pattern_for_formula($operators);
  preg_match_all($pattern, $formula, $matches);

  return $matches[0];
}

function make_split_pattern_for_formula($operators)
{
  $temp = $operators;
  array_walk($temp, function(&$operator) {
    $operator = "\\".$operator;
  });

  return sprintf("/\\d+|[%s]/", implode('', $temp));
}
