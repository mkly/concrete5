<?php
defined('C5_EXECUTE') or die("Access Denied.");

class Concrete5_Model_FileVersionFileSystem {

	public $parent;

	public function refreshAttributes($firstRun = false) {
		$fh = Loader::helper('file');
		$ext = $fh->getExtension($this->parent->fvFilename);
		$ftl = FileTypeList::getType($ext);
		$db = Loader::db();

		if (!file_exists($this->parent->getPath())) {
			return File::F_ERROR_FILE_NOT_FOUND;
		}

		$size = filesize($this->parent->getPath());

		$title = ($firstRun) ? $this->parent->getFilename() : $this->parent->getTitle();

		$db->Execute('update FileVersions set fvExtension = ?, fvType = ?, fvTitle = ?, fvSize = ? where fID = ? and fvID = ?',
			array($ext, $ftl->getGenericType(), $title, $size, $this->parent->getFileID(), $this->parent->getFileVersionID())
		);
		if (is_object($ftl)) {
			if ($ftl->getCustomImporter() != false) {
				Loader::library('file/inspector');

				$db->Execute('update FileVersions set fvGenericType = ? where fID = ? and fvID = ?',
					array($ftl->getGenericType(), $this->parent->getFileID(), $this->parent->getFileVersionID())
				);

				// we have a custom library script that handles this stuff
				$cl = $ftl->getCustomInspector();
				$cl->inspect($this->parent);

			}
		}
		$this->parent->refreshThumbnails(false);
		$f = $this->parent->getFile();
		$f->refreshCache();
		$f->reindex();
	}

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
