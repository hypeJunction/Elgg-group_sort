Group List Sorting for Elgg
===========================
![Elgg 2.0](https://img.shields.io/badge/Elgg-2.0.x-orange.svg?style=flat-square)

## Features

 * Implements generic API and UI for sorting group lists
 * By default, provides sorting by Name, Membership count, Time created, Latest activity
 * Provides a filter to list open and closed groups, featured groups, groups user administers or a member of, group invitations
 * Extendable via hooks

![Group Sort](https://raw.github.com/hypeJunction/Elgg-group_sort/master/screenshots/groups.png "Group Search and Sort Interface")

## Usage

### List users

```php

echo elgg_view('lists/groups', array(
	'options' => array(
		'types' => 'group',
	),
	'callback' => 'elgg_list_entities',
));
```

### Custom sort fields

Use `'sort_fields','group'` plugin hook to add new fields to the sort select input.
Use `'sort_options', 'group'` to add custom queries to ege* options.