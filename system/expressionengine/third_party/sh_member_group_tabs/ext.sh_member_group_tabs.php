<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// include config file
include(PATH_THIRD.'sh_member_group_tabs/config.php');
 
// ------------------------------------------------------------------------

/**
 * Member Group Tabs Extension
 *
 * @package		Member Group Tabs
 * @category	Extension
 * @author		Bransin Anderson @ Surprise Highway
 * @link		http://surprisehighway.com/
 */

class Sh_member_group_tabs_ext {
	
	public $settings 		= array();
	public $description		= 'Allows you to create navigation tabs for member groups in the control panel.';
	public $docs_url		= SH_TABS_DOCS;
	public $name			= SH_TABS_NAME;
	public $settings_exist	= 'y';
	public $version			= SH_TABS_VERSION;
	
	private $EE;
	
	/**
	 * Constructor
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	public function __construct($settings = '')
	{
		$this->EE =& get_instance();
		$this->settings = $settings;
	}
	
	// ----------------------------------------------------------------------
	
	/**
	 * Settings Form
	 *
	 * @param   Array   Settings
	 * @return  void
	 */
	public function settings_form($current)
	{
		$this->_set_page_title();
		$this->EE->load->helper('form');
		$this->EE->load->library('table');

		$this->EE->cp->add_to_head('		
			<style type="text/css">
			.mainTable .hint { font-size: 11px; padding-left: 10px; font-weight: normal; opacity: .5; }
			</style>
			<script type="text/javascript">
			jQuery(function()
			{
				// Each table
				$(".mainTable").each(function()
				{	
					if ($(this).find(\'tr\').length >= 3 && $(this).find(\'td.sh_no_results\').length == 0)
					{
						// Make table rows full width for sortable dragging
						$("td").each(function()
						{
		        			$(this).css("width", $(this).width() +"px");
		    			});
						
						// Instantiate the table
						$(this).sortable(
						{
							delay: 100,
							axis : "y",
							items: "tbody tr",
							handle: ".sh_order_cell",
							start: function(event, ui)
							{
								ui.placeholder.html("<td colspan=\'4\' style=\'padding: 1em\'>&nbsp;</td>");
							},
							stop:	function(event, ui)
							{
								// Reset and add odd and even classes
						        $("tr:even", this).removeClass("odd even").addClass("even");
						        $("tr:odd", this).removeClass("odd even").addClass("odd");
    						}
						});
						
						// Set pointer of draggable handle
						$(this).find(\'.sh_order_cell\').css("cursor", "pointer");
					}
				});
			});
			</script>
		');
		
		$vars = array();
		$vars['settings'] = array();

		// query member groups that have access to CP
		$query = $this->EE->db->get_where('member_groups', array('can_access_cp' => 'y'));

		if ($query->num_rows() > 0)
		{
			// each member group
			foreach($query->result() as $group)
			{
				// set the group title
				$vars['settings']['sh_member_group_tabs'][$group->group_id] = array(
					'group_title' => $group->group_title
				);

				// create a place in our master settings array for tabs
				$vars['settings']['sh_member_group_tabs'][$group->group_id]['tabs'] = array();

				// if there are existing settings saved
				if (count($current) > 0)
				{
					// combine our existing saved extension settings to our master array
					// this is so we can repopulate the saved data in the view file
					foreach ($current['sh_member_group_tabs'] as $key => $val)
					{
						
						// combine by member group id key
						if ($group->group_id == $key)
						{	
							// place the saved settings into our global array under the tab key
							$vars['settings']['sh_member_group_tabs'][$group->group_id]['tabs'] = $val['tabs'];
						}

					}
				}
			}
		}		

		// load view
		return $this->EE->load->view('index', $vars, TRUE);		

	}

	/**
	 * Save Settings
	 *
	 * @return void
	 */

	public function save_settings()
	{
		$this->EE->lang->loadfile('sh_member_group_tabs');

		// unauthorized access
		if (empty($_POST))
		{
			show_error($this->EE->lang->line('unauthorized_access'));
		}

		// loop through each member group
		foreach ($this->EE->input->post('sh_member_group_tabs') as $key => $val)
		{
			foreach ($val['tabs'] as $tab_id => $tab_val)
			{
				// delete tabs that have no url or name values set and ones that are marked for deletion
				if (! $tab_val['name'] OR ! $tab_val['url'] OR isset($_POST['sh_member_group_tabs'][$key]['tabs'][$tab_id]['delete']))
				{
					unset($_POST['sh_member_group_tabs'][$key]['tabs'][$tab_id]);
				}
			}
		}

		// remove submit button value from our $_POST data
		unset($_POST['submit']);

		$this->EE->security->xss_clean($_POST);

		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->update('extensions', array('settings' => serialize($_POST)));

		$this->EE->session->set_flashdata('message_success', $this->EE->lang->line('preferences_updated'));
		$this->EE->functions->redirect(BASE.AMP.'C=addons_extensions'.AMP.'M=extension_settings'.AMP.'file=sh_member_group_tabs');

	}
	
	// ----------------------------------------------------------------------
	
	/**
	 * CP Menu Array
	 *
	 * @return void
	 */

	public function cp_menu_array($menu)
	{	
		// in case other extensions use this hook
		if ($this->EE->extensions->last_call !== FALSE)
		{
			$menu = $this->EE->extensions->last_call;
		}

		if ( ! empty($this->settings['sh_member_group_tabs']))
		{
			// get our tabs belonging to member group
			foreach ($this->settings['sh_member_group_tabs'][$this->EE->session->userdata['group_id']]['tabs'] as $tab_id => $tab_val)
			{	

				// fingerprint came into the picture v2.6.0
				$session_fingerprint = (APP_VER >= '2.6.0') ? ee()->session->userdata['fingerprint'] : $this->EE->session->userdata['session_id'];

				// EE 2.6 requires the admin session fingerprint on every URL in the CP
				$fingerprint_regex = '/S=([a-zA-Z0-9]*)/';
				$get_session_fingerprint = 'S='.$session_fingerprint;

				// are we working with an absolute path?
				$absolute_path = preg_match('/(http|https):\/\//', $tab_val['url']);
				
				// only mask extraterrestrial life (absolute links)
				$masked_url = FALSE;

				// absolute path
				if ($absolute_path)
				{
					$masked_url = TRUE;
				}
				// relative path
				else
				{
					// relative url - with fingerprint
					if (preg_match($fingerprint_regex, $tab_val['url']))
					{
						$tab_val['url'] = preg_replace($fingerprint_regex, $get_session_fingerprint, $tab_val['url']);
					}
					// relative url - no fingerprint, we'll append it to the url
					else
					{
						$tab_val['url'] = ($tab_val['url'] .AMP. $get_session_fingerprint);
					}
				}

				// Serve up the tabs
				$this->EE->lang->language['nav_sh_tab_'.$tab_id] = $tab_val['name'];
				
				if ($masked_url === TRUE)
				{
					$menu['sh_tab_'.$tab_id] = $this->EE->cp->masked_url($tab_val['url']);
				}
				else
				{
					$menu['sh_tab_'.$tab_id] = $tab_val['url'];
				}
			}
		}

		return $menu;
	}

	// ----------------------------------------------------------------------

	/**
	 * Set Page Title
	 */
	private function _set_page_title($line = SH_TABS_NAME)
	{
		if ($line != SH_TABS_NAME)
		{
			$this->EE->cp->set_breadcrumb(BASE.AMP.$this->_base, $this->EE->lang->line(SH_TABS_NAME));
		}

		if (APP_VER >= '2.6.0')
		{
			ee()->view->cp_page_title = $this->EE->lang->line($line);
		}
		else
		{
			// Before EE 2.6 - DECAPITATED
			$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line($line));
		}
	}

	// ----------------------------------------------------------------------

	/**
	 * Activate Extension
	 *
	 * This function enters the extension into the exp_extensions table
	 *
	 * @see http://codeigniter.com/user_guide/database/index.html for
	 * more information on the db class.
	 *
	 * @return void
	 */
	public function activate_extension()
	{
		// Setup custom settings in this array.
		$this->settings = array();
		
		$hooks = array('cp_menu_array');

		foreach ($hooks as $hook)
		{
			$this->EE->db->insert('extensions', array(
				'class'		=> __CLASS__,
				'method'	=> $hook,
				'hook'		=> $hook,
				'settings'	=> serialize($this->settings),
				'priority'	=> 10,
				'version'	=> SH_TABS_VERSION,
				'enabled'	=> 'y'
			));
		}
	}	

	// ----------------------------------------------------------------------

	/**
	 * Disable Extension
	 *
	 * This method removes information from the exp_extensions table
	 *
	 * @return void
	 */
	function disable_extension()
	{
		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->delete('extensions');
	}

	// ----------------------------------------------------------------------

	/**
	 * Update Extension
	 *
	 * This function performs any necessary db updates when the extension
	 * page is visited
	 *
	 * @return 	mixed	void on update / false if none
	 */
	function update_extension($current = '')
	{
		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}
	}	
	
	// ----------------------------------------------------------------------
}

/* End of file ext.sh_member_group_tabs.php */
/* Location: /system/expressionengine/third_party/sh_member_group_tabs/ext.sh_member_group_tabs.php */