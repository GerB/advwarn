<?php
/**
 *
 * Advanced Warnings. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Ger, https://github.com/GerB
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */
namespace ger\advwarn\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Advanced Warnings Event listener.
 */
class main_listener implements EventSubscriberInterface
{

    static public function getSubscribedEvents()
    {
        return array(
            'core.modify_mcp_modules_display_option' => 'get_default_warnings',
            'core.page_header_after' => 'display_warnbox',
            'core.mcp_warn_user_after' => 'set_unread_warning',
            'core.mcp_warn_post_after' => 'set_unread_warning',
            'core.mcp_warn_user_before' => 'set_prewarn_vars',
            'core.mcp_warn_post_before' => 'set_prewarn_vars',
            'core.add_log' => 'parse_warning_markup'
        );
    }

    protected $template;
    protected $user;
    protected $db;
    protected $helper;
    private $postrow;
    private $reasons_table;

    /**
     * Constructor
     *
     * @param \phpbb\template\template				$template
     * @param \phpbb\user							$user
     * @param \phpbb\db\driver\driver_interface 	$db
     * @param \phpbb\controller\helper 				$helper
     */
    public function __construct(\phpbb\template\template $template, \phpbb\user $user, \phpbb\db\driver\driver_interface $db, \phpbb\controller\helper $helper, $reasons_table)
    {
        $this->template = $template;
        $this->user = $user;
        $this->db = $db;
        $this->helper = $helper;
        $this->reasons_table = $reasons_table;
    }

    /**
     * Set marker for unread warning
     *
     * @param \phpbb\event\data	$event	Event object 
     */
    public function set_unread_warning($event)
    {
        // Search the last sent PM from current user (e.g. the moderator) to the warned user
        // While not 100% certainty, it seems quite safe to gamble that the moderator doesn't 
        // send a regular PM to the same offender in the same second as he sends a warning
        $sql = 'SELECT msg_id FROM ' . PRIVMSGS_TO_TABLE . ' 
	 			WHERE user_id = "' . (int) $event['user_row']['user_id'] . '"
	 			AND author_id = "' . (int) $this->user->data['user_id'] . '"
			 	ORDER BY msg_id DESC';
        if ($result = $this->db->sql_query($sql)) {
            $msg_id = $this->db->sql_fetchrow($result)['msg_id'];
            $this->db->sql_freeresult($result);
            if (!empty($msg_id)) {
                $sql = 'UPDATE ' . USERS_TABLE . '
						SET unread_warning = "' . (int) $msg_id . '"
						WHERE user_id = "' . (int) $event['user_row']['user_id'] . '"';
                $this->db->sql_query($sql);
            }
        }
        return;
    }

    /**
     * Display warnbox for warned user
     *
     * @param \phpbb\event\data	$event	Event object 
     */
    public function display_warnbox($event)
    {
        if (($this->user->data['unread_warning'] > 0) && (strpos($this->user->data['session_page'], 'adv_warning') === FALSE)) {
            redirect($this->helper->route('ger_advwarn_view'));
        }
    }

    /**
     * Force sending pm the right way
     *
     * @param \phpbb\event\data	$event	Event object 
     */
    public function set_prewarn_vars($event)
    {
        $this->user->add_lang_ext('ger/advwarn', 'common');
        $event['notify'] = true;
        $event['user_row']['user_lang'] = 'nl';
    }

    /**
     * Parse warning text as HTML to provide more readable texts for moderators
     * 
     * @param \phpbb\event\data	$event	Event object
     */
    public function parse_warning_markup($event)
    {
        if ($event['mode'] == 'user' && $event['log_operation'] == 'LOG_USER_WARNING_BODY') {
            $sql_ary = $event['sql_ary'];
            unset($event['sql_ary']);
            $text = unserialize($sql_ary['log_data'])[0];
            $uid = $bitfield = $options = ''; // will be modified by generate_text_for_storage
            $allow_bbcode = $allow_urls = $allow_smilies = true;
            generate_text_for_storage($text, $uid, $bitfield, $options, $allow_bbcode, $allow_urls, $allow_smilies);
            $parsed = str_replace('<br>', '', generate_text_for_display($text, $uid, $bitfield, $options));
            $sql_ary['log_data'] = serialize(array($parsed));
            $event['sql_ary'] = $sql_ary;
        }
    }

    /**
     * Load default warnings from the db
     *
     * @param \phpbb\event\data	$event	Event object
     */
    public function get_default_warnings($event)
    {
        $this->user->add_lang_ext('ger/advwarn', 'common');
        if ($event['mode'] == 'warn_post') {
            $this->fetch_postrow($event['post_id']);
        }
        $sql = 'SELECT * FROM ' . $this->reasons_table;
        if ($result = $this->db->sql_query($sql)) {
            while ($row = $this->db->sql_fetchrow($result)) {
                $reasons[$row['reason_id']] = $row;
            }
        }    
        foreach ($reasons as $reason) {

            $this->template->assign_block_vars('default_warnings', array(
                'reason_id' => $reason['reason_id'],
                'reason_short' => $reason['reason_short'],
                'reason_pm_text' => $this->replace_tokens($reason['reason_pm_text'], $event),
            ));
        }
    }

    /**
     * Fetch post row
     */
    private function fetch_postrow($post_id)
    {
        // We have a post id but no user
        $sql = 'SELECT * FROM ' . POSTS_TABLE . ' WHERE post_id = ' . (int) $post_id;
        if ($result = $this->db->sql_query($sql)) {
            $this->postrow = $this->db->sql_fetchrow($result);
            $bbcode_options = (($this->postrow['enable_bbcode']) ? OPTION_FLAG_BBCODE : 0) +
                (($this->postrow['enable_smilies']) ? OPTION_FLAG_SMILIES : 0) +
                (($this->postrow['enable_magic_url']) ? OPTION_FLAG_LINKS : 0);
            $this->postrow['raw_post_text'] = generate_text_for_edit($this->postrow['post_text'], $this->postrow['bbcode_uid'], $bbcode_options)['text'];
        }
        $this->db->sql_freeresult($result);
        $this->template->assign_var('RAW_POST_TEXT', $this->postrow['raw_post_text']);
    }

    /**
     * Replace tokens
     * @param string $pm_text
     * @return string
     */
    private function replace_tokens($pm_text, $event)
    {
        if ($event['mode'] == 'warn_post') {
            $this->db->sql_freeresult($result);
            $sql = 'SELECT username FROM ' . USERS_TABLE . ' WHERE user_id = ' . (int) $this->postrow['poster_id'];
            if ($result = $this->db->sql_query($sql)) {
                $username = $this->db->sql_fetchrow($result)['username'];
            }
            $this->db->sql_freeresult($result);
        }

        if ($event['mode'] == 'warn_user') {
            // We have a post id but no user
            $sql = 'SELECT username FROM ' . USERS_TABLE . ' WHERE user_id = ' . (int) $event['user_id'];
            if ($result = $this->db->sql_query($sql)) {
                $username = $this->db->sql_fetchrow($result)['username'];
            }
            $this->db->sql_freeresult($result);
        }
        
        $pm_text = addslashes(htmlspecialchars_decode($pm_text, ENT_COMPAT));

        $tokens = array(
            "\n" => "\\n",
            '{post_id}' => empty($event['post_id']) ? '{post_id}' : $event['post_id'],
            '{topic_id}' => empty($event['topic_id']) ? '{topic_id}' : $event['topic_id'],
            '{post_text}' => '___post_text___', // Prevent ugly JS encoding, do replacement there
            '{topic_title}' => empty($this->postrow['post_subject']) ? '{topic_title}' : $this->postrow['post_subject'],
            '{username}' => $username,
            '{moderator_name}' => $this->user->data['username'],
            '{boardrules}' => '[url='. $this->helper->route('phpbb_boardrules_main_controller') .']deze pagina[/url]', // @TODO: rules ext route
        );
        return str_ireplace(array_keys($tokens), array_values($tokens), $pm_text);
    }
}
