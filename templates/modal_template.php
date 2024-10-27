<?php
namespace P3QVideoConnect;
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<!-- Modal -->
<div id="threeQModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button class="close" type="button" data-dismiss="modal">
                    <span class="dashicons dashicons-no"></span>
                </button>
                <!-- <img src="<?php echo esc_url( P3QVC_VIDEOS_ROOT_URL. 'images/3q-icon-small.jpg' ) ?>"> -->
                <div class="headline">
                    <h4 class="modal-title">Select a video that you would like to insert.</h4>
                </div>
            </div>
            <div id="threeQ_modal_body" class="modal-body">
                <ul class="nav nav-tabs" id="threeQ_tab" role="tablist">
                    <li class="nav-item active">
                        <a class="nav-link" id="threeQ_mediathek_tab" data-toggle="tab" href="#threeQ_mediathek" role="tab" aria-controls="threeQ_mediathek" aria-selected="true" aria-expanded="true">Mediathek</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" onclick="threeQLoadLivestreams(this)" id="threeQ_livestream_tab" data-toggle="tab" href="#threeQ_livestream" role="tab" aria-controls="threeQ_livestream" aria-selected="false">Livestreams</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" onclick="threeQLoadUploadForm(this)" id="threeQ_upload_tab" data-toggle="tab" href="#threeQ_upload" role="tab" aria-controls="threeQ_upload" aria-selected="false">File upload</a>
                    </li>
                </ul>
                <div class="tab-content" id="threeQ_tab_content">
                    <div class="tab-pane fade active in" id="threeQ_mediathek" role="tabpanel" aria-labelledby="threeQ_mediathek_tab">
                        <img id="threeQ_spinner" class="threeQ_spinner" src="<?php echo esc_url( P3QVC_VIDEOS_ROOT_URL. 'images/3q-spinner.svg' ) ?>">
                        <div id="threeQMediaContent" class="threeQContent">
                        </div>
                    </div>
                    <div class="tab-pane fade" id="threeQ_livestream" role="tabpanel" aria-labelledby="threeQ_livestream_tab">
                        <img id="threeQ_livestream_spinner" class="threeQ_spinner" src="<?php echo esc_url( P3QVC_VIDEOS_ROOT_URL. 'images/3q-spinner.svg' ) ?>">
                        <div id="threeQLivestreamContent" class="threeQContent">
                        </div>
                    </div>
                    <div class="tab-pane fade" id="threeQ_upload" role="tabpanel" aria-labelledby="threeQ_upload_tab">
                        <img id="threeQ_upload_spinner" class="threeQ_spinner" src="<?php echo esc_url( P3QVC_VIDEOS_ROOT_URL. 'images/3q-spinner.svg' ) ?>">
                        <div id="threeQUploadContent">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" type="button" data-dismiss="modal">Close</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->