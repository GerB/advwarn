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
 * Advanced Warnings ACP module.
 */
class main_module
{

    public $u_action;
    private $reasons_table = 'warn_reasons';

    public function __construct()
    {
        global $table_prefix;
        $this->reasons_table = $table_prefix . 'warn_reasons';
    }

    public function main($id, $mode)
    {
        global $request, $template, $user;
        $user->add_lang_ext('ger/advwarn', 'common');
        $this->tpl_name = 'acp_advwarn_body';
        $this->page_title = $user->lang('ACP_WARN_REASONS');
        add_form_key('ger/advwarn');
		
	    $reasons = $this->get_reasons();

        if ($request->is_set_post('submit')) 
	    {
            if (!check_form_key('ger/advwarn')) 
		    {
                trigger_error('FORM_INVALID');
            }
		    // Is a new category added?
			if ($request->is_set_post('add_reason'))
			{	    
			    // Setup reason in DB
			    $reason = array(
				    'reason_short' => $request->variable('add_reason', '', true),
				    'reason_pm_text' => '',
			    );
			    $this->store_reason($reason);
			}
			else
			{
			    foreach($reasons as $reason_id => $reason)
			    {
					$reason_data = array(
						'reason_id' => $reason_id,
						'reason_short' => $request->variable($reason_id . '_reason_short', '', true),
						'reason_pm_text' => $request->variable($reason_id . '_reason_pm_text', '', true),
					);
					$this->store_reason($reason_data);
			    }
			}
            trigger_error($user->lang('ACP_WARN_REASONS_SAVED') . adm_back_link($this->u_action));
        }
		else if ($request->variable('action', '') == 'delete')
		{
			$reason_id = $request->variable('reason_id', 0);
			if ($this->delete_reason($reason_id))
			{
				trigger_error($user->lang('ACP_CMBB_SETTING_SAVED') . adm_back_link($this->u_action));
			}
				trigger_error($user->lang('ERROR_FAILED_DELETE') . adm_back_link($this->u_action), E_USER_WARNING);
		}
	    
	
	    // Build page
        foreach ($reasons as $reason_id => $row) {
            $template->assign_block_vars('warn_reasons', array(
                'REASON_ID'         => $row['reason_id'],
                'REASON_SHORT'      => $row['reason_short'],
                'REASON_PM_TEXT'    => $row['reason_pm_text'],
                'U_REASON_DELETE'   => $this->u_action . "&amp;action=delete&amp;reason_id=" . $reason_id
            ));
        }

        $template->assign_vars(array(
            'U_ACTION' => $this->u_action,
            'U_ADD_ACTION' => $this->u_action . "&amp;action=add",
        ));
    }

    /**
     * Get all reasons
     * @return array
     */
    private function get_reasons()
    {
        global $db;
        $sql = 'SELECT * FROM ' . $this->reasons_table;
        if ($result = $db->sql_query($sql)) {
            while ($row = $db->sql_fetchrow($result)) {
                $return[$row['reason_id']] = $row;
            }
            return $return;
        }
        return false;
    }

    /**
     * Store a reason in the reasons table
     * @global object $db
     * @param array $reason
     * @return int
     */
    private function store_reason($reason)
	{
//        var_dump($reason);die;
	    global $db;
		if (isset($reason['reason_id']))
		{
			$reason_id = $reason['reason_id'];
			unset($reason['reason_id']);
			$action = 'UPDATE ' . $this->reasons_table . ' SET ' . $db->sql_build_array('UPDATE', $reason) . ' WHERE reason_id = "' . $reason_id . '"';
		}
		else
		{
			$action = 'INSERT INTO ' . $this->reasons_table . ' ' . $db->sql_build_array('INSERT', $reason);
		}

		if (!$db->sql_query($action))
		{
			return false;
		}
		else
		{
			return isset($reason_id) ? $reason_id : $db->sql_nextid();
		}
	}
}
