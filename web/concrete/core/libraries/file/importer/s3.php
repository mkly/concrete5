<?php
defined('C5_EXECUTE') or die("Access Denied.");

class Concrete5_Library_FileImporterS3 {

	public $parent;

	public function import($pointer, $filename = false, $fr = false) {
		Loader::library('3rdparty/S3');
		$s3 = new S3(AWS_ACCESS_KEY, AWS_SECRET_KEY, false, AWS_ENDPOINT);

		$prefix = substr(uniqid(), 0, 12);
		// TODO Real error logging
		if(!$s3->putObjectFile($pointer, AWS_BUCKET, $prefix.'-'.$filename, S3::ACL_PUBLIC_READ)) {
			return FileImporter::E_PHP_FILE_ERROR_DEFAULT;
		}

		$info = $s3->getObjectInfo(AWS_BUCKET, $prefix.'-'.$filename);
		Log::addEntry(print_r($info, 1));

		if (!($fr instanceof File)) {
			$fv = File::add($filename, $prefix);
			$fv->refreshAttributes(true);
			$fr = $fv->getFile();
		} else {
			$fv = $fr->getVersionToModify(true);
			$fv->updateFile($filename, $prefix);
			$fv->refreshAttributes();
		}

		$fr->refreshCache();
		return $fv;
	}

}
