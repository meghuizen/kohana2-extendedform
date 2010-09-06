/**
 * @author Michiel
 */

var extendedForm = {
	hasjQuery: (typeof(jQuery) != 'undefined' && jQuery),
	formdata: null,
	
	setupForm: function (cformdata) {
		this.formdata = cformdata;
		
		if (this.formdata.length > 0)
		for (objectid in this.formdata) {
			var cobject = document.getElementById(objectid);
			
			if (typeof(cobject == 'undefined') || cobject == null)
				continue;
			
			var cobjectdata = this.formdata[objectid];
			
			if (typeof(cobjectdata.required != 'undefined') && cobjectdata.required)
				cobject.required = true;
		}
		
		if (this.hasjQuery) {
			//set default submit button on the form when pressing on the return key, to the first one with the class .default
			jQuery('form input, form select').live('keypress', function (e) {
				var mybuttons = jQuery(this).parents('form:first').find('button[type=submit].default, input[type=submit].default');
		        if (mybuttons.length <= 0 || jQuery(this).attr('type') == 'button' || jQuery(this).attr('type') == 'submit')
		            return true;
		
		        if ((e.which && e.which == 13) || (e.keyCode && e.keyCode == 13)) {
					if (mybuttons.length > 1)
						mybuttons = mybuttons.eq(0);
					
		            mybuttons.click();
					
		            return false;
		        } else {
		            return true;
		        }
		    });
			
			//see: http://plugins.jquery.com/project/aphanumeric
			if (typeof(jQuery.fn.numeric) != 'undefined') {
				//set all input fields with int class, to allow only numeric characters, with no exception
				jQuery('input.int, input.integer').numeric();
				
				//set all input fields with float class, to allow only numeric characters, with exception for the (dot) and the (comma) characters
				jQuery('input.float, input.double, input[type=numeric]').numeric({
					allow: '.,'
				});
			}
			
			//see: http://digitalbush.com/projects/masked-input-plugin
			if (typeof(jQuery.fn.mask) != 'undefined') {
				//set all input fields with date class, to enable date mask
				jQuery('input.date, input[type=date]').mask('99-99-9999');
			}
			
			//see: http://jqueryui.com/
			if (typeof(jQuery.fn.datepicker) != 'undefined') {
				//set all input fields with date class, to enable data picket
				jQuery('input.date, input[type=date]').datepicker({ minDate: new Date(1900, 1 - 1, 1), dateFormat: 'dd-mm-yy' });
			}
			
			//see: http://bassistance.de/jquery-plugins/jquery-plugin-validation/
			if (typeof(jQuery.fn.validate) != 'undefined') {
				jQuery('form').each(function () {
					jQuery(this).validate();
				});
				
				jQuery('input[required], select[required], textarea[required], input.required, select.required, textarea.required').each(function () {
					jQuery(this).rules('add', {
						required: true
					});
				});
				
				jQuery('input[type=url], input.url').each(function () {
					jQuery(this).rules('add', {
						url: true
					});
				});
				jQuery('input[type=email], input.email').each(function () {
					jQuery(this).rules('add', {
						email: true
					});
				});
			}
		}
	},
	
	validate: function () {
		return true;
	},
	
	getErrors: function () {
		return [];
	}
};

if (typeof window.extendedForm == 'undefined') {
	window.extendedForm = extendedForm;
}
