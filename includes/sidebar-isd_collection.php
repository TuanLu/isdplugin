<?php 
//$allPages = getAllDesignPages(); 
const DEFAULT_IMG_URL = 'https://ishopdesign.com/wp-content/themes/ishopdesign/images/logo2.png';
?>
<div id="isd_collection">
  <span class="isd_overlay"></span>
  <div class="isd_collect_content_wrap">
    <span class="isd_close_bt">X</span>
    <h1>Responsive Website Template Gallery</h1>
    <div class="isd_collect_content">
      <div class="isd_collect_left">
        <h2>All categories</h2>
        <ul class="isd_list_cate">
          <li>All Design</li>
          <li>Blog</li>
          <li>Accommodation</li>
          <li>Business & Service</li>
          <li>Fashion & Beauty</li>
          <li>Health</li>
        </ul>
      </div>
      <div class="isd_list_items">
        <h1>Comming Soon!</h1>
<!--
        <ul>
          <?php foreach($allPages as $page) : ?>
          <li>
            <img src="<?php echo $page['thumbnail'] != "" ? $page['thumbnail'] : DEFAULT_IMG_URL;  ?>" />
            <label> <?php echo $page['title'] ?> </label>
            <a target="_blank" href="<?php echo $page['view'] ?>">Preview</a>
            <a class="isd_use it" href="<?php echo get_bloginfo('url') ?>/design-page/?template-id=<?php echo $page['id'] ?>">Use it</a>
          </li>
          <?php endforeach; ?>
        </ul>
-->
      </div>
    </div>
  </div>
</div>