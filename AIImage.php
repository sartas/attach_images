<?php

class AIImage extends Record
{
	const TABLE_NAME = 'ai_image';
	
	public $page_id;
	
	function beforeDelete()
	{
		use_helper('Dir');
			
		$file_path = FROG_ROOT . '/' . PUBLIC_FILES . '/gallery/' . $this->page_id .'/' . $this->file_name;
		$image_file = new DirFileImage( $file_path );
		$image_file->remove(true);
		
		return true;
	}
	
	public function url()
	{
		return URL_PUBLIC . PUBLIC_FILES . '/gallery/' . $this->page_id .'/' . $this->file_name;
	}
	
	public function thumb( $width = null, $height = null )
	{
		if( Plugin::isEnabled('image_resizer') )
		{
			return URL_PUBLIC . PUBLIC_FILES . '/gallery/' . $this->page_id .'/' . ( $width ? $width : 0 ) . 'x' . ( $height ? $height : 0 ) . '-' . $this->file_name;
		}
		else
			return $this->url();
	}
	
	public function find( $offset = 0 )
	{
		return Record::findOneFrom('AIImage', 'page_id = "'. (int)$this->page_id .'" ORDER BY id ASC LIMIT 1 OFFSET ' . $offset);
	}
	
	public function findAll()
	{
		return Record::findAllFrom('AIImage', 'page_id = "'. (int)$this->page_id .'" ORDER BY id ASC');
	}
}

?>