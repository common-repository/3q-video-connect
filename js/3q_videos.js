/*
 * function to add the Video-Player shortcode to the wp-editor
 */
function add3QmediaFile(fileId, projectId, projectSecret) {		
    jQuery.ajax({
        url: threeQ_rootURL+"projects/"+projectId+"/files/"+fileId+"/playouts",
        type: "get",
        dataType: "json",
    	beforeSend: function(request) {
    	    request.setRequestHeader("X-AUTH-APIKEY", threeQ_token);
    	},
        success: function (data) {
            shortcode = '[3q type="video" data-id="'+stripHtmlCode(data.FilePlayouts[0].Id)+'" data-projectId="'+projectId+'" data-projectSecret="'+projectSecret+'" layout="responsive" ';
            jQuery.ajax({
                url: threeQ_rootURL+"projects/"+projectId+"/files/"+fileId+"/pictures/standard",
                type: "get",
                dataType: "json",
                beforeSend: function(request) {
                    request.setRequestHeader("X-AUTH-APIKEY", threeQ_token);
                },
                success: function (data) {
                    shortcode += 'data-thumb="'+stripHtmlCode(data.URI)+'"]'
                },
                complete: function() {
                    wp.media.editor.insert(shortcode);
                }
            });	
        },
        complete: function() {
            jQuery('#threeQModal').modal("hide");
        }
    });
}

/*
 * function to add the Livestream shortcode to the wp-editor 
 */
function add3QLivestream(element, projectId, channelId) {
    var shortcode;
    jQuery.ajax({
        url: threeQ_rootURL+"channels/"+channelId+"/embed",
        type: "get",
        dataType: "json",
    	beforeSend: function(request) {
    	    request.setRequestHeader("X-AUTH-APIKEY", threeQ_token);
    	},
        success: function (data) {
            shortcode = '[3q type="livestream" data-image="'+jQuery(element).data('image')+'" data-projectId="'+projectId+'" data-projectSecret="'+jQuery(element).data('projectsecret')+'" data-channelId="'+channelId+'" data-playerUrl="'+data.ChannelEmbedCodes.PlayerURL+'" layout="responsive" ]';
            wp.media.editor.insert(shortcode);
        },
        complete: function() {
            jQuery('#threeQModal').modal("hide");
        }
    });
}

/*
 * function to load the upload-form and display it inside modal window
 */ 
function threeQLoadUploadForm(element) {
    var url = threeQ_baseURL+"?rest_route=/3q-video-connect/v1/getUploadForm/html/";
    if (jQuery('#threeQUploadContent').children().length == 0) { // only load the form once
        jQuery.ajax({
            url: url,
            type: "get",
            dataType: "json",
            success: function (data) {
                jQuery('#threeQ_upload_spinner').hide();
                jQuery('#threeQUploadContent').html(data.html);
                var threeQUpload = new threeQupload(jQuery('#threeq_upload_project').val(),'threeQ-file', threeQUploadFinished);
                jQuery("#threeQ_StartChunkUpload").on("click", function() {
                    threeQUpload.startUpload(1);
                });
                jQuery("#threeQ_CancelChunkUpload").on("click", function() {
                    threeQUpload.cancelUpload();
                });
            }
        });
    }
}

/*
 * function to save the metadata for a uploaded file
 * shows a message on success inside the modal window
 */
function threeQUploadFinished(projectId, fileId){
    
    var metadata_uri = threeQ_rootURL+"projects/"+projectId+"/files/"+fileId+"/metadata"
    var postData = {
        Title: jQuery('input[name="threeQ_title"]').val(),
//        DisplayTitle: jQuery('input[name="threeQ_display_title"]').val(),
        Description: jQuery('textarea[name="threeQ_description"]').val()
    };
    var xhr = new XMLHttpRequest();
    xhr.onload = function (e) {
        if (this.status === 200) {
            var successMessage = '<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">&times;</button><h4>Finished!</h4>The file has been uploaded successfully.</div>';
            jQuery('#threeQ_responseMessage').html(successMessage);
            jQuery("#threeQ_upload_form").get(0).reset();
            jQuery('#threeQ_status').css('width', '0%');
            jQuery('.upload-status .threeQ-uploadedBytes').html('0');
            jQuery('.upload-status .threeQ-totalBytes').html('0');
            jQuery('.upload-status .threeQ-velocity').html('0');
            jQuery('.upload-status .threeQ-remainingTime').html('0');
        }
    };
    xhr.open("PUT", metadata_uri, true);
    xhr.setRequestHeader('Content-Type', 'application/json; charset=UTF-8');
    xhr.setRequestHeader('X-AUTH-APIKEY', threeQ_token);
    xhr.send(JSON.stringify(postData));
}
   
/*
 * function to load the video list and display it inside modal window
 */
function threeQLoadClips(offset=0, projectId=0, catId=0, order='create', period=0) {
    var url = threeQ_baseURL+"?rest_route=/3q-video-connect/v1/getVideoList/html/";
    if(projectId != 0){
        url += "&projectId="+projectId;
    }
    if(offset != 0){
        url += "&offset="+offset;
    }
    if(catId != 0){
        url += "&catId="+catId;
    }
    url += "&orderby="+order;
    if(period != 0){
        url += "&period="+period;
    }
    jQuery('#threeQMediaContent').empty();
    jQuery('#threeQ_spinner').show();
    jQuery.ajax({
        url: url,
        type: "get",
        dataType: "json",
        success: function (data) {
            jQuery('#threeQ_spinner').hide();
            jQuery('#threeQMediaContent').html(data.html);
            jQuery('#threeq_cat_select').change(function(){
            	threeQLoadClips(0, jQuery("#threeq_project_select").val(), jQuery(this).val(), jQuery("#threeq_order_select").val(), jQuery("#threeq_period_select").val());
            });
            jQuery('#threeq_project_select').change(function(){
            	threeQLoadClips(0, jQuery(this).val(), jQuery("#threeq_cat_select").val(), jQuery("#threeq_order_select").val(), jQuery("#threeq_period_select").val());
            });
            jQuery('#threeq_order_select').change(function(){
            	threeQLoadClips(0, jQuery("#threeq_project_select").val(), jQuery("#threeq_cat_select").val(), jQuery(this).val(), jQuery("#threeq_period_select").val() );
            });
            jQuery('#threeq_period_select').change(function(){
            	threeQLoadClips(0, jQuery("#threeq_project_select").val(), jQuery("#threeq_cat_select").val(), jQuery("#threeq_order_select").val(), jQuery(this).val() );
            });
        }
    });
}

/*
 * function to load the channel list and display it inside modal window
 */
function threeQLoadLivestreams(element) {
    var url = threeQ_baseURL+"?rest_route=/3q-video-connect/v1/getChannelList/html/";
    if (jQuery('#threeQLivestreamContent').children().length == 0){
        jQuery.ajax({
            url: url,
            type: "get",
            dataType: "json",
            success: function (data) {
                jQuery('#threeQ_livestream_spinner').hide();
                jQuery('#threeQLivestreamContent').html(data.html);
            }
        });
    }
}

jQuery(function($) {	
    $(document).ready(function(){
    	$('#threeQ-add-media').click(function() {
            threeQLoadClips(0, 0, 0, 'create', 0);
            $('#threeQModal').modal("show");
            return true;
    	});
    });
});

function stripHtmlCode(html)
{
    var tmp = document.createElement("DIV");
    tmp.innerHTML = html;
    return tmp.textContent || tmp.innerText || "";
}

class threeQupload {
	
    constructor(projectId, field_name, callback) {
        this.instance = this;
        this.callback = callback;
        this.projectId = projectId;
        this.apiKey = threeQ_token;
        this.apiURI = threeQ_rootURL+"projects/" + projectId + "/files";
        this.field_name = field_name;
        this.xhr = null;
        this.uploadLocation = null;
        this.uploadCanceled = false;
        this.timestarted = null;
    }

    startUpload(chunked) {
        var instance = this.instance;
        var fileId = null;
        this.timestarted = new Date();
        this.uploadCanceled = false;
        var file = document.getElementById(this.field_name).files[0];
        if (!file) return alert("Couldn't get the file from the input.");
        // POST a HTTP request to api and get the upload location
        var postData = {
            FileName: file.name,
            FileFormat: file.name.substr(file.name.lastIndexOf('.') + 1)
        };
        this.xhr = new XMLHttpRequest();
        this.xhr.onload = function (e) {
            // the upload location is returned in the Location header
            if (this.status === 201) {
                instance.uploadLocation = this.getResponseHeader("Location");
                if (instance.uploadLocation !== null) {
                    // start upload
                    if(chunked) {
                        instance.uploadFileInChunks(instance.uploadLocation, file);
                    } else {
                        fileId = instance.uploadAsWholeFile(instance.uploadLocation, file);
                    }
                }
            } else if(this.status === 400) {
                var errorMessage = '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button><h4>Error!</h4>Wrong file input.</div>';
                jQuery('#threeQ_responseMessage').html(errorMessage);
                jQuery("#threeQ_upload_form").get(0).reset();
            }
        };
        this.xhr.open("POST", this.apiURI, true);
        this.xhr.setRequestHeader('Content-Type', 'application/json; charset=UTF-8');
        this.xhr.setRequestHeader('X-AUTH-APIKEY', this.apiKey);
        this.xhr.send(JSON.stringify(postData));
        return fileId;
    }

    uploadAsWholeFile(uri, file) {
        var xhr = this.xhr;
        var callback = this.callback;
        var projectId = this.projectId;

        this.uploadCanceled = false;
        xhr = new XMLHttpRequest();
    // upload progress
        xhr.upload.onprogress = function (e) {
            var progress = Math.ceil((e.loaded * 100) / e.total);
            jQuery("#threeQ_status").css('width',progress+"%");
        };
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                var response = JSON.parse(xhr.responseText);
                if (xhr.status === 201) {
                    callback(projectId, response.FileId);
                } else {
                    callback(projectId, false);
                }
            }
        };
        // put content in body
        xhr.open("PUT", uri);
        xhr.send(file);
    }

    uploadFileInChunks(uri, file) {
        this.uploadCanceled = false;
        // upload first chunk
        this.uploadNextChunk(uri, file, 0);
    }

    uploadNextChunk(uri, file, uploadedBytes) {
        var maxChunkSize = 5 * 1024 * 1024; // 5 MiB
        var remainingSize = file.size - uploadedBytes;
        var currentChunkSize = (maxChunkSize < remainingSize) ? maxChunkSize : remainingSize;

        var instance = this.instance;
        var xhr = this.xhr;
        var callback = this.callback;
        var projectId = this.projectId;
        var uploadCanceled = this.uploadCanceled;
        var timestarted = this.timestarted;

        var reader = new FileReader();
        reader.onload = function (e) {
            xhr = new XMLHttpRequest();
            xhr.open("PUT", uri, true);
            xhr.onreadystatechange = function () {
                if (this.readyState !== 4) return;
                uploadedBytes += currentChunkSize;

                // print statistics
                var progress = Math.ceil((uploadedBytes * 100) / file.size);
                jQuery("#threeQ_status").css('width',progress+"%");

                jQuery(".threeQ-uploadedBytes").html(instance.round(uploadedBytes/(1024*1024),2));
                jQuery(".threeQ-totalBytes").html(instance.round(file.size/(1024*1024),2)+" MBytes");

                var timeElapsed = (new Date()) - timestarted; //assumng that timeStarted is a Data Object
                var uploadSpeed = uploadedBytes / (timeElapsed/1000); //upload speed in second
                jQuery(".threeQ-velocity").html(instance.round(uploadSpeed/(1024*1024),2)+" Mbit/s");

                var remainingTime =  (file.size-uploadedBytes)/uploadSpeed;
                jQuery(".threeQ-remainingTime").html(instance.forHumans(Math.round(remainingTime)));

                // last chunk or upload canceled: return
                if (uploadedBytes === file.size ) {
                    jQuery(".threeQ-remainingTime").html("--.--");
                    var response = JSON.parse(xhr.responseText);
                            callback(projectId, response.FileId);
                    return;
                } else if(uploadCanceled) {
                    console.log("Upload wurde abgebrochen");
                } else {
                    instance.uploadNextChunk(uri, file, uploadedBytes);
                }
            };
            // Content-Range Header
            var currentRangeEnd = uploadedBytes + currentChunkSize - 1;
            var currentRange = uploadedBytes + '-' + currentRangeEnd;
            xhr.setRequestHeader('Content-Range', 'bytes ' + currentRange + '/' + file.size);
            // send content
            xhr.send(e.target.result);
        };
        // read chunk from file
        reader.readAsArrayBuffer(file.slice(uploadedBytes, uploadedBytes + currentChunkSize));
    }

    resumeUpload() {
        var xhr = this.xhr;
        var uploadLocation = this.uploadLocation;

        this.uploadCanceled = false;
        var file = document.getElementById("file").files[0];
        if (!file) return alert("Couldn't get the file from the input.");

        xhr = new XMLHttpRequest();
        xhr.onload = function (e) {
            // if 308, get Range Header and resume upload
            if (this.status === 308) {
                var rangeHeader = this.getResponseHeader("Range");
                var uploadedBytes = parseInt(rangeHeader.substr(rangeHeader.lastIndexOf("-") + 1));
                uploadNextChunk(uploadLocation, file, uploadedBytes + 1);
            }
        };
        // PUT with "Content-Range: bytes */filesize" return a 308 response with last Range Header
        xhr.open("PUT", uploadLocation, true);
        xhr.setRequestHeader('Content-Range', 'bytes */' + file.size);
        xhr.send();
    }

    cancelUpload() {
        this.uploadCanceled = true;
    }

    round(wert, dez) {
        wert = parseFloat(wert);
        if (!wert) return 0;
        dez = parseInt(dez);
        if (!dez) dez=0;
        var umrechnungsfaktor = Math.pow(10,dez);
        return Math.round(wert * umrechnungsfaktor) / umrechnungsfaktor;
    }

    forHumans ( seconds ) {
        var levels = [
            [Math.floor(seconds / 31536000), 'years'],
            [Math.floor((seconds % 31536000) / 86400), 'days'],
            [Math.floor(((seconds % 31536000) % 86400) / 3600), 'hours'],
            [Math.floor((((seconds % 31536000) % 86400) % 3600) / 60), 'minutes'],
            [(((seconds % 31536000) % 86400) % 3600) % 60, 'seconds'],
        ];
        var returntext = '';

        for (var i = 0, max = levels.length; i < max; i++) {
            if ( levels[i][0] === 0 ) continue;
            returntext += ' ' + levels[i][0] + ' ' + (levels[i][0] === 1 ? levels[i][1].substr(0, levels[i][1].length-1): levels[i][1]);
        }
        return returntext.trim();
    }
}