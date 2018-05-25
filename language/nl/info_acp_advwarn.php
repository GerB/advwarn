<?php
/**
 *
 * Advanced Warnings. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Ger, https://github.com/GerB
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'ACP_WARN_REASONS'			=> 'Attenderingsredenen',
	'ACP_DEMO_TITLE'			=> 'Demo Module',
	'DELETE_REASON_CONFIRM'			=> 'Weet je  zeker dat je deze reden wil verwijderen? Dit kan niet ongedaan worden gemaakt.',
	'ACP_WARN_REASONS_EXPLAIN'		=> '<p>Hier kun je redenen met standaardtektsten invoeren om gebruikers te attenderen op de regels. Deze redenen worden weergegeven in een selectbox in het moderatorpaneel. Je kunt de volgende placeholders gebruiken:</p>'
						    . '<strong class="tabbed">{post_id}</strong>: Bericht/post id<br />'
						    . '<strong class="tabbed">{topic_id}</strong>: Onderwerp/topic id<br />'
						    . '<strong class="tabbed">{post_text}</strong>: Inhoud van het bericht<br />'
						    . '<strong class="tabbed">{topic_title}</strong>: Onderwerptitel<br />'
						    . '<strong class="tabbed">{username}</strong>: Gebruikersnaam van degene die geattendeerd wordt<br />'
						    . '<strong class="tabbed">{moderator_name}</strong>: Gebruikersnaam van degene die de attendering opstelt<br />'
						    . '<strong class="tabbed">{boardrules}</strong>: Link naar de regels'
						    . '<p><br /><strong>Let op:</strong> Sommige placeholders kunnen alleen vervangen worden bij attenderingen die vanuit een specifiek bericht worden verzonden<br />'
    						    . 'De placeholders worden automatisch ingevuld bij het selecteren van de reden van attendering. De standaardtekst kan nog op maat gemaakt worden voor het verzenden.</p>',
	'CREATE_REASON'				=> 'Reden toevoegen',
	'WARN_REASON'				=> 'Reden',
	'ACP_WARN_REASON_EXPAND'		=> 'Toon/verberg standaardtekst',
	'NO_REASONS'				=> 'Geen redenen opgegeven',
	'ACP_WARN_REASON_DELETED'		=> 'Attenderingsreden succesvol verwijderd.',
	'ACP_WARN_REASONS_SAVED'		=> 'Attenderingsreden succesvol opgeslagen.',
	'ERROR_FAILED_DELETE'			=> 'Er is een onbekende fout opgetreden bij het verwijderen.'
));
