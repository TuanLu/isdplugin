<?php
/**
* Asset path for developer purpose
* ---------------------------------
* http://localhost:8080/public //==> Webpack Dev Server
* http://localhost/wordpress/wp-content/themes/isd //==> Wordpress Server
* https://ishopdesign-469a2.firebaseapp.com //==> Firebase Cloud Server
**/


if(defined('ISD_LANDING') && !is_user_logged_in()) {
  redirectToHomePage();
  exit();
} else {
  if(!defined('ISD_TRIAL_PAGE')) {
    my_force_login(); 
  } 
}

$serverName = $_SERVER['SERVER_NAME'];
$defaultAssetPath = plugin_dir_url( dirname( __FILE__ ) ) . '/public/';
$baseAssetPath = $defaultAssetPath;
switch($serverName) {
  case 'localhost':
    //If webpack server is running, then reference all files from there
    $devServerContent = @file_get_contents('http://localhost:8080/public/index.html');
    if($devServerContent !== false) {
      $jsBasePath = 'http://localhost:8080/public';
      $cssBasePath = 'http://localhost:8080/public/styles/';
    } else {
      $jsBasePath = $defaultAssetPath . '/js';  
      $cssBasePath = $defaultAssetPath . '/styles/';
    }
    break;
  default:
    $jsBasePath = $defaultAssetPath . '/js';
    $cssBasePath = $defaultAssetPath . '/styles/';
    break;
}
/** End check asset path for developer purpose **/
global $current_user;
get_currentuserinfo();
$design_value = $current_post_user_id = '';
$title = "iShop Design Website Builder";
$description = "iShop Design - A web app that helps customers freely build an amazing website, wireframes, mockups for all devices in mere minutes without code knowledge.";
$keywords = "ishop design website builder,create a stunning website, website builder, build your own website, web design tool, drag and drop website builder, create a landing page, effective landing page, ecommerce landing page, best website builder, responsive website builder, landing page builder";

$postId = 0;
$templateId = 0;
$previewUrl = '';
if(is_front_page()) {
  $postId = getHomepageDesignId();
}
if(isset($_GET['id']) && $_GET['id']) {
  $postId = $_GET['id'];
}
if(isset($_GET['template-id']) && $_GET['template-id']) {
  $templateId = $_GET['template-id'];
}
if(defined('ISD_TRIAL_PAGE')) {
  $postId = ISD_TRIAL_PAGE;
}
if($postId || $templateId) {
  $designId = $postId;
  if($templateId) {
    $designId = $templateId;
  }
  $post = get_post($designId);
  if($post && $post->post_author != $current_user->ID 
     && !is_super_admin() 
     && !isset($_GET['template-id'])
     && !defined('ISD_TRIAL_PAGE')) {
    echo __("You don't have permisstion to edit this post. Click <a href='". get_bloginfo('url') ."'>here</a> to continue!");
    exit(); 
  }
  //Get design info
  $design_value = getDesignJson($designId);
  $design_value_array = json_decode($design_value, true);
  if(isset($design_value_array['main']['design_info'])) {
    if(isset($design_value_array['main']['design_info']['title']) 
       && $design_value_array['main']['design_info']['title'] != "") {
      $title = $design_value_array['main']['design_info']['title'];
    }
    if(isset($design_value_array['main']['design_info']['description']) 
       && $design_value_array['main']['design_info']['description'] != "") {
      $description = $design_value_array['main']['design_info']['description'];
    }
    if(isset($design_value_array['main']['design_info']['keywords']) 
    && $design_value_array['main']['design_info']['keywords'] != "") {
      $keywords = $design_value_array['main']['design_info']['keywords'];
    }
  }
  $previewUrl = getPreviewUrl($designId);
} else {
  $design_value = getDesignJson($postId);
}
//Get custom font 
$customFontInfor = getCustomFonts($postId);
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width">
    <meta name="keywords" content="<?php echo $keywords ?>">
    <meta name="description" content="<?php echo $description ?>">
    <title><?php echo $title ?></title>
    <link href="<?php echo $baseAssetPath ?>css/normalize.css" rel="stylesheet">
    <link href="<?php echo $baseAssetPath ?>css/semantic/semantic.min.css" rel="stylesheet">
    <link href="<?php echo $baseAssetPath ?>css/semantic/isd_semantic.css" rel="stylesheet">
    <link href="<?php echo $baseAssetPath ?>css/elusive-icons.min.css" rel="stylesheet">
    <link href="<?php echo $baseAssetPath ?>css/style.css" rel="stylesheet">
    <link href="<?php echo $baseAssetPath ?>css/google-fonts.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $cssBasePath ?>isd_styles.css" media="screen">
    <link rel="stylesheet" href="<?php echo $cssBasePath ?>isd_theme_style.css" media="screen">
    <link href="<?php echo $baseAssetPath ?>css/isd_animate.css" rel="stylesheet">
    <?php if(defined('ISD_LANDING')) : ?>
    <style>
      #header_section, #footer_section {
        display: none!important;
      }
    </style>
    <?php endif; ?>
    <?php if($customFontInfor['fontFace'] != '') : ?>
      <?php echo '<style>' . $customFontInfor['fontFace'] . '</style>' ?>
    <?php endif; ?>
    <style>
    #isd_app {
      display: none;
    }
    .loading_wrap {
      width: 200px;
      height: 200px;
      margin: auto;
      position: fixed;
      top: calc(50% - 100px);
      left: calc(50% - 100px);
    }
    .loading_wrap .ui.dimmer {
      background: #03A9F4;
    }
    </style>
  </head>
  <body>
    <div id="isd_app"></div>
    <div class="ui segment loading_wrap">
      <div class="ui active dimmer">
        <div class="ui massive text loader">Loading</div>
      </div>
    </div>
    <input type="hidden" id="isd_user_id" value="<?php echo $current_user->ID ?>" />
    <input type="hidden" id="isd_edit_mode" value="yes" />
    <input type="hidden" id="isd_plugins" value="<?php echo htmlentities(getPlugins(), ENT_COMPAT,'UTF-8') ?>"/>
    <input type="hidden" id="isd_post_id" value="<?php echo $postId ?>" />
    <input type="hidden" id="isd_template_id" value="<?php echo $templateId ?>" />
    <?php if(isdWooAPIReady()) :  ?>
    <input type="hidden" id="isd_currency_symbol" value="<?php echo get_woocommerce_currency_symbol() ?>" />
    <input type="hidden" id="wc_placeholder_img_src" value="<?php echo wc_placeholder_img_src() ?>" />
    <?php endif; ?>
    <input type="hidden" id="isd_base_dir" value="<?php echo get_template_directory() ?>" />
    <input type="hidden" id="isd_platform" value="wordpress" />
    <input type="hidden" id="isd_base_url" value="<?php echo $baseAssetPath ?>/" />
    <input type="hidden" id="isd_blog_url" value="<?php echo get_bloginfo('url'); ?>" />
    <input type="hidden" id="isd_preview_url" value="<?php echo $previewUrl ?>"/>
    <input type="hidden" value="<?php echo admin_url( "admin-ajax.php" ) ?>" id="isd_auth_ajax" />
    <?php if($customFontInfor['fonts'] != '') : ?>
    <input type="hidden" id="isd_custom_font" value="<?php echo htmlentities($customFontInfor['fonts'], ENT_COMPAT,'UTF-8') ?>"/>
    <?php endif; ?>
    <script>
      let isdTrial =  '';
      <?php if(defined('ISD_TRIAL_PAGE')): ?>
      isdTrial =  <?php echo ISD_TRIAL_PAGE ?>;
      <?php endif; ?>
    </script>
    <script src="<?php echo $jsBasePath ?>/ishopdesign.min.js"></script>
    <script src="<?php echo $baseAssetPath ?>js/semantic.min.js"></script>
    
  </body>
</html>
