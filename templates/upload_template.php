<?php
namespace P3QVideoConnect;
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="row">
    <div class="col-xs-12">
        <div class="row">
            <form id="threeQ_upload_form">
                <div class="col-xs-12" id="threeQ_responseMessage"></div>
		<div class="col-xs-5">
                    <?php echo $uploadProjectSelector;?>
                    <div class="form-group">
                        <label for="threeQ-file">Pick a file to start the upload!</label>
                        <input type="file" class="form-control-file btn" accept="video/*" id="threeQ-file">
                    </div>
                    <h4>Status</h4>
                    <div class="progress progress-success">
                        <div  id="threeQ_status" class="bar" style="width:0%"></div>
                    </div>
                    <div class="upload-status">
                        <div class="col-xs-4">
                            Uploaded:<br> <span class="threeQ-uploadedBytes">0</span> of <span class="threeQ-totalBytes">0</span> 
                        </div>
                        <div class="col-xs-4">
                            Upload speed:<br> <span class="threeQ-velocity">0</span>
                        </div>
                        <div class="col-xs-4">
                            Remaining time:<br> <span class="threeQ-remainingTime">0</span>
                        </div>
                        <div class="col-xs-12" style="padding-top: 28px;">
                            <button type="button" value="Start" id="threeQ_StartChunkUpload" class="btn btn-default pull-right">Start</button>
                            <button type="button" value="Cancel" id="threeQ_CancelChunkUpload" class="btn btn-default pull-right" style="margin-right: 12px;">Cancel</button>
                        </div>
                        <div class="col-xs-12">
                            <p class="bg-warning" style="padding: 12px; margin-top: 12px;">
                                <span class="dashicons dashicons-editor-help"></span>
                                Here you can upload your video including metadata to the 3Q CDN. <br>
                                First select the video-file, then fill in the metadata.<br>
                                Now you can start the upload process.<br>
                                After successful upload, the form is reset and you can start with the next file.<br>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-xs-7">
                    <h4>Metadata</h4>
                    <div class="form-group">
                        <label for="title" class="">Title</label>
                        <input type="text" class="form-control" name="threeQ_title" placeholder="Title of the video">
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" name="threeQ_description" rows="6" placeholder="Description of the video"></textarea>
                    </div>
                </div>
            </form>
        </div>
    </div>	
</div>						