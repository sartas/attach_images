$(function(){
	var $dialog 	= false;
		
	var body_id = $('body:first').attr('id');
	
	if( body_id == 'body_page_edit' || body_id == 'body_page_add' )
	{
		var ai_image_delete_handler = function()
		{
			if( confirm(frog.__('Are you sure?')) )
			{
				frog.loaderShow();
				
				$.ajax({
					url: $(this).attr('href'),
					dataType: 'json',
					
					success: function( content )
					{
						frog.loaderHide();
						
						if( content != 'false' )
						{
							$('#attach_images_list_images .item[rel="'+ content +'"]').remove();
						}
						else
						{
							frog.error('Image not deleted.');
						}
					},
					
					error: function()
					{
						frog.loaderHide();
						
						frog.error('Error when trying to delete image.');
					}
				});
			}
			
			return false;
		};
	
		$('#attach_images_list_images .delete').click(ai_image_delete_handler);
	
		$('#ail_add a').click(function()
		{
			if( $dialog === false )
			{
				frog.loaderShow();
				
				var page_id = parseInt( $('#attach_images_list').attr('rel') );
				
				$.ajax({
					url: $(this).attr('href') + '/' + page_id,
					dataType: 'html',
					
					success: function( content )
					{
						frog.loaderHide();
						
						$dialog = $(content).dialog({
							width:		450,
							height:		300,
							modal:		true,
							resizable:	false,
							
							buttons: [
								{
									text: 'Upload',
									click: function()
									{
										$(this).find('#ai-upload-file').uploadifyUpload();
									}
								},
								{
									text: 'Close',
									click: function()
									{
										$(this).dialog('close');
									}
								}
							]
						});
						
						var onError_handler = function( event, ID, fileObj, errorObj )
						{
							frog.error( 'Gallery plugin: File not uploaded by SWF uploader.', errorObj );
						};
						
						var onAllComplete_handler = function( event, data )
						{
							frog.loaderShow();
							
							$.ajax({
								url: '?/plugin/attach_images/get_uploaded/' + page_id,
								dataType: 'json',
								success: function( content )
								{
									frog.loaderHide();
									
									if( content != 'false' )
									{
										for( var i=0; i<content.length; ++i )
										{
											$('#attach_images_list_images')
												.append('<div class="item" rel="'+ content[i].id +'"><img src="'+ content[i].thumb +'" alt="'+ content[i].alternate +'" /><a class="delete" href="?/plugin/attach_images/delete/'+ content[i].id +'">&times;</a></div>')
													.find('.delete:last').click( ai_image_delete_handler );
											
										}
									}
								},
								error: function()
								{
									frog.loaderHide();
									
									frog.error('Error when when getting uploaded images list.');
								}
							});
							
							$dialog.dialog('close');
						};
						
						// Uploadify
						$dialog.find('#ai-upload-file').uploadify({
							// options
							uploader  : PLUGINS_URL + 'files/uploadify/uploadify.swf',
							script    : $dialog.find('#ai-upload-form').attr('action'),
							expressInstall : PLUGINS_URL + 'files/uploadify/expressInstall.swf',
							cancelImg : PLUGINS_URL + 'files/uploadify/cancel.png',
							wmode     : 'transparent',
							multi     : true,
							buttonText: 'Add files',
							fileExt   : '*.jpg;*.gif;*.png',
							fileDesc  : 'Images',
							scriptData: {
								'PHPSESSID': frogFileUpload.getPhpsessid(),
								'page_id'  : page_id
							},
							method    : 'post',
							auto      : false,
							
							// events
							onError   : onError_handler,
							onAllComplete: onAllComplete_handler
						});
					},
					
					error: function()
					{
						frog.loaderHide();
						
						frog.error('Error when getting upload images form.');
					}
				});
			}
			else
			{
				$dialog.dialog();
			}			
			
			return false;
		});
	}

});