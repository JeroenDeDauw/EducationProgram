/*
 * Adaptation of Bootstrap Tags Input library - Javascript
 * @see https://github.com/timschlechter/bootstrap-tagsinput
 * @see http://timschlechter.github.io/bootstrap-tagsinput/examples/
 *
 * @license MIT
 *
 * Included in the the Education Program MediaWiki extension.
 * @see https://www.mediawiki.org/wiki/Extension:Education_Program
 *
 * Changes from upstream:
 * - Changed style names and some default style.
 * - Disabled left-and-right arrows because they break typeahead.js's
 *   autocomplete.
 * - Improved automatic adjustment of input area for multiple lines
 *   of tags.
 * - Added automatic generation of tags after pasting a comma-,
 *   newline- or tab-separated list. This involved changing
 *   the input element to a textarea.
 * - Added addTagFilter option, for providing a callback that
 *   modifies tags before they are added.
 * - Trigger itemRemoved event only when an item is really removed.
 * - Changed tag display to inline-block.
 * - Fixed html escaping of multiple spaces for correct display
 *   of tab labels with multiple spaces in Chrome.
 * - Miscelaneous CSS tweaks.
 * - Superficial changes for JSLint.
 *
 * Upstream: https://raw.github.com/TimSchlechter/bootstrap-tagsinput/a1e07e3da271cadee58aa888c0721ba56cc777f7/dist/bootstrap-tagsinput.js
 */

// TODO add up- and down-arrow navigation, undo with control-Z

(function($) {
  "use strict";

  var defaultOptions = {
    tagClass: function(item) {
      return 'ep-tagsinfo-label';
    },
    itemValue: function(item) {
      return item ? item.toString() : item;
    },
    itemText: function(item) {
      return this.itemValue(item);
    },
    freeInput: true,
    maxTags: undefined,
    confirmKeys: [13],
    confirmWithEmptyInputKeys: [13],
    onTagExists: function(item, $tag) {
      $tag.hide().fadeIn();
    }
  };

  /**
   * Constructor function
   */
  function TagsInput(element, options) {
    this.itemsArray = [];

    this.$element = $(element);
    this.$element.hide();

    this.isSelect = (element.tagName === 'SELECT');
    this.multiple = (this.isSelect && element.hasAttribute('multiple'));
    this.objectItems = options && options.itemValue;

    this.$container = $('<div class="ep-tagsinput"></div>');
    this.$input = $('<textarea wrap="off" rows="1" size="1" type="text" />').appendTo(
        this.$container);
    this.$textmeter = $('<div class="ep-tagsinput-textmeter" style="left: -9999px; visibility: hidden; position: absolute; white-space: nowrap" />')
      .appendTo(this.$container);

    this.$element.after(this.$container);

    this.lastInputVal = '';

    this.build(options);
  }

  TagsInput.prototype = {
    constructor: TagsInput,

    /**
     * Adds the given item as a new tag. Pass true to dontPushVal to prevent
     * updating the elements val()
     */
    add: function(item, dontPushVal) {
      var self = this;

      if (self.options.maxTags && self.itemsArray.length >= self.options.maxTags)
        return;

      // Ignore falsey values, except false
      if (item !== false && !item)
        return;

      // call filter function if set
      if (self.options.addTagFilter) {
        item = self.options.addTagFilter(item);
      }

      // Throw an error when trying to add an object while the itemValue option was not set
      if (typeof item === "object" && !self.objectItems)
        throw("Can't add objects when itemValue option is not set");

      // Ignore strings only containg whitespace
      if (item.toString().match(/^\s*$/))
        return;

      // If SELECT but not multiple, remove current tag
      if (self.isSelect && !self.multiple && self.itemsArray.length > 0)
        self.remove(self.itemsArray[0]);

      if (typeof item === "string" && self.$element[0].tagName === 'INPUT') {
        var items = item.split(',');
        if (items.length > 1) {
          for (var i = 0; i < items.length; i++) {
            self.add(items[i], true);
          }

          if (!dontPushVal)
            self.pushVal();
          return;
        }
      }

      var itemValue = self.options.itemValue(item),
          itemText = self.options.itemText(item),
          tagClass = self.options.tagClass(item);

      // Ignore items already added
      var existing = $.grep(self.itemsArray, function(item) { return self.options.itemValue(item) === itemValue; } )[0];
      if (existing) {
        // Invoke onTagExists
        if (self.options.onTagExists) {
          var $existingTag = $(".ep-tagsinput-tag", self.$container).filter( function() { return $(this).data("item") === existing; });
          self.options.onTagExists(item, $existingTag);
        }
        return;
      }

      // register item in internal array and map
      self.itemsArray.push(item);

      // add a tag element
      var encodedItemText = htmlEncode(itemText);
      var $tag = $('<span class="ep-tagsinput-tag ' + htmlEncode(tagClass) + '">' +
          encodedItemText +
          '<span data-role="remove"></span></span>');
      $tag.data('item', item);
      self.findInputWrapper().before($tag);
      // $tag.after(' '); removed this line: don't add in whitespace, rather set margins

      // add <option /> if item represents a value not present in one of the <select />'s options
      if (self.isSelect && !$('option[value="' + escape(itemValue) + '"]', self.$element)[0]) {
        var $option = $('<option selected>' + encodedItemText + '</option>');
        $option.data('item', item);
        $option.attr('value', itemValue);
        self.$element.append($option);
      }

      if (!dontPushVal)
        self.pushVal();

      // Add class when reached maxTags
      if (self.options.maxTags === self.itemsArray.length)
        self.$container.addClass('ep-tagsinput-max');

      self.$element.trigger($.Event('itemAdded', { item: item }));

      self.refreshInputWidth();
    },

    /**
     * Removes the given item. Pass true to dontPushVal to prevent updating the
     * elements val()
     */
    remove: function(item, dontPushVal) {
      var self = this;

      if (self.objectItems) {
        if (typeof item === "object")
          item = $.grep(self.itemsArray, function(other) { return self.options.itemValue(other) == self.options.itemValue(item); } )[0];
        else
          item = $.grep(self.itemsArray, function(other) { return self.options.itemValue(other) == item; } )[0];
      }

      if (item) {
        $('.ep-tagsinput-tag', self.$container).filter(function() { return $(this).data('item') === item; }).remove();
        $('option', self.$element).filter(function() { return $(this).data('item') === item; }).remove();
        self.itemsArray.splice(self.itemsArray.indexOf(item), 1);
      }

      if (!dontPushVal)
        self.pushVal();

      // Remove class when reached maxTags
      if (self.options.maxTags > self.itemsArray.length)
        self.$container.removeClass('ep-tagsinput-max');

      self.refreshInputWidth();

      // only trigger event if we added an item
      if (item) {
        self.$element.trigger($.Event('itemRemoved', { item: item }));
      }
    },

    /**
     * Removes all items
     */
    removeAll: function() {
      var self = this;

      $('.ep-tagsinput-tag', self.$container).remove();
      $('option', self.$element).remove();

      while(self.itemsArray.length > 0)
        self.itemsArray.pop();

      self.pushVal();

      if (self.options.maxTags && !this.isEnabled())
        this.enable();

      self.refreshInputWidth();
    },

    /**
     * Refreshes the tags so they match the text/value of their corresponding
     * item.
     */
    refresh: function() {
      var self = this;
      $('.ep-tagsinput-tag', self.$container).each(function() {
        var $tag = $(this),
            item = $tag.data('item'),
            itemValue = self.options.itemValue(item),
            itemText = self.options.itemText(item),
            tagClass = self.options.tagClass(item);

          // Update tag's class and inner text
          $tag.attr('class', null);
          $tag.addClass('ep-tagsinput-tag ' + htmlEncode(tagClass));
          $tag.contents().filter(function() {
            return this.nodeType == 3;
          })[0].nodeValue = htmlEncode(itemText);

          if (self.isSelect) {
            var option = $('option', self.$element).filter(function() { return $(this).data('item') === item; });
            option.attr('value', itemValue);
          }
      });

      self.refreshInputWidth();
    },

    /**
     * Returns the items added as tags
     */
    items: function() {
      return this.itemsArray;
    },

    /**
     * Assembly value by retrieving the value of each item, and set it on the
     * element.
     */
    pushVal: function() {
      var self = this,
          val = $.map(self.items(), function(item) {
            return self.options.itemValue(item).toString();
          });

      self.$element.val(val, true).trigger('change');
    },

    /**
     * Initializes the tags input behaviour on the element
     */
    build: function(options) {
      var self = this;

      self.options = $.extend({}, defaultOptions, options);
      var typeahead = self.options.typeahead || {};

      // When itemValue is set, freeInput should always be false
      if (self.objectItems)
        self.options.freeInput = false;

      makeOptionItemFunction(self.options, 'itemValue');
      makeOptionItemFunction(self.options, 'itemText');
      makeOptionItemFunction(self.options, 'tagClass');

      // for backwards compatibility, self.options.source is deprecated
      if (self.options.source)
        typeahead.source = self.options.source;

      if (typeahead.source && $.fn.typeahead) {
        makeOptionFunction(typeahead, 'source');

        self.$input.typeahead({
          source: function (query, process) {
            function processItems(items) {
              var texts = [];

              for (var i = 0; i < items.length; i++) {
                var text = self.options.itemText(items[i]);
                map[text] = items[i];
                texts.push(text);
              }
              process(texts);
            }

            this.map = {};
            var map = this.map,
                data = typeahead.source(query);

            if ($.isFunction(data.success)) {
              // support for Angular promises
              data.success(processItems);
            } else {
              // support for functions and jquery promises
              $.when(data)
               .then(processItems);
            }
          },
          updater: function (text) {
            self.add(this.map[text]);
          },
          matcher: function (text) {
            return (text.toLowerCase().indexOf(this.query.trim().toLowerCase()) !== -1);
          },
          sorter: function (texts) {
            return texts.sort();
          },
          highlighter: function (text) {
            var regex = new RegExp( '(' + this.query + ')', 'gi' );
            return text.replace( regex, "<strong>$1</strong>" );
          }
        });
      }

      self.$container.on('click', $.proxy(function(event) {
        self.$input.focus();
      }, self));

      self.$input.on('keydown', $.proxy(function(event) {
        var tmpVal,
            $inputWrapper = self.findInputWrapper();

        switch (event.which) {
          // BACKSPACE
          case 8:
            if (doGetCaretPosition(self.$input[0]) === 0) {
              var prev = $inputWrapper.prev();
              if (prev) {
                self.remove(prev.data('item'));
              }
            }
            break;

          // DELETE
          case 46:
            if (doGetCaretPosition(self.$input[0]) === 0) {
              var next = $inputWrapper.next();
              if (next) {
                self.remove(next.data('item'));
              }
            }
          break;
          default:

            tmpVal = self.$input.val();

            // if there's no text to turn into a tag and the
            // user presses one of the confirmWithEmptyInputKeys,
            // trigger the confirmWithEmptyInput event
            // (useful for submitting forms on enter)
            if (tmpVal.length === 0 &&
              self.options.confirmWithEmptyInputKeys
              .indexOf(event.which) >= 0) {

            self.$element.trigger(
                $.Event('confirmWithEmptyInput'));

            // When key corresponds one of the confirmKeys, add
            // current input as a new tag
            } else if (self.options.freeInput &&
              self.options.confirmKeys
              .indexOf(event.which) >= 0) {

              self.clearInputVal();
              self.add(tmpVal);
              event.preventDefault();
            }
        }
      }, self));

      self.$input.on('input', function(event) {
        var val, valLength, replacedVal;

        val  = self.$input.val();
        valLength = val.length;

        self.$input.attr('size', Math.max(1, valLength + 1));

        // Handle copy-paste of tab-, newline- and comma-separated lists.

        // If only one character has been added to the input value,
        // it wasn't from pasing a list. Note that transforming
        // all tabs and etners to a comma may prevent selection of
        // a typeahead suggestion.
        if (valLength - self.lastInputVal.length !== 1) {

          replacedVal = val.replace( /[\t\n]/g, ',' );

          if (replacedVal.indexOf( ',' ) !== -1) {
            self.clearInputVal();
            self.add( replacedVal );
          }
        }

        self.lastInputVal = val;
        self.refreshInputWidth();
      } );

      // Remove icon clicked
      self.$container.on('click', '[data-role=remove]', $.proxy(function(event) {
        self.remove($(event.target).closest('.ep-tagsinput-tag').data('item'));
      }, self));

      // Only add existing value as tags when using strings as tags
      if (self.options.itemValue === defaultOptions.itemValue) {
        if (self.$element[0].tagName === 'INPUT') {
          self.add(self.$element.val());
        } else {
          $('option', self.$element).each(function() {
            self.add($(this).attr('value'), true);
          });
        }
      }
    },

    /**
     * Removes all tagsinput behaviour and unregsiter all event handlers
     */
    destroy: function() {
      var self = this;

      // Unbind events
      self.$container.off('keypress', 'input');
      self.$container.off('click', '[role=remove]');

      self.$container.remove();
      self.$element.removeData('tagsinput');
      self.$element.show();
    },

    /**
     * Sets focus on the tagsinput
     */
    focus: function() {
      this.$input.focus();
    },

    /**
     * Returns the internal input element
     */
    input: function() {
      return this.$input;
    },

    /**
     * Returns the element which is wrapped around the internal input. This
     * is normally the $container, but typeahead.js moves the $input element.
     */
    findInputWrapper: function() {
      var elt = this.$input[0],
          container = this.$container[0];
      while(elt && elt.parentNode !== container)
        elt = elt.parentNode;

      return $(elt);
    },

    /**
     * Clears the input field and makes it very narrow. It is sometimes
     * important to do this to avoid flickering in the browser when
     * adding a tag.
     */
    clearInputVal: function() {
      this.$input.val('');
      // don't set to zero, that would make it lose focus (on Chrome)
      this.$input.width(2);
    },

    refreshInputWidth: function() {
      var self, containerRPadding, containerRBorder, containerLeft, rightLimit,
      containerLPadding, containerWidth, containerLBorder, $lastTag, tagRMargin,
      leftLimit, availableWidth, inputValWidth, newWidth;

      self = this;

      // Set the text in our hidden element for measuring the pixel
      // length of the text in input.
      // Replace spaces with html so we can measure them, too.
      self.$textmeter.html(self.$input.val().replace(/ /g,'&nbsp;'));

      // do everything else following a timeout, to allow the browser
      // to render $textmeter and possibly other stuff
      setTimeout(function() {

        // find absolute X coordinate of the right edge of available space
        containerRPadding = parseFloat(self.$container.css('padding-right'));
        containerRPadding = isNaN(containerRPadding) ? 0 : containerRPadding;

        containerRBorder = parseFloat(self.$container.css('border-right-width'));
        containerRBorder = isNaN(containerRBorder) ? 0 : containerRBorder;

        containerLeft = self.$container.offset().left;

        rightLimit = containerLeft + self.$container.outerWidth() -
          containerRBorder - containerRPadding;

        // total available for input within container element
        containerLPadding = parseFloat(self.$container.css('padding-left'));
        containerLPadding = isNaN(containerLPadding) ? 0 : containerLPadding;

        containerWidth = self.$container.innerWidth() -
          containerLPadding - containerRPadding;

        // find absolute X coordinate of the left edge of available space
        $lastTag = $('.ep-tagsinput-tag', self.$container).last();
        if ($lastTag.length === 0) {
          containerLBorder = parseFloat(self.$container.css('border-left-width'));
          containerLBorder = isNaN(containerLBorder) ? 0 : containerLBorder;

          leftLimit = containerLeft + containerLBorder + containerLPadding;

        } else {
          tagRMargin = parseFloat($lastTag.css('margin-right'));
          tagRMargin = isNaN(tagRMargin) ? 0 : tagRMargin;

          leftLimit = $lastTag.offset().left + $lastTag.outerWidth() + tagRMargin;
        }

        // final calculation for available width
        // 10 is a pretty arbitrary value here, a bit of extra leeway.
        availableWidth = Math.max( rightLimit - leftLimit - 10, 0 );

        // actual width of text entered into input element
        inputValWidth = self.$textmeter.outerWidth();

        // set width
        // If there isn't space, we set the input space to the whole
        // width of the container, which will wrap it onto a newline.
        newWidth = inputValWidth > availableWidth ?
          containerWidth : availableWidth ;

        newWidth = Math.max( newWidth, 3);

        self.$input.width( newWidth );
      }, 1);
    },
  };

  /**
   * Register JQuery plugin
   */
  $.fn.tagsinput = function(arg1, arg2) {
    var results = [];

    this.each(function() {
      var tagsinput = $(this).data('tagsinput');

      // Initialize a new tags input
      if (!tagsinput) {
        tagsinput = new TagsInput(this, arg1);
        $(this).data('tagsinput', tagsinput);
        results.push(tagsinput);

        if (this.tagName === 'SELECT') {
          $('option', $(this)).attr('selected', 'selected');
        }

        // Init tags from $(this).val()
        $(this).val($(this).val());
      } else {
        // Invoke function on existing tags input
        var retVal = tagsinput[arg1](arg2);
        if (retVal !== undefined)
          results.push(retVal);
      }
    });

    if (typeof arg1 == 'string') {
      // Return the results from the invoked function calls
      return results.length > 1 ? results : results[0];
    } else {
      return results;
    }
  };

  $.fn.tagsinput.Constructor = TagsInput;

  /**
   * Most options support both a string or number as well as a function as
   * option value. This function makes sure that the option with the given
   * key in the given options is wrapped in a function
   */
  function makeOptionItemFunction(options, key) {
    if (typeof options[key] !== 'function') {
      var propertyName = options[key];
      options[key] = function(item) { return item[propertyName]; };
    }
  }
  function makeOptionFunction(options, key) {
    if (typeof options[key] !== 'function') {
      var value = options[key];
      options[key] = function() { return value; };
    }
  }
  /**
   * HtmlEncodes the given value
   */
  var htmlEncodeContainer = $('<div />');
  function htmlEncode(value) {
    if (value) {

      // Strangely, encoded &nbsp; entities are problematic for some versions
      // of Chrome, so we use plain spaces, and in css set white-space to "pre".
      // (jQuery encoding adds &nbsp;'s for consecutive spaces.)
      return htmlEncodeContainer.text(value).html().replace(/&nbsp;/g, '\u00a0');
    } else {
      return '';
    }
  }

  /**
   * Returns the position of the caret in the given input field
   * http://flightschool.acylt.com/devnotes/caret-position-woes/
   */
  function doGetCaretPosition(oField) {
    var iCaretPos = 0;
    if (document.selection) {
      oField.focus();
      var oSel = document.selection.createRange();
      oSel.moveStart('character', -oField.value.length);
      iCaretPos = oSel.text.length;
    } else if (oField.selectionStart || oField.selectionStart == '0') {
      iCaretPos = oField.selectionStart;
    }
    return (iCaretPos);
  }

  /**
   * Initialize tagsinput behaviour on inputs and selects which have
   * data-role=tagsinput
   */
  $(function() {
    $("input[data-role=tagsinput], select[multiple][data-role=tagsinput]").tagsinput();
  });
})(window.jQuery);
