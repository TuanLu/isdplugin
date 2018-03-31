<?php
const ISD_CURRENT_VERSION = "1.4.4";
/**
 * All header or footer design json
 **/
 const ISD_HEADER_IDS_KEY = 'ISD_HEADER_IDS_KEY';
 const ISD_FOOTER_IDS_KEY = 'ISD_FOOTER_IDS_KEY';
 const ISD_JSON_KEY = '_isd_json';
 const ISD_IS_TEMPLATE = '_isd_is_template';
 const ISD_HOMEPAGE_OPTION = 'isd_homepage_design';
 const ISD_POST_TYPE = 'isd';
 const ISD_CONTAINER_TEMPLATE_TYPE = 'container_template';
 const ISD_CONTACT_FORM_TYPE = 'isd_contact_type';
 const ISD_APP_VERSION_OPTION_KEY = 'ISD_APP_VERSION';
 const ISD_HEADER_KEY = 'ISD_HEADER_CONTAINER';
 const ISD_FOOTER_KEY = 'ISD_FOOTER_CONTAINER';
 const ISD_CONTAINER_TEMPLATE_SLUG = 'container-templates';
 const ISD_CUSTOM_FONT_KEY = '_isd_fonts';
//Fix wp_ob_end() warning issue
remove_action( 'shutdown', 'wp_ob_end_flush_all', 1 );
/**
* Upload File Handle
**/
const MAX_UPLOAD_FILESIZE = 5000000;//5M
function isd_upload_file_handle() {
  $response = array(
    'status' => 'error',
    'message' => 'Something went wrong! Can not upload file to server!'
  );
  //Directory of image. Magento will different with Wordpress
  $target_dir = get_template_directory() . '/isd_app/media/upload/';
  $postId = $_POST['post_id'] ? $_POST['post_id'] : 0 ;
  $filename = basename($_FILES["filename"]["name"]);
  //if($postId) {
    //Handle in wordpress 
    $attach_id = media_handle_upload('filename', $postId);
    if($attach_id) {
      $response['status'] = 'success';
      $response['message'] = 'Image have been successfully uploaded!';
      $response['data'] = array(
        'thumbnail' => wp_get_attachment_thumb_url( $attach_id),
        'url' => wp_get_attachment_url( $attach_id),
        'thumbnailId' => $attach_id
      );
    }
  //}
  //Try to upload image without wordpress function
  if($response['status'] == 'error') {
    $target_file = $target_dir . $filename;
    $uploadOk = 1;
    $imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
    // Check if image file is a actual image or fake image
    if(isset($_POST["submit"])) {
        $check = getimagesize($_FILES["filename"]["tmp_name"]);
        if($check !== false) {
            //echo "File is an image - " . $check["mime"] . ".";
            $uploadOk = 1;
        } else {
            $response['message'] = "File is not an image.";
            $uploadOk = 0;
        }
    }
    // Check if file already exists
    if (file_exists($target_file)) {
        //$response['message'] = "Sorry, file already exists.";
        $filename = time() . '_' . $filename;
        $target_file = $target_dir . $filename;
        //If file already exists then rename the file and allow upload normally
        $uploadOk = 1;
    }
    // Check file size
    if ($_FILES["filename"]["size"] > MAX_UPLOAD_FILESIZE) {
        $response['message'] = "Sorry, your file is too large.";
        $uploadOk = 0;
    }
    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
    && $imageFileType != "gif" ) {
        $response['message'] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }
    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 1) {
      if (move_uploaded_file($_FILES["filename"]["tmp_name"], $target_file)) {
          //Create thumbnail for this image
          $image = wp_get_image_editor( $target_dir . $filename );
          if ( ! is_wp_error( $image ) ) {
            $image->resize( 150, 150, true );
            $image->save( $target_dir . 'resized-' . $filename  );
          }
          $response['status'] = 'success';
          $response['message'] = "The file ". $filename. " has been uploaded.";
          $mediaBaseUrl = get_template_directory_uri() . '/isd_app/media/upload/';
          $response['data'] = array(
            'url' => $mediaBaseUrl . $filename,
            'thumbnail' => $mediaBaseUrl . 'resized-' .  $filename,
          );
      } else {
          $response['message'] = "Sorry, there was an error uploading your file.";
      }
    }
  }
  echo json_encode($response);
  exit;
}
add_action('wp_ajax_isd_upload_image', 'isd_upload_file_handle');
add_action('wp_ajax_nopriv_isd_upload_image', 'isd_upload_file_handle');

/**
* Import JSON File Handle
* Not using this function yet. Using FileReader of javascript now
**/
function isd_import_json_handle() {
  $response = array(
    'status' => 'error',
    'message' => 'Something went wrong! Can not upload file to server!'
  );
  try {
    // Undefined | Multiple Files | $_FILES Corruption Attack
    // If this request falls under any of them, treat it invalid.
    if (
        !isset($_FILES['filename']['error']) ||
        is_array($_FILES['filename']['error'])
    ) {
        throw new RuntimeException('Invalid parameters.');
    }

    // Check $_FILES['upfile']['error'] value.
    switch ($_FILES['filename']['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            //throw new RuntimeException('No file sent.');
        $response['message'] = 'No file sent.';
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            //throw new RuntimeException('Exceeded filesize limit.');
          $response['message'] = 'Exceeded filesize limit.';
        default:
            //throw new RuntimeException('Unknown errors.');
            $response['message'] = 'Unknown errors.';
    }

    // You should also check filesize here. 
    if ($_FILES['filename']['size'] > MAX_UPLOAD_FILESIZE) {
        //throw new RuntimeException('Exceeded filesize limit.');
      $response['message'] = 'Exceeded filesize limit.';
    }

    // DO NOT TRUST $_FILES['upfile']['mime'] VALUE !!
    // Check MIME Type by yourself.
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    if (false === $ext = array_search(
        $finfo->file($_FILES['filename']['tmp_name']),
        array(
            'txt' => 'text/plain',
        ),
        true
    )) {
        //throw new RuntimeException('Invalid file format.');
      $response['message'] = 'Invalid file format.';
    }

    $content = file_get_contents($_FILES['filename']['tmp_name']);
    if($content) {
      $response['status'] = 'success';
      $response['message'] = 'Read file successfully!';
      $response['data'] = array(
        'url' => $content
      );
    }
  } catch (RuntimeException $e) {
      $response['message'] = $e->getMessage();
  }
  
  echo json_encode($response);
  exit;
}
add_action('wp_ajax_isd_upload_json', 'isd_import_json_handle');
add_action('wp_ajax_nopriv_isd_upload_json', 'isd_import_json_handle');

/**
 * Check valid json or not
 */
function isValidJson($string) {
  $validJson = json_decode($string, true);
  if(is_array($validJson)) {
    return true;
  }
  return false;
}
/**
 * Valid lastest save design
 */
function checkDesignJson($postId) {
  if($postId) {
    //Get all metadata of this post
    $designInfos = get_post_meta($postId, ISD_JSON_KEY);
    if(!empty($designInfos)) {
      //Get lastest save value
      $design_value = $designInfos[count($designInfos) - 1];
      return isValidJson($design_value);
    } else {
      $post = get_post($postId);
      if($post->ID) {
        return isValidJson($post->post_excerpt); 
      }
    } 
  }
  return false;
}
/**
 * Get design json
 */
function getDesignJson($postId) {
  $designString = '';
  //Get design content
  if($postId) {
    //Get all metadata of this post
    $designInfos = get_post_meta($postId, ISD_JSON_KEY);
    if(!empty($designInfos)) {
      //Get lastest save and valid json option
      for($i = count($designInfos) - 1; $i >= 0; $i--) {
        if(isValidJson($designInfos[$i])) {
          $designString = $designInfos[$i];
          break;
        }
      }
    } else {
      $post = get_post($postId);
      if($post && $post->ID) {
        if(isValidJson($post->post_excerpt)) {
          $designString = $post->post_excerpt;
        }
      }
    } 
  }
  $designStringArr = $headerArr = $footerArr = array();
  $merge_props = array('containers', 'rows', 'columns', 'elements', 'cssMap');
  //Get Header Content
  $headerContent = getLatestExtraData(ISD_HEADER_IDS_KEY);
  if(!$headerContent || $headerContent == '') {
    //Try to get header data from old version
    $headerContent = get_option(ISD_HEADER_KEY);
  }
  if($headerContent != "") {
    $headerArr = json_decode($headerContent, true);
  }
  //Get Footer Content
  $footerContent = getLatestExtraData(ISD_FOOTER_IDS_KEY);
  if(!$footerContent || $footerContent == '') {
    //Try to get header data from old version
    $footerContent = get_option(ISD_FOOTER_KEY);
  }
  if($footerContent !== '') {
    $footerArr = json_decode($footerContent, true);
  }
  if($designString != '') {
    try {
      $designStringArr = json_decode($designString, true);
      //Merge header
      if(!empty($designStringArr) && !empty($headerArr)) {
        foreach($merge_props as $prop) {
          if(isset($designStringArr['main'][$prop]) && isset($headerArr[$prop])) {
            $designStringArr['main'][$prop] = array_merge($designStringArr['main'][$prop], $headerArr[$prop]);
          }
        }  
      }
      // Merge footer
      if(!empty($designStringArr) && !empty($footerArr)) {
        foreach($merge_props as $prop) {
          if(isset($designStringArr['main'][$prop]) && isset($footerArr[$prop])) {
            $designStringArr['main'][$prop] = array_merge($designStringArr['main'][$prop], $footerArr[$prop]);
          }
        } 
      }
    } catch(Exception $e) {
      
    }
  } else {
    //Try to get header or footer container
    if(!empty($headerArr)) {
      $designStringArr['main'] = $headerArr;
    }
    if(!empty($footerArr)) {
      foreach($merge_props as $prop) {
        if(isset($designStringArr['main'][$prop]) && isset($footerArr[$prop])) {
          $designStringArr['main'][$prop] = array_merge($designStringArr['main'][$prop], $footerArr[$prop]);
        }
      }
    }
    // Add design screen width info to json if postID = 0
    if(!isset($designStringArr['screenWidth'])) {
      if(isset($headerArr['screenWidth'])) {
        $designStringArr['screenWidth'] = $headerArr['screenWidth'];
      } else {
        if(isset($footerArr['screenWidth'])) {
          $designStringArr['screenWidth'] = $headefooterArrrArr['screenWidth'];
        }
      }
    }
  }
  if(!empty($designStringArr)) {
    $designStringArr = attachProductListToJson($designStringArr);
    $designStringArr = attachServerDataToJson($designStringArr);
    return json_encode($designStringArr);  
  }
}
/**
 * Handle save design
 **/

function isd_updatedesign() {
  //die('update save function');
    // Handle request then generate response using WP_Ajax_Response
    $response = array(
      'status' => 'error',
      'message' => __('Something went wrong! Can not save this design to server!')
    );
    //$postString = file_get_contents("php://input");
    $postString = $_POST['wpData'];
    try {
      if($postString != "") {
        $postStringDecode = json_decode(stripslashes($postString), true);
        if(isset($postStringDecode['isdTrial']) && $postStringDecode['isdTrial'] != "") {
          $response = array(
            'status' => 'error',
            'message' => __('Bạn đang dùng phiên bản dùng thử của iShopDesign! Tính năng lưu chưa được kích hoạt!')
          );
          echo json_encode($response);
          wp_die();
        }
        //Save from category, product or special page
        if(isset($postStringDecode['isd_page']) && $postStringDecode['isd_page'] != "") {
          $response['status']  	= 'success';
          $response['message']  = 'Your design has been updated';
          $response['isd_page']  = $postStringDecode['isd_page'];
        } else {
          if(!empty($postStringDecode) && isset($postStringDecode['postId']) && $postStringDecode['postId'] == 0){
            $new_post = array(
              //'ID'           => $_POST['pid'],
              'post_type'		 => ISD_POST_TYPE,
              'post_title'   => $postStringDecode['main']['design_info']['title'],
              'post_content'   => $postStringDecode['main']['design_info']['page_type'],
              //'post_excerpt' => json_encode($postStringDecode, JSON_UNESCAPED_UNICODE),
              'post_status'	 => 'publish',
            );
            if(isset($postStringDecode['main']['design_info']['page_type'])) {
              $new_post['post_content'] = $postStringDecode['main']['design_info']['page_type'];
            }
            // Update the post into the database
            $pid = wp_insert_post($new_post);
            if($pid!=0){
              $newPostData = json_encode($postStringDecode, JSON_UNESCAPED_UNICODE);
              //Add design json to meta data even on first time, skip post_excerpt
              $result = add_post_meta($pid, ISD_JSON_KEY, wp_slash($newPostData));
              if($result) {
                if(isset($postStringDecode['main']['design_info']['thumbnailId'])) {
                  set_post_thumbnail($pid, $postStringDecode['main']['design_info']['thumbnailId']);
                }
                $response['status']  	= 'success';
                $response['message']  = 'Your design has been saved';
                $response['postId']  	= $pid;
                $response['postUrl'] = get_post_permalink($response['postId']);
              }
            }else{
              $response['message']  = 'Something went wrong. The design have not been saved yet!';
            }
          } else {
            //Update title of design only
            $update_post_data = array(
              'ID'           => $postStringDecode['postId'],
              'post_title'   => $postStringDecode['main']['design_info']['title'],
            );
            if(isset($postStringDecode['main']['design_info']['thumbnailId'])) {
              set_post_thumbnail($postStringDecode['postId'], $postStringDecode['main']['design_info']['thumbnailId']);
            }
            if(isset($postStringDecode['main']['design_info']['page_type'])) {
              $update_post_data['post_content'] = $postStringDecode['main']['design_info']['page_type'];
            }
            $result = wp_update_post( $update_post_data );
            //Add update data to metadata
            
            if(isset($postStringDecode['postId']) && $postStringDecode['postId'] != "") {
              $newPostData = json_encode($postStringDecode, JSON_UNESCAPED_UNICODE);
              // echo wp_slash($newPostData);
              // die;
              $result = add_post_meta($postStringDecode['postId'], ISD_JSON_KEY, wp_slash($newPostData));  
              if($result!=0) {
                //Delete old design to save space of database
                cleanISDPostMeta($postStringDecode['postId']);
                //Check real json stored in database
                $isValid = checkDesignJson($postStringDecode['postId']);
                if($isValid) {
                  $response['status']  = 'success';
                  $response['message']  = 'Your design has been updated.';
                  $response['postId']  	= $postStringDecode['postId'];
                  $response['postUrl'] = get_post_permalink($response['postId']);
                } else {
                  $response['status']  = 'error';
                  $response['message']  = 'The design haved been saved but in invalid json format!';
                }
                
              }else{
                $response['status']  = 'error';
                $response['message']  = 'Something went wrong. The design have not been updated yet!';
              }
            }          
          }
        }
      }
    } catch(Exception $e) {
      //print_r($e->getMessage());
    }
    // Update the post into the database
    //$result = wp_update_post( $my_post );
    echo json_encode($response);
    // Don't forget to stop execution afterward.
    wp_die();
}
add_action('wp_ajax_isd_updatedesign', 'isd_updatedesign');
add_action('wp_ajax_nopriv_isd_updatedesign', 'isd_updatedesign');

/**
 * Handle set a design as homepage
 **/

function isd_sethomepage() {
  // Handle request then generate response using WP_Ajax_Response
  $response = array(
    'status' => 'error',
    'message' => __('Something went wrong! Can not set this design as homepage!')
  );
  $designID = $_POST['designID'];
  try {
    if($designID) {
      $currentHomepageId = get_option(ISD_HOMEPAGE_OPTION);
      if($currentHomepageId !== false) {
        $result = update_option(ISD_HOMEPAGE_OPTION, $designID);
      } else {
        $result = add_option(ISD_HOMEPAGE_OPTION, $designID);
      }
      if($result) {
        $homepageId = get_option(ISD_HOMEPAGE_OPTION);
        $response['status'] = 'success';
        $response['message'] = 'Well done. The homepage have been updated!';
        $response['homepageID'] = $homepageId;
      }
    }
  } catch(Exception $e) {
    //print_r($e->getMessage());
  }
  // Update the post into the database
  //$result = wp_update_post( $my_post );
  echo json_encode($response);
  // Don't forget to stop execution afterward.
  wp_die();
}
add_action('wp_ajax_isd_sethomepage', 'isd_sethomepage');
add_action('wp_ajax_nopriv_isd_sethomepage', 'isd_sethomepage');
/**
 * Handle duplicate page using ajax. Fix plugin can't duplicate page
 **/

function isd_duplicate_page() {
  // Handle request then generate response using WP_Ajax_Response
  $response = array(
    'status' => 'error',
    'message' => __('Something went wrong! Can not set this design as homepage!')
  );
  $designID = $_POST['designID'];
  try {
    if($designID) {
      $designString = '';
      $designInfos = get_post_meta($designID, ISD_JSON_KEY);
      if(!empty($designInfos)) {
        //Get lastest save and valid json option
        for($i = count($designInfos) - 1; $i >= 0; $i--) {
          if(isValidJson($designInfos[$i])) {
            $designString = $designInfos[$i];
            break;
          }
        }
      } else {
        $post = get_post($designID);
        if($post && $post->ID) {
          if(isValidJson($post->post_excerpt)) {
            $designString = $post->post_excerpt;
          }
        }
      } 
      if($designString) {
        $oldpost = get_post($designID);
        $designJsonArr = json_decode($designString, true);
        $newPostTitle = $oldpost->post_title . ' Copy';
        if(isset($designJsonArr['main']['design_info']['title'])) {
          $designJsonArr['main']['design_info']['title'] = $newPostTitle;
        }
        $newDesignJson = json_encode($designJsonArr, JSON_UNESCAPED_UNICODE);
        $new_post = array(
          'post_type'		 => ISD_POST_TYPE,
          'post_title'   => $newPostTitle,
          'post_content'   => $oldpost->post_content,
          'post_excerpt' => 'Duplicate page',//this data field is "text" but not "longtext"
          'post_status'	 => 'publish',
        );
        // Insert the post into the database
        $pid = wp_insert_post($new_post, true);
        if($pid) {
          //Insert meta data for this design
          $newDesignJson = wp_slash($newDesignJson);
          $result = add_post_meta($pid, ISD_JSON_KEY, $newDesignJson);
          //Add font meta if exists 
          $fonts = get_post_meta($designID, ISD_CUSTOM_FONT_KEY);
          if(!empty($fonts)) {
            foreach ($fonts as $font) {
              add_post_meta($pid, ISD_CUSTOM_FONT_KEY, $font);
            }
          }
          $response['status'] = 'success';
          $response['message'] = 'Well done. The page have been duplicated!';
        }

      }
    }
  } catch(Exception $e) {
    //print_r($e->getMessage());
  }
  // Update the post into the database
  //$result = wp_update_post( $my_post );
  echo json_encode($response);
  // Don't forget to stop execution afterward.
  wp_die();
}
add_action('wp_ajax_isd_duplicate_page', 'isd_duplicate_page');
add_action('wp_ajax_nopriv_isd_duplicate_page', 'isd_duplicate_page');
/**
 * Handle set template
 **/

function isd_set_template() {
  // Handle request then generate response using WP_Ajax_Response
  $response = array(
    'status' => 'error',
    'message' => __('Something went wrong! Can not set this design to template collection!')
  );
  $designID = $_POST['designID'];
  try {
    if($designID) {
      $isTemplate = get_post_meta($designID, ISD_IS_TEMPLATE);
      if(!empty($isTemplate)) {
        $newValue = ($isTemplate[0] == 1) ? 0 : 1;
        $result = update_post_meta($designID, ISD_IS_TEMPLATE, $newValue);
        $response['data'] = $newValue;
      } else {
        $result = add_post_meta($designID, ISD_IS_TEMPLATE, 1, true);
      }
      if($result) {
        $response['status'] = 'success';
        $response['message'] = 'Well done. This design had added to template collection';
      }
    }
  } catch(Exception $e) {
    //print_r($e->getMessage());
  }
  // Update the post into the database
  //$result = wp_update_post( $my_post );
  echo json_encode($response);
  // Don't forget to stop execution afterward.
  wp_die();
}
add_action('wp_ajax_isd_set_template', 'isd_set_template');
add_action('wp_ajax_nopriv_isd_set_template', 'isd_set_template');
/**
 * Handle delete design
 **/
function isd_deletedesign() {
  // Handle request then generate response using WP_Ajax_Response
  $response = array(
    'status' => 'error',
    'message' => __('Something went wrong! Can not remove this page!')
  );
  $designID = $_POST['designID'];
  try {
    if($designID) {
      $result = wp_trash_post($designID);
      if($result) {
        $response['status'] = 'success';
        $response['message'] = 'Done. The page have been removed!';
      }
    }
  } catch(Exception $e) {
    //print_r($e->getMessage());
  }
  // Update the post into the database
  //$result = wp_update_post( $my_post );
  echo json_encode($response);
  // Don't forget to stop execution afterward.
  wp_die();
}
add_action('wp_ajax_isd_deletedesign', 'isd_deletedesign');
add_action('wp_ajax_nopriv_isd_deletedesign', 'isd_deletedesign');
/**
 * Handle export design
 **/
function isd_export_design() {
  $target_dir = get_template_directory() . '/isd_app/media/export/';
  $response = array(
    'status' => 'error',
    'message' => __('Something went wrong! Can not export this design!')
  );
  //$postString = file_get_contents("php://input");
  $postString = json_encode($_POST['wpData'], JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT);
  try {
    if($postString != "") {
      //Save this file to server
      if(!file_exists($target_dir)) {
        //Create new folder here
        mkdir($target_dir, 0777, true);
      }
      if(file_exists($target_dir)) {
        $filename = 'isd_export_' . time() . '.txt';
        file_put_contents($target_dir . $filename, $postString);
        $response['file_url'] = get_template_directory_uri() . '/isd_app/media/export/' . $filename;
        $response['status'] = 'success';
      }
    }
  } catch(Exception $e) {

  }
  echo json_encode($response);
  // Don't forget to stop execution afterward.
  wp_die();
}
add_action('wp_ajax_isd_exportdesign', 'isd_export_design');
add_action('wp_ajax_nopriv_isd_exportdesign', 'isd_export_design');

/**
 * Register ISD Builder Post Type
 **/
if(!post_type_exists(ISD_POST_TYPE)) {
  add_action ('init', 'register_isd_builder_post_type');
}
function register_isd_builder_post_type() {
  $args = array(
    'labels' => array(
      'name' => 'ISD Pages'
    ),
    'public' => true,
    'has_archive' => true,
    'rewrite' => array('slug' => 'design'),
    //'taxonomies' => array('category'),
    'supports' => array('title', 'excerpt', 'author', 'thumbnail', 'editor')
  );
  register_post_type(ISD_POST_TYPE, $args);
}
/**
 * Register Container Template Post Type
 **/
if(!post_type_exists(ISD_CONTAINER_TEMPLATE_TYPE)) {
  add_action ('init', 'register_isd_container_template_post_type');
}
function register_isd_container_template_post_type() {
  $args = array(
    'labels' => array(
      'name' => 'ISD Templates'
    ),
    'public' => true,
    'has_archive' => true,
    'rewrite' => array('slug' => 'isd-template'),
    'taxonomies' => array('category'),
    'supports' => array('title', 'excerpt', 'author', 'thumbnail', 'editor')
  );
  register_post_type(ISD_CONTAINER_TEMPLATE_TYPE, $args);
}
/**
 * Get Homepage design ID
 **/
function getHomepageDesignId() {
  $homepageId = get_option(ISD_HOMEPAGE_OPTION);
  //Check post exists and publish
  $post = get_post($homepageId);
  if($homepageId && $post && $post->post_status !== "trash") {
    return $homepageId;
  }
  return 0;
}
/**
 * Need admin logged in to use the page
 **/
function my_force_login() {
  //if (!is_single()) return;
  if (!is_user_logged_in()) {
    auth_redirect();
  }
}
/**
 * Get all design pages
 **/
function getAllDesignPages() {
  $allDesigns = array();
  $pages_query = array(
    'sort_order' => 'asc',
    'post_type'  => 'isd',
    'posts_per_page' => '-1'
  );
  query_posts($pages_query);
  if( have_posts() ) {
    while( have_posts() ){ 
      the_post();
      $allDesigns[] = array(
        'id' => get_the_ID(),
        'title' => get_the_title(),
        'thumbnail' => get_the_post_thumbnail_url( get_the_ID(), 'medium' ),
        'view' => get_permalink(),
        'content' => get_the_content(),
        'created_date' => get_the_date()
      );
    }
  }
  return $allDesigns;
  wp_reset_query();
}
/**
 * Get all design post
 **/
 function getAllDesignPost() {
  $allDesigns = getAllDesignPages();
  $allPost = [];
  foreach($allDesigns as $key => $page) {
    if($page['content'] == 'post') {
      $allPost[] = $page;
    }
  }
  return $allPost;
}
/**
 * Get menu items
 **/
function getMenus() {
  $allDesigns = array();
  $pages_query = array(
    'sort_order' => 'asc',
    'posts_per_page' => -1,
    'post_type'  => array('page', 'isd')
  );
  query_posts($pages_query);
  if( have_posts() ) {
    while( have_posts() ){ 
      the_post();
      $title = get_the_title();
      $link = get_permalink();
      if($title != "" && $link != "") {
        $allDesigns[] = array(
          'title' => $title,
          'value' => $link,
        ); 
      }
    }
  }
  return $allDesigns;
  wp_reset_query();
}
/**
 * Ajax get menu list
 **/
function isd_getmenus() {
  $response = array(
    'status' => 'error',
    'message' => __('Something went wrong! Can not get menu list!')
  );
  try {
    $menus = getMenus();
    if(!empty($menus)) {
      $response['status'] = 'success';
      $response['message'] = 'Get menu list successfully!';
      if(is_woocommerce_activated_isd()) {
        //Get top level category list 
        $response['categories'] = getTopLevelCategories();
      }
      $response['menus'] = $menus;
    }
  } catch(Exception $e) {

  }
  echo json_encode($response);
  // Don't forget to stop execution afterward.
  wp_die();
}
add_action('wp_ajax_isd_getmenus', 'isd_getmenus');
add_action('wp_ajax_nopriv_isd_getmenus', 'isd_getmenus');
/**
 * Check current user is admin
 **/
function isSiteAdmin(){
  return in_array('administrator',  wp_get_current_user()->roles);
}
/**
 * Get Design Page Base URL: http://ishopdesign.com/design-page/
 **/
function getDesignPageUrl(){
  return get_bloginfo('url') . '/design-page';
}
/**
 * Get Manage Page Base URL: http://ishopdesign.com/manage/
 **/
function getManagePageUrl(){
  return get_bloginfo('url') . '/manage';
}
/**
 * Get LiveApp Info
 * @Action
 **/
const LIVE_APP_DOMAIN = 'https://ishopdesign.com/api_plugin/';
const LIVE_APP_URL = 'https://ishopdesign.com/api_plugin/?action=';
function getLiveAppInfo($action, $extraRequest = '', $url = LIVE_APP_URL){
  try {
    $content = @file_get_contents($url . $action . $extraRequest);
    if($content !== false) {
      return $content;
    }
  } catch(Exception $e) {
    throw new Exception("Error Processing Request", 1);
  }
}
/**
 * Get App Version
 **/
function getVersion(){
  return getLiveAppInfo('version');
}
/**
 * Check App Update
 **/
function checkAppUpdate(){
  $currentVersion = get_option(ISD_APP_VERSION_OPTION_KEY);
  if($currentVersion === false) {
    add_option(ISD_APP_VERSION_OPTION_KEY, ISD_CURRENT_VERSION);
  } else {
    $appVersion = getVersion();
    if($appVersion && $currentVersion !== $appVersion) {
      return array(
        'message' => "iShopDesign <strong>$appVersion</strong> is available!",
        'version' => $appVersion
      );
    } else {
      //echo "Same version";
    }
  }
}
/**
 * Get Current App Version
 **/
function getLocalAppVersion() {
  return get_option(ISD_APP_VERSION_OPTION_KEY);
}
/**
 * Check before update app
 */
function checkBeforeUpdate() {
  $response = array(
    'status' => 'error',
    'message' => 'The folder: wp-content/plugins/ishopdesign is not writable. Please change folder permission and click upgrade button again!', 
  );
  //Check plugin folder or theme folder writeable or not 
  $path = getPluginPath();
  if(is_writable($path)) {
    return true;
  }
  return $response;
}
/**
* Get Plugin Dir
**/
function getPluginPath() {
  return WP_PLUGIN_DIR . '/ishopdesign/';
}
function updateApp() {
  $isOk = checkBeforeUpdate();
  if($isOk !== true) return false;
  try {
    $appPackage = getLiveAppInfo('app-package');
    if($appPackage) {
      $themePath = getPluginPath(); 
      $_temp = explode('-', $appPackage);
      $appVersion = str_replace('.zip', '', $_temp[1]);
      $liveAppUrl = LIVE_APP_DOMAIN . $appPackage;
      $localAppPath = $themePath . '/' . $appPackage;
      $liveAppPackage = file_get_contents($liveAppUrl);
      //Download app package to local
      $result = file_put_contents($localAppPath, $liveAppPackage);
      if($result) {
        if(file_exists($localAppPath)) {
          //Extract isd app package to local
          $isOk = unzipAppAndMerge($localAppPath);
          if($isOk) {
            //Clean zip file
            unlink($localAppPath);
            //Remove empty Application folder
            unlink($themePath . 'Applications/MAMP/htdocs/isd_api');
            //Update app version to database
            if(!$appVersion) {
              $appVersion = getVersion();
            }
            if($appVersion) {
              $isUpdateOption = update_option(ISD_APP_VERSION_OPTION_KEY, $appVersion);
              if($isUpdateOption) {
                $response = array(
                  'status' => 'success',
                  'message' => 'iShopDesign have been updated successfully!'
                );
              }
            }
          }
        } 
      } else {
        $response = array(
          'status' => 'error',
          'message' => 'Something when wrong! Can not download app package!'
        );
      }
    } else {
      $response = array(
        'status' => 'error',
        'message' => 'Something when wrong! Can not get app package!'
      );
    }
  } catch(Exception $e) {
    //throw new Exception('Something went wrong! Can not update app this time. Please try later!');
  }
  return $response;
}
/**
* Upzip App Package and merge
* @zipPath Zip file
**/
function unzipAppAndMerge($zipPath) {
  if(file_exists($zipPath)) {
    $zip = new ZipArchive;
    if ($zip->open($zipPath) === TRUE) {
      $themePath = getPluginPath(); 
      $zip->extractTo($themePath . '/');
      $zip->close();
      return true;
    } else {
      return false;
    }
  }
  return false;
}
/**
* Get design preview url
* @postId
**/
function getPreviewUrl($postId) {
  $url = get_post_permalink($postId);
  if($url) {
    return $url;
  }
  return '';
}
/**
 * Handle update app via ajax
 **/
function isd_updateapp() {
  $response = updateApp();
  echo json_encode($response);
  // Don't forget to stop execution afterward.
  wp_die();
}
add_action('wp_ajax_isd_updateapp', 'isd_updateapp');
add_action('wp_ajax_nopriv_isd_updateapp', 'isd_updateapp');
/**
 * Create page and assign a template to the page
 **/
function create_page($title, $template) {
  if (is_admin()){
    $new_page_title = $title;
    $new_page_content = 'ISD Auto Page';
    $new_page_template = $template; //ex. template-custom.php. Leave blank if you don't want a custom page template.
    //don't change the code bellow, unless you know what you're doing
    $page_check = get_page_by_title($new_page_title);
    $new_page = array(
        'post_type' => 'page',
        'post_title' => $new_page_title,
        'post_content' => $new_page_content,
        'post_status' => 'publish',
        'post_author' => 1,
    );
    if(!isset($page_check->ID)){
      $new_page_id = wp_insert_post($new_page);
      if(!empty($new_page_template)){
        update_post_meta($new_page_id, '_wp_page_template', $new_page_template);
      }
    }
  }
}
/**
 * Create main pages of isd
 **/
function create_isd_pages() {
  //Create manage page
  create_page('Design Page', 'page-design.php');
  //Create design page
  create_page('Manage', 'page-manage.php');
}
add_action('after_switch_theme', 'create_isd_pages');
/**
 * Get domain of the request
 **/
function getDomain($url) {
  $url_info = parse_url($url);
  if(isset($url_info['host'])) {
    return str_replace('www.', '',$url_info['host']);
  }
  return '';
}
/**
 * Get license key
 **/
function getLicenseKey() {
  return get_option(ISD_LICENSE_KEY_OPTION);
}
/**
 * Handle active key action
 **/
const ISD_LICENSE_KEY_OPTION = 'isd_license_key_option';
function isd_active_key() {
  // Handle request then generate response using WP_Ajax_Response
  $response = array(
    'status' => 'error',
    'message' => __('Something went wrong! Can not active the key!')
  );
  $licenseKey = $_POST['license_key'];
  try {
    if($licenseKey) {
      $currentKey = get_option(ISD_LICENSE_KEY_OPTION);
      if($currentKey !== false) {
        if($licenseKey == $currentKey) {
          $result = true;
        } else {
          $result = update_option(ISD_LICENSE_KEY_OPTION, $licenseKey);
        }
       } else {
         $result = add_option(ISD_LICENSE_KEY_OPTION, $licenseKey);
       }
       if($result) {
         $currentKey = get_option(ISD_LICENSE_KEY_OPTION);
         $response = getLicenseActiveInfo();
       }
     }
   } catch(Exception $e) {
     //print_r($e->getMessage());
   }
   echo json_encode($response);
   // Don't forget to stop execution afterward.
   wp_die();
}
add_action('wp_ajax_isd_active_key', 'isd_active_key');
add_action('wp_ajax_nopriv_isd_active_key', 'isd_active_key');
/**
 * Get license key
 **/
function getLicenseActiveInfo() {
  $currentKey = getLicenseKey();
  $domain = getDomain(get_bloginfo('url'));
  $licenseKeyInfo = array(
    'status' => 'error',
    'message' => 'License key is not valid!'
  );
  if($currentKey != "" && $domain != "") {
    $extraRequest = "&domain=$domain&key=$currentKey";
    $result = getLiveAppInfo('license', $extraRequest, 'https://ishopdesign.com/api/?action=');
    try {
      return json_decode($result, true);
    } catch(Exception $e) {

    }
  }
  return $licenseKeyInfo;
}
/**
 * Get App Info
 **/
function getAppInfo() {
  return $licenseInfo = getLicenseActiveInfo();
}
/**
 * Add product list to json if isd_product_list exists.
 * @param {array} $designJsonArr 
 **/
function attachProductListToJson($designJsonArr) {
  if(!is_woocommerce_activated_isd()) {
    return $designJsonArr;
  }
  $productIds = array();
  if(isset($designJsonArr['main']['elements'])) {
    foreach($designJsonArr['main']['elements'] as $_element) {
      if(isset($_element['type']) && $_element['type'] === "isd_product_list") {
        if(isset($_element['productIds']) && !empty($_element['productIds'])) {
          foreach ($_element['productIds'] as $key => $_id) {
            $productIds[] = $_id;  
          }
        }
      }
    }
  }
  $productIds = array_unique($productIds);
  if(!empty($productIds)) {
    $productListArr = array();
    foreach ($productIds as $key => $value) {
      $productListArr[] = getProductInfoById($value);
    }
    $designJsonArr['product']['productList'] = $productListArr;
  }
  return $designJsonArr;
}
/**
 * Add server side info for final string: blog list, shortcode, ...
 * @param {array} $designJsonArr 
 **/
 function attachServerDataToJson($designJsonArr) {
  $postIds = array();
  if(isset($designJsonArr['main']['elements'])) {
    foreach($designJsonArr['main']['elements'] as $_eID => $_element) {
      if(isset($_element['type'])) {
        switch ($_element['type']) {
          case 'isd_bloglist':
            if(isset($_element['postIds']) && !empty($_element['postIds'])) {
              foreach ($_element['postIds'] as $key => $_id) {
                $postIds[] = $_id;  
              }
            }
            break;
          case 'isd_contact_form':
            //Check if contact form 7 shortcode
            if($_element['templateID'] === "contact7") {
              $designJsonArr['main']['elements'][$_eID]['shortcodeHTML'] = do_shortcode($_element['html']);
            }
          case 'isd_custom_html':
            //Check html is shortcode
            if(isset($_element['is_shortcode'])) {
              $designJsonArr['main']['elements'][$_eID]['shortcodeHTML'] = do_shortcode($_element['html']);
            }
            break;
        }
      }
    }
  }
  $postIds = array_unique($postIds);
  if(!empty($postIds)) {
    //Post array with key for easy access 
    $postArrWithKey = array();
    $attachedPost = array();
    $allPost = getAllDesignPost();
    foreach ($allPost as $post) {
      $postArrWithKey[$post['id']] = $post;
    };
    $postListArr = array();
    foreach ($postIds as $id) {
      if(isset($postArrWithKey[$id])) {
        $attachedPost[] = $postArrWithKey[$id];
      }
    }
    $designJsonArr['post']['postList'] = $attachedPost;
  }
  return $designJsonArr;
}
/**
 * Get woocommece product ID
 * @param {string} $id
 **/
function getProductInfoById($id) {
  $product = get_product($id);
  if($product && $product->id) {  
    $thumbnails = array();
    if($product->image_id) {
      $thumbnails[] = array(
        'src' => get_the_post_thumbnail_url( $product->id )
      );
    } else {
      //Placeholder image
      $thumbnails[] = array(
        'src' => wc_placeholder_img_src()
      );
    }
    return array(
      'id' => $product->id,
      'name' => $product->name,
      'price' => $product->price,
      'regular_price' => wc_price($product->regular_price),
      'sale_price' => wc_price($product->sale_price),
      'images' => $thumbnails,
      'description' => $product->description,
      'short_description' => $product->short_description,
      'categories' => $product->categories,
      'price_html' => $product->get_price_html(),
      'add_to_cart_url' => get_bloginfo('url') . '/cart/?add-to-cart=' . $product->id,
      'product_url' => $product->get_permalink(),
    );
  }
  return $product;
}

//add_action( 'get_header', 'isd_special_page', 10 );

function isd_special_page() {
  if(is_product_category()) {
    //Show my own category and product page
    include_once('isd_category.php');
    exit();
  }
  if(is_product()) {
    include_once('isd_product.php');
    exit();
  }
}
/**
 * Get Category Page Data
 **/
function getCategoryPageData() {
  global $wp_query;
  $pageData = get_object_vars($wp_query->get_queried_object());
  //List all product of this category 
  $productArgs     = array( 
    'post_type' => 'product', 
    'posts_per_page' => 16,
    'product_cat' => $pageData['slug'],
  );
  $products = get_posts( $productArgs );
  $productArr = array();
  foreach($products as $product) {
    $productData = wc_get_product($product->ID);
    $productThumbnail = get_the_post_thumbnail_url( $product->ID );
    if(!$productThumbnail) {
      $productThumbnail = wc_placeholder_img_src();
    }
    $productArr[] = array(
      'name' => $productData->get_name(),
      'src' => $productThumbnail,
      'price_html' => $productData->get_price_html(),
      'product_url' => $productData->get_permalink(),
      'add_to_cart_url' => $productData->add_to_cart_url()
    );
  }
  $pageData['product_list'] = $productArr;

  $categoryListArr = getTopLevelCategories();
  $pageData['category_list'] = $categoryListArr;
  return $pageData;
}
/**
 * Get Product Page Data
 **/
 function getProductPageData() {
  global $wp_query;
  $pageData = get_object_vars($wp_query->get_queried_object());
  $productData = wc_get_product($pageData['ID']);
  $productArr = array(
    'name' => $productData->get_name(),
    'price_html' => $productData->get_price_html(),
    'product_url' => $productData->get_permalink(),
    'add_to_cart_url' => $productData->add_to_cart_url(),
    'image_ids' => $productData->get_gallery_image_ids(),
    'image' => wp_get_attachment_url($productData->get_image_id()),
  );
  $productArr['description'] = $pageData['post_content'];
  $productArr['short_description'] = $pageData['post_excerpt'];
  
  return $productArr;
}
/**
 * Get Top Level Category
 **/
function getTopLevelCategories() {
  //Categories list 
  $taxonomy = 'product_cat';
  $orderby = 'ID';
  $show_count = 1;      // 1 for yes, 0 for no
  $pad_counts = 1;      // 1 for yes, 0 for no
  $hierarchical = 1;      // 1 for yes, 0 for no
  //$title = '<h2>' . _x('Our Products', 'mfarma') . '</h2>';
  $hide_empty = 0;
  $args = array(
    'taxonomy' => $taxonomy,
    'orderby' => $orderby,
    'order' => 'ASC',
    'parent' => '0',
    'show_count' => $show_count,
    'pad_counts' => $pad_counts,
    'hierarchical' => $hierarchical,
    //'title_li' => $title,
    'hide_empty' => $hide_empty,
    //'echo' => 0,
  );
  $categoryList = get_categories($args);
  $categoryListArr = array();
  foreach ($categoryList as $category) {
    $categoryData = get_object_vars($category);
    $categoryData['category_url'] = get_term_link( $categoryData['term_id'], 'product_cat' );
    $categoryListArr[] = $categoryData;
  }
  return $categoryListArr;
}
/**
 * Check if woocommerce actived or not
 **/
function is_woocommerce_activated_isd() {
  if ( class_exists( 'woocommerce' ) ) { return true; } else { return false; }
}
/**
 * Check if woocommerce api ready to use
 **/
function isdWooAPIReady() {
  if(is_woocommerce_activated_isd()) {
    //Check woo_api folder exists or not
    $wooAPIPath = ABSPATH . 'woo_api';
    if(file_exists($wooAPIPath)) {
      return true;
    } else {
      return false;
    }
  }
  return false;
}
/**
 * ISD Plugin Lists
 **/
 function getPlugins() {
  $plugins = array(
    'isd_text', 
    'isd_image', 
    'isd_button', 
    'isd_icon', 
    'isd_contact_form', 
    'isd_menu',
    'isd_divider',
    'isd_custom_html',
    'isd_shortcode',
    'isd_tab',
    'isd_slider',
    'isd_bloglist',
  );
  if(isdWooAPIReady()) {
    $plugins[] = 'isd_product_list';
  }
  return json_encode($plugins);
}

/**
 * @param {string} $optionKey 
 **/
function getLatestExtraData($optionKey) {
  $ids = get_option($optionKey);
  $result = false;
  if($ids !== false && $ids !== "") {
    $idsArr = explode(',', $ids);
    if(is_array($idsArr)) {
      for($i = count($idsArr) -1; $i >= 0; $i --) {
        $optionData = get_option($idsArr[$i]);
        if($optionData !== false) {
          if(isValidJson($optionData)) {
            $result = $optionData;
            break;
          }
        }
      }
    }
  }
  return $result;
}
/**
 *  
 * @param {string} $optionKey => ISD_HEADER_IDS or ISD_FOOTER_IDS
 * @param {string} $keyPrefix => ISD_HEADER_time() or ISD_FOOTER_time()
 * @param {string} $data 
 **/
function saveExtraData($optionKey, $keyPrefix, $data) {
  $extraDataKey = $keyPrefix . time();
  $result = add_option($extraDataKey , $data);
  $isOK = false;
  if($result) {
    //Add this key to ids collection 
    $keyIds = get_option($optionKey);
    if($keyIds === false) {
      $isOK = add_option($optionKey, $extraDataKey);
    } else {
      $keyIdsArr = explode(',', $keyIds);
      $keyIdsArr[] = $extraDataKey;
      $isOK = update_option($optionKey, implode(',', $keyIdsArr));
    }
  }
  return $isOK;
}

/**
 *  
 * Get all image attachment
 **/
function getMediaImages() {
  global $current_user;
  $query_images_args = array(
    'post_type'      => 'attachment',
    'post_mime_type' => 'image',
    'post_status'    => 'inherit',
    'posts_per_page' => - 1,
    'author' => $current_user->id
  );
  $query_images = new WP_Query( $query_images_args );
  $images = array();
  foreach ( $query_images->posts as $image ) {
    //print_r($image);
    $images[] = array(
      'id' => $image->ID,
      'src' => wp_get_attachment_url($image->ID),
      'thumbnail' => wp_get_attachment_thumb_url( $image->ID )
    );
  }
  return $images;
}
/**
 * Get images attachment request
 **/

 function isd_get_all_images() {
  // Handle request then generate response using WP_Ajax_Response
  $response = array(
    'status' => 'error',
    'message' => __('Something went wrong! Can not load image from server!')
  );
  
  try {
    $images = getMediaImages();
    if(!empty($images)) {
      $response['status'] = 'success';
      $response['message'] = 'All images have been returned!';
      $response['data'] = $images;
    } else {
      $response['status'] = 'success';
      $response['message'] = 'No image found!';
    }
  } catch(Exception $e) {
    //print_r($e->getMessage());
  }
  // Update the post into the database
  //$result = wp_update_post( $my_post );
  echo json_encode($response);
  // Don't forget to stop execution afterward.
  wp_die();
}
add_action('wp_ajax_isd_get_all_images', 'isd_get_all_images');
add_action('wp_ajax_nopriv_isd_get_all_images', 'isd_get_all_images');

/**
 * Handle get post list
 **/

 function isd_get_post_list() {
  // Handle request then generate response using WP_Ajax_Response
  $response = array(
    'status' => 'error',
    'message' => __('Something went wrong! Can not get post list from server!')
  );
  try {
    $post = getAllDesignPost();
    $response['status'] = 'success';
    $response['message'] = 'Get post list successfully!';
    $response['post'] = $post;
  } catch(Exception $e) {
    //print_r($e->getMessage());
  }
  echo json_encode($response);
  // Don't forget to stop execution afterward.
  wp_die();
}
add_action('wp_ajax_isd_get_post_list', 'isd_get_post_list');
add_action('wp_ajax_nopriv_isd_get_post_list', 'isd_get_post_list');

function kriesi_pagination($pages = '', $range = 2)
{  
     $showitems = ($range * 2)+1;  

     global $paged;
     if(empty($paged)) $paged = 1;

     if($pages == '')
     {
         global $wp_query;
         $pages = $wp_query->max_num_pages;
         if(!$pages)
         {
             $pages = 1;
         }
     }   

     if(1 != $pages)
     {
         echo "<div class='pagination'>";
         if($paged > 2 && $paged > $range+1 && $showitems < $pages) echo "<a href='".get_pagenum_link(1)."'>&laquo;</a>";
         if($paged > 1 && $showitems < $pages) echo "<a href='".get_pagenum_link($paged - 1)."'>&lsaquo;</a>";

         for ($i=1; $i <= $pages; $i++)
         {
             if (1 != $pages &&( !($i >= $paged+$range+1 || $i <= $paged-$range-1) || $pages <= $showitems ))
             {
                 echo ($paged == $i)? "<span class='current'>".$i."</span>":"<a href='".get_pagenum_link($i)."' class='inactive' >".$i."</a>";
             }
         }

         if ($paged < $pages && $showitems < $pages) echo "<a href='".get_pagenum_link($paged + 1)."'>&rsaquo;</a>";  
         if ($paged < $pages-1 &&  $paged+$range-1 < $pages && $showitems < $pages) echo "<a href='".get_pagenum_link($pages)."'>&raquo;</a>";
         echo "</div>\n";
     }
}
/**
 * Handle send contact form
 **/

function isd_send_message() {
  // Handle request then generate response using WP_Ajax_Response
  $response = array(
    'status' => 'error',
    'message' => __('Something went wrong! Can not send the message to server!')
  );
  $data = $_POST['data'];
  try {
    if(!empty($data)) {
      //find admin email 
      $isSent = sendEmail($data);
      if($isSent) {
        $response = array(
          'status' => 'success',
          'message' => (isset($data['success_message']) && $data['success_message'] != "") ? $data['success_message'] : __('Email have been sent successfully!')
        );
      } else {
        $response = array(
          'status' => 'error',
          'message' => __('Something went wrong. Can not send email this time!')
        );
      }
    }
  } catch(Exception $e) {
    //print_r($e->getMessage());
  }
  // Update the post into the database
  //$result = wp_update_post( $my_post );
  echo json_encode($response);
  // Don't forget to stop execution afterward.
  wp_die();
}
add_action('wp_ajax_isd_send_message', 'isd_send_message');
add_action('wp_ajax_nopriv_isd_send_message', 'isd_send_message');

function sendEmail($data) {
  $to = isset($data['adminEmail']) ? $data['adminEmail'] : '';
  //find user email 
  $email = '';
  foreach ($data as $fieldName => $value) {
    if(is_email($value)) {
      $email = $value;
      break;
    }
  } 

  $subject = isset($data['emailSubject']) ? $data['emailSubject'] : 'Contact Form Message';
  $senderName = isset($data['senderName']) ? $data['senderName'] : '';
  $sender = 'From: '. $senderName .' <'.$email.'>' . "\r\n";
  //Skip default field
  $skipFields = array(
    'success_message',
    'adminEmail',
    'emailSubject',
    'senderName',
    'ccEmails',
    'bccEmails'
  );

  $message = '';
  saveContactInfo(json_encode($data));
  foreach($data as $name => $value) {
    if(in_array($name, $skipFields)) {
      continue;
    }
    $message .= $name . ': ' . $value . "\r\n<br/>";
  }
  $headers[] = 'MIME-Version: 1.0' . "\r\n";
  $headers[] = 'Content-type: text/html; charset=utf-8' . "\r\n";
  $headers[] = "X-Mailer: PHP \r\n";
  $headers[] = $sender;
  //Add cc emails to header 
  if(isset($data['ccEmails']) && $data['ccEmails'] != '') {
    $ccEmails = explode(',', $data['ccEmails']);
    foreach((array) $ccEmails as $ccEmail) {
      $headers[] = 'Cc: ' . trim($ccEmail);
    }
  }
  if(isset($data['bccEmails']) && $data['bccEmails'] != '') {
    $bccEmails = explode(',', $data['bccEmails']);
    foreach((array) $bccEmails as $bccEmail) {
      $headers[] = 'Bcc: ' . trim($bccEmail);
    }
  }
  $mail = wp_mail( $to, $subject, $message, $headers );
  if( $mail ) 
    return true;
  else
    return false;
}

function redirectToHomePage() {
  wp_redirect(get_bloginfo('url') . '?login=show');
}

/**
 * Register ISD Contact Form Info
 **/
if(!post_type_exists(ISD_CONTACT_FORM_TYPE)) {
  add_action ('init', 'register_isd_contact_post_type');
}
function register_isd_contact_post_type() {
  $args = array(
    'labels' => array(
      'name' => 'Customer Contact'
    ),
    'public' => true,
    'has_archive' => true,
    'rewrite' => array('slug' => 'customer_contact'),
    //'taxonomies' => array('category'),
    'supports' => array('title', 'excerpt', 'author', 'thumbnail', 'editor')
  );
  register_post_type(ISD_CONTACT_FORM_TYPE, $args);
}
//This function should place in customer theme functions.php
// function saveCustomContactInfo($data) {
  
// }
function saveContactInfo($data) {
  if(function_exists('saveCustomContactInfo')) {
    saveCustomContactInfo($data);
  } else {
    $createTime = date("d-m-Y h:i:sa");
    $new_post = array(
      'post_type'		 => ISD_CONTACT_FORM_TYPE,
      'post_title'   => 'Contact Form - ' . $createTime,
      'post_content'   => $data,
      'post_status'	 => 'private',
    );
    $pid = wp_insert_post($new_post);
    if($pid) {
      return true;
    }
    return false;
  }
}
/**
 * Load external script before main app js
 */
function getExternalScript($externalFiles) {
  $scripts = '';
  foreach((array) $externalFiles as $file) {
    //Check js file or not
    $fileExt = pathinfo($file);
    if($fileExt['extension'] == "js") {
      $scripts .= '<script src="'. $file .'"></script>';
    }
  }
  return $scripts;
}
/**
 * Handle download design
 **/
function isd_download_design() {
  // Handle request then generate response using WP_Ajax_Response
  $response = array(
    'status' => 'error',
    'message' => __('Something went wrong! Can not download this design!')
  );
  $designID = $_POST['designID'];
  try {
    if($designID) {
      //$result = downloadDesign($designID);
      $result = zipFilesToExport($designID);
      if($result) {
        $response['status'] = 'success';
        $response['message'] = 'Done.!';
        $response['url'] = $result;
      }
    }
  } catch(Exception $e) {
    //print_r($e->getMessage());
  }
  // Update the post into the database
  //$result = wp_update_post( $my_post );
  echo json_encode($response);
  // Don't forget to stop execution afterward.
  wp_die();
}
add_action('wp_ajax_isd_download_design', 'isd_download_design');
add_action('wp_ajax_nopriv_isd_download_design', 'isd_download_design');

/**
 * Download static design, mostly for ladinging page
 */
function getStaticIndex($postId, $design_value) {

  $title = "iShopDesign landing page builder";
  $description = "Thiết kế landing page miễn phí, dễ dàng và nhanh chóng với công cụ iShopDesign. Phục vụ các chiến dịch marketing và quảng cáo online.";
  $keywords = "ishopdesign website builder,ishopdesign landing page builder, best website builder, responsive website builder, landing page builder";
  $thumbnail = get_template_directory_uri() . "/isd_app/images/logo300x300.png";
  $externalFiles = array();

  //Get design info
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

  $html =  '<html>';
  $html .= '<head>';
  $html .= '<meta charset="UTF-8">';
  $html .= '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />';
  $html .= '<meta name="keywords" content="'. $keywords .'">';
  $html .= '<meta name="description" content="'. $description .'">';
  $html .= '<title>'. $title .'</title>';

  $styles = getDesignStyleFiles($postId);
  foreach($styles as $style) {
    $temp = explode('/', $style);
    $html .= '<link href="isd_app/css/'. end($temp) .'" rel="stylesheet">';
  }
  $html .= '<style>#header_section, #footer_section {display: none!important;}</style>';
  $html .= '</head>';

  $html .= '<body>';

  $html .= '<div id="isd_app"></div>';
  $html .= '<input type="hidden" id="isd_post_id" value="'. $postId .'" />';
  $html .= '<input type="hidden" id="isd_static_deploy" value="1" />';
  $html .= '<input type="hidden" id="isd_plugins" value="'. htmlentities(getPlugins(), ENT_COMPAT,'UTF-8') .'" />';
  $html .= '<input type="hidden" id="isd_import_json" value="'. htmlentities($design_value, ENT_COMPAT,'UTF-8') .'" />';

  $html .= '<script src="isd_app/js/jquery.min.js"></script>';
  $html .= '<script src="isd_app/js/form.min.js"></script>';
  $html .= getExternalScript($externalFiles);
  $html .= '<script src="isd_app/js/ishopdesign.viewonly.min.js"></script>';


  $html .= '</body>';
  $html .= '</html>';

  return $html;
}
/**
 * Zip all files from a design
 */
function zipFilesToExport($postId) {
  $design_value = getDesignJson($postId);
  $imageArr = array();
  $responsiveModes = array('tabletland','tablet', 'mobileland', 'mobile480', 'mobile');
  if($design_value && $postId) {
    //Create design folder 
    $wpUploadInfo = wp_upload_dir();
    $baseDir = $wpUploadInfo['basedir'];
    $designFilesDir = $baseDir . '/isd/' . $postId;
    $isdAppDir = $designFilesDir . '/isd_app/uploads/';
    if(!file_exists($designFilesDir)) {
      mkdir($designFilesDir);
    }
    //Extract string
    $designDecoded = json_decode($design_value, true);
    //Image of all elements
    if(isset($designDecoded['main']['elements'])) {
      foreach($designDecoded['main']['elements'] as $eIdex => $element) {
        if(isset($element['type']) && $element['type'] = 'isd_image') {
          //Desktop mode
          if(isset($element['src'])) {
            $imageArr[] = $element['src'];
            $copiedName = copyFileFromUrl($isdAppDir, $element['src']);
            $designDecoded['main']['elements'][$eIdex]['src'] = 'isd_app/uploads/' . $copiedName;
          }
          foreach($responsiveModes as $mIndex => $mode) {
            if(isset($element[$mode]['src'])) {
              $imageArr[] = $element[$mode]['src'];
              $copiedName = copyFileFromUrl($isdAppDir, $element[$mode]['src']);
              $designDecoded['main']['elements'][$eIdex][$mode]['src'] = 'isd_app/uploads/' . $copiedName;
            }
          }
        }
      }
    }
    //Background image of containers, rows, columns
    $layouts = array('containers', 'rows', 'columns');
    foreach($layouts as $layout) {
      if(isset($designDecoded['main'][$layout])) {
        foreach($designDecoded['main'][$layout] as $lIndex => $item) {
          if(isset($item['bgImageUrl'])) {
            $imageArr[] = $item['bgImageUrl'];
            $copiedName = copyFileFromUrl($isdAppDir, $item['bgImageUrl']);
            $designDecoded['main'][$layout][$lIndex]['bgImageUrl'] = 'isd_app/uploads/' . $copiedName;
          }
          foreach($responsiveModes as $mIndex => $mode) {
            if(isset($item[$mode]['bgImageUrl'])) {
              $imageArr[] = $item[$mode]['bgImageUrl'];
              $copiedName = copyFileFromUrl($isdAppDir, $item[$mode]['bgImageUrl']);
              $designDecoded['main'][$layout][$lIndex][$mode]['bgImageUrl'] = 'isd_app/uploads/' . $copiedName;
            }
          }
        }
      }
    }
    //Create index file 
    $indexContent = getStaticIndex($postId, json_encode($designDecoded));
    file_force_contents($designFilesDir . '/index.html', $indexContent);
    //Copy design assets 
    copyAssetFiles($postId);
    if(file_exists($designFilesDir)) {
      $downloadFilename = 'ISD_' . $postId . '.zip';
      $result = zipData($designFilesDir, $baseDir . '/isd/' . $downloadFilename);
      if(file_exists($baseDir . '/isd/' . $downloadFilename)) {
        return $wpUploadInfo['baseurl'] . "/isd/$downloadFilename";
      }
    }
  }
}
/**
 * Get design script files
 */
function getDesignScriptFiles($postId) {
  $scripts = array(
    'js/ishopdesign.viewonly.min.js', 
    'js/jquery.min.js',
    'js/form.min.js',
  );
  return $scripts;
}
/**
 * Get design style files
 */
function getDesignStyleFiles($postId) {
  $styles = array(
    'css/semantic/semantic.min.css', 
    'css/semantic/isd_semantic.css', 
    'css/google-fonts.css',
    'css/isd_animate.css',
    'styles/isd_theme_style.css'
  );
  return $styles;
}
/** 
 * Copy asset files of a design 
 * */
function copyAssetFiles($postId) {
  $scripts = getDesignScriptFiles($postId);
  $styles = getDesignStyleFiles($postId);
  $wpUploadInfo = wp_upload_dir();
  $baseDir = $wpUploadInfo['basedir'];
  $designFilesDir = $baseDir . '/isd/' . $postId;
  //ISD Theme Folder
  $isdAppBaseDir = getPluginPath() . '/isd_app/';
  foreach($scripts as $file) {
    $temp = explode('/', $file);
    file_force_contents($designFilesDir . '/isd_app/js/' . end($temp), file_get_contents($isdAppBaseDir . $file));
  }
  foreach($styles as $file) {
    $temp = explode('/', $file);
    file_force_contents($designFilesDir . '/isd_app/css/' . end($temp), file_get_contents($isdAppBaseDir . $file));
  }
  //Copy fonts 
  isd_recurse_copy($isdAppBaseDir . 'fonts', $designFilesDir . '/isd_app/fonts');
  //Copy semantic default theme asset
  isd_recurse_copy($isdAppBaseDir . 'css/semantic/themes', $designFilesDir . '/isd_app/css/themes');
}
/** 
 * Copy file from url 
 * */
function copyFileFromUrl($designFilesDir, $url) {
  if(strpos($url, '/wp-content/uploads/')) {
    $wpUploadInfo = wp_upload_dir();
    $baseDir = $wpUploadInfo['basedir'];
    $temp = explode('/wp-content/uploads/', $url);
    if(isset($temp[1]) && file_exists($baseDir . '/' . $temp[1])) {
      file_force_contents($designFilesDir . '/' . $temp[1], file_get_contents($baseDir . '/' . $temp[1]));
    }
    if(file_exists($designFilesDir . '/' . $temp[1])) {
      return $temp[1];
    }
  }
  return false;
}
/**
 * Copy a folder to other
 */
function isd_recurse_copy($src,$dst) { 
  $dir = opendir($src); 
  @mkdir($dst); 
  while(false !== ( $file = readdir($dir)) ) { 
      if (( $file != '.' ) && ( $file != '..' )) { 
          if ( is_dir($src . '/' . $file) ) { 
            isd_recurse_copy($src . '/' . $file,$dst . '/' . $file); 
          } 
          else { 
              copy($src . '/' . $file,$dst . '/' . $file); 
          } 
      } 
  } 
  closedir($dir); 
  return true;
}
/**
 * File put contents fails if you try to put a file in a directory that doesn't exist. This creates the directory
 */
function file_force_contents($dir, $contents){
  $parts = explode('/', $dir);
  $file = array_pop($parts);
  $dir = '';
  foreach($parts as $part)
      if(!is_dir($dir .= "/$part")) mkdir($dir);
  file_put_contents("$dir/$file", $contents);
}
/**
 * Zip App Files
 */
function zipData($source, $destination) {
  if (extension_loaded('zip') === true) {
      if (file_exists($source) === true) {
          $zip = new ZipArchive();
          if ($zip->open($destination, ZIPARCHIVE::CREATE) === true) {
              $source = realpath($source);
              if (is_dir($source) === true) {
                  $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);
                  foreach ($files as $file) {
                    //Skip Applications folder 
                    // if(strpos($file, 'Applications')) {
                    //   continue;
                    // }
                    $file = realpath($file);
                    if (is_dir($file) === true) {
                        $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                    } else if (is_file($file) === true) {
                        $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
                    }
                  }
              } else if (is_file($source) === true) {
                  $zip->addFromString(basename($source), file_get_contents($source));
              }
          }
          return $zip->close();
      }
  }
  return false;
}
/**
 * Get all category of container template
 */
function getContainerTemplateCategories() {
  $cate = get_category_by_slug(ISD_CONTAINER_TEMPLATE_SLUG);
  if($cate && $cate->term_id) {
    $templateCates = get_categories(array(
      'parent' => $cate->term_id
    ));
    return $templateCates;
  }
}
/**
 * Get post by category
 */
function getTemplateByCategory($categoryId) {
  $args = array(
    'sort_order' => 'asc',
    'post_type'  => ISD_CONTAINER_TEMPLATE_TYPE,
    'posts_per_page' => '-1',
    'cat' => $categoryId
  );
  $templates = array();
  $query = new WP_Query( $args );
  if ( $query->have_posts() ) {
    // The Loop
    while ( $query->have_posts() ) {
      $query->the_post();
      $thumbnail = get_the_post_thumbnail_url( );
      if($thumbnail != '' || true) {
        $templates[] = array(
          'thumbnail' => get_the_post_thumbnail_url( ),
          'content' => get_the_content(),
        );
      }
    }
    wp_reset_postdata();
  }
  return $templates;
}

/**
 * Get all container templates
 */
function getTemplateCollection() {
  $cates = getContainerTemplateCategories();
  $allTemplates = array();
  $allTemplates[] = array(
    'id' => 0,
    'name' => 'All Templates',
    'items' => array()
  );
  if(!$cates) return $allTemplates;
  foreach($cates as $cate) {
    //print_r($cate);
    $allTemplates[] = array(
      'id' => $cate->term_id, 
      'name' => $cate->name,
      'items' => getTemplateByCategory($cate->term_id)
    );
  }
  return $allTemplates;
}
/**
 * Get all container templates
 **/

function isd_get_all_container_templates() {
  // Handle request then generate response using WP_Ajax_Response
  $response = array(
    'status' => 'error',
    'message' => __('Something went wrong! Can not load template from server!')
  );
  
  try {
    $templates = getTemplateCollection();
    if(!empty($templates)) {
      $response['status'] = 'success';
      $response['message'] = 'All templates have been returned!';
      $response['data'] = $templates;
    } else {
      $response['status'] = 'success';
      $response['message'] = 'No template found!';
    }
  } catch(Exception $e) {
    //print_r($e->getMessage());
  }
  // Update the post into the database
  //$result = wp_update_post( $my_post );
  echo json_encode($response);
  // Don't forget to stop execution afterward.
  wp_die();
}
add_action('wp_ajax_isd_get_all_container_templates', 'isd_get_all_container_templates');
add_action('wp_ajax_nopriv_isd_get_all_container_templates', 'isd_get_all_container_templates');

/**
* Upload Font File Handle
**/
function isd_upload_font_handle() {
  $response = array(
    'status' => 'error',
    'message' => 'Something went wrong! Can not upload file to server!'
  );
  try {
    // Undefined | Multiple Files | $_FILES Corruption Attack
    // If this request falls under any of them, treat it invalid.
    if (
        !isset($_FILES['filename']['error']) ||
        is_array($_FILES['filename']['error'])
    ) {
        echo json_encode($response);
        exit();
    }
    $isOk = false;
    // Check $_FILES['upfile']['error'] value.
    switch ($_FILES['filename']['error']) {
        case UPLOAD_ERR_OK:
          $isOK = true;
          break;
        case UPLOAD_ERR_NO_FILE:
          //throw new RuntimeException('No file sent.');
          $response['message'] = 'No file sent.';
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
          //throw new RuntimeException('Exceeded filesize limit.');
          $response['message'] = 'Exceeded filesize limit.';
        default:
          //throw new RuntimeException('Unknown errors.');
          $response['message'] = 'Unknown errors.';
    }
    if($isOK) {
      $pathInfo = pathinfo($_FILES['filename']['name']);
      //Check path extension 
      $ext = strtolower($pathInfo['extension']);
      $accepts = array('ttf', 'otf', 'woff', 'woff2');
      if(!in_array($ext, $accepts)) {
        $response['message'] = 'Invalid file format.';
      } else {
        $target_dir = get_template_directory() . '/isd_app/media/upload/fonts/';
        if(defined('ISD_PLUGIN')) {
          $target_dir = WP_PLUGIN_DIR . '/ishopdesign/public/media/upload/fonts/';
        }
        if(!file_exists($target_dir)) {
          mkdir($target_dir, 0777);
        }
        $postId = $_POST['post_id'] ? $_POST['post_id'] : '' ;
        $filename = basename($_FILES["filename"]["name"]);
        $target_file = $target_dir . $filename;

        // Check if file already exists
        if (file_exists($target_file)) {
          $filename = time() . '_' . $filename;
          $target_file = $target_dir . $filename;
          //If file already exists then rename the file and allow upload normally
        }
        if($postId !== '') {
          if (move_uploaded_file($_FILES["filename"]["tmp_name"], $target_file)) {
            $response['status'] = 'success';
            $response['message'] = "The file ". $filename. " has been uploaded.";
            $mediaBaseUrl = get_template_directory_uri() . '/isd_app/media/upload/fonts/';
            //Save to post meta: ISD_CUSTOM_FONT_KEY
            $fontInfo = array(
              'title' => basename($pathInfo['basename'], '.' . $ext),
              'filename' => $filename
            );
            $fontInfos = add_post_meta($postId, ISD_CUSTOM_FONT_KEY, json_encode($fontInfo));
            $response['data'] = array(
              'url' => $mediaBaseUrl . $filename,
              'font' => $fontInfo
            );
          } else {
              $response['message'] = "Sorry, there was an error uploading your file.";
          }
        } else {
          $response['message'] = "You need to save the design before upload custom font!";
        }
      }
    }
  } catch (RuntimeException $e) {
      $response['message'] = $e->getMessage();
  }
  
  echo json_encode($response);
  exit;
}
add_action('wp_ajax_isd_upload_font', 'isd_upload_font_handle');
add_action('wp_ajax_nopriv_isd_upload_font', 'isd_upload_font_handle');

/**
 * Get custom font of post
 */
function getCustomFonts($postId) {
  $fonts = get_post_meta($postId, ISD_CUSTOM_FONT_KEY);
  $fontDropdown = array();
  $fontFace = '';
  if(!empty($fonts)) {
    $mediaBaseUrl = get_template_directory_uri() . '/isd_app/media/upload/fonts/';
    if(defined('ISD_PLUGIN')) {
      $mediaBaseUrl = plugins_url() . '/ishopdesign/public/media/upload/fonts/';
    }
    foreach($fonts as $font) {
      $fontDecode = json_decode($font, true);
      $fontFace .= '@font-face {
        font-family: "'. $fontDecode['title'] .'";
        src:  url('. $mediaBaseUrl . $fontDecode['filename'] . ');
      }';
      $fontDropdown[] = array(
        'title' => $fontDecode['title'],
        'value' => $fontDecode['title']
      );
    }
  }
  return array(
    'fonts' => empty($fontDropdown) ? '' : json_encode($fontDropdown),
    'fontFace' => $fontFace
  );
}
/**
 * Defined custom action hook
 */
function after_body_start() {
	do_action('after_body_start');
}
function before_body_end() {
	do_action('before_body_end');
}

/**
 * Get Design Json via Ajax, speed up server response
 */
function isd_get_design_json() {
  // Handle request then generate response using WP_Ajax_Response
  $response = array(
    'status' => 'error',
    'message' => __('Something went wrong! Can not get design json from server!')
  );
  try {
    if(isset($_POST['postId'])) {
      $postId = $_POST['postId'];
      $designJson = getDesignJson($postId);
      if($designJson != '') {
        $response['status'] = 'success';
        $response['message'] = 'Design have been loaded!';
        $response['data'] = $designJson;
      }
    }    
  } catch(Exception $e) {
    //print_r($e->getMessage());
  }
  echo json_encode($response, JSON_HEX_APOS | JSON_UNESCAPED_UNICODE);
  // Don't forget to stop execution afterward.
  wp_die();
}
add_action('wp_ajax_isd_get_design_json', 'isd_get_design_json');
add_action('wp_ajax_nopriv_isd_get_design_json', 'isd_get_design_json');

/**
 * Clean database
 * post_meta table get large after user save many time!
 */
function cleanISDPostMeta($postId) {
  global $wpdb;
  $max_history = 5;
  $results = $wpdb->get_results( "SELECT * FROM {$wpdb->postmeta} WHERE meta_key = '_isd_json' AND post_id = {$postId} ORDER BY meta_id DESC" );
  foreach($results as $index => $design) {
    if($index >= $max_history) {
      $wpdb->delete( $wpdb->postmeta, array( 'meta_id' => $design->meta_id ), array( '%d' ) );
    }
  }
}
function cleanISDSite() {
  global $wpdb;
  $isdPost = $wpdb->get_results( "SELECT * FROM {$wpdb->posts} WHERE post_type = 'isd'" );
  foreach($isdPost as $postIndex => $post) {
    cleanISDPostMeta($post->ID);
  }
}