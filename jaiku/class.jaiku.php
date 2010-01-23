<?php

/**
  * class.jaiku.php - A PHP class for interacting with Jaiku.com.
  *
  * For more information, see http://ruk.ca/w/index.php/Class.jaiku.php
  *
  * Required PHP5 with cURL support (see http://php.net/curl for details).
  *
  * Using this class you can:
  *
  *        - update your presence (message, location and icon)
  *
  * There's no provision for grabbing others' presence data from Jaiku.com
  * as this would mean a whole heck of a lot of HTML parsing.
  *
  * Example usage:
  *
  *		require_once("class.jaiku.php");
  *		$j = new Jaiku("username","personal_key");
  *		$j->UpdatePresence("Hey, I'm using class.jaiku.php!",'My Office', 329);
  *     $p = $j->GetPresence();
  *
  * This program is free software; you can redistribute it and/or modify
  * it under the terms of the GNU General Public License as published by
  * the Free Software Foundation; either version 2 of the License, or (at
  * your option) any later version.
  *
  * This program is distributed in the hope that it will be useful, but
  * WITHOUT ANY WARRANTY; without even the implied warranty of
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
  * General Public License for more details.
  *
  * You should have received a copy of the GNU General Public License
  * along with this program; if not, write to the Free Software
  * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
  * USA
  *
  * @version 0.15, April 28, 2007
  * @author Peter Rukavina <peter@rukavina.net>
  * @author Andy Smith (http://termie.jaiku.com/) - helpfully provided patch http://ruk.jaiku.com/presence/38958
  * @copyright Copyright &copy; 2007, Reinvented Inc.
  * @license http://www.fsf.org/licensing/licenses/gpl.txt GNU Public License
  */

class Jaiku {

    /**
      * Construct a new Jaiku object.
      * @param string $username your Jaiku.com username
      * @param string $personal_key your Jaiku.com developer key -- login and visit http://api.jaiku.com to get yours
      */
	function __construct($username = null, $personal_key = null) {

		$this->username = $username;
        $this->personal_key = $personal_key;

		// Set up the Jaiku icons
		$this->SetIcons();

	}

    /**
      * Update your Jaiku presence.
      * @param string $message your presence message (i.e. "At the dentist")
      * @param string $location your location (i.e. "Downtown, New York City, USA")
      * @param string $icon your icon number (if it's a number it's interpreted as an icon number, otherwise an icon keyword)
      * @param string $generated (optional) "yes" if the update is machine generated
      */	
	function UpdatePresence($message = '', $location = '', $icon = 300 ,$generated = '') {

		// Let's us pass icons by name as well as number.
		if (!is_numeric($icon)) {
			if (array_key_exists($icon,$this->icons)) {
				$icon = $this->icons[$icon];
			}
			else {
				$icon = 300;
			}
		}

		$postdata =  "method=presence.send";
		$postdata .= "&user=" . urlencode($this->username);
		$postdata .= "&personal_key=" . $this->personal_key;
		$postdata .= "&generated=" . $generated;
		$postdata .= "&message=" . urlencode($message);
		$postdata .= "&icon=" . $icon;
		$postdata .= "&location=" . urlencode($location);

		$rv = $this->SendToJaiku($postdata,"http://api.jaiku.com/json");
		
		if ($rv == '{"status":"ok"}') {
			return true;
		}
		else {
			return false;
		}
	}

    /**
      * Grab the Jaiku presence message and location for a given user.
      * @param string $screenname the "Jaiku name" (for lack of a better work) of the user -- the XXX part of their XXX.jaiku.com personal URL.
      */
	function GetPresence($username = null) {

		if (!$username) {
			$username = $this->username;
		}
		
		require_once("parser_php5.php");

		$url = $username . ".jaiku.com/presence/xml";
		$xml = $this->SendToJaiku(null,$url);

		$parser = new XMLParser($xml);
		$parser->Parse();
		
		$p = new Presence($username);
		
		$p->line = $parser->document->line[0]->tagData;
		$p->city = $parser->document->location[0]->tagChildren[0]->tagData;
		$p->country = $parser->document->location[0]->tagChildren[1]->tagData;
		
		return $p;
	}
	
    /**
      * HTTP POST of data, using cURL, to Jaiku.com
      * @param string $postdata querystring-style of data elements to post
      * @param string $target target URL to post to.
      */	
	function SendToJaiku($postdata,$target) { 
	
		$ch = curl_init(); 
		curl_setopt ($ch, CURLOPT_URL,$target); 
		curl_setopt ($ch, CURLOPT_USERAGENT, "JaikuPHP/0.14"); 		
		curl_setopt ($ch, CURLOPT_TIMEOUT, 20); 
		curl_setopt ($ch, CURLOPT_FOLLOWLOCATION,1); 
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
		if ($postdata) {
			curl_setopt ($ch, CURLOPT_POSTFIELDS, $postdata); 
			curl_setopt ($ch, CURLOPT_POST, 1); 
		}
		$result = curl_exec ($ch); 
		curl_close($ch); 
	
		return $result;
	}

    /**
      * Construct an array of icons and icon numbers.
      * This is likely proprietary-ish data.  And it will probably change.  Pulled from Jaiku.com.
      */	
	function SetIcons() {

		$this->icons = array(
			"airport" => 316,
			"alarm clock" => 302,
			"balloons" => 348,
			"bandaid" => 384,
			"barrier" => 350,
			"basketball" => 321,
			"beer" => 322,
			"bike" => 388,
			"binoculars" => 323,
			"blading" => 387,
			"boiler" => 324,
			"bomb" => 310,
			"book" => 354,
			"bra" => 311,
			"bus" => 317,
			"camera" => 320,
			"car" => 301,
			"casette" => 305,
			"champaign" => 315,
			"cigarettes" => 313,
			"clean" => 386,
			"clock" => 349,
			"cloudy" => 398,
			"coffee" => 319,
			"computer" => 329,
			"cone" => 332,
			"disc" => 336,
			"driller" => 333,
			"ear muffs" => 346,
			"event" => 337,
			"exclamationmark" => 338,
			"football" => 339,
			"football shoe" => 340,
			"fork" => 341,
			"game" => 331,
			"gameboy" => 342,
			"grenade" => 343,
			"grumpy" => 318,
			"hand" => 344,
			"hanger" => 345,
			"happy" => 393,
			"home" => 392,
			"ice cream" => 381,
			"laptop" => 351,
			"leep" => 363,
			"lifejacket" => 391,
			"loudspeaker" => 303,
			"love" => 347,
			"makeup" => 383,
			"megaphone" => 352,
			"microwave" => 353,
			"middle finger" => 355,
			"movie" => 382,
			"notes" => 356,
			"picnic" => 390,
			"pils" => 389,
			"play" => 335,
			"popcorn" => 334,
			"question" => 357,
			"rollator" => 358,
			"rollerblade" => 307,
			"salt" => 360,
			"scoop" => 309,
			"scull" => 361,
			"search" => 397,
			"shop" => 396,
			"shuttlecock" => 359,
			"sk8" => 362,
			"snorkeling" => 364,
			"snowflake" => 365,
			"soda" => 366,
			"song" => 367,
			"speechbubble" => 300,
			"spraycan" => 368,
			"sticks" => 369,
			"storm" => 370,
			"straitjacket" => 371,
			"subway" => 372,
			"sun" => 374,
			"taxi" => 375,
			"technics" => 376,
			"theatre" => 395,
			"toaster" => 377,
			"toiletpaper" => 394,
			"train" => 378,
			"tram" => 304,
			"trashcan" => 327,
			"travel" => 373,
			"tv" => 328,
			"underwear" => 306,
			"uzi" => 308,
			"videocamera" => 330,
			"videotape" => 312,
			"vinyl" => 314,
			"walk" => 325,
			"wallclock" => 326,
			"wheelchair" => 379,
			"wine" => 385,
			"zippo" => 380);
	}
}

class Presence {

	function __construct($username) {

		$this->username = $username;

	}
}
?>