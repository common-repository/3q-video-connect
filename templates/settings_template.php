<?php
namespace P3QVideoConnect;
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<h2>3Q Settings</h2>
<div id="threeQ_settings" class="container pull-left">
    <?php if($update && $error === false) : ?>
    <div class="row">
        <div class="col-xs-12">
            <div class="alert alert-success">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                Your settings have been saved successfully.
            </div>
        </div>
    </div>
    <?php elseif ($update && $error === true) : ?>
    <div class="row">
        <div class="col-xs-12">
            <div class="alert alert-danger">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <?php
                foreach ($messages as $message) :
                    echo "- ".$message."<br>";
                endforeach;
                ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <div class="row">
        <form class="form-horizontal" action="" method="post">
            <div class="form-group">
                <label for="threeQ_url" class="col-sm-3 control-label">3Q API URI:</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" name="root_url" id="threeQ_url" value="<?php echo $rootUrl; ?>" size="8">
                </div>
            </div>
            <div class="form-group">
                <label for="threeQ__api_token" class="col-sm-3 control-label">API access key:</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="q3_api_token" name="api_token" value="<?php echo $apiToken; ?>" placeholder="API Token">
                </div>
            </div>
            <div class="form-group">
                <label for="threeQ__pager" class="col-sm-3 control-label">Number of videos per page:</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="threeQ__pager" name="pager" value="<?php echo $pager; ?>" placeholder="24">
                </div>
            </div>
            <div class="form-group">
                <div class="col-xs-12">
                    <button type="submit" class="btn btn-primary pull-right">Save settings</button>
                </div>
            </div>
        </form>
    </div>
</div>