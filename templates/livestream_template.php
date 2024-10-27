<?php
namespace P3QVideoConnect;
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$image = P3QVC_VIDEOS_ROOT_URL. 'images/sdn_placeholder_320x180.svg';
if($channel['Project']['ThumbURI'] != null){
	$image = $channel['Project']['ThumbURI'];
}
?>
<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2 threeQBox">
    <div class="threeQMediaItem">
        <figure class="imghvr-push-up" onclick="add3QLivestream(this, <?php echo esc_html($channel['Project']['Id']) ?>, <?php echo esc_html($channel['Id']) ?>)"
                        data-image="<?php echo esc_url($image) ?>" data-projectsecret="<?php echo esc_attr($project_secret) ?>" >
            <div class="threeQVideoBox">
                    <div class="imgContainer" style="background-image:url(<?php echo $image ?>);">
                    </div>
            </div>
            <p class=""><?php echo esc_html($channel['Project']['Label']) ?></p>
            <figcaption>
                <h3><?php echo esc_html($channel['Project']['Label']) ?></h3>
                <div class="threeQvideoInfos">
                    <div class="threeQvideoText center-block">
                        <div class="status-text">status :</div>
                        <div class="status">
                        <?php 
                            if($channel['ChannelStatus']['IsOnline'] == true) {
                                echo '<div class="alert alert-success" role="alert">streaming</div>';
                            } else {
                                echo '<div class="alert alert-warning" role="alert">ready</div>';
                            }
                        ?>
                        </div>
                    </div>
                </div>
                <div class="addIcon">
                    <span title="Insert" class="dashicons dashicons-plus-alt"></span>
                </div>
            </figcaption>
        </figure>
    </div>
</div>	