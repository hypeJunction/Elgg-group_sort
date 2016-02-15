<?php

$entity = elgg_extract('entity', $vars);

$sort_options = array(
	'alpha::asc',
	'alpha::desc',
	'time_created::asc',
	'time_created::desc',
	'member_count::desc',
	'member_count::asc',
	'last_action::asc',
	'last_action::desc',
);

$inputs = '';
foreach ($sort_options as $option) {
	$input = elgg_view('input/checkbox', array(
		'type' => 'checkbox',
		'name' => "params[$option]",
		'value' => 1,
		'checked' => ($entity->$option),
	));
	$label = elgg_format_element('label', [],  $input . elgg_echo("group:sort:$option"));
	$inputs .= elgg_format_element('li', [], $label);
}

echo elgg_format_element('ul', ['class' => 'elgg-checkboxes'], $inputs);