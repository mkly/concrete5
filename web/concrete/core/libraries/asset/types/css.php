<?

defined('C5_EXECUTE') or die("Access Denied.");
class Concrete5_Library_CSSAsset extends Asset {
	
	protected $assetSupportsMinification = true;
	protected $assetSupportsCombination = true;

	public function getAssetDefaultPosition() {
		return Asset::ASSET_POSITION_HEADER;
	}

	public function getAssetType() {return 'css';}

	protected static function getDirectory() {
		if (!file_exists(DIR_FILES_CACHE . '/' . DIRNAME_CSS)) {
			$proceed = @mkdir(DIR_FILES_CACHE . '/' . DIRNAME_CSS);
		} else {
			$proceed = true;
		}
		if ($proceed) {
			return DIR_FILES_CACHE . '/' . DIRNAME_CSS;
		} else {
			return false;
		}
	}

    static function changePaths( $content, $current_path, $target_path )
    {
        $current_path = rtrim( $current_path, "/" );
        $target_path = rtrim( $target_path, "/" );
        $current_path_slugs = explode( "/", $current_path );
        $target_path_slugs = explode( "/", $target_path );
        $smallest_count = min( count( $current_path_slugs ), count( $target_path_slugs ) );
        for( $i = 0; $i < $smallest_count && $current_path_slugs[$i] === $target_path_slugs[$i]; $i++ );
        $change_prefix = implode( "/", array_merge( array_fill( 0, count( $target_path_slugs ) - $i, ".." ), array_slice( $current_path_slugs, $i ) ) );
        if( strlen( $change_prefix ) > 0 ) $change_prefix .= "/";

        $content = preg_replace_callback(
            '/
            @import\\s+
            (?:url\\(\\s*)?     # maybe url(
            [\'"]?              # maybe quote
            (.*?)               # 1 = URI
            [\'"]?              # maybe end quote
            (?:\\s*\\))?        # maybe )
            ([a-zA-Z,\\s]*)?    # 2 = media list
            ;                   # end token
            /x',
            function( $m ) use ( $change_prefix ) {
                $url = $change_prefix.$m[1];
                $url = str_replace('/./', '/', $url);
                do {
                    $url = preg_replace('@/(?!\\.\\.?)[^/]+/\\.\\.@', '/', $url, 1, $changed);
                } while( $changed );
                return "@import url('$url'){$m[2]};";
            },
            $content
        );
        $content = preg_replace_callback(
            '/url\\(\\s*([^\\)\\s]+)\\s*\\)/',
            function( $m ) use ( $change_prefix ) {
                // $m[1] is either quoted or not
                $quote = ($m[1][0] === "'" || $m[1][0] === '"')
                    ? $m[1][0]
                    : '';
                $url = ($quote === '')
                    ? $m[1]
                    : substr($m[1], 1, strlen($m[1]) - 2);

                if( '/' !== $url[0] && strpos( $url, '//') === FALSE ) {
                    $url = $change_prefix.$url;
                    $url = str_replace('/./', '/', $url);
                    do {
                        $url = preg_replace('@/(?!\\.\\.?)[^/]+/\\.\\.@', '/', $url, 1, $changed);
                    } while( $changed );
                }
                return "url({$quote}{$url}{$quote})";
            },
            $content
        );
        return $content;
    }

	public static function combine($assets) {
		if ($directory = self::getDirectory()) {
			$filename = '';
			for ($i = 0; $i < count($assets); $i++) {
				$asset = $assets[$i];
				$filename .= $asset->getAssetURL();
			}
			$filename = sha1($filename);
			$cacheFile = $directory . '/' . $filename . '.css';
			if (!file_exists($cacheFile)) {
				$css = '';
				foreach($assets as $asset) {
					$css .= file_get_contents($asset->getAssetPath()) . "\n\n";
					$css = self::changePaths($css, substr($asset->getAssetURL(), 0, strrpos($asset->getAssetURL(), '/')), REL_DIR_FILES_CACHE . '/' . DIRNAME_CSS);
				}
				@file_put_contents($cacheFile, $css);
			}
			
			$asset = new CSSAsset();
			$asset->setAssetURL(REL_DIR_FILES_CACHE . '/' . DIRNAME_CSS . '/' . $filename . '.css');
			$asset->setAssetPath($directory . '/' . $filename . '.css');
			return array($asset);
		}
		
		return $assets;
	}

	public static function minify($assets) {
		if ($directory = $this->getDirectory()) {
			$filename = '';
			for ($i = 0; $i < count($assets); $i++) {
				$asset = $assets[$i];
				$filename .= $asset->getAssetURL();
			}
			$filename = sha1($filename);
			$cacheFile = $directory . '/' . $filename . '.css';
			if (!file_exists($cacheFile)) {
				Loader::library('3rdparty/cssmin');
				$css = '';
				foreach($assets as $asset) {
					$css .= file_get_contents($asset->getAssetPath()) . "\n\n";
				}
				$css = CssMin::minify($css);
				@file_put_contents($cacheFile, $css);
			}
		
			$asset = new CSSAsset();
			$asset->setAssetURL(REL_DIR_FILES_CACHE . '/' . DIRNAME_CSS . '/' . $filename . '.css');
			$asset->setAssetPath($directory . '/' . $filename . '.css');
			return array($asset);
		}
		
		return $assets;
	}

	public function __toString() {
		return '<link rel="stylesheet" type="text/css" href="' . $this->getAssetURL() . '" />';
	}

}