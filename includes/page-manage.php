<?php my_force_login(); ?>
<?php if(is_super_admin()) : ?>
<?php
$homeId = getHomepageDesignId();
$defaultAssetPath = plugin_dir_url( '' ) . 'ishopdesign/public/';
global $current_user;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
  <head>
      <meta charset="<?php bloginfo( 'charset' ); ?>">
      <meta name="viewport" content="width=device-width">
      <link rel="profile" href="http://gmpg.org/xfn/11">
      <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">

      <link href="<?php echo $defaultAssetPath ?>css/normalize.css" rel="stylesheet">
      <link href="<?php echo $defaultAssetPath ?>css/semantic/semantic.min.css" rel="stylesheet">
      <link href="<?php echo $defaultAssetPath ?>css/semantic/isd_semantic.css" rel="stylesheet">
      <link href="<?php echo $defaultAssetPath ?>css/elusive-icons.min.css" rel="stylesheet">
      <link href="<?php echo $defaultAssetPath ?>css/style.css" rel="stylesheet">
      <link href="<?php echo $defaultAssetPath ?>css/google-fonts.css" rel="stylesheet">
      <link rel="stylesheet" href="<?php echo $defaultAssetPath ?>styles/isd_manage.css" media="screen">
      <title>iShop Design Dashboard</title>
  </head>
  <body>
    <div id="isd_page" class="hfeed site">
      <div id="isd_header">
        <div class="isd_wrapper ui grid padded">
          <div class="four wide column">
            <a class="logo" href="<?php echo get_home_url(); ?>">
              <i class="home icon huge"></i>
            </a>
          </div>
          <div class="eight wide column center aligned">
            <?php
            $result = array();
            $result = checkAppUpdate();
            if(isset($result['message'])) {
              ?>
              <div class="isd-version-info">
                <label><?php echo $result['message'] ?></label>
                <div class="button ui blue inverted" id="isd_update_btn">Update Now</div>
              </div>
              <?php
            }
            ?>
          </div>
          <div class="four wide column right aligned">
            <div class="welcome-user">
              <span>Welcome <b><?php echo $current_user->user_login ?></b></span>, 
              <a class="login_button" href="<?php echo wp_logout_url( home_url() ) ?>">Logout</a>
            </div>
          </div>
        </div>
      </div>
      <div id="isd_content" class="isd_site_content">
        <div id="isd_page_content">
          <input type="hidden" id="isd_blog_url" value="<?php echo get_bloginfo('url'); ?>" />
          <div class="isd_wrapper isd_manage ui grid">
              <div class="two wide column isd_sidebar_left">
                  <div class="ui vertical accordion">
                    <div class="active title">
                     <a href="<?php echo getManagePageUrl() ?>">
                      <i class="dashboard icon"></i>
                        Dashboard
                      </a>
                    </div>
                    <div class="content isd_blank_content"></div>
                  </div>
              </div>
              <div class="isd_maincontent fourteen wide column">
                  <?php if(!isset($_GET['section'])) : ?>
                  <div class="ui grid">
                    <div class="eight wide column">
                      <h2 class="header">DASHBOARD</h2>
                    </div>
                    <div class="eight wide column right aligned">
                      <a class="button icon ui teal" href="<?php echo getDesignPageUrl() ?>">
                        <i class="plus icon"></i> Create new page
                      </a>
                    </div>
                  </div>
                  <div class="isd_page_content">
                    <div class="isd_list_page">
                        <?php
                          $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
                          $args = array(
                            //'author' => $current_user->ID,
                            'sort_order' => 'asc',
                            'post_type'  => 'isd',
                            'posts_per_page' => '10',
                            'paged' => $paged,
                          );
                          if(!is_super_admin()) {
                            $args['author'] = $current_user->ID;
                          }
                          $query = new WP_Query($args);
                         
                        if( $query->have_posts() ) {
                            ?>
                            <ul class="ui grid">
                            <li class="row">
                                <span class="two wide column">Image</span>
                                <span class=" five wide column">Title</span>
                                <span class=" two wide column">Created Date</span>                                
                                <span class=" two wide column">Homepage</span>
                                <?php if(defined('ISD_LANDING')) : ?>
                                <span class=" two wide column">Template</span>
                                <?php else: ?>
                                <span class=" two wide column">Status</span>
                                <?php endif; ?>
                                <span class=" three wide column">Actions</span>
                            </li>
                            <?php
                            while( $query->have_posts() ): $query->the_post();
                              $img = get_the_post_thumbnail( );
                              if(!$img){
                                  $img = '<img src="https://ishopdesign.com/wp-content/themes/ishopdesign/images/logo2.png" width="120" />';
                              }
                              ?>
                                <li class="row">
                                    <span class="isd_img_col two wide column"><?php echo $img; ?>&nbsp;</span>
                                    <span class="five wide column"><?php echo the_title(); ?></span>
                                    <span class="two wide column">
                                      <?php echo get_the_date('d/m/Y') ?>
                                      <?php 
                                      if(defined('ISD_LANDING')) {
                                        ?>
                                        <div class="ui label">
                                          <i class="user icon"></i> <?php echo get_the_author() ?>
                                        </div>
                                        <?php
                                      }
                                      ?>
                                    </span>
                                    <span class="two wide column">
                                      <div class="ui checkbox toggle isd_pagehome">
                                        <input type="radio" <?php echo ($homeId == get_the_ID()) ? 'checked' : ''; ?> value="<?php echo get_the_ID() ?>" />
                                        <label></label>
                                      </div>
                                    </span>
                                    <?php if(defined('ISD_LANDING')) : ?>
                                    <span class="two wide column">
                                      <div class="ui checkbox toggle isd_set_template">
                                        <?php $isTemplate = get_post_meta($post->ID, ISD_IS_TEMPLATE); ?>
                                        <input type="radio" <?php echo (!empty($isTemplate) && $isTemplate[0] == 1) ? 'checked' : ''; ?> value="<?php echo $post->ID ?>" />
                                        <label></label>
                                      </div>
                                    </span>
                                    <?php else: ?>
                                    <span class="two wide column"><?php echo get_post_status() ?></span>
                                    <?php endif; ?>
                                    <span class="three wide column">
                                      <div class="buttons ui tiny">
                                        <a class="mini primary ui icon button" target="_blank" href="<?php echo get_permalink(); ?>">
                                          <i class="icon eye"></i>
                                        </a>
                                        <a href="<?php echo getDesignPageUrl('url') ?>/?id=<?php echo get_the_ID() ?>" target="_blank" class="mini ui button icon teal">
                                          <i class="icon edit"></i>
                                        </a>
<!--
                                        <a title="Duplicate Page" href="<?php echo get_bloginfo('url') ?>/?isd-duplicate=<?php echo get_the_ID() ?>" class="mini ui button icon blue">
                                          <i class="icon copy"></i>
                                        </a>
-->
                                        <button rel="<?php echo get_the_ID() ?>" class="mini ui blue button icon duplicate_isd_page">
                                          <i class="icon copy"></i>
                                        </button>
                                         <!-- <button rel="<?php echo get_the_ID() ?>" class="mini ui teal button icon download_isd_page">
                                          <i class="icon download"></i>
                                        </button> -->
                                        <button rel="<?php echo get_the_ID() ?>" class="mini ui orange button icon delete_isd_page">
                                          <i class="icon trash"></i>
                                        </button>
                                      </div>
                                    </span>
                                </li>
                                <?php
                            endwhile;
                            ?>
                            </ul>
                            <div class="isd_design_pagination ui grid">
                              <?php kriesi_pagination($query->max_num_pages);?>
                            </div>
                            <?php
                            wp_reset_postdata();
                        } else {
                          ?>
                          <div class="ui message info">
                            You have no design yet. Please using iShopDesign Tool to create your own page now!
                          </div>
                          <?php
                        }
                        //wp_reset_query();
                        ?>
                    </div>
                  </div>
                  <?php endif; ?>
                  <?php if(getLocalAppVersion()) : ?>
                    <div class="version segment" style="text-align:center;clear:both;margin-top: 20px;">
                      iShopDesign version <span class="version-number"><?php echo getLocalAppVersion() ?></span>
                    </div>
                  <?php endif; ?>
              </div>
              <?php //get_sidebar('isd_modules') ?>
          </div>
        </div>
      </div>
    </div>
    <?php include_once('sidebar-isd_collection.php') ?>
    <script type="text/javascript" src="<?php echo $defaultAssetPath ?>js/jquery.min.js"></script>
    <script src="<?php echo $defaultAssetPath ?>js/semantic.min.js"></script>
    <script type="text/javascript" src="<?php echo $defaultAssetPath ?>js/custom.js"></script>
    <script type="text/javascript" src="<?php echo $defaultAssetPath ?>js/isd_backend.js"></script>
  </body>
</html>
<?php endif; ?>
<?php if(is_user_logged_in() && !is_super_admin()) : ?>
  <?php 
  if(file_exists(get_template_directory() . '/page-users.php')) {
    include(get_template_directory() . '/page-users.php');
  }
  ?>
<?php endif; ?>