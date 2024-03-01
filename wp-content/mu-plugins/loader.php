<?php

function includeAllPHP($dir) {
	$directory = new RecursiveDirectoryIterator($dir);
	$iterator = new RecursiveIteratorIterator($directory);

	foreach ($iterator as $file) {
		if ($file->isFile() && $file->getExtension() == 'php') {
			require $file->getPathname();
		}
	}
}
