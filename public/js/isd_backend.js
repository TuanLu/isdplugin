var isdBackend = jQuery.noConflict();
isdBackend(document).ready(function($){
  let ISDBackend = {
    setHomepage(id, e) {
      if(!id) return false;
      $.ajax({
        data: {
          designID : id,
          action : 'isd_sethomepage'
        },
        type: 'post',
        url : $('#isd_blog_url').val() + '/wp-admin/admin-ajax.php',
        beforeSend: function() {
          $(e.target).parent().find('.loader').remove();
          $(e.target).parent().append('<div class="ui active loader"></div>');
        }
      }).done(function(data) {
        // If successful
        try {
          if(!data) {
            console.log('error');
            return;
          }
          var dataResponse = JSON.parse(data);
          if(dataResponse.status == "success") {
            $(e.target).parent().find('.loader').remove();
          } else {
            console.log('error');
          }
        } catch(error) {
          console.log('error');
          console.info(error);
        }
      }).fail(function(jqXHR, textStatus, errorThrown) {
          // If fail
          console.log(textStatus + ': ' + errorThrown);
      });
    },
    setDesignToTemplate(id, e) {
      if(!id) return false;
      $.ajax({
        data: {
          designID : id,
          action : 'isd_set_template'
        },
        type: 'post',
        url : $('#isd_blog_url').val() + '/wp-admin/admin-ajax.php',
        beforeSend: function() {
          $(e.target).parent().find('.loader').remove();
          $(e.target).parent().append('<div class="ui active loader"></div>');
        }
      }).done(function(data) {
        // If successful
        try {
          if(!data) {
            console.log('error');
            return;
          }
          var dataResponse = JSON.parse(data);
          if(dataResponse.status == "success") {
            $(e.target).parent().find('.loader').remove();
            //Remove checked radio
            if(dataResponse.data == 0) {
              $(e.target).parent().find('input[type="radio"]').attr('checked', false);
            }
          } else {
            console.log('error');
          }
        } catch(error) {
          console.log('error');
          console.info(error);
        }
      }).fail(function(jqXHR, textStatus, errorThrown) {
          // If fail
          console.log(textStatus + ': ' + errorThrown);
      });
    },
    duplicatePage(id, e) {
      if(!id) return false;
      $.ajax({
        data: {
          designID : id,
          action : 'isd_duplicate_page'
        },
        type: 'post',
        url : $('#isd_blog_url').val() + '/wp-admin/admin-ajax.php',
        beforeSend: function() {
          $(e.target).parent().find('.loader').remove();
          $(e.target).parent().append('<div class="ui active loader"></div>');
        }
      }).done(function(data) {
        // If successful
        try {
          if(!data) {
            console.log('error');
            return;
          }
          var dataResponse = JSON.parse(data);
          if(dataResponse.status == "success") {
            $(e.target).parent().find('.loader').remove();
            window.location.reload();
          } else {
            console.log('error');
          }
        } catch(error) {
          console.log('error');
          console.info(error);
        }
      }).fail(function(jqXHR, textStatus, errorThrown) {
          // If fail
          console.log(textStatus + ': ' + errorThrown);
      });
    },
    deleteDesign(id, e) {
      if(!id) return false;
      if(!confirm("Are you sure you want to delete this page?")) {
        return false;
      }
      $.ajax({
        data: {
          designID : id,
          action : 'isd_deletedesign'
        },
        type: 'post',
        url : $('#isd_blog_url').val() + '/wp-admin/admin-ajax.php',
        beforeSend: function() {
          $(e.target).addClass('loading');
        }
      }).done(function(data) {
        // If successful
        try {
          if(!data) {
            console.log('error');
            return;
          }
          var dataResponse = JSON.parse(data);
          if(dataResponse.status == "success") {
            $(e.target).removeClass('loading');
            $(e.target).closest('li').fadeOut();
          } else {
            console.log('error');
          }
        } catch(error) {
          console.log('error');
          console.info(error);
        }
      }).fail(function(jqXHR, textStatus, errorThrown) {
          // If fail
          console.log(textStatus + ': ' + errorThrown);
      });
    },
    downloadDesign(id, e) {
      if(!id) return false;
      $.ajax({
        data: {
          designID : id,
          action : 'isd_download_design'
        },
        type: 'post',
        url : $('#isd_blog_url').val() + '/wp-admin/admin-ajax.php',
        beforeSend: function() {
          $(e.target).addClass('loading');
        }
      }).done(function(data) {
        // If successful
        try {
          if(!data) {
            console.log('error');
            return;
          }
          var dataResponse = JSON.parse(data);
          if(dataResponse.status == "success") {
            $(e.target).removeClass('loading');
            window.location.href = dataResponse.url;
          } else {
            console.log('error');
          }
        } catch(error) {
          console.log('error');
          console.info(error);
        }
      }).fail(function(jqXHR, textStatus, errorThrown) {
          // If fail
          console.log(textStatus + ': ' + errorThrown);
      });
    },
    updateApp(e) {
      if($(e.target).hasClass('loading')) return false;
      let defaultMessage = 'Something went wrong! Can not update iShopDesign plugin!';
      $.ajax({
        data: {
          action : 'isd_updateapp'
        },
        type: 'post',
        url : $('#isd_blog_url').val() + '/wp-admin/admin-ajax.php',
        beforeSend: function() {
          $(e.target).addClass('loading');
        }
      }).done(function(data) {
        // If successful
        try {
          if(!data) {
            console.log('error');
            return;
          }
          var dataResponse = JSON.parse(data);
          if(dataResponse.status == "success") {
            $('.isd-version-info').fadeOut('slow');
          } else {
            alert(dataResponse.message || defaultMessage);
            console.log('error');
          }
          $(e.target).removeClass('loading');
        } catch(error) {
          console.log('error');
          alert(defaultMessage);
          $(e.target).removeClass('loading');
          console.info(error);
        }
      }).fail(function(jqXHR, textStatus, errorThrown) {
          // If fail
          alert(defaultMessage);
        $(e.target).removeClass('loading');
          console.log(textStatus + ': ' + errorThrown);
      });
    },
    activeLicenseKey: function(e) {
      var userKey = $("#isd_license_key").val();
      if(userKey == "") {
         alert('Please enter the license key');
      } else {
        if($(e.target).hasClass('loading')) return false;
        $.ajax({
          data: {
            action : 'isd_active_key',
            license_key: userKey
          },
          type: 'post',
          url : $('#isd_blog_url').val() + '/wp-admin/admin-ajax.php',
          beforeSend: function() {
            $(e.target).addClass('loading');
          }
        }).done(function(data) {
          // If successful
          try {
            if(!data) {
              console.log('error');
              return;
            }
            var dataResponse = JSON.parse(data);
            if(dataResponse.status == "success") {
              $(e.target).removeClass('loading');
              $("#isd_to_manage_page").css('visibility', 'visible');
            } else {
              $(e.target).removeClass('loading');
              $("#isd_to_manage_page").css('visibility', 'hidden');
              alert(dataResponse.message);
            }
            if(dataResponse.message) {
              $(".license-active-info").html(dataResponse.message);
            }
          } catch(error) {
            console.log('error');
            alert('Something went wrong! Can not active key for this site! Please contact ishopdesign.com for more details!');
            console.info(error);
          }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            // If fail
            console.log(textStatus + ': ' + errorThrown);
        });
      }
    }
  }
  /** All action list here */
  $('.isd_list_page .ui.checkbox.toggle.isd_pagehome').click(function(e) {
    var pageID = $(e.target).parent().find('input[type="radio"]').val();
    ISDBackend.setHomepage(pageID, e);
  });
  $('.isd_list_page .ui.checkbox.toggle.isd_set_template').click(function(e) {
    var pageID = $(e.target).parent().find('input[type="radio"]').val();
    ISDBackend.setDesignToTemplate(pageID, e);
  });
  $('.duplicate_isd_page').click(function(e) {
    ISDBackend.duplicatePage($(this).attr('rel'), e);
  });
  $('.delete_isd_page').click(function(e) {
    ISDBackend.deleteDesign($(this).attr('rel'), e);
  });
  $('.download_isd_page').click(function(e) {
    ISDBackend.downloadDesign($(this).attr('rel'), e);
  });
  $("#isd_update_btn").click(function(e) {
    ISDBackend.updateApp(e);
  });
  $("#isd_active_license").click(function(e) {
    ISDBackend.activeLicenseKey(e);
  });
})
