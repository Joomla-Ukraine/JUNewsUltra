/**
 * Main JavaScript file
 *
 * @package         NoNumber Framework
 * @version         13.11.9
 *
 * @author          Peter van Westen <peter@nonumber.nl>
 * @link            http://www.nonumber.nl
 * @copyright       Copyright © 2013 NoNumber All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

(function($)
{
	if (typeof( window['nnScripts'] ) == "undefined") {
		$(document).ready(function()
		{
			// remove all empty control groups
			$('div.control-group > div').each(function(i, el)
			{
				if ($(el).html().trim() == '') {
					$(el).remove();
				}
			});
			$('div.control-group').each(function(i, el)
			{
				if ($(el).html().trim() == '') {
					$(el).remove();
				}
			});
			$('div.control-group > div.hide').each(function(i, el)
			{
				$(el).parent().css('margin', 0);
			});
		});

		nnScripts = {
			loadajax: function(url, succes, fail, query, timeout, dataType)
			{
				if (url.substr(0, 9) != 'index.php') {
					url = url.replace('http://', '');
					url = 'index.php?nn_qp=1&url=' + escape(url);
					if (timeout) {
						url += '&timeout=' + timeout;
					}
				}
				dt = dataType ? dataType : '';
				$.ajax({
					type: 'post',
					url: url,
					dataType: dt,
					success: function(data)
					{
						if (succes) {
							eval(succes + ';');
						}
					},
					error: function(data)
					{
						if (fail) {
							eval(fail + ';');
						}
					}
				});
			},

			displayVersion: function(data, extension, version, is_pro)
			{
				if (!data) {
					return;
				}

				var xml = nnScripts.getObjectFromXML(data);

				if (!xml) {
					return;
				}

				if (typeof(xml[extension]) == 'undefined') {
					return;
				}

				dat = xml[extension];

				if (!dat || typeof(dat['version']) == 'undefined' || !dat['version']) {
					return;
				}

				var new_version = dat['version'];
				var compare = nnScripts.compareVersions(version, new_version);

				if (compare != '<') {
					return;
				}

				el = $('#nonumber_newversionnumber_' + extension);
				if (el) {
					el.text(new_version);
				}
				el = $('#nonumber_version_' + extension);
				if (el) {
					el.css('display', 'block');
				}
			},

			toggleSelectListSelection: function(id)
			{
				var el = document.getElement('#' + id);
				if (el && el.options) {
					for (var i = 0; i < el.options.length; i++) {
						if (!el.options[i].disabled) {
							el.options[i].selected = !el.options[i].selected;
						}
					}
				}
			},

			toggleSelectListSize: function(id)
			{
				var link = document.getElement('#toggle_' + id);
				var el = document.getElement('#' + id);
				if (link && el) {
					if (!el.getAttribute('rel')) {
						el.setAttribute('rel', el.getAttribute('size'));
					}
					if (el.getAttribute('size') == el.getAttribute('rel')) {
						el.setAttribute('size', ( el.length > 100 ) ? 100 : el.length);
						link.getElement('span.show').setStyle('display', 'none');
						link.getElement('span.hide').setStyle('display', 'inline');
						if (typeof( window['nnToggler'] ) != "undefined") {
							nnToggler.autoHeightDivs();
						}
					} else {
						el.setAttribute('size', el.getAttribute('rel'));
						link.getElement('span.hide').setStyle('display', 'none');
						link.getElement('span.show').setStyle('display', 'inline');
					}
				}
			},

			in_array: function(needle, haystack, casesensitive)
			{
				if ({}.toString.call(needle).slice(8, -1) != 'Array') {
					needle = [needle];
				}
				if ({}.toString.call(haystack).slice(8, -1) != 'Array') {
					haystack = [haystack];
				}

				for (var h = 0; h < haystack.length; h++) {
					for (var n = 0; n < needle.length; n++) {
						if (casesensitive) {
							if (haystack[h] == needle[n]) {
								return true;
							}
						} else {
							if (haystack[h].toLowerCase() == needle[n].toLowerCase()) {
								return true;
							}
						}
					}
				}
				return false;
			},

			getObjectFromXML: function(xml)
			{
				if (!xml) {
					return;
				}

				var obj = [];
				$(xml).find('extension').each(function()
				{
					el = [];
					$(this).children().each(function()
					{
						el[this.nodeName.toLowerCase()] = String($(this).text()).trim();
					});
					if (typeof(el.alias) !== 'undefined') {
						obj[el.alias] = el;
					}
					if (typeof(el.extname) !== 'undefined' && el.extname != el.alias) {
						obj[el.extname] = el;
					}
				});

				return obj;
			},

			compareVersions: function(num1, num2)
			{
				num1 = num1.split('.');
				num2 = num2.split('.');

				var let1 = '';
				var let2 = '';

				max = Math.max(num1.length, num2.length);
				for (var i = 0; i < max; i++) {
					if (typeof(num1[i]) == 'undefined') {
						num1[i] = '0';
					}
					if (typeof(num2[i]) == 'undefined') {
						num2[i] = '0';
					}

					let1 = num1[i].replace(/^[0-9]*(.*)/, '$1');
					num1[i] = num1[i].toInt();
					let2 = num2[i].replace(/^[0-9]*(.*)/, '$1');
					num2[i] = num2[i].toInt();

					if (num1[i] < num2[i]) {
						return '<';
					} else if (num1[i] > num2[i]) {
						return '>';
					}
				}

				// numbers are same, so compare trailing letters
				if (let2 && (!let1 || let1 > let2)) {
					return '>';
				} else if (let1 && (!let2 || let1 < let2 )) {
					return '<';
				} else {
					return '=';
				}
			},

			setRadio: function(id, value)
			{
				value = value ? 1 : 0;
				document.getElements('input#jform_' + id + value + ',input#jform_params_' + id + value + ',input#advancedparams_' + id + value).each(function(el)
				{
					el.click();
				});
			},

			setToggleTitleClass: function(input, value)
			{
				el = $(input);
				el = el.parent().parent().parent().parent();

				el.removeClass('alert-success').removeClass('alert-error');
				if (value === 2) {
					el.addClass('alert-error');
				} else if (value) {
					el.addClass('alert-success');
				}
			}
		}
	}

	$(document).ready().delay(1000, function()
	{
		$('.btn-group.nn_btn-group label').click(function()
		{
			var label = $(this);
			var input = $('#' + label.attr('for'));

			label.closest('.btn-group').find('label').removeClass('active btn-success btn-danger btn-primary');
			if (input.val() == '' || input.val() == -2) {
				label.addClass('active btn-primary');
			} else if (input.val() == -1) {
				label.addClass('active');
			} else if (input.val() == 0) {
				label.addClass('active btn-danger');
			} else {
				label.addClass('active btn-success');
			}
			input.prop('checked', true);
		});
		$('.btn-group.nn_btn-group input[checked=checked]').each(function()
		{
			$('label[for=' + $(this).attr('id') + ']').removeClass('active btn-success btn-danger btn-primary');
			if ($(this).val() == '' || $(this).val() == -2) {
				$('label[for=' + $(this).attr('id') + ']').addClass('active btn-primary');
			} else if ($(this).val() == -1) {
				$('label[for=' + $(this).attr('id') + ']').addClass('active');
			} else if ($(this).val() == 0) {
				$('label[for=' + $(this).attr('id') + ']').addClass('active btn-danger');
			} else {
				$('label[for=' + $(this).attr('id') + ']').addClass('active btn-success');
			}
		});
	})
})(jQuery);