<?php
// This file is part of Mindmap module for Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Mindmap auto node
 *
 * @package    mod
 * @subpackage mindmap
 * @author ekpenso.com
 * @copyright  2011 Tõnis Tartes <tonis.tartes@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$limit = isset($_POST['limit']) ? (int) $_POST['limit'] : 5;
$text = isset($_POST['text']) ? $_POST['text'] : '';

$url = 'http://%s.wikipedia.org/w/index.php?title=%s&action=edit';

// crawl wikipedia data
$html = mindmap__curlHelper(sprintf($url, 'de', urlencode($text)));

// if there is this string we are redirected to another page...
$pattern = '!#REDIRECT\[\[(.*)\]\]!U';

if(preg_match($pattern, $html, $matches)) {
    $text = $matches[1];
    $html = mindmap__curlHelper(sprintf($url, 'de', urlencode($text)));
}

preg_match_all('#\[\[([a-zA-Z0-9 _-]*)\]]#u', $html, $matches);

$nodes = array();

foreach($matches[1] as $m) {
    if(!empty($m) && !in_array($m, $nodes)) {
            $nodes[] = $m;	
    }
    if(count($nodes)>=$limit) {
            break;
    }
}
	
/** 
 * Helper function to crawl a given url and return the content.
 * @param String $url The url to open
 * @return String The content of $url
 */    	
function mindmap__curlHelper($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    return curl_exec($ch);		
}

header('Content-Type: application/xml');?><xml>
<?php foreach($nodes as $node):?>
	<node><?php echo $node;?></node>
<?php endforeach;?>
</xml>

