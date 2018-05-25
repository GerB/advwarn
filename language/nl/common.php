<?php
/**
 *
 * Advanced Warnings. An extension for the phpBB Forum Software package. [Dutch]
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
    'ADD_WARNING'                   => 'Gebruiker attenderen',
	'ADD_WARNING_EXPLAIN'           => 'Attendeer deze gebruiker middels onderstaand formulier. De gebruiker zal een PB met opgemaakte tekst (alleen BBcode) ontvangen in het logboek zul je alleen platte tekst teruglezen.<br>
                                        Selecteer een reden om een standaardbericht te genereren wat je vervolgens op maat kunt maken',
	'CONFIRM_WARNING'               => 'Hierbij bevestig ik dat ik dit bericht heb gelezen',
    'USER_VIEW_WARNING_EXPLAIN'     => 'Om het forum te kunnen gebruiken dien je te bevestigen dat je dit bericht hebt gelezen',
	'WARNING_PM_SUBJECT'			=> 'Een moderator wil je aandacht voor het volgende',
    'WARNING_PM_BODY'               => '%s', // Prevent the default quoting
));
