<?php
namespace P3QVideoConnect;
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="col-xs-12 col-sm-10 col-md-8 center-v-h">
    <?php if($error['error'] == "no-items") : ?>
        <div class="alert alert-warning">
            <strong>No content found!</strong><br> <?php echo esc_html($error['message']) ?>
        </div>
    <?php elseif ($error['error'] == "request-failed") : ?>
        <div class="alert alert-danger">
            <strong>An error has occurred!</strong><br> <?php echo esc_html($error['message']) ?>
        </div>
    <?php else : ?>
        <div class="alert alert-danger">
            <strong>Settings error!</strong><br> <?php echo esc_html($error['message']) ?>
        </div>
    <?php endif; ?>
</div>