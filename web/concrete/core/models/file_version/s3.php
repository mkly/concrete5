<?php
defined('C5_EXECUTE') or die("Access Denied.");

class Concrete5_Model_FileVersionS3 {
	public $parent;

	public function refreshAttributes($firstRun = false) {
		Loader::library('3rdparty/S3');
		$ext = Loader::helper('file')->getExtension($this->parent->fvFilename);
		$ftl = FileTypeList::getType($ext);
		$db = Loader::db();

		$s3 = new S3(AWS_ACCESS_KEY, AWS_SECRET_KEY, false, AWS_ENDPOINT);
		$s3->setExceptions();
		$info = $s3->getObjectInfo(AWS_BUCKET, $this->parent->fvPrefix . '-' . $this->parent->fvFilename);

		$size = $info['size'];

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
		return 'http://' . AWS_ENDPOINT . '/' . urlencode(AWS_BUCKET) . '/' . urlencode($this->parent->fvPrefix . '-' . $this->parent->fvFilename);
	}

	public function getRelativePath($fullurl = false) {
		return AWS_BUCKET . '/' . $this->parent->fvFilename;
	}

	public function getURL() {
		return $this->getPath();
	}

}
