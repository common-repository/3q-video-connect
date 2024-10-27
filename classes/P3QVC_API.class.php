<?php
namespace P3QVideoConnect;
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 *
 * @author pan
 * @desc ?rest_route=/3q-video-connect/v1/getVideoList/test
 *
 */
class P3QVC_API {

    private $root_url;

    private $apitoken = null;

    private $projectId = 0;
    private $projectSecret = null;

    private $requestContext = null;

    private $totalCount = null;
    private $limit = 24;
    private $offset = 0;

    private $catId = 0;
    private $period = 'all';
    private $orderBy = 'create';

    private $projects = null;

    private $orders = array(
        'create' => 'creation date (desc)',
        'lastupdate' => 'last update (desc)'
    );

    private $periods = array(
        'all' => 'all',
        'lasthour' => 'last hour',
        'lastday' => 'last day',
        'lastweek' => 'last week',
        'lastmonth' => 'last month',
        'lastyear' => 'last year'
    );

    public function __construct() {
        $this->root_url = get_option(P3QVC_PLUGIN_SUFFIX."root_url", "https://sdn.3qsdn.com/api/v2/");
        add_action( 'rest_api_init', array( $this , 'p3qvc_init' ), 2 );
    }

    /**
     * function init
     * @desc initializes the API-class
     */
    public function p3qvc_init () {
        $this->apitoken = get_option(P3QVC_PLUGIN_SUFFIX.'api_token', '');

        if($this->apitoken) {
            // prepare options for http-requests to the 3Q-API
            $options = array(
                'http'=>array(
                    'method'=>"GET",
                    'header'=>"Content-Type: application/json\r\n" ."X-AUTH-APIKEY: ".$this->apitoken."\r\n"
                )
            );
            $this->requestContext = stream_context_create($options);
            // load all projects related to the api-token
            $this->projects = $this->getProjects();
        }


        if(isset($_GET['projectId']) && is_numeric($_GET['projectId'])){
            $this->projectId = sanitize_text_field($_GET['projectId']);
        } else {
            if(!empty($this->projects['vod'])) {
                $this->projectId = reset($this->projects['vod'])['Id'];
            } else {
                $this->projectId = null;
            }
        }
        if(isset($_GET['projectSecret'])){
            $this->projectSecret = sanitize_text_field($_GET['projectSecret']);
        } else {
            if(!empty($this->projects['vod'])) {
                $this->projectSecret = reset($this->projects['vod'])['SecurityKey'];
            } else {
                $this->projectSecret = null;
            }
        }

        if(get_option(P3QVC_PLUGIN_SUFFIX.'pager') != false) {
            $this->limit = get_option(P3QVC_PLUGIN_SUFFIX.'pager');
        }

        if(isset($_GET['offset']) && is_numeric($_GET['offset'])) {
            $this->offset = sanitize_text_field($_GET['offset']);
        }
        if(isset($_GET['catId']) && is_numeric($_GET['catId'])) {
            $this->catId = sanitize_text_field($_GET['catId']);
        }
        if(isset($_GET['orderby']) && is_numeric($_GET['orderby'])) {
            $this->orderBy = sanitize_text_field($_GET['orderby']);
        }
        if(isset($_GET['period']) && in_array($_GET['period'], $this->periods)) {
            $this->period = sanitize_text_field($_GET['period']);
        }


        register_rest_route( '3q-video-connect/v1', 'getVideoList/json', array(
            'methods'  => \WP_REST_Server::READABLE,
            'callback' => array( $this , 'getJsonVideoList'),
        ) );
        register_rest_route( '3q-video-connect/v1', 'getVideoList/html', array(
            'methods'  => \WP_REST_Server::READABLE,
            'callback' => array( $this , 'getVideoList'),
        ) );
        register_rest_route( '3q-video-connect/v1', 'getChannelList/html', array(
            'methods'  => \WP_REST_Server::READABLE,
            'callback' => array( $this , 'getChannelList'),
        ) );
        register_rest_route( '3q-video-connect/v1', 'getUploadForm/html', array(
            'methods'  => \WP_REST_Server::READABLE,
            'callback' => array( $this , 'getUploadForm'),
        ) );
    }

    /**
     * function getUploadForm
     * @desc provides the generated html-code for the video-upload view
     * @param \WP_REST_Request $request
     * @return mixed
     */
    public function getUploadForm( \WP_REST_Request $request ){
        $jsonArr = array();
        $htmlOutput = "";

        $jsonArr = array();
        $htmlOutput = "";

        if ($this->apitoken == null || $this->apitoken == '' || $this->apitoken === false) {
            $error['error'] = "apitoken";
            $error['message'] = "No API token exists or API token is invalid. Please enter an API token in the settings for the 3Q-Videos plugin.";
            ob_start();
            include P3QVC_VIDEOS_ROOT_PATH.'/templates/error_template.php';
            $htmlOutput .= ob_get_clean();
        } elseif ($this->projectId== null || $this->projectId== '') {
            $error['error'] = "projectid";
            $error['message'] = "No project id available. Please enter a Project Id in the settings for the 3Q-Videos plugin.";
            ob_start();
            include P3QVC_VIDEOS_ROOT_PATH.'/templates/error_template.php';
            $htmlOutput .= ob_get_clean();
        } else {
            $vod_projects = $this->projects['vod'];

            if (!empty($vod_projects)) {
                // create the select field to set the project to upload the file
                $uploadProjectSelector = "<div class=\"form-group\"><label class=\"projectLabel\">Choose a project:</label>";
                $uploadProjectSelector .= "<select id=\"threeq_upload_project\" class=\"form-control threeQ-select\">";
                $i = 0;
                foreach ($vod_projects as $vod_project) {
                    $uploadProjectSelector .= "<option " . ($i == 0 ? 'selected' : '') . " value=\"" . esc_attr($vod_project['Id']) . "\">" . esc_html($vod_project['Label']) . "</option>";
                    $i++;
                }
                $uploadProjectSelector .= "</select></div>";
                ob_start();
                include P3QVC_VIDEOS_ROOT_PATH . '/templates/upload_template.php';
                $htmlOutput .= ob_get_clean();
            } else {
                $error['error'] = "projectid";
                $error['message'] = "Your API token does not have a VOD project associated with it. Therefore, the upload is currently not possible.";
                ob_start();
                include P3QVC_VIDEOS_ROOT_PATH . '/templates/error_template.php';
                $htmlOutput .= ob_get_clean();
            }
        }
        $jsonArr['html'] = $htmlOutput;
        return json_decode(json_encode($jsonArr));
    }

    /**
     * function provides the html code to display all channels (livestream and audio)
     * @param \WP_REST_Request $request
     * @return string
     */
    public function getChannelList( \WP_REST_Request $request ) {

        $jsonArr = array();
        $htmlOutput = "";

        if ($this->apitoken == null || $this->apitoken == '' || $this->apitoken === false) {
            $error['error'] = "apitoken";
            $error['message'] = "No API token exists or API token is invalid. Please enter an API token in the settings for the 3Q-Videos plugin.";
            ob_start();
            include P3QVC_VIDEOS_ROOT_PATH.'/templates/error_template.php';
            $htmlOutput .= ob_get_clean();
        }
//        elseif ($this->projectId== null || $this->projectId== '') {
//            $error['error'] = "projectid";
//            $error['message'] = "No project id available. Please enter a Project Id in the settings for the 3Q-Videos plugin.";
//            ob_start();
//            include P3QVC_VIDEOS_ROOT_PATH.'/templates/error_template.php';
//            $htmlOutput .= ob_get_clean();
//        }
        else {
            $channels = $this->getChannels();
            $htmlOutput = "<div class=\"row\"><div class=\"col-xs-12\">";
            foreach ($channels['Channels'] as $channel) {
                ob_start();
                $project_secret = $this->projects['livestream'][$channel['Project']['Id']]['SecurityKey'];
                include P3QVC_VIDEOS_ROOT_PATH . '/templates/livestream_template.php';
                $htmlOutput .= ob_get_clean();
            }
            $htmlOutput .= "</div></div>";
        }

        $jsonArr['html'] = $htmlOutput;
        return json_decode(json_encode($jsonArr));
    }

    /**
     * function provides html code for videos
     * @param \WP_REST_Request $request
     * @return mixed
     */
    public function getVideoList( \WP_REST_Request $request ) {

        $jsonArr = array();
        $htmlOutput = "";

        if ($this->apitoken == null || $this->apitoken == '' || $this->apitoken === false) {
            $error['error'] = "apitoken";
            $error['message'] = "No API token exists or API token is invalid. Please enter an API token in the settings for the 3Q-Videos plugin.";
            ob_start();
            include P3QVC_VIDEOS_ROOT_PATH.'/templates/error_template.php';
            $htmlOutput .= ob_get_clean();
        } elseif ($this->projectId== null || $this->projectId== '') {
            $error['error'] = "projectid";
            $error['message'] = "No project id available. Please enter a Project Id in the settings for the 3Q-Videos plugin.";
            ob_start();
            include P3QVC_VIDEOS_ROOT_PATH.'/templates/error_template.php';
            $htmlOutput .= ob_get_clean();
        } else {
            $videos = $this->requestVideos();

            if(is_array($videos) && empty($videos)){
                $htmlOutput .= "<div class=\"row\"><div class=\"col-xs-12\">";
                $htmlOutput .= $this->getFilter();
                $htmlOutput .= "</div></div>";

                $error['error'] = "no-items";
                $error['message'] = "This query returned an empty result, please try something else.";
                ob_start();
                include P3QVC_VIDEOS_ROOT_PATH.'/templates/error_template.php';
                $htmlOutput .= ob_get_clean();

            } elseif($videos == false){
                $error['error'] = "request-failed";
                $error['message'] = "The API token and / or the project id seem to be wrong. Please correct your settings. <br>";
                $error['message'] .= "An HTTP error has occurred (404 Forbidden | 403 Not Found). <br>";
                ob_start();
                include P3QVC_VIDEOS_ROOT_PATH.'/templates/error_template.php';
                $htmlOutput .= ob_get_clean();
            } else {
                $htmlOutput .= "<div class=\"row\"><div class=\"col-xs-12\">";
                $htmlOutput .= $this->getFilter();
                $pager = $this->getPager($this->totalCount);
                $htmlOutput .= $pager;
                $htmlOutput .= "</div></div>";

                $htmlOutput .= "<div class=\"row\"><div class=\"col-xs-12\">";

                foreach ($videos as $video) {
                    ob_start();
                    include P3QVC_VIDEOS_ROOT_PATH.'/templates/item_template.php';
                    $htmlOutput .= ob_get_clean();
                }
                $htmlOutput .= "</div></div>";

                $htmlOutput .= $pager;
            }
        }
        $jsonArr['html'] = $htmlOutput;
        return json_decode(json_encode($jsonArr));
    }

    /**
     * test function to
     * @param \WP_REST_Request $request
     * @unused
     * @return mixed
     */
    public function getJsonVideoList( \WP_REST_Request $request ) {
        $videos = $this->requestVideos();
        // Return either a WP_REST_Response or WP_Error object
        return json_decode(json_encode($videos));
    }

    /**
     * function filter
     * @desc generates the html-code for all filters in view
     * @return string
     */
    private function getFilter() {
        $filterHtml = "<form class=\"form-inline\">";

        // project select
        $filterHtml .= "<div class=\"form-group\"><label class=\"projectLabel\">Project:</label>";
        $filterHtml .= "<select id=\"threeq_project_select\" class=\"form-control threeQ-select\">";

        $vod_projects = $this->projects['vod'];
        if(!empty($vod_projects)) {
            foreach ($vod_projects as $project) {
                if($project['Id'] == $this->projectId){
                    $filterHtml .= "<option selected value=\"".esc_attr($project['Id'])."\">".esc_html($project['Label'])."</option>";
                } else {
                    $filterHtml .= "<option value=\"".esc_attr($project['Id'])."\">".esc_html($project['Label'])."</option>";
                }
            }
        }
        $filterHtml .= "</select></div>";

        // categorie select
        $filterHtml .= "<div class=\"form-group\"><label class=\"filterLabel\">Category:</label>";
        $filterHtml .= "<select id=\"threeq_cat_select\" class=\"form-control threeQ-select\"> ";
        if($this->catId == 0) {
            $filterHtml .= "<option selected value=\"0\">all categories</option>";
        } else {
            $filterHtml .= "<option value=\"0\">all categories</option>";
        }
        $clipcats = $this->getClipCats();
        foreach ($clipcats['Categories']as $cat) {
            $selected = '';
            if($this->catId != false && $this->catId == $cat['Id']) {
                $selected = "selected";
            }
            $filterHtml .= "<option ".esc_attr($selected)." value=\"".esc_attr($cat['Id'])."\">".esc_html($cat['Label'])."</option>";
        }
        $filterHtml .= "</select></div>";

        // order select
        $filterHtml .= "<div class=\"form-group\"><label class=\"orderLabel\">Order:</label>";
        $filterHtml .= "<select id=\"threeq_order_select\" class=\"form-control threeQ-select\">";
        foreach ($this->orders AS $key => $order) {
            if($this->orderBy == $key){
                $filterHtml .= "<option selected value=\"".esc_attr($key)."\">".esc_html($order)."</option>";
            } else {
                $filterHtml .= "<option value=\"".esc_attr($key)."\">".esc_html($order)."</option>";
            }
        }
        $filterHtml .= "</select></div>";
        // period select
        $filterHtml .= "<div class=\"form-group\"><label class=\"periodLabel\">Period:</label>";
        $filterHtml .= "<select id=\"threeq_period_select\" class=\"form-control threeQ-select\">";
        foreach ($this->periods AS $key => $period) {
            if($this->period == $key){
                $filterHtml .= "<option selected value=\"".esc_attr($key)."\">".esc_html($period)."</option>";
            } else {
                $filterHtml .= "<option value=\"".esc_attr($key)."\">".esc_html($period)."</option>";
            }
        }
        $filterHtml .= "</select></div>";
        $filterHtml .= "</form>";
        return $filterHtml;
    }

    /**
     * function getProjects
     * @desc function requests all projects related to the api-token
     * 			and saves the projects for vod and livestream in keys [vod|livestream] in the return array
     * @return array[]
     */
    private function getProjects() {
        $projects['vod'] = array();
        $projects['livestream'] = array();
        $request_url = $this->root_url."projects";
        $response = file_get_contents($request_url,false,$this->requestContext);
        if($response === false) {
            $this->apitoken = false;
            return null;
        } else {
            $projects_response = json_decode($response,true);

            foreach($projects_response['Projects'] AS $project) {
                if($project['StreamType']['Id'] == 1) {
                    // vod project
                    $projects['vod'][$project['Id']] = $project;
                } elseif ($project['StreamType']['Id'] == 2 || $project['StreamType']['Id'] == 3) {
                    // livestream project
                    $projects['livestream'][$project['Id']] = $project;
                }
            }
        }
        return $projects;
    }

    /**
     * @name getChannels
     * @desc requests all channels (livestreams) related to the API-Key
     * @return array
     */
    private function getChannels() {
        $channels = "";
        $request_url = $this->root_url."channels";
        $channels = file_get_contents($request_url,false,$this->requestContext);
        $response = json_decode($channels, true);
        return $response;
    }

    /**
     * @name getClipCats
     * @desc provides the 3q categories from one customer
     * !!! the categories are not dependent to a specific project (valid for all projects)
     * @return array
     */
    private function getClipCats() {
        $request_url = $this->root_url."categories";
        $clipcats = file_get_contents($request_url,false,$this->requestContext);
        $cats = json_decode($clipcats, true);
        return $cats;
    }

    /**
     * request 3Q-API and prepare videos array
     * @return array|false
     */
    private function requestVideos() {
        date_default_timezone_set('UTC');
        // prepare the get url
        $request_url = $this->root_url."projects/".$this->projectId."/files?IncludeMetadata=true&IncludeProperties=true&Limit=".$this->limit;
        if($this->offset != 0) {
            $request_url .= "&Offset=".$this->offset;
        }
        if($this->catId != 0) {
            $request_url .= "&CategoryId=".$this->catId;
        }
        $request_url .= "&OrderBy=".$this->orderBy;
        if($this->period != 'all') {
            $request_url .= "&Period=".$this->period;
        }
        // request all files
        $filelist = @file_get_contents($request_url,false,$this->requestContext);
        if($http_response_header[0] == "HTTP/1.1 403 Forbidden" || $http_response_header[0] == "HTTP/1.1 404 Not Found"){
            return false;
        }
        $j_filelist = json_decode($filelist, true);

        $this->totalCount = $j_filelist['TotalCount'];
        $videos = array();
        foreach ($j_filelist['Files'] AS $file) {
            $video['id'] = $file['Id'];
            $video['title'] = $file['Metadata']['Title'];
            $video['image'] = $file['Metadata']['StandardFilePicture']['URI'];
            $video['thumb'] = $file['Metadata']['StandardFilePicture']['ThumbURI'];
            $video['length'] = gmdate("H:i:s", $file['Properties']['Length']);
            $video['created'] = date("d.m.Y", strtotime($file['CreatedAt']));
            $video['updated'] = date("d.m.Y", strtotime($file['LastUpdateAt']));
            $videos[] = $video;
        }
        return $videos;
    }

    /**
     * function getPager
     * @desc provides the pager for the videos
     * @param int $totalCount
     * @return string
     */
    private function getPager($totalCount) {
        $currentPage = 1;
        $totalPages = ceil($totalCount/$this->limit);
        if($this->offset != 0) {
            $currentPage = ($this->offset / $this->limit) + 1;
        }
        $pagerHtml = "<div class=\"pagination\">";
        $pagerHtml .= "<ul class=\"pagination pagination-large\">";
        if($currentPage > 1) {
            $pagerHtml .= "<li><a rel=\"prev\" href=\"#\" onclick=\"threeQLoadClips(0, ".esc_attr($this->projectId).", ".esc_attr($this->catId).", '".esc_attr($this->orderBy)."', '".esc_attr($this->period)."')\" title=\"First page\" >&lang;&lang;&lang;</a></li>";
            $pagerHtml .= "<li><a rel=\"prev\" href=\"#\" onclick=\"threeQLoadClips(".(esc_attr($this->offset)-esc_attr($this->limit)).", ".esc_attr($this->projectId).", ".esc_attr($this->catId).", '".esc_attr($this->orderBy)."',  '".esc_attr($this->period)."')\" title=\"Back\" >&lang;</a></li>";
        } else {
            $pagerHtml .= "<li class=\"disabled\"><a rel=\"prev\" href=\"#\" onclick=\"threeQLoadClips(0, ".$this->projectId.", ".$this->catId.", '".$this->orderBy."',  '".$this->period."')\" title=\"First page\" >&lang;&lang;&lang;</a></li>";
            $pagerHtml .= "<li class=\"disabled\"><a rel=\"prev\" href=\"#\" onclick=\"threeQLoadClips(".($this->offset-$this->limit).", ".$this->projectId.", ".$this->catId.", '".$this->orderBy."',  '".$this->period."')\" title=\"Back\" >&lang;</a></li>";
        }
        if($currentPage <= 6) {
            $maxPages = ($totalPages < 11) ? $totalPages : 11;
            // show always the first 10 pages
            for($i=0; $i<$maxPages; $i++) {
                if($currentPage == $i+1) {
                    $pagerHtml .= "<li  class=\"active\"><a rel=\"prev\" href=\"#\" onclick=\"threeQLoadClips(".($this->limit*$i).", ".$this->projectId.", ".$this->catId.", '".$this->orderBy."',  '".$this->period."')\">".($i+1)."</a></li>";
                } else {
                    $pagerHtml .= "<li><a rel=\"prev\" href=\"#\" onclick=\"threeQLoadClips(".($this->limit*$i).", ".$this->projectId.", ".$this->catId.", '".$this->orderBy."',  '".$this->period."')\">".($i+1)."</a></li>";
                }
            }
        } else {
            $maxPages = ($totalPages < $currentPage+5) ? $totalPages : $currentPage+5;
            for($i=$currentPage-6; $i<$maxPages; $i++) {
                if($currentPage == $i+1) {
                    $pagerHtml .= "<li  class=\"active\"><a rel=\"prev\" href=\"#\" onclick=\"threeQLoadClips(".($this->limit*$i).", ".$this->projectId.", ".$this->catId.", '".$this->orderBy."', '".$this->period."')\">".($i+1)."</a></li>";
                } else {
                    $pagerHtml .= "<li><a rel=\"prev\" href=\"#\" onclick=\"threeQLoadClips(".($this->limit*$i).", ".$this->projectId.", ".$this->catId.", '".$this->orderBy."', '".$this->period."')\">".($i+1)."</a></li>";
                }
            }
        }
        if($currentPage < $totalPages) {
            $pagerHtml .= "<li><a rel=\"prev\" href=\"#\" onclick=\"threeQLoadClips(". ($this->offset + $this->limit) .", ".$this->projectId.", ".$this->catId.", '".$this->orderBy."', '".$this->period."')\" title=\"Next\" >&rang;</a></li>";
            $pagerHtml .= "<li><a rel=\"prev\" href=\"#\" onclick=\"threeQLoadClips(". ($totalPages-1) * $this->limit .", ".$this->projectId.", ".$this->catId.", '".$this->orderBy."', '".$this->period."')\" title=\"Last page\" >&rang;&rang;&rang;</a></li>";
        } else {
            $pagerHtml .= "<li class=\"disabled\"><a rel=\"prev\" href=\"#\" onclick=\"threeQLoadClips(". ($this->offset + $this->limit) .", ".$this->projectId.",  ".$this->catId.", '".$this->orderBy."', '".$this->period."')\ title=\"Next\" >&rang;</a></li>";
            $pagerHtml .= "<li class=\"disabled\"><a rel=\"prev\" href=\"#\" onclick=\"threeQLoadClips(". ($totalPages-1) * $this->limit .", ".$this->projectId.",  ".$this->catId.", '".$this->orderBy."', '".$this->period."')\" title=\"Last page\" >&rang;&rang;&rang;</a></li>";
        }
        $pagerHtml .= "</ul>";
        $pagerHtml .= "<div class=\"pagerText\">Page ".$currentPage." of ".$totalPages." (Total number of videos : ".$totalCount." ) </div>";
        $pagerHtml .= "</div>"; // div pagination end
        return $pagerHtml;
    }

    private function formatBytes($size, $precision = 2)
    {
        $base = log($size, 1024);
        $suffixes = array('', 'KB', 'MB', 'GB', 'TB');
        return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[floor($base)];
    }

}