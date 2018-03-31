<?php
$serverName = $_SERVER['SERVER_NAME'];
$defaultAssetPath = plugin_dir_url( dirname( __FILE__ ) ) . '/public/';
$baseAssetPath = $defaultAssetPath;
$postId = 0;
$title = "iShopDesign landing page builder";
$description = "Thiết kế landing page miễn phí, dễ dàng và nhanh chóng với công cụ iShopDesign. Phục vụ các chiến dịch marketing và quảng cáo online.";
$keywords = "ishopdesign website builder,ishopdesign landing page builder, best website builder, responsive website builder, landing page builder";
$thumbnail = $baseAssetPath . "images/logo300x300.png";
if(get_the_post_thumbnail_url() != "") {
  $thumbnail = get_the_post_thumbnail_url();
}

$design_value = '';
$currentAction = '';
if($_GET && isset($_GET['action'])) {
  $currentAction = $_GET['action'];
}
if(is_front_page()) {
  $postId = getHomepageDesignId();
} else {
  if(get_the_ID()) {
    $postId = get_the_ID(); 
  }
}
$externalFiles = array();
if($postId) {
  //Get design info
  $design_value = getDesignJson($postId);
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
    if(isset($design_value_array['main']['design_info']['thumbnail']) 
    && $design_value_array['main']['design_info']['thumbnail'] != "") {
      $thumbnail = $design_value_array['main']['design_info']['thumbnail'];
    }
  }
  if(isset($design_value_array['externalResource']['files']) && !empty($design_value_array['externalResource']['files'])) {
    $externalFiles = $design_value_array['externalResource']['files'];
  }
}
//Get custom font 
$customFontInfor = getCustomFonts($postId);
?>
<html>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta name="keywords" content="<?php echo $keywords ?>">
    <meta name="description" content="<?php echo $description ?>">
    <title><?php echo $title ?></title>
    <!-- Facebook Format-->
    <meta property="og:url"           content="<?php echo get_permalink() ?>" />
    <meta property="og:type"          content="article" />
    <meta property="og:title"         content="<?php echo $title ?>" />
    <meta property="og:description"   content="<?php echo $description  ?>" />
    <meta property="og:image"         content="<?php echo $thumbnail ?>" />
    <!-- End Facebook Format -->
    <link href="<?php echo $baseAssetPath ?>/css/semantic/semantic.min.css" rel="stylesheet">
    <link href="<?php echo $baseAssetPath ?>/css/semantic/isd_semantic.css" rel="stylesheet">
    <link href="<?php echo $baseAssetPath ?>/css/google-fonts.css" rel="stylesheet">
    <link href="<?php echo $baseAssetPath ?>/css/isd_animate.css" rel="stylesheet">
    <link href="<?php echo $baseAssetPath ?>/styles/isd_theme_style.css" rel="stylesheet">
    <style>
      .isd_object_item.isd_text  p {
        margin: 0;
        line-height: inherit;
      }
    </style>
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
  </head>
  <body>
    <?php if($currentAction == "preview") : ?>
    <a class="ui button compact icon teal" href="<?php echo getDesignPageUrl() ?>?id=<?php echo $postId ?>" style="position: fixed; right: 10;top:10;z-index:10000">
      <i class="icon edit"></i>
      Edit Page
    </a>
    <?php else : ?>
      <?php if((isSiteAdmin() || (is_front_page() && $postId == 0)) && !defined('ISD_LANDING')) : ?>
      <a class="ui button compact icon teal" href="<?php echo getManagePageUrl() ?>" style="position: fixed; right: 10;top:10;z-index:10000">
        <i class="icon dashboard"></i>
        Manage Your Pages
      </a>
      <?php endif; ?>
    <?php endif; ?>
    <input type="hidden" id="isd_plugins" value="<?php echo htmlentities(getPlugins(), ENT_COMPAT,'UTF-8') ?>"/>
    <?php if(isdWooAPIReady()) :  ?>
    <input type="hidden" id="wc_placeholder_img_src" value="<?php echo wc_placeholder_img_src() ?>" />
    <input type="hidden" id="isd_currency_symbol" value="<?php echo get_woocommerce_currency_symbol() ?>" />
    <?php endif; ?>
    <input type="hidden" value="<?php echo admin_url( "admin-ajax.php" ) ?>" id="isd_auth_ajax" />
    <input type="hidden" id="isd_base_img_url" value="<?php echo get_template_directory_uri() ?>/isd_app" />
    <input type="hidden" id="isd_platform" value="wordpress" />
    <input type="hidden" id="isd_blog_url" value="<?php echo get_bloginfo('url'); ?>" />
    <input type="hidden" id="isd_post_id" value="<?php echo $postId ?>" />
    <div id="isd_app" class=""></div>
    <input type="hidden" id="isd_import_json" value="<?php echo htmlentities($design_value, ENT_COMPAT,'UTF-8') ?>" />
    <?php if($customFontInfor['fonts'] != '') : ?>
    <input type="hidden" id="isd_custom_font" value="<?php echo htmlentities($customFontInfor['fonts'], ENT_COMPAT,'UTF-8') ?>"/>
    <?php endif; ?>
    <script src="<?php echo $defaultAssetPath ?>/js/jquery.min.js"></script>
    <script src="<?php echo $defaultAssetPath ?>/js/form.min.js"></script>
    <?php echo getExternalScript($externalFiles) ?>
    <script src="<?php echo $defaultAssetPath ?>/js/ishopdesign.viewonly.min.js"></script>
    <!--Debug Section-->
    <?php if(isset($_GET['debug'])) : ?>
    <div type="hidden" id="debug_result">Debug Result Will Show Here</div>
    <?php endif; ?>
  </body>
</html>
