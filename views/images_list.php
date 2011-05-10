<div style="clear:both;width:100%;">
	<h3>Прикрепленные изображения</h3>

	<div id="attach_images_list" rel="<?php echo $page_id; ?>">
		<div id="attach_images_list_images">
			<?php foreach ( $images as $item ): ?>
				<div class="item" rel="<?php echo $item->id; ?>" title="<?php echo $item->alternate; ?>">
					<img src="<?php echo $item->thumb( 80, 80 ); ?>" alt="<?php echo $item->alternate; ?>" />
					<a class="delete" href="<?php echo get_url( 'plugin/attach_images/delete/' . $item->id ); ?>" title="<?php echo __( 'Remove' ); ?>">&times;</a>
				</div>
			<?php endforeach; ?>
		</div>

		<div id="ail_add"><a href="<?php echo get_url( 'plugin/attach_images/add' ); ?>" title="<?php echo __( 'Add images' ); ?>">+</a></div>
	</div>
</div>