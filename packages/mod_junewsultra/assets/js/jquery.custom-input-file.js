/**
 * jQuery Custom Input File Plugin (
 * http://rozhdestvenskiy.ru/projects/jquery-custom-input-file/ ) Copyright (c)
 * Roman Rozhdestvenskiy ( sbmaxx@gmail.com )
 */
"use strict";
/*global jQuery, console*/
/*jslint white: true, browser: true, undef: true, nomen: true, eqeqeq: true, plusplus: true, bitwise: true, regexp: true, newcap: true, immed: true, strict: true */

(function ($) {

    function log(msg) {
        if (console && console.log) {
            console.log("custom file input: " + msg);
        }
    }
    function getElementCoordinates(el) {
        var offset = $(el).offset();

        return {
            left : offset.left,
            right : el.offsetLeft + el.offsetWidth,
            top : offset.top,
            bottom : el.offsetTop + el.offsetHeight
        };
    }

    function getId() {
        if (typeof (getId.id) === "undefined") {
            getId.id = 0;
        }
        return ++getId.id;
    }

    $.fn.customInputFile = function (settings) {
        var config = {
            replacementClass       : "customInputFile",
            replacementClassHover  : "customInputFileHover",
            replacementClassActive : "customInputFileActive",
            filenameClass          : "customInputFileName",
            wrapperClass           : "customInputFileWrapper",
            replacement : $('<div />', {
                "text" : "upload",
                "class": "customInputFile"
            }),
            filename : $('<div />', {
                "class": "customInputFileName"
            }),
            inputCss: {
                fontSize : '600px',
                position : "absolute",
                right : 0,
                margin : 0,
                padding : 0
            },
            changeCallback: $.noop
        };

        if (settings) {
            $.extend(config, settings);
        }
        var $input,
            $replacement = $(config.replacement),
            $filename = $(config.filename),
            currentId = 0;

        function toggleVisibility(value) {
            $input.parent().css('visibility', value ? "visible" : "hidden");
        }
        function toggleHoverClass(value) {
            $replacement.toggleClass(config.replacementClassHover, value);
        }
        function toggleActiveClass(value) {
            $replacement.toggleClass(config.replacementClassActive, value);
        }

        function bind() {
            function onChange() {
                var val = $(this).val().replace(/.*(\/|\\)/, "");

                if ($filename.get(0).nodeName.toUpperCase() === 'INPUT') {
                    $filename.val(val);
                } else {
                    $filename.text(val);
                }

                if (val) {
                    $filename.show();
                } else {
                    $filename.hide();
                }
                toggleActiveClass(val !== '');
                config.changeCallback(val);
            }
            function onClear() {
                var el    = $input.get(0);
                var attrs = {};
                for (var i = 0; i < el.attributes.length; i += 1) {
                    var attrib = el.attributes[i];
                    if (attrib.specified === true && attrib.name !== 'value') {
                        attrs[attrib.name] = attrib.value;
                    }
                }
                attrs.value = "";
                $input = $('<input />', attrs);
                $(this).replaceWith($input);

                $input.css(config.inputCss).bind('change', onChange).bind('clear', onClear).trigger('change');
                toggleActiveClass(false);
            }

            $input.bind('change', onChange).bind('clear', onClear);
        }

        function createWrapper() {
            var cord = getElementCoordinates($replacement);
            var wrap = $('<div />', {
                css : {
                    position : "absolute",
                    visibility : "hidden",
                    overflow : "hidden",
                    zIndex : 10000000,
                    opacity : 0,
                    left : cord.left,
                    top : cord.top,
                    width : $replacement.get(0).offsetWidth,
                    height : $replacement.get(0).offsetHeight,
                    margin : 0,
                    padding : 0,
                    direction : "ltr"
                }
            })
            .addClass(config.wrapperClass)
            .bind('mouseout', function () {
                toggleVisibility(false);
                toggleHoverClass(false);
            });

            $input.wrap(wrap).css(config.inputCss);
        }

        function createFileName() {
            try {
                if (! jQuery.contains(document.body, $filename.get(0))) {
                    throw ("not found");
                }
            } catch (e) {
                if (! $filename.length) {
                    throw ("filename is empty");
                }
                $replacement.after($filename);
            }
            $filename.addClass(config.filenameClass + '-' + currentId);
        }

        function replace() {
            $replacement
                .addClass(config.replacementClass + '-' + currentId)
                .insertBefore($input)
                .bind('mouseenter', function () {
                    toggleVisibility(true);
                    toggleHoverClass(true);
                })
                .bind('init', function () {
                    createWrapper();
                    createFileName();
                })
                .trigger('init');
        }

        function init(el) {
            currentId = getId();
            $input = $(el);

            bind();
            replace();
        }

        this.each(function () {
            try {
                init(this);
            } catch (e) {
                log(e);
            }
        });
        return this;
    };
}(jQuery));