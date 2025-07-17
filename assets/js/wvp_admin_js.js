/***** enable sub product only for simple product ******/

jQuery(document).on("change", "#product-type", function() {
  if( jQuery("#product-type").val() === 'simple' ) {
    jQuery("#wvp_subprod_isenable").removeAttr("disabled", "disabled");
  } else {
    jQuery("#wvp_subprod_isenable").attr("disabled", "disabled");
  }
});


/***** Select defaults *****/
// jQuery(document).on("change", ".sub_item", function() {
//   jQuery(".wvp_sub_item").hide();
//   jQuery("input[type='radio'].sub_item:checked").each( function() {
//     var div_class = jQuery(this).val();
//     var sub_pid = jQuery(this).attr("data-sub_pid");
//     jQuery(".wvp_sp_id_"+sub_pid+"_"+div_class).show();
//   });
// });


/***** Select defaults *****/
jQuery(document).on("change", ".item", function() {
  var div_class = jQuery(this).attr("data-item");
  if( jQuery(this).is(":checked") ) {
    jQuery(".item-option."+div_class).show();
  } else {
    jQuery("."+div_class).hide();
    jQuery("."+div_class).find("input:radio").each( function() {
      jQuery(this).prop("checked", false);
    });
  }
});


/***** Used in Add New Sub Product start *****/

jQuery(document).on("click", ".wvp_add_part", function() {
	var part_name = jQuery(".part_name").val();
	if( part_name != '' ) {
		var html = jQuery("#sps_setting_table").html();
		var html_content = html.replace(/{wvp_part_name}/g, part_name);
		jQuery(".wvp_parts_name").append( html_content );
	}
	jQuery(".part_name").val('');
});

jQuery(document).on("click", ".wvp_part_remove", function() {
	jQuery(this).parents(".wvp_part_name").remove();
});



/***** Used in Background for upload image start *****/

jQuery(document).ready( function($) {
    function ct_media_upload(button_class) {
        var _custom_media = true,
        _orig_send_attachment = wp.media.editor.send.attachment;
        $('body').on('click', button_class, function(e) {
          	var button_id = '#'+$(this).attr('id');
            var hidden_id = '#'+$(this).attr('data-hidden');
            var image_wrapper_id = '#'+$(this).attr('data-image-wrapper');
           	var send_attachment_bkp = wp.media.editor.send.attachment;
           	var button = $(button_id);
           	_custom_media = true;
           	wp.media.editor.send.attachment = function(props, attachment){
             	if ( _custom_media ) {
               		$(hidden_id).val(attachment.id);
               		$(image_wrapper_id).html('<img class="custom_media_image" src="" style="margin:0;padding:0;max-height:100px;float:none;" />');
               		$(image_wrapper_id+' .custom_media_image').attr('src',attachment.url).css('display','block');
             	} else {
               		return _orig_send_attachment.apply( button_id, [props, attachment] );
             	}
            }
        	wp.media.editor.open(button);
        	return false;
     	});
    }
    ct_media_upload('.wvp_bg_media_button.button'); 
    $('body').on('click','.wvp_bg_media_remove',function(){
        var hidden_id = '#'+$(this).attr('data-hidden');
        var image_wrapper_id = '#'+$(this).attr('data-image-wrapper');
       	$(hidden_id).val('');
       	$(image_wrapper_id).html('<img class="custom_media_image" src="" style="margin:0;padding:0;max-height:100px;float:none;" />');
    });
    $(document).ajaxComplete(function(event, xhr, settings) {
       	var queryStringArr = settings.data.split('&');
       	if( $.inArray('action=add-tag', queryStringArr) !== -1 ){
         	var xml = xhr.responseXML;
         	$response = $(xml).find('term_id').text();
         	if($response!=""){
           		// Clear the thumb image
           		$('#wvp_bg_image_wrapper').html('');
         	}
       	}
    });
});
 

/***** Used in part color for get sub products *****/

jQuery(document).on("change", ".wvp_wc_product_ids", function() {

    var product_ids = [];
    jQuery(".wvp_wc_product_ids:checked").each(function(){
        product_ids.push(jQuery(this).val());
    });
    
    var ajax_data = {};
    ajax_data['action'] = 'wvp_get_sub_product';
    ajax_data['wvp_wc_product_ids'] = product_ids;

    if( jQuery("#post_ID").length > 0 ) {
        ajax_data['post_id'] = jQuery("#post_ID").val();
    }
    
    jQuery.ajax({
        method: "POST",
        url: ajaxurl,
        data: ajax_data, 
        dataType: "json",
        beforeSend: function() {
        },
        success: function(data) {
            if( data.status == 'error' ) {
                jQuery(".wvp_sp_version").hide();
                jQuery(".wvp_item_parts").hide();
            } else {
                jQuery(".wvp_sp_version").show();
                jQuery(".wvp_item_parts").show();
            }
            jQuery(".wvp_sub_product_row").replaceWith(data.message);
        }
    });
})

jQuery(document).on("change", ".wvp_sub_product_id", function() {

    var sub_product_id = jQuery(this).val();

    // Get sub product versions
    var ajax_data = {};
    ajax_data['action'] = 'wvp_get_sub_product_version';
    ajax_data['wvp_sub_product_id'] = sub_product_id;
    
    jQuery.ajax({
        method: "POST",
        url: ajaxurl,
        data: ajax_data, 
        dataType: "json",
        beforeSend: function() {
        },
        success: function(data) {
            jQuery(".wvp_sp_version").replaceWith(data.message);
            jQuery(".wvp_sp_version").show();
        }
    });


    // Get Item Parts
    var ajax_data = {};
    ajax_data['action'] = 'wvp_get_item_parts';
    ajax_data['wvp_sub_product_id'] = sub_product_id;
    
    jQuery.ajax({
        method: "POST",
        url: ajaxurl,
        data: ajax_data, 
        dataType: "json",
        beforeSend: function() {
        },
        success: function(data) {
            jQuery(".wvp_item_parts").replaceWith(data.message);
            jQuery(".wvp_item_parts").show();
        }
    });
});