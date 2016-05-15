<?php
/**
*	 Copyright 2015 Pedro Henrique
*    Licensed under the Apache License, Version 2.0 (the "License");
*    you may not use this file except in compliance with the License.
*    You may obtain a copy of the License at
*    http://www.apache.org/licenses/LICENSE-2.0
*    Unless required by applicable law or agreed to in writing, software
*    distributed under the License is distributed on an "AS IS" BASIS,
*    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
*    See the License for the specific language governing permissions and
*    limitations under the License.
*
*
* CleanSite
* Pedro Henrique - system.pedrohenrique@gmail.com
* How to use at https://github.com/systemmovie/CleanSite
**/

function getTerminalSizeOnWindows() {
	$output = array();
	$size = array('width'=>0,'height'=>0);
	exec('mode',$output);
	foreach($output as $line) {
	$matches = array();
	$w = preg_match('/^\s*columns\:?\s*(\d+)\s*$/i',$line,$matches);
	if($w) {
	  	$size['width'] = intval($matches[1]);
	} else {
		$h = preg_match('/^\s*lines\:?\s*(\d+)\s*$/i',$line,$matches);
		if($h) {
			$size['height'] = intval($matches[1]);
		}
	}
		if($size['width'] AND $size['height']) {
			break;
		}
	}
	return $size;
}


if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
	$terminal_size = getTerminalSizeOnWindows();
    $terminal_width = $terminal_size['width'] - 1;
} else {
	$terminal_width = exec('tput cols');
}


function print_logo() {
	print("   ____ _                  ____  _ _         \n");
	print("  / ___| | ___  __ _ _ __ / ___|(_) |_ ___   \n");
	print(" | |   | |/ _ \/ _` | '_ \\___ \| | __/ _ \  \n");
	print(" | |___| |  __/ (_| | | | |___) | | ||  __/  \n");
	print("  \____|_|\___|\__,_|_| |_|____/|_|\__\___|  \n");
	print("                                             \n");
	print("Pedro Henrique - system.pedrohenrique@gmail.com\n");

}

print_logo();

// Recursive glob
function rglob($pattern, $flags = 0) {
    $files = glob($pattern, $flags); 
    foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
    	// avoid auto-removing the database
    	if (strpos($dir, "malicious_code") === false) {
			$files = array_merge($files, rglob($dir.'/'.basename($pattern), $flags));
		}
    }
    return $files;
}


$malicious_code_file_names = rglob("./malicious_code/*.txt");
$malicious_code = [];

print("Loading the malicious code database\n");

foreach($malicious_code_file_names as $file_name) {
	array_push($malicious_code, file_get_contents($file_name));
}

$directory = empty($argv[1]) ? "." : $argv[1];
$file_extension = empty($argv[2]) ? "*" : "*." . $argv[2];

$dir_name = ($directory == ".") ? "this directory" : $directory;

print("Loading files on {$dir_name}\n");

chdir($directory);
$files_to_scan_file_names = rglob($directory . "/" . $file_extension);

$files_count = count($files_to_scan_file_names);

print("Number of files: " . $files_count . "\n");

$percent = 0;
$index   = 0;

foreach($files_to_scan_file_names as $file_name) {

	if (is_dir($file_name) == false) {
		$file_data = file_get_contents($file_name);

		$index++;
		$percent = ($index / $files_count) * 100;
		$percent = substr($percent, 0, 5);

		$file_name_short = str_ireplace($directory, "", $file_name);
		
		if ((strlen($file_name_short) + 11) > $terminal_width ) {
			$new_length = (strlen($file_name_short) + 11) - $terminal_width;
			$file_name_short = substr($file_name_short, $new_length);
		}
		
		$output_str = "[" . $percent . "%] - " . $file_name_short;
		$output_str = str_pad($output_str, $terminal_width);
		print($output_str);
		print("\r");

		foreach($malicious_code as $code) {
			$file_data = str_ireplace($code, "", $file_data);
		}

		file_put_contents($file_name, $file_data);
	}
}

print("Done scan\n");