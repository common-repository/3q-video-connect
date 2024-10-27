<?php
namespace P3QVideoConnect;
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$image = esc_url( P3QVC_VIDEOS_ROOT_URL. 'images/sdn_placeholder_320x180.svg' );
if($video['thumb'] != null && $video['thumb'] != ""){
	$image = esc_url($video['thumb']);
}
?>
<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2 threeQBox">
    <div class="threeQMediaItem">
        <figure class="imghvr-push-up" onclick="add3QmediaFile('<?php echo esc_html($video['id']); ?>', '<?php echo esc_html($this->projectId); ?>', '<?php echo esc_html($this->projectSecret); ?>')">
            <div class="threeQVideoBox">
                <div class="imgContainer" style="background-image:url(<?php echo $image ?>);">
                </div>
            </div>
            <p class=""><?php echo esc_html($video['title']); ?></p>
            <figcaption>
                <h3><?php echo esc_html($video['title']); ?></h3>
                <div class="threeQvideoInfos">
                    <div class="threeQvideoText">
                        <span class="title">uploaded: </span>
                        <span class="text"><?php echo esc_html($video['created']); ?></span>
                    </div>
                    <div class="threeQvideoText">
                        <span class="title">length: </span>
                        <span class="text"><?php echo esc_html($video['length']); ?></span>
                    </div>
                </div>
                <div class="addIcon">
                    <span title="Insert" class="dashicons dashicons-plus-alt"></span>
                </div>
            </figcaption>
        </figure>
    </div>
</div>		