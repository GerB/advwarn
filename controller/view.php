<?php

/**
 *
 * Advanced warnings
 *
 * @copyright (c) 2016 Ger Bruinsma
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace ger\advwarn\controller;

class view
{

	protected $helper;
	protected $template;
	protected $user;
	protected $db;
	protected $phpbb_root_path;
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config		$config
	 * @param \phpbb\controller\helper	$helper
	 * @param \phpbb\template\template	$template
	 * @param \phpbb\user				$user
	 * @param \phpbb\auth				$auth
	 * @param \phpbb\request			$request
	 * @param string					$phpbb_root_path

	 */
	public function __construct(\phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\user $user, \phpbb\db\driver\driver_interface $db, $phpbb_root_path, $php_ext)
	{
		$this->helper = $helper;
		$this->template = $template;
		$this->user = $user;
		$this->db = $db;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * Controller for route /view/{confirm}
	 *
	 * @param int		$confirm
	 * @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
	 */
	public function handle($confirm = 0)
	{
        if (!function_exists('get_folder'))
        {
            include($this->phpbb_root_path . 'includes/functions_privmsgs.' . $this->php_ext);
        }
        
        // Grab message data
        $sql = 'SELECT t.*, p.*, u.*
                FROM ' . PRIVMSGS_TO_TABLE . ' t, ' . PRIVMSGS_TABLE . ' p, ' . USERS_TABLE . ' u
                WHERE t.user_id = ' . $this->user->data['user_id'] . "
                    AND p.author_id = u.user_id
                    AND t.msg_id = p.msg_id
                    AND p.msg_id = " . (int) $this->user->data['unread_warning'];
        $result = $this->db->sql_query($sql);
        $message_row = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);        
        
		if (empty($confirm)) 
		{
			// Show message to indicate warning
			$this->user->add_lang('ucp');	
			$this->user->add_lang_ext('ger/advwarn', 'common');	
			if (!function_exists('view_message'))
			{
				include($this->phpbb_root_path . 'includes/ucp/ucp_pm_viewmessage.' . $this->php_ext);
			}
			if (!class_exists('p_master'))
			{
				include($this->phpbb_root_path . 'includes/functions_module.' . $this->php_ext);
			}
			
			// Build page
			$module = new \p_master();
			$module->list_modules('ucp');
			$module->set_active('pm', 'view');

			$module->assign_tpl_vars(append_sid("{$this->phpbb_root_path}ucp.$this->php_ext"));
			view_message('pm', 'view', PRIVMSGS_NO_BOX, $this->user->data['unread_warning'], get_folder($this->user->data['user_id']), $message_row);
            $this->template->assign_vars(array(
                'S_PRIVMSGS'        => true,
                'U_CONFIRM'         => $this->helper->route('ger_advwarn_view', array('confirm' => $this->user->data['unread_warning'])),
            ));

			return $this->helper->render('ucp_view_warning.html', $message_row['message_subject']);
			
		}
		else
		{
            // Just some abuse prevention
            if ($confirm == $message_row['msg_id'])
            {
                // Set PM as read
                update_unread_status(1, $message_row['msg_id'], $this->user->data['user_id'], $message_row['folder_id']);

                // Set phpbb_users.unread_warning to 0
                $sql = 'UPDATE ' . USERS_TABLE . '
                    SET unread_warning = "0"
                    WHERE user_id = "' . (int) $this->user->data['user_id'] . '"';
                $this->db->sql_query($sql);
                // Redirect to PM inbox
                redirect(append_sid("{$this->phpbb_root_path}ucp.$this->php_ext", "i=pm&amp;folder=inbox"));		
            }
        }
	}
}
// EoF