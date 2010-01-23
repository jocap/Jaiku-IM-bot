#!/usr/local/bin/php -q
<?php
/**
  * jaiku-agi.php - An Asterisk AGI script for updating Jaiku presence.
  *
  * For more information, see http://ruk.ca/wiki/Jaiku_By_Telephone
  *
  * Requires an Asterisk server (http://www.asterisk.org) and the PHPAGI
  * class that lets AGI (Asterisk Gateway Interface) scripts be written
  * in PHP (see http://phpagi.sourceforge.net/).
  *
  * Also requires class.jaiku.php (see http://ruk.ca/wiki/class.jaiku.php)
  *
  * The text-by-DTMF method for entering text is a little arcane; you can
  * read about it at http://www.voip-info.org/wiki-Asterisk+cmd+DTMFToText
  *     
  * This program is free software; you can redistribute it and/or modify
  * it under the terms of the GNU General Public License as published by
  * the Free Software Foundation; either version 2 of the License, or (at
  * your option) any later version.

  * This program is distributed in the hope that it will be useful, but
  * WITHOUT ANY WARRANTY; without even the implied warranty of
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
  * General Public License for more details.

  * You should have received a copy of the GNU General Public License
  * along with this program; if not, write to the Free Software
  * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
  * USA
  *
  * @version 0.1, December 24, 2006
  * @author Peter Rukavina <peter@rukavina.net>
  * @copyright Copyright &copy; 2006, Reinvented Inc.
  * @license http://www.fsf.org/licensing/licenses/gpl.txt GNU Public License
  */

// Set a script timeout of 60 seconds
set_time_limit(60);

// Require the phpagi.php class
require_once('phpagi.php');

// Report on all errors.
error_reporting(E_ALL);

// Require class.jaiku.php to talk to Jaiku
require_once("/www/php-classes/jaiku/class.jaiku.php");

// Create a new Jaiku object; replace with your username, password and screen name here.
$j = new Jaiku("username","password",'screenname');

// Create a new AGI object.
$agi = new AGI();

// Answer the incoming call.
$agi->answer();

// Say hello.
$agi->text2wav("Hello.");

// Read back the current presence
ReadPresence($agi,$j);

// Loop until we've either quit, timed out, or completed.
$alldone = 0;
do {
	$text = EnterPresence($agi);
	$rv = UpdateMenu($agi,$j,$text);
	
} while (!$alldone);

// Say good-bye
$agi->text2wav('Goodbye');

// Hang up the call.
$agi->hangup();

/**
  * Read back the current Jaiku presence.
  * @param object $agi the AGI object
  * @param object $j the Jaiku object
  */
function ReadPresence($agi,$j) {
	$j->GetPresence();
	$agi->text2wav("Your Jaiku presence is currently " . $j->message);
}

/**
  * Allow for input of the new Jaiku presence.
  * @param object $agi the AGI object
  */
function EnterPresence($agi) {
	// See http://www.voip-info.org/wiki-Asterisk+cmd+DTMFToText for text input details
	$agi->text2wav("After the beep, key in your new presence message.");
	$text = $agi->text_input('UPPERCASE');
	$agi->text2wav("You entered $text");
	return $text;
}

/**
  * After presence has been entered, confirm update or all for re-entering or quit.
  * @param object $agi the AGI object
  * @param object $j the Jaiku object
  * @param string $text the text entered by DTMF that will be used as new presence message  
  */
function UpdateMenu($agi,$j,$text) {

	global $alldone;

	// Read a menu
	$agi->text2wav("Press 1 to update Jaiku, 2 to enter again, 3 to exit.");
	
	// Wait for a single keypress, with 30 second timeout
	$option = $agi->wait_for_digit(3000);
	
	// If we got a digit...
	if ($option['code'] == 200) {
	
		// Convert the ASCII code of the digit to a string
		$digit = chr($option['result']);
		
		// Update Jaiku
		if ($digit == '1') {
			$agi->text2wav("Connecting to Jaiku.com");
			$j->GetJaikuSession();
			$agi->text2wav("Sending presence message.");
			$j->UpdatePresence($text);
			$agi->text2wav("Jaiku has been updated.");
			$alldone = 1;
		}
		
		// Allow for re-entry.
		else if ($digit == '2') {
			$agi->text2wav("Enter again.");
		}
		
		// Quit.
		else {
			$alldone = 1;
		}
	}
	else {
	
		// Quit.
		$alldone = 1;
	}
}
?>