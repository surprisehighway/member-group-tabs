<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Member Group Tabs config file
 */

if ( ! defined('SH_TABS_NAME'))
{
	define('SH_TABS_NAME', 'Member Group Tabs');
	define('SH_TABS_PACKAGE', 'sh_member_group_tabs');
	define('SH_TABS_VERSION', '1.4.1');
	define('SH_TABS_DOCS', 'https://github.com/surprisehighway/member-group-tabs');
}

/**
 * < EE 2.6.0 backward compat
 * Hat tip @ low - http://gotolow.com
 */
if ( ! function_exists('ee'))
{
	function ee()
	{
		static $EE;
		if ( ! $EE) $EE = get_instance();
		return $EE;
	}
}

/* End of file config.php */
/* Location: ./system/expressionengine/third_party/sh_member_group_tabs/config.php */