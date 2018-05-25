<?php
/**
 *
 * Advanced Warnings. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Ger, https://github.com/GerB
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace ger\advwarn\acp;

/**
 * Advanced Warnings ACP module info.
 */
class main_info
{
	public function module()
	{
		return array(
			'filename'	=> '\ger\advwarn\acp\main_module',
			'title'		=> 'ACP_WARN_REASONS',
			'modes'		=> array(
				'settings'	=> array(
					'title'	=> 'ACP_WARN_REASONS',
					'auth'	=> 'acl_a_board',
				),
			),
		);
	}
}
