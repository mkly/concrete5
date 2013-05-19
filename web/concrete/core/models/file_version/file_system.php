<?php
defined('C5_EXECUTE') or die("Access Denied.");

class Concrete5_Model_FileVersionFileSystem {

	public $parent;

	public function getPath() {
		$f = Loader::helper('concrete/file');
		if ($this->parent->fslID > 0) {
			Loader::model('file_storage_location');
			$fsl = FileStorageLocation::getByID($this->parent->fslID);
			$path = $f->mapSystemPath($this->fvPrefix, $this->parent->fvFilename, false, $fsl->getDirectory());
		} else {
			$path = $f->getSystemPath($this->parent->fvPrefix, $this->parent->fvFilename);
		}
		return $path;
	}

	public function getRelativePath($fullurl = false) {
		$f = Loader::helper('concrete/file');
		if ($this->parent->fslID > 0) {
			$c = Page::getCurrentPage();
			if($c instanceof Page) {
				$cID = $c->getCollectionID();
			} else {
				$cID = 0;
			}
			$path = BASE_URL . View::url('/download_file', 'view_inline', $this->parent->getFileID(),$cID);
		} else {
			if ($fullurl) {
				$path = BASE_URL . $f->getFileRelativePath($this->parent->fvPrefix, $this->parent->fvFilename );
			} else {
				$path = $f->getFileRelativePath($this->parent->fvPrefix, $this->parent->fvFilename );
			}
		}
		return $path;
	}

	public function getURL() {
		return BASE_URL . $this->getRelativePath();
	}
}
