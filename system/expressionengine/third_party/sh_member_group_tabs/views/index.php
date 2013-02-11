<?=form_open('C=addons_extensions'.AMP.'M=save_extension_settings'.AMP.'file=sh_member_group_tabs');?>

<?php
$this->table->set_template($cp_pad_table_template);

// total member groups
$total_member_groups = count($settings['sh_member_group_tabs']);
// set count to 0
$member_group_count = 0;

// for each member group
foreach ($settings['sh_member_group_tabs'] as $member_group_key => $member_group_val)
{
	$member_group_count++;

	echo '<h3>'. $member_group_val['group_title'] .'</h3>';

	// set headings
	$this->table->set_heading(
		array('data' => lang('sh_tab_order'), 'style' => 'width: 5%;'),
		array('data' => lang('sh_tab_name'). ' <span class="hint">(e.g. Template Manager)</span>', 'style' => 'width: 20%;'),
		array('data' => lang('sh_tab_url'). ' <span class="hint">(e.g. /admin.php?&D=cp&C=design&M=manager)</span>', 'style' => 'width: 60%;'),
		array('data' => lang('sh_tab_delete'), 'style' => 'width: 10%;')
	);

	// load our saved tab rows
	if (count($settings['sh_member_group_tabs'][$member_group_key]['tabs']) > 0)
	{
		// incremental tab key
		// we use this when adding a new row so we don't overwrite existing rows
		$i_tab_key = 0;

		foreach ($settings['sh_member_group_tabs'][$member_group_key]['tabs'] as $tab_key => $tab_val)
		{
			$this->table->add_row(
				array('data' => '&nbsp;', 'class' => 'sh_order_cell', 'style' => 'background-image: url('. PATH_CP_GBL_IMG .'it-vert-arrow.png); background-repeat: no-repeat; background-position: center;'),
				'<input type="text" name="sh_member_group_tabs['. $member_group_key .'][tabs]['. $tab_key .'][name]" value="'. $tab_val['name'] .'" />',
				'<input type="text" name="sh_member_group_tabs['. $member_group_key .'][tabs]['. $tab_key .'][url]" value="'. $tab_val['url'] .'" />',
				'<input type="checkbox" name="sh_member_group_tabs['. $member_group_key .'][tabs]['. $tab_key .'][delete]" value="y" />'
			);

			// grab the highest tab key in member group
			if ($tab_key > $i_tab_key) $i_tab_key = $tab_key;
		}

		// increment the tab key
		++$i_tab_key;
		
		// order cell includes background when tab rows exist
		// this variable is applied later to the table and differs upon results
		$order_cell = array('data' => '&nbsp;', 'class' => 'sh_order_cell', 'style' => 'background-image: url('. PATH_CP_GBL_IMG .'it-vert-arrow.png); background-repeat: no-repeat; background-position: center;');

	}	
	// no tab results for this member group
	else
	{
		$this->table->add_row(array(
			'data' => lang('sh_tab_no_results'),
			'colspan' => 4,
			'class' => 'sh_no_results'
		));

		$i_tab_key = 0;
		$order_cell = array('data' => '&nbsp;', 'class' => 'sh_order_cell');	
	}
		
	$this->table->add_row(
		$order_cell,
		'<input type="text" name="sh_member_group_tabs['. $member_group_key .'][tabs]['. $i_tab_key .'][name]" value="" />',
		array('data' => '<input type="text" name="sh_member_group_tabs['. $member_group_key .'][tabs]['. $i_tab_key .'][url]" value="" />'),
		'');

	echo $this->table->generate();

	// last row
	if ($total_member_groups !== $member_group_count) echo '<p>&nbsp;</p>';

}

?>

<p><?=form_submit('submit', lang('sh_submit'), 'class="submit"')?></p>

<?php $this->table->clear()?>
<?=form_close()?>

<?php

/* End of file index.php */
/* Location: ./system/expressionengine/third_party/sh_member_group_tabs/views/index.php */