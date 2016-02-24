<?php

$identifier = elgg_extract('identifier', $vars, 'groups');

$fields = '';

if (elgg_extract('show_subtype', $vars, false)) {
	$types = get_registered_entity_types();
	$types = elgg_trigger_plugin_hook('search_types', 'get_queries', $params, $types);

	$subtypes = elgg_extract('group', $types);
	if (!empty($subtypes)) {
		$subtype_options_values = array('' => elgg_echo('group:subtype:all'));
		foreach ($subtypes as $subtype) {
			$subtype_options_values[$subtype] = elgg_echo("item:group:$subtype");
		}
		$fields .= elgg_view_input('select', array(
			'name' => 'entity_subtype',
			'value' => elgg_extract('subtype', $vars, ''),
			'options_values' => $subtype_options_values,
			'class' => 'group-sort-select',
			'label' => elgg_echo('group:subtype:label'),
			'field_class' => 'group-sort-select-field',
		));
	}
}

if (elgg_extract('show_rel', $vars, true)) {
	$rel_options = group_sort_get_rel_options($vars);
	if (!empty($rel_options)) {
		$rel_options_values = array();
		foreach ($rel_options as $rel_option) {
			$rel_options_values[$rel_option] = elgg_echo("$identifier:rel:$rel_option");
		}
		$fields .= elgg_view_input('select', array(
			'name' => 'rel',
			'value' => elgg_extract('rel', $vars, ''),
			'options_values' => $rel_options_values,
			'class' => 'group-sort-select',
			'label' => elgg_echo('group:rel:label'),
			'field_class' => 'group-sort-select-field',
		));
	}
}

if (elgg_is_active_plugin('search') && elgg_extract('show_search', $vars, true)) {
	$fields .= elgg_view_input('text', array(
		'name' => 'query',
		'value' => elgg_extract('query', $vars),
		'class' => 'group-sort-query',
		'label' => elgg_echo('group:sort:search:label'),
		'field_class' => 'group-sort-query-field',
		'placeholder' => elgg_echo('group:sort:search:placeholder'),
	));
}

if (elgg_extract('show_sort', $vars, true)) {
	$sort_options = group_sort_get_sort_options($vars);
	if (!empty($sort_options)) {
		$sort_options_values = array();
		foreach ($sort_options as $sort_option) {
			$sort_options_values[$sort_option] = elgg_echo("$identifier:sort:$sort_option");
		}
		$fields .= elgg_view_input('select', array(
			'name' => 'sort',
			'value' => elgg_extract('sort', $vars, 'time_created::desc'),
			'options_values' => $sort_options_values,
			'class' => 'group-sort-select',
			'label' => elgg_echo('group:sort:label'),
			'field_class' => 'group-sort-select-field',
		));
	}
}

if (!$fields) {
	return;
}

echo elgg_format_element('div', [
	'class' => 'group-sort-fieldset',
		], $fields);

echo elgg_view_input('hidden', array(
	'name' => 'entity_type',
	'value' => 'group',
));

echo elgg_view_input('submit', array(
	'class' => 'hidden',
));
?>
<script>
	require(['forms/group/sort']);
</script>