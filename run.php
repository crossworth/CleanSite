<?php
/**
* CleanSite
* Pedro Henrique - system.pedrohenrique@gmail.com
* 2015
**/


function rglob($pattern, $flags = 0) {
    $files = glob($pattern, $flags); 
    foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
        $files = array_merge($files, rglob($dir . '/' . basename($pattern), $flags));
    }
    return $files;
}


$terminal_width = exec('tput cols');

$bad_code  = file_get_contents("bad_code.txt");
$directory = empty($argv[1]) ? "." : $argv[1];
$file_extension = empty($argv[2]) ? "*" : "*." . $argv[2];

$files = rglob($directory . "/" . $file_extension);

$files_count = 0;
foreach ($files as $file) {
	if (!is_dir($file)) {
		$files_count++;
	}
}
	
print("CleanSite - Wordpress\n");
print("Pedro Henrique - system.pedrohenrique@gmail.com\n");
print("2015\n");
print("------------------------------------------------------\n");
print("Number of files: " . $files_count . "\n");
	
$percent = 0;
$index   = 0;
foreach ($files as $file) {
	if (!is_dir($file)) {
		

		$file_source = file_get_contents($file);
		$file_source = str_ireplace($bad_code, "", $file_source);
		file_put_contents($file, $file_source);

		$index++;
		$percent = ($index / $files_count) * 100;
		$percent = substr($percent, 0, 5);
	
		$file_name_short = str_ireplace($directory, "", $file);
		$output_str = "[" . $percent . "%] - " . $file_name_short;
		$output_str = str_pad($output_str, $terminal_width);
		print($output_str);
		print("\r");
	}
}

print("\n");
print("Done\n");