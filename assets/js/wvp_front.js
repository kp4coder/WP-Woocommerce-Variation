jQuery(document).ready( function() {
	if( jQuery(".wvp_background").length > 0 ) {
		jQuery(".wvp_background").trigger('change');
		calculate_price();
	}

	jQuery("input[type='radio'].sub_item:checked").each( function() {
		select_sub_item( jQuery(this) );
	});
});

jQuery(document).on("change", ".wvp_background", function() {
	jQuery(".woocommerce-product-gallery .woocommerce-product-gallery__image").hide();
	jQuery(".woocommerce-product-gallery .woocommerce-product-gallery__image--placeholder").hide();
	jQuery(".wvp_image").css( "background-image", 'url("' + jQuery(".wvp_background:checked").attr("data-item") + '")' );
	jQuery(".wvp_image").show();
});

jQuery(document).on("change", ".item", function() {
	var div_class = jQuery(this).attr("data-item");
	if( jQuery(this).is(":checked") ) {
		jQuery(".item-option.wvp_sub_pid_"+div_class).show();
		jQuery(".item-option.wvp_sub_pid_"+div_class).each( function() {
			select_color( jQuery(this).find(".item_component:radio:checked") );
		});
	} else {
		jQuery(".wvp_sub_pid_"+div_class).hide();
	}
	calculate_price();
});

jQuery(document).on("change", "input[type='radio'].sub_item", function() {
	select_sub_item( jQuery(this) );
});

jQuery(document).on("change", "input[type='radio'].item_component", function() {
	select_color( jQuery(this) );
});

function select_sub_item( elem ) {

	var spv_id = elem.attr("data-item");
	var sub_pid = elem.attr("data-sub_pid");

	jQuery(".wvp_option_desc.wvp_sub_pid_desc_"+sub_pid+" p").hide();
	jQuery(".wvp_option_desc.wvp_sub_pid_desc_"+sub_pid+" p.wvp_spv_desc_"+spv_id).show();

	jQuery(".item-option-color.wvp_sub_pid_"+sub_pid).each( function() {
		jQuery(this).find(".lbl_item_componenet").hide();
		jQuery(this).find(".wvp_spv_"+spv_id).css('display', 'inline-block');

		if(jQuery(this).find(".wvp_spv_"+spv_id+" .item_component:radio:checked").length > 0) {
			select_color( jQuery(this).find(".wvp_spv_"+spv_id+" .item_component:radio:checked") );
		} else {
			// console.log(jQuery(this).find(".wvp_spv_"+spv_id+" .item_component:radio").val());
			jQuery(this).find(".wvp_spv_"+spv_id+" .item_component:radio").attr("checked", "checked");
			select_color( jQuery(this).find(".wvp_spv_"+spv_id+" .item_component:radio") );
		}

	});

	calculate_price();
}

function select_color( elem ) {

	var item = elem.attr("data-item");
	var color = elem.attr("id");
	var sub_pid = elem.attr("data-sub_pid");
	var spv_id = jQuery("input[type='radio'][data-sub_pid='"+sub_pid+"'].sub_item:checked").val();

	jQuery('.wvp_item_'+item).hide();
	jQuery('.wvp_item_'+item+'.wvp_sp_version_'+spv_id+'.'+color).show();

	jQuery(".wvp_item_color_"+item).removeClass("wvp_active");
	elem.parent().addClass("wvp_active");
}

function calculate_price() {

	// get currency sysmbol
	var newSpan = document.createElement('span');
	newSpan.textContent = jQuery(".woocommerce-Price-currencySymbol").html();
    newSpan.setAttribute('class', 'woocommerce-Price-currencySymbol');


	let regular_price = parseFloat( jQuery("#regular_price").val() );
	let price = parseFloat( jQuery("#price").val() );
	jQuery("input[type='radio'].sub_item:checked").each( function() {
		var sub_id = jQuery(this).attr('data-sub_pid');

		if( jQuery("input[type='checkbox'][data-item='"+sub_id+"'].item").is(":checked") ) {
			regular_price = regular_price + parseFloat( jQuery(this).attr("data-price") );
			price = price + parseFloat( jQuery(this).attr("data-price") );
		}
	});

	jQuery("#wvp_post").val(price);

	if( parseFloat( jQuery("#sale_price").val() ) > 0 ) {
		jQuery(".price del span.amount bdi").html(newSpan.outerHTML + regular_price);
		jQuery(".price ins span.amount bdi").html(newSpan.outerHTML + price);
	} else {
		jQuery(".price span.amount bdi").html(newSpan.outerHTML + price);
	}
}

function setCookie( cname, cvalue, exdays = 1 ) {
  var d = new Date();
  d.setTime(d.getTime() + (exdays*24*60*60*1000));
  var expires = "expires="+ d.toUTCString();
  document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}