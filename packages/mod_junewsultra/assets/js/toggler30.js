/**
 * JavaScript file for Element: Toggler
 * Adds slide in and out functionality to elements based on an elements value
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
	if (typeof( window['nnToggler'] ) == "undefined") {

		$(document).ready(function()
		{
			if ($('.nntoggler').length) {
				nnToggler.initialize();
			} else {
				// Try again 2 seconds later, because IE sometimes can't see object immediately
				$(function()
				{
					if ($('.nntoggler').length) {
						nnToggler.initialize();
					}
				}).delay(2000);
			}
		});

		nnToggler = {
			togglers: {}, // holds all the toggle areas
			elements: {}, // holds all the elements and their values that affect toggle areas

			initialize: function()
			{
				this.togglers = $('.nntoggler');
				if (!this.togglers.length) {
					return;
				}

				this.initTogglers();
			},

			initTogglers: function()
			{
				var self = this;

				var new_togglers = {};

				$.each(this.togglers, function(i, toggler)
				{
					// init togglers
					if (toggler.id) {
						$(toggler).show();
						$(toggler).removeAttr('height');
						toggler.height = $(toggler).height();
						toggler.elements = {};
						toggler.nofx = $(toggler).hasClass('nntoggler_nofx');
						toggler.method = ( $(toggler).hasClass('nntoggler_and') ) ? 'and' : 'or';
						toggler.ids = toggler.id.split('___');
						for (var i = 1; i < toggler.ids.length; i++) {
							keyval = toggler.ids[i].split('.');

							key = keyval[0];
							val = 1;
							if (keyval.length > 1) {
								val = keyval[1];
							}

							if (typeof( toggler.elements[key] ) == "undefined") {
								toggler.elements[key] = [];
							}
							toggler.elements[key].push(val);

							if (typeof( self.elements[key] ) == "undefined") {
								self.elements[key] = {};
								self.elements[key].elements = [];
								self.elements[key].values = [];
								self.elements[key].togglers = [];
							}
							self.elements[key].togglers.push(toggler.id);
						}

						new_togglers[toggler.id] = toggler;
					}
				});
				this.togglers = new_togglers;
				new_togglers = null;

				this.setElements();

				// hide togglers that should be
				$.each(this.togglers, function(i, toggler)
				{
					self.toggleByID(toggler.id, 1);
				});

				$(document.body).delay(250).css('cursor', '');
			},

			autoHeightDivs: function()
			{
				// set all divs in the form to auto height
				$.each($('div.col div, div.fltrt div'), function(i, el)
				{
					if (el.getStyle('height') != '0px'
						&& !el.hasClass('input')
						&& !el.hasClass('nn_hr')
						// GK elements
						&& el.id.indexOf('gk_') === -1
						&& el.className.indexOf('gk_') === -1
						&& el.className.indexOf('switcher-') === -1
						) {
						el.css('height', 'auto');
					}
				});
			},

			toggle: function(el_name)
			{
				this.setValues(el_name);
				for (var i = 0; i < this.elements[el_name].togglers.length; i++) {
					this.toggleByID(this.elements[el_name].togglers[i]);
				}
				//this.autoHeightDivs();
			},

			toggleByID: function(id, nofx)
			{
				if (typeof( this.togglers[id] ) == "undefined") {
					return;
				}

				var toggler = this.togglers[id];

				var show = this.isShow(toggler);

				if (nofx || toggler.nofx) {
					if (show) {
						$(toggler).show();
					} else {
						$(toggler).hide();
					}
				} else {
					if (show) {
						$(toggler).slideDown();
					} else {
						$(toggler).slideUp();
					}
				}
			},

			isShow: function(toggler)
			{
				var show = ( toggler.method == 'and' );
				for (el_name in toggler.elements) {
					var vals = toggler.elements[el_name];
					var values = this.elements[el_name].values;
					if (values != null && values.length && ( ( vals == '*' && values != '' ) || nnScripts.in_array(vals, values) )) {
						if (toggler.method == 'or') {
							show = 1;
							break;
						}
					} else {
						if (toggler.method == 'and') {
							show = 0;
							break;
						}
					}
				}

				return show;
			},

			setValues: function(el_name)
			{
				var els = this.elements[el_name].elements;

				var values = [];
				// get value
				$.each(els, function(i, el)
				{
					switch (el.type) {
						case 'radio':
						case 'checkbox':
							if (el.checked) {
								values.push(el.value);
							}
							break;
						default:
							if (typeof( el.elements ) != "undefined" && el.elements.length > 1) {
								for (var i = 0; i < el.elements.length; i++) {
									if (el.checked) {
										values.push(el.value);
									}
								}
							} else {
								values.push(el.value);
							}
							break;
					}
				});
				this.elements[el_name].values = values;
			},

			setElements: function()
			{
				var self = this;
				$.each($('input, select'), function(i, el)
				{
					el_name = el.name.replace('@', '_').replace('[]', '').replace(/(?:jform\[params\]|jform|params|advancedparams)\[(.*?)\]/g, '\$1').trim();
					if (el_name !== '') {
						if (typeof( self.elements[el_name]) != "undefined") {
							self.elements[el_name].elements.push(el);
							self.setValues(el_name);
							self.setElementEvents(el, el_name);
						}
					}
				});
			},

			setElementEvents: function(el, el_name)
			{
				var self = this;
				var type;
				if (typeof( el.type ) == "undefined") {
					if ($(el).prop("tagName") == 'select') {
						type = 'select';
					}
				} else {
					type = el.type;
				}

				var func = function() { self.toggle(el_name); };

				switch (type) {
					case 'radio':
					case 'checkbox':
						$(el).bind('click', func).bind('keyup', func);
						break;
					case 'select':
					case 'select-one':
					case 'text':
						$(el).bind('change', func).bind('keyup', func);
						break;
					default:
						$(el).bind('change', func);
						break;
				}
			}
		}
	}
})(jQuery);