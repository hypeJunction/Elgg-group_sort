<?php

/**
 * Group List Sort
 *
 * @author Ismayil Khayredinov <info@hypejunction.com>
 * @copyright Copyright (c) 2015, Ismayil Khayredinov
 */
require_once __DIR__ . '/autoloader.php';

elgg_register_event_handler('init', 'system', 'group_sort_init');

/**
 * Initialize the plugin
 * @return void
 */
function group_sort_init() {
	elgg_extend_view('elgg.css', 'forms/group/sort.css');
}

/**
 * Returns as list of sort options
 *
 * @param array $params Params to pass to the hook
 * @return array
 */
function group_sort_get_sort_options(array $params = array()) {

	$fields = array();

	$plugin = elgg_get_plugin_from_id('group_sort');
	$settings = $plugin->getAllSettings();
	foreach ($settings as $k => $val) {
		if (!$val) {
			continue;
		}
		list($sort, $option) = explode('::', $k);
		if ($sort && in_array(strtolower($option), array('asc', 'desc'))) {
			$fields[] = $k;
		}
	}
	return elgg_trigger_plugin_hook('sort_fields', 'group', $params, $fields);
}

/**
 * Adds sort options to the ege* options array
 * 
 * @param array  $options   ege* options
 * @param string $field     Sort field
 * @param string $direction Sort direction (asc|desc)
 * @return array
 */
function group_sort_add_sort_options(array $options = array(), $field = 'time_created', $direction = 'desc') {

	$dbprefix = elgg_get_config('dbprefix');
	$direction = strtoupper($direction);
	if (!in_array($direction, array('ASC', 'DESC'))) {
		$direction = 'DESC';
	}

	$order_by = explode(',', elgg_extract('order_by', $options, ''));
	array_walk($order_by, 'trim');
	
	$options['joins']['groups_entity'] = "JOIN {$dbprefix}groups_entity AS groups_entity ON groups_entity.guid = e.guid";

	switch ($field) {

		case 'type' :
		case 'subtype' :
		case 'guid' :
		case 'owner_guid' :
		case 'container_guid' :
		case 'site_guid' :
		case 'enabled' :
		case 'time_created';
		case 'time_updated' :
		case 'last_action' :
		case 'access_id' :
			array_unshift($order_by, "e.{$field} {$direction}");
			break;

		case 'member_count' :
			$options['joins']['member_count'] = "LEFT JOIN {$dbprefix}entity_relationships AS member_count ON member_count.guid_two = e.guid AND member_count.relationship = 'member'";
			$options['selects']['member_count'] = "COUNT(member_count.guid_one) as member_count";
			$options['group_by'] = 'member_count.guid_two';
			
			array_unshift($order_by, "member_count {$direction}");
			break;

//		case 'featured' :
//			$name_id = elgg_get_metastring_id('featured_group');
//			$value_id = elgg_get_metastring_id('yes');
//			$options['joins']['featured_md'] = "JOIN {$dbprefix}metadata AS featured_md ON featured_md.entity_guid = e.guid AND featured_md.name_id = $name_id AND featured_md.value_id = $value_id";
//			$options['selects']['featured_ts'] = "featured_md.time_created AS featured_ts";
//			//$options['group_by'] = 'featured_md.guid_two';
//
//			array_unshift($order_by, "featured_ts {$direction}");
//			break;
	}

	// Always order by name for matching fields
	$order_by[] = "groups_entity.name ASC";

	$options['order_by'] = implode(', ', array_unique(array_filter($order_by)));

	return elgg_trigger_plugin_hook('sort_options', 'group', null, $options);
}

/**
 * Adds relationship/metadata filters to the ege* options array
 *
 * @param array  $options   ege* options
 * @param string $rel       Filter name
 * @param string $user      User entity that relationship is determined for
 * @return array
 */
function group_sort_add_rel_options(array $options = array(), $rel = '', $user = null) {

	$dbprefix = elgg_get_config('dbprefix');

	if (!isset($user)) {
		$user = elgg_get_logged_in_user_entity();
	}

	$guid = ($user) ? (int) $user->guid : 0;
	
	switch ($rel) {

		case 'closed' :
		case 'open' :
			$name_id = elgg_get_metastring_id('membership');
			$value_id = elgg_get_metastring_id(ACCESS_PUBLIC);
			$operand = $rel == 'open' ? '=' : '!=';
			$options['wheres']['membership_md'] = "EXISTS (SELECT 1 FROM {$dbprefix}metadata 
				WHERE entity_guid = e.guid AND name_id = $name_id AND value_id $operand $value_id)";
			break;

		case 'featured' :
			$name_id = elgg_get_metastring_id('featured_group');
			$value_id = elgg_get_metastring_id('yes');
			$options['wheres']['featured_md'] = "EXISTS (SELECT 1 FROM {$dbprefix}metadata 
				WHERE entity_guid = e.guid AND name_id = $name_id AND value_id = $value_id)";
			break;

		case 'member' :
			$options['wheres']['member_rel'] = "EXISTS (SELECT 1 FROM {$dbprefix}entity_relationships 
				WHERE guid_one = $guid AND relationship = 'member' AND guid_two = e.guid)";
			break;

		case 'admin' :
			$options['wheres']['admin_rel'] = "(e.owner_guid = $guid OR EXISTS (SELECT 1 FROM {$dbprefix}entity_relationships
				WHERE guid_one = $guid AND relationship = 'group_admin' AND guid_two = e.guid))";
			break;

		case 'invited' :
			$options['wheres']['invited_rel'] = "EXISTS (SELECT 1 FROM {$dbprefix}entity_relationships
				WHERE guid_two = $guid AND relationship = 'invited' AND guid_one = e.guid)";
			break;

		case 'membership_request' :
			$options['wheres']['membership_request_rel'] = "EXISTS (SELECT 1 FROM {$dbprefix}entity_relationships
				WHERE guid_one = $guid AND relationship = 'membership_request' AND guid_two = e.guid)";
			break;
	}

	$params = array(
		'rel' => $rel,
		'user' => $user,
	);
	
	return elgg_trigger_plugin_hook('rel_options', 'group', $params, $options);
}

/**
 * Returns a list of group type/relationsihp filter options
 *
 * @param array $params Params to pass to the hook
 * @return array
 */
function group_sort_get_rel_options(array $params = array()) {

	$options = array(
		'',
		'open',
		'closed',
		'featured',
	);

	if (elgg_is_logged_in()) {
		$options[] = 'member';
		if (elgg_get_plugin_setting('limited_groups', 'groups') != 'yes' || elgg_is_admin_logged_in()) {
			$options[] = 'admin';
		}
		$options[] = 'invited';
		$options[] = 'membership_request';
	}
	return elgg_trigger_plugin_hook('sort_relationships', 'group', $params, $options);
}

/**
 * Adds search query options to the ege* options array
 *
 * @param array  $options   ege* options
 * @param string $query     Query
 * @return array
 */
function group_sort_add_search_query_options(array $options = array(), $query = '') {

	if (!elgg_is_active_plugin('search')) {
		return $options;
	}

	$dbprefix = elgg_get_config('dbprefix');
	$options['joins']['groups_entity'] = "JOIN {$dbprefix}groups_entity AS groups_entity ON groups_entity.guid = e.guid";

	$fields = array('name');
	$options['wheres'][] = search_get_where_sql('groups_entity', $fields, ['query' => $query], false);
	return $options;
}