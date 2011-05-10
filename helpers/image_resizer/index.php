<?php

if ( !defined( 'DEBUG' ) )
	die;

class ImageResizerController extends Controller {

	public function index()
	{
		$args = func_get_args();
		$file_path = join( '/', $args );

		$ext = strtolower( preg_replace( '/^.+\./', '', $file_path ) );

		if ( in_array( $ext, array('jpg', 'jpeg', 'gif', 'png') ) === false )
			page_not_found();

		$f_expl = explode( '-', $args[count( $args ) - 1] );
		$f_size = explode( 'x', array_shift( $f_expl ) );
		$f_name = join( '-', $f_expl );

		array_pop( $args );

		$real_file_path = realpath( FROG_ROOT . '/' . PUBLIC_FILES . '/' ) . '/' . join( '/', $args ) . '/' . $f_name;

		if ( count( $f_size ) < 2 || file_exists( $real_file_path ) == false )
			page_not_found();

		if ( strstr( $_SERVER["HTTP_USER_AGENT"], 'MSIE' ) === false )
		{
			header( "Cache-Control: private, max-age=10800, pre-check=10800" );
			header( "Pragma: private" );
			header( "Expires: " . date( DATE_RFC822, strtotime( " 2 day" ) ) );

			// Cache image
			$file_mtime = filemtime( $real_file_path );

			if ( isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) && (strtotime( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) == $file_mtime) )
			{
				// send the last mod time of the file back
				header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', $file_mtime ) . ' GMT', true, 304 );
				exit;
			}

			header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', $file_mtime ) . ' GMT', true, 302 );
		}

		use_helper( 'Dir' );

		$image = new DirFileImage( $real_file_path );
		$image->thumb( $f_size[0], $f_size[1] );
		
		$image->save();

		echo($image);
	}

}

Dispatcher::addRoute( array(
	'/' . PUBLIC_FILES . '/:any' => 'image_resizer/index/$1'
) );
?>