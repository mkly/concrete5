<?php
defined('C5_EXECUTE') or die("Access Denied.");

class Concrete5_Library_FileImporterFileSystem {

	public $parent;

	protected function storeFile($prefix, $pointer, $filename, $fr = false) {
		// assumes prefix are 12 digits
		$fi = Loader::helper('concrete/file');
		$path = false;
		if ($fr instanceof File) {
			if ($fr->getStorageLocationID() > 0) {
				Loader::model('file_storage_location');
				$fsl = FileStorageLocation::getByID($fr->getStorageLocationID());
				$path = $fi->mapSystemPath($prefix, $filename, true, $fsl->getDirectory());
			}
		}

		if ($path == false) {
			$path = $fi->mapSystemPath($prefix, $filename, true);
		}
		$r = @copy($pointer, $path);
		@chmod($path, FILE_PERMISSIONS_MODE);
		return $r;
	}

	public function import($pointer, $filename = false, $fr = false) {
		if ($filename == false) {
			// determine filename from $pointer
			$filename = basename($pointer);
		}

		$fh = Loader::helper('validation/file');
		$fi = Loader::helper('file');
		$filename = $fi->sanitize($filename);

		// test if file is valid, else return FileImporter::E_FILE_INVALID
		if (!$fh->file($pointer)) {
			return FileImporter::E_FILE_INVALID;
		}

		if (!$fh->extension($filename)) {
			return FileImporter::E_FILE_INVALID_EXTENSION;
		}

		$prefix = $this->generatePrefix();

		// do save in the FileVersions table

		// move file to correct area in the filesystem based on prefix
		$response = $this->storeFile($prefix, $pointer, $filename, $fr);
		if (!$response) {
			return FileImporter::E_FILE_UNABLE_TO_STORE;
		}

		if (!($fr instanceof File)) {
			// we have to create a new file object for this file version
			$fv = File::add($filename, $prefix);
			$fv->refreshAttributes(true);
			$fr = $fv->getFile();
		} else {
			// We get a new version to modify
			$fv = $fr->getVersionToModify(true);
			$fv->updateFile($filename, $prefix);
			$fv->refreshAttributes();
		}

		$fr->refreshCache();
		return $fv;
	}

	public function generatePrefix() {
		$prefix = rand(10, 99) . time();
		return $prefix;
	}
}
