var isd = jQuery.noConflict();
isd(document).ready(function($){
    $('.isd_list_cate li').click(function(){
        if(!$(this).hasClass('isd_ct_cr')){
            var cat = $(this).attr('ct');
            if(cat == 'all'){
              $('#isd_list_collection > div').slideDown(500);
            }else{
              $('.isd_list_cate li.isd_ct_cr').removeClass('isd_ct_cr');
              $(this).addClass('isd_ct_cr');
              $('#isd_list_collection > div').each(function(){
                  if($(this).hasClass(cat)){
                      $(this).slideDown(500);
                  }else{
                      $(this).slideUp(500);
                  }
              })
            }
        }
    })
    $('.isd_close_bt, .isd_overlay').click(function(){
        $('#isd_collection').slideUp(500);
    });
    $('#isd_from_template').click(function(){
        $('#isd_collection').slideDown(500);
    });
    $('.ui.accordion').accordion();
    $('.ui.checkbox').checkbox();
})
