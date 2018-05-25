<?php
/**
 *
 * Advanced warning system
 *
 * @copyright (c) 2017, Ger, https://github.com/GerB
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace ger\advwarn\migrations;

class install_advwarn extends \phpbb\db\migration\migration
{
// 	public function effectively_installed()
// 	{
// 		return isset($this->config['feedpostbot_cron_last_run']);
// 	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v320\v320');
	}

	public function update_schema()
	{
		return array(
			'add_tables' => array(
				$this->table_prefix . 'warn_reasons'	 => array(
					'COLUMNS'		 => array(
						'reason_id'	 	 => array('UINT:10', null, 'auto_increment'),
						'reason_short'	 => array('VCHAR:255', ''),
						'reason_pm_text' => array('MTEXT_UNI', ''),
						'reason_log_text' => array('VCHAR:255', 0),
					),
					'PRIMARY_KEY'	 => 'reason_id',
				),
			),
			
			'add_columns'        => array(
            	$this->table_prefix . 'users'        => array(
                	'unread_warning'    => array('UINT', 0, 'after' => 'user_unread_privmsg'),
 	           ),		
			),	
			array('module.add', array(
					'acp',
					'ACP_BOARD_CONFIGURATION',
					array(
						'module_basename'	 => '\ger\advwarn\acp\main_module',
						'modes'				 => array('reasons'), // Should correspond to ./acp/main_info.php modes
					),
				)),			
		);
	}
	
	public function revert_schema()
	{
		return array(
			'drop_columns'	=> array(
				$this->table_prefix . 'users'			=> array(
					'unread_warning',
				),
			),
			'drop_tables'		=> array(
				$this->table_prefix . 'warn_reasons',
			),
		);
	}
}


