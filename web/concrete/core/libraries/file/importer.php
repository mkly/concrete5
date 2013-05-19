<?

defined('C5_EXECUTE') or die("Access Denied.");
/**
 * @package Core
 * @category Concrete
 * @author Andrew Embler <andrew@concrete5.org>
 * @copyright  Copyright (c) 2003-2009 Concrete5. (http://www.concrete5.org)
 * @license    http://www.concrete5.org/license/     MIT License
 *
 */

/**

 * @package Core
 * @author Andrew Embler <andrew@concrete5.org>
 * @category Concrete
 * @copyright  Copyright (c) 2003-2009 Concrete5. (http://www.concrete5.org)
 * @license    http://www.concrete5.org/license/     MIT License
 *
 */
class Concrete5_Library_FileImporter {
	
	protected $class;
	/** 
	 * PHP error constants - these match those founds in $_FILES[$field]['error] if it exists
	 */
	const E_PHP_FILE_ERROR_DEFAULT = 0;
	const E_PHP_FILE_EXCEEDS_UPLOAD_MAX_FILESIZE = 1;
	const E_PHP_FILE_EXCEEDS_HTML_MAX_FILE_SIZE = 2;
	const E_PHP_FILE_PARTIAL_UPLOAD = 3;
	const E_PHP_NO_FILE = 4;
	
	/** 
	 * concrete5 internal error constants
	 */
	const E_FILE_INVALID_EXTENSION = 10;
	const E_FILE_INVALID = 11; // pointer is invalid file, is a directory, etc...
	const E_FILE_UNABLE_TO_STORE = 12;
	
	public function __construct() {
		$class = 'FileImporter'.FILE_STORAGE_METHOD;
		$this->class = new $class;
		$this->class->parent = &$this;
	}

	/** 
	 * Returns a text string explaining the error that was passed
	 */
	public function getErrorMessage($code) {
		$msg = '';
		switch($code) {
			case FileImporter::E_PHP_NO_FILE:
			case FileImporter::E_FILE_INVALID:
				$msg = t('Invalid file.');
				break;
			case FileImporter::E_FILE_INVALID_EXTENSION:
				$msg = t('Invalid file extension.');
				break;
			case FileImporter::E_PHP_FILE_PARTIAL_UPLOAD:
				$msg = t('The file was only partially uploaded.');
				break;
			case FileImporter::E_PHP_FILE_EXCEEDS_HTML_MAX_FILE_SIZE:
			case FileImporter::E_PHP_FILE_EXCEEDS_UPLOAD_MAX_FILESIZE:
				$msg = t('Uploaded file is too large. The current value of upload_max_filesize is %s', ini_get('upload_max_filesize'));
				break;
			case FileImporter::E_FILE_UNABLE_TO_STORE:			
				$msg = t('Unable to copy file to storage directory. Please check permissions on your upload directory and ensure they can be written to by your web server.');
				break;
			case FileImporter::E_PHP_FILE_ERROR_DEFAULT:
			default:
				$msg = t("An unknown error occurred while uploading the file. Please check that file uploads are enabled, and that your file does not exceed the size of the post_max_size or upload_max_filesize variables.\n\nFile Uploads: %s\nMax Upload File Size: %s\nPost Max Size: %s", ini_get('file_uploads'), ini_get('upload_max_filesize'), ini_get('post_max_size'));			
				break;
		}
		return $msg;
	}

	protected function storeFile($prefix, $pointer, $filename, $fr = false) {
		return $this->class->storeFile($prefix, $pointer, $filename, $fr);
	}

	/** 
	 * Imports a local file into the system. The file must be added to this path
	 * somehow. That's what happens in tools/files/importers/.
	 * If a $fr (FileRecord) object is passed, we assign the newly imported FileVersion
	 * object to that File. If not, we make a new filerecord.
	 * @param string $pointer path to file
	 * @param string $filename
	 * @param FileRecord $fr
	 * @return number Error Code | FileVersion
	 */
	public function import($pointer, $filename = false, $fr = false) {
		return $this->class->import($pointer, $filename, $fr);
	}
	
}
