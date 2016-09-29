<?php
//  +------------------------------------------------------------------------+
//  | O!MPD, Copyright © 2015-2016 Artur Sierzant	                         |
//  | http://www.ompd.pl           		                                     |
//  |                                                                        |
//  |                                                                        |
//  | This program is free software: you can redistribute it and/or modify   |
//  | it under the terms of the GNU General Public License as published by   |
//  | the Free Software Foundation, either version 3 of the License, or      |
//  | (at your option) any later version.                                    |
//  |                                                                        |
//  | This program is distributed in the hope that it will be useful,        |
//  | but WITHOUT ANY WARRANTY; without even the implied warranty of         |
//  | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          |
//  | GNU General Public License for more details.                           |
//  |                                                                        |
//  | You should have received a copy of the GNU General Public License      |
//  | along with this program.  If not, see <http://www.gnu.org/licenses/>.  |
//  +------------------------------------------------------------------------+


require_once('include/initialize.inc.php');
require_once('include/play.inc.php');

global $cfg, $db;
$action = get('action');
$source = get('source');
$dest = get('dest');
$data = array();
$data['action_status'] = false;

$query = mysqli_query($db,'SELECT player_host, player_port	FROM player WHERE player_id = ' . mysqli_real_escape_string($db,$source) . '');

$source_f = mysqli_fetch_assoc($query);

$source_host = $source_f['player_host'];
$source_port = $source_f['player_port'];

$query = mysqli_query($db,'SELECT player_host, player_port	FROM player WHERE player_id = ' . mysqli_real_escape_string($db,$dest) . '');

$dest_f = mysqli_fetch_assoc($query);

$dest_host = $dest_f['player_host'];
$dest_port = $dest_f['player_port'];

$source_playlist = mpdSilent('playlist', $source_host, $source_port);
if (!$source_playlist) {
	echo json_encode($data);
	exit();
}

$isDestAlive = mpdSilent('status', $dest_host, $dest_port);
if (!$isDestAlive) {
	echo json_encode($data);
	exit();
}
if ($action == 'Sync'){
	$action_status = mpdSilent('clear', $dest_host, $dest_port);
	$playlist_len = count($source_playlist);
	for ($i = 0; $i < $playlist_len; $i++){
		$action_status = mpdSilent('add "' . $source_playlist[$i] . '"', $dest_host, $dest_port);
	}
	$source_status = mpdSilent('status', $source_host, $source_port);
	$song = $source_status['song'];
	$seek = explode(":",$source_status['time']);
	//mpd('play "' . $song . '"', $dest_host, $dest_port);
	$action_status = mpdSilent('seek ' . $song . ' ' . ($seek[0]), $dest_host, $dest_port);
}
elseif ($action == 'Copy') {
	$action_status = mpdSilent('clear', $dest_host, $dest_port);
	$playlist_len = count($source_playlist);
	for ($i = 0; $i < $playlist_len; $i++){
		$action_status = mpdSilent('add "' . $source_playlist[$i] . '"', $dest_host, $dest_port);
	}
}
elseif ($action == 'Add') {
	$playlist_len = count($source_playlist);
	for ($i = 0; $i < $playlist_len; $i++){
		$action_status = mpdSilent('add "' . $source_playlist[$i] . '"', $dest_host, $dest_port);
	}
	
}

if(!$action_status) $action_status = true;

$data['source_host']		= $source_host;
$data['dest_host']			= $dest_host;
$data['action_status']		= $action_status;

echo json_encode($data);
?>