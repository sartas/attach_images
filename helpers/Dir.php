<?php

if ( !defined( 'DEBUG' ) )
	die;

/*
  Class Dir
  Working with directories
 */

abstract class DirAndFiles {

	protected $_path = null;

	abstract function remove();

	// Return directory path
	public function getPath()
	{
		return $this->_path;
	}

	// Return file extension
	public function getExt()
	{
		return strtolower( preg_replace( '/^.+\./', '', $this->_path ) );
	}

	// FrogCMS path shorted
	public function getPathShort( $dir_name = PUBLIC_FILES )
	{
		return substr( $this->_path, strpos( $this->_path, $dir_name ), strlen( $this->_path ) );
	}

	// Get file name
	public function getName()
	{
		return substr( $this->_path, strrpos( $this->_path, '/' ) + 1, strlen( $this->_path ) );
	}

	// ID hash
	public function getId()
	{
		return md5( $this->_path );
	}

	// Return file permissions
	public function getPerms()
	{
		return fileperms( $this->_path );
	}

	// Short permistions
	public function getPermsShort()
	{
		$perms = $this->getPerms();

		//$p = decoct($perms);

		return substr( sprintf( '%o', $perms ), -4, 4 );
		//return (strlen($p) > 4 ? substr($p, -4) : $p);
	}

// Return file permissions as humannized string

	public function getPermsString()
	{
		$perms = $this->getPerms( $this->_path );

		if ( ($perms & 0xC000) == 0xC000 )
		{
			// Socket
			$info = 's';
		}
		elseif ( ($perms & 0xA000) == 0xA000 )
		{
			// Symbolic Link
			$info = 'l';
		}
		elseif ( ($perms & 0x8000) == 0x8000 )
		{
			// Regular
			$info = '-';
		}
		elseif ( ($perms & 0x6000) == 0x6000 )
		{
			// Block special
			$info = 'b';
		}
		elseif ( ($perms & 0x4000) == 0x4000 )
		{
			// Directory
			$info = 'd';
		}
		elseif ( ($perms & 0x2000) == 0x2000 )
		{
			// Character special
			$info = 'c';
		}
		elseif ( ($perms & 0x1000) == 0x1000 )
		{
			// FIFO pipe
			$info = 'p';
		}
		else
		{
			// Unknown
			$info = 'u';
		}

		// Owner
		$info .= ( ($perms & 0x0100) ? 'r' : '-');
		$info .= ( ($perms & 0x0080) ? 'w' : '-');
		$info .= ( ($perms & 0x0040) ?
						(($perms & 0x0800) ? 's' : 'x' ) :
						(($perms & 0x0800) ? 'S' : '-'));

		// Group
		$info .= ( ($perms & 0x0020) ? 'r' : '-');
		$info .= ( ($perms & 0x0010) ? 'w' : '-');
		$info .= ( ($perms & 0x0008) ?
						(($perms & 0x0400) ? 's' : 'x' ) :
						(($perms & 0x0400) ? 'S' : '-'));

		// World
		$info .= ( ($perms & 0x0004) ? 'r' : '-');
		$info .= ( ($perms & 0x0002) ? 'w' : '-');
		$info .= ( ($perms & 0x0001) ?
						(($perms & 0x0200) ? 't' : 'x' ) :
						(($perms & 0x0200) ? 'T' : '-'));

		return $info;
	}

	public function endsWith( $haystack, $needle )
	{
		return strrpos( $haystack, $needle ) === strlen( $haystack ) - strlen( $needle );
	}

}

// end


class Dir extends DirAndFiles {

	private $_files = array();
	private $_dirs = array();

	// Directory path as constructor param
	public function __construct( $path, $sort_callback = 'natsort' )
	{
		if ( is_dir( $path ) )
		{
			// Set directory path
			$this->_path = str_replace( '\\', '/', realpath( $path ) );


			// Read directory items
			$dir = opendir( $this->_path );

			while ( $item = readdir( $dir ) )
			{
				if ( strpos( $item, '.' ) !== 0 )
				{
					if ( is_dir( $this->_path . '/' . $item ) )
					{
						$this->_dirs[] = $item;
					}
					else
					{
						$this->_files[] = $item;
					}
				}
			}

			// Sorting
			call_user_func_array( $sort_callback, array(&$this->_dirs) );
			call_user_func_array( $sort_callback, array(&$this->_files) );
		}
		else
		{
			throw new Exception( 'This is not valid directory path!' );
		}
	}

	// Return all subdirectories of this directory
	public function getDirs()
	{
		$dirs = array();

		if ( empty( $dirs ) )
		{
			foreach ( $this->_dirs as $dir_name )
			{
				$dirs[] = new Dir( $this->_path . '/' . $dir_name );
			}
		}

		return $dirs;
	}

	// Return all files of this directory
	public function getFiles()
	{
		$files = array();

		if ( empty( $files ) )
		{
			foreach ( $this->_files as $file_name )
			{
				$ext = $this->getExt();

				$class_name = 'DirFile';

				switch ( $ext )
				{
					case 'jpg':
					case 'jpeg':
					case 'gif':
					case 'png':
						$class_name = 'DirFileImage';
						break;
				}

				$files[] = new $class_name( $this->_path . '/' . $file_name );
			}
		}

		return $files;
	}

	// Remove dir and subdirs
	public function remove()
	{
		// Remove all directory files
		if ( !empty( $this->_files ) )
		{
			foreach ( $this->getFiles() as $file )
			{
				if ( !$file->remove() )
					return false;
			}
		}

		// Remove all sub directories
		if ( !empty( $this->_dirs ) )
		{
			foreach ( $this->getDirs() as $dir )
			{
				if ( !$dir->remove() )
					return false;
			}
		}

		return @rmdir( $this->_path );
	}

	// Return directory path
	public function __toString()
	{
		return $this->_path;
	}

	// Check for childrens (directories or files)
	public function hasChildrens( $only_dirs = false )
	{
		if ( $only_dirs )
			return!(empty( $this->_dirs ));
		else
			return!(empty( $this->_dirs ) && empty( $this->_files ));
	}

	// Check for files
	public function hasFiles()
	{
		return!empty( $this->_files );
	}

}

// end




class DirFile extends DirAndFiles {

	// File path as constructor param
	public function __construct( $path )
	{
		if ( is_file( $path ) )
		{
			$this->_path = str_replace( '\\', '/', $path );
		}
		else
		{
			throw new Exception( 'This is not valid file path!' );
		}
	}

	// Get file size
	public function getSize()
	{
		return filesize( $this->_path );
	}

	// Short permistions
	public function getPermsShort( $octal = true )
	{
		$perms = $this->getPerms();

		$cut = $octal ? 2 : 3;

		return substr( decoct( $perms ), $cut );
	}

	// Remove file
	public function remove()
	{
		return @unlink( $this->_path );
	}

	// Return file path
	public function __toString()
	{
		return $this->_path;
	}

	// Only dir path
	public function getDirPath()
	{
		return substr( $this->getPathShort(), 0, strrpos( $this->getPathShort(), '/' ) + 1 );
	}

	// Full dir path
	public function getFullDirPath()
	{
		return substr( $this->getPath(), 0, strrpos( $this->getPath(), '/' ) + 1 );
	}

}

// end





class DirFileImage extends DirFile {

	private $image = null;
	private $image_width;
	private $image_height;
	private $image_type;

	public function __construct( $path )
	{
		if ( is_file( $path ) )
		{
			$this->_path = str_replace( '\\', '/', $path );
		}
		else
		{
			throw new Exception( 'This is not valid file path!' );
		}
	}

	public function __toString()
	{
		$this->output();
	}

	private function load()
	{
		$image_info = getimagesize( $this->_path );
		$this->image_width = $image_info[0];
		$this->image_height = $image_info[1];
		$this->image_type = $image_info[2];

		if ( $this->image_type == IMAGETYPE_JPEG )
		{
			$this->image = imagecreatefromjpeg( $this->_path );
		}
		elseif ( $this->image_type == IMAGETYPE_GIF )
		{
			$this->image = imagecreatefromgif( $this->_path );
		}
		elseif ( $this->image_type == IMAGETYPE_PNG )
		{
			$this->image = imagecreatefrompng( $this->_path );
		}
	}

	public function save( $filename=null, $image_type=IMAGETYPE_JPEG, $compression=75, $permissions=null )
	{
		if ( !$this->image )
		{
			$this->load();
		}
		if ( $filename == null )
		{
			$filename = $this->getDirPath() . $this->getNewWidth() . 'x' . $this->getNewHeight() . '-' . $this->getName();
		}

		if ( $permissions != null )
		{
			chmod( $filename, $permissions );
		}

		if ( $image_type == IMAGETYPE_JPEG )
		{
			return imagejpeg( $this->image, $filename, $compression );
		}
		elseif ( $image_type == IMAGETYPE_GIF )
		{
			return imagegif( $this->image, $filename );
		}
		elseif ( $image_type == IMAGETYPE_PNG )
		{
			return imagepng( $this->image, $filename );
		}
	}

	public function remove( $remove_cache = false )
	{
		if ( $remove_cache == true )
		{
			$this->remove_cache();

			return parent::remove();
		}
	}

	public function remove_cache()
	{
		$Dir = new Dir( $this->getFullDirPath() );

		foreach ( $Dir->getFiles() as $file )
		{
			if ( $this->endsWith( $file, $this->getName() ) )
			{
				$file->remove();
			}
		}
	}

	public function output( $image_type = IMAGETYPE_JPEG )
	{
		switch ( $image_type )
		{
			case IMAGETYPE_JPEG: header( 'Content-type: image/jpg' );
				break;
			case IMAGETYPE_GIF: header( 'Content-type: image/gif' );
				break;
			case IMAGETYPE_PNG: header( 'Content-type: image/png' );
				break;
		}

		if ( $image_type == IMAGETYPE_JPEG )
		{
			imagejpeg( $this->image );
		}
		elseif ( $image_type == IMAGETYPE_GIF )
		{
			imagegif( $this->image );
		}
		elseif ( $image_type == IMAGETYPE_PNG )
		{
			imagepng( $this->image );
		}
	}

	public function getWidth()
	{
		if ( !$this->image )
		{
			$this->load();
		}
		return $this->image_width;
	}

	public function getHeight()
	{
		if ( !$this->image )
		{
			$this->load();
		}
		return $this->image_height;
	}

	public function getNewWidth()
	{
		return imagesx( $this->image );
	}

	public function getNewHeight()
	{
		return imagesy( $this->image );
	}

	public function resizeToHeight( $height )
	{
		$ratio = $height / $this->getHeight();
		$width = $this->getWidth() * $ratio;

		$this->resize( $width, $height );
	}

	public function resizeToWidth( $width )
	{
		$ratio = $width / $this->getWidth();
		$height = $this->getheight() * $ratio;

		$this->resize( $width, $height );
	}

	public function scale( $scale )
	{
		$width = $this->getWidth() * ($scale / 100);
		$height = $this->getheight() * ($scale / 100);

		$this->resize( $width, $height );
	}

	public function resize( $width, $height )
	{
		$new_image = imagecreatetruecolor( $width, $height );
		imagecopyresampled( $new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight() );

		$this->image = $new_image;
	}

	public function thumb( $max_width, $max_height, $method = 'crop', $bgColour = null )
	{
		// get the current dimensions of the image
		$src_width = $this->getWidth();
		$src_height = $this->getHeight();

		// if either max_width or max_height are 0 or null then calculate it proportionally
		if ( !$max_width )
		{
			$max_width = $src_width / ($src_height / $max_height);
		}
		elseif ( !$max_height )
		{
			$max_height = $src_height / ($src_width / $max_width);
		}

		// initialize some variables
		$thumb_x = $thumb_y = 0; // offset into thumbination image
		// if scaling the image calculate the dest width and height
		$dx = $src_width / $max_width;
		$dy = $src_height / $max_height;

		if ( $method == 'scale' )
		{
			$d = max( $dx, $dy );
		}
		// otherwise assume cropping image
		else
		{
			$d = min( $dx, $dy );
		}

		$new_width = $src_width / $d;
		$new_height = $src_height / $d;

		// sanity check to make sure neither is zero
		$new_width = max( 1, $new_width );
		$new_height = max( 1, $new_height );

		$thumb_width = min( $max_width, $new_width );
		$thumb_height = min( $max_height, $new_height );

		// if bgColour is an array of rgb values, then we will always create a thumbnail image of exactly
		// max_width x max_height
		if ( is_array( $bgColour ) )
		{
			$thumb_width = $max_width;
			$thumb_height = $max_height;
			$thumb_x = ($thumb_width - $new_width) / 2;
			$thumb_y = ($thumb_height - $new_height) / 2;
		}
		else
		{
			$thumb_x = ($thumb_width - $new_width) / 2;
			$thumb_y = ($thumb_height - $new_height) / 2;
		}

		// create a new image to hold the thumbnail
		$thumb = imagecreatetruecolor( $thumb_width, $thumb_height );

		if ( is_array( $bgColour ) )
		{
			$bg = imagecolorallocate( $thumb, $bgColour[0], $bgColour[1], $bgColour[2] );
			imagefill( $thumb, 0, 0, $bg );
		}

		// copy from the source to the thumbnail
		imagecopyresampled( $thumb, $this->image, $thumb_x, $thumb_y, 0, 0, $new_width, $new_height, $src_width, $src_height );
		$this->image = $thumb;
	}

	public function watermark( $min_size = 200 )
	{
		$margin = 7;

		$this->watermark_image_light = 'images/watermark_light.png';
		$this->watermark_image_dark = 'images/watermark_dark.png';

		$image_width = $this->getWidth();
		$image_height = $this->getHeight();

		list ( $watermark_width, $watermark_height ) = getimagesize( $this->watermark_image_light );

		$watermark_x = $image_width - $margin - $watermark_width;
		$watermark_y = $image_height - $margin - $watermark_height;

		$watermark_x2 = $watermark_x + $watermark_width;
		$watermark_y2 = $watermark_y + $watermark_height;

		if ( $watermark_x < 0 or $watermark_y < 0 or $watermark_x2 > $image_width or $watermark_y2 > $image_height or $image_width < $min_size or $image_height < $min_size )
		{
			return;
		}

		$test = imagecreatetruecolor( 1, 1 );
		imagecopyresampled( $test, $this->image, 0, 0, $watermark_x, $watermark_y, 1, 1, $watermark_width, $watermark_height );
		$rgb = imagecolorat( $test, 0, 0 );

		$r = ($rgb >> 16) & 0xFF;
		$g = ($rgb >> 8) & 0xFF;
		$b = $rgb & 0xFF;

		$max = min( $r, $g, $b );
		$min = max( $r, $g, $b );
		$lightness = (double) (($max + $min) / 510.0);
		imagedestroy( $test );

		$watermark_image = ($lightness < 0.5) ? $this->watermark_image_light : $this->watermark_image_dark;

		$watermark = imagecreatefrompng( $watermark_image );

		imagealphablending( $this->image, TRUE );
		imagealphablending( $watermark, TRUE );

		imagecopy( $this->image, $watermark, $watermark_x, $watermark_y, 0, 0, $watermark_width, $watermark_height );

		imagedestroy( $watermark );
	}

}

// end
?>