<?php if(!defined('DEBUG')) die;

function ai_view_page_edit_plugins_2_handler( $page )
{
	$page_id = empty($page->id) ? 0 : (int)$page->id;
	
	$where = 'page_id="'. $page_id .'" ORDER BY id ASC';
	
	$images = Record::findAllFrom('AIImage', $where);
	
	echo new View('../../plugins/attach_images/views/images_list', array(
		'images' 	=> $images,
		'page_id'  	=> $page_id
	));
}

function ai_page_add_after_save_handler( $page )
{
	$page_id = empty($page->id) ? 0 : (int)$page->id;
	
	$db = Record::getConnection();
	
	$db->query('UPDATE '. TABLE_PREFIX .'ai_image SET page_id="'. (int)$page_id .'" WHERE page_id="0"');
}

function ai_page_delete_handler( $page )
{
	$page_id = empty($page->id) ? 0 : (int)$page->id;
	
	$db = Record::getConnection();
	
	$images = Record::findAllFrom('AIImage', 'page_id = "'. $page_id .'"');
	
	foreach( $images as $image )
	{
		$image->delete();
	}
}

function ai_page_found_handler( $page )
{
	$ai_image = new AIImage();
	$ai_image->page_id = $page->id;
	
	$page->images = $ai_image;
}

function ai_getImageByPageId( $page_id, $offset = 0 )
{
	return Record::findOneFrom('AIImage', 'page_id = "'. (int)$page_id .'" ORDER BY id ASC LIMIT 1 OFFSET ' . $offset);
}

function ai_getImagesByPageId( $page_id )
{
	return Record::findAllFrom('AIImage', 'page_id = "'. (int)$page_id .'" ORDER BY id ASC');
}


/*
	INIT
*/
Plugin::addController('attach_images');

AutoLoader::addFile( 'AIImage', CORE_ROOT . '/plugins/attach_images/AIImage.php' );

Observer::observe('view_page_edit_plugins', 'ai_view_page_edit_plugins_2_handler');

Observer::observe('page_add_after_save', 'ai_page_add_after_save_handler');

Observer::observe('page_found', 'ai_page_found_handler');

?>