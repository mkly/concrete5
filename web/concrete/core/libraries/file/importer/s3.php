<?php
defined('C5_EXECUTE') or die("Access Denied.");

class Concrete5_Library_FileImporterS3 {

	public $parent;

	protected function storeFile($prefix, $pointer, $filename, $fr = false) {
		Loader::library('3rdparty/S3');
	}

	public function import($pointer, $filename = false, $fr = false) {
		Loader::libray('3rdparty/S3');
	}
}
