define([
    'jquery',
    'uiRegistry',
    'jquery/ui',
    'Magento_Ui/js/modal/modal',
    'mage/translate',
    'mage/backend/validation'
    // eslint-disable-next-line no-unused-vars
], function ($, registry) {
    'use strict';

    $.widget('ui.tagit', {
        options: {
            allowDuplicates: false,
            caseSensitive: true,
            fieldName: 'tags',
            placeholderText: null, // Sets `placeholder` attr on input field.
            readOnly: false, // Disables editing.
            removeConfirmation: false, // Require confirmation to remove tags.
            tagLimit: null, // Max number of tags allowed (null for unlimited).

            // Used for autocomplete, unless you override `autocomplete.source`.
            availableTags: [],

            // Use to override or add any options to the autocomplete widget.
            //
            // By default, autocomplete.source will map to availableTags,
            // unless overridden.
            autocomplete: {},

            // Shows autocomplete before the user even types anything.
            showAutocompleteOnFocus: false,

            // When enabled, quotes are unneccesary for inputting multi-word tags.
            allowSpaces: true,

            // The below options are for using a single field instead of several
            // for our form values.
            //
            // When enabled, will use a single hidden field for the form,
            // rather than one per tag. It will delimit tags in the field
            // with singleFieldDelimiter.
            //
            // The easiest way to use singleField is to just instantiate tag-it
            // on an INPUT element, in which case singleField is automatically
            // set to true, and singleFieldNode is set to that element. This
            // way, you don't need to fiddle with these options.
            singleField: false,

            // This is just used when preloading data from the field, and for
            // populating the field with delimited tags as the user adds them.
            singleFieldDelimiter: ',',

            // Set this to an input DOM node to use an existing form field.
            // Any text in it will be erased on init. But it will be
            // populated with the text of tags as they are created,
            // delimited by singleFieldDelimiter.
            //
            // If this is not set, we create an input node for it,
            // with the name given in settings.fieldName.
            singleFieldNode: null,

            // Whether to animate tag removals or not.
            animate: true,

            // Optionally set a tabindex attribute on the input that gets
            // created for tag-it.
            tabIndex: null,

            // Event callbacks.
            beforeTagAdded: null,
            afterTagAdded: null,

            beforeTagRemoved: null,
            afterTagRemoved: null,

            onTagClicked: null,
            onTagLimitExceeded: null,

            // DEPRECATED:
            //
            // /!\ These event callbacks are deprecated and WILL BE REMOVED at some
            // point in the future. They're here for backwards-compatibility.
            // Use the above before/after event callbacks instead.
            onTagAdded: null,
            onTagRemoved: null,

            // `autocomplete.source` is the replacement for tagSource.
            tagSource: null,

            // Do not use the above deprecated options.

            // Keycodes from jquery/ui
            keyCode: {
                BACKSPACE: 8,
                COMMA: 188,
                DELETE: 46,
                DOWN: 40,
                END: 35,
                ENTER: 13,
                ESCAPE: 27,
                HOME: 36,
                LEFT: 37,
                NUMPAD_ADD: 107,
                NUMPAD_DECIMAL: 110,
                NUMPAD_DIVIDE: 111,
                NUMPAD_ENTER: 108,
                NUMPAD_MULTIPLY: 106,
                NUMPAD_SUBTRACT: 109,
                PAGE_DOWN: 34,
                PAGE_UP: 33,
                PERIOD: 190,
                RIGHT: 39,
                SPACE: 32,
                TAB: 9,
                UP: 38
            }
        },

        _create: function () {
            // for handling static scoping inside callbacks
            var that = this;

            // There are 2 kinds of DOM nodes this widget can be instantiated on:
            //     1. UL, OL, or some element containing either of these.
            //     2. INPUT, in which case 'singleField' is overridden to true,
            //        a UL is created and the INPUT is hidden.
            if (this.element.is('input')) {
                this.tagList = $('<ul></ul>').insertAfter(this.element);
                this.options.singleField = true;
                this.options.singleFieldNode = this.element;
                this.element.addClass('tagit-hidden-field');
            } else {
                this.tagList = this.element.find('ul, ol').andSelf().last();
            }

            // eslint-disable-next-line
            this.tagInput = $('<input type="text" ' + '/' + '>').addClass('ui-widget-content');

            if (this.options.readOnly) {
                this.tagInput.attr('disabled', 'disabled');
            }

            if (this.options.tabIndex) {
                this.tagInput.attr('tabindex', this.options.tabIndex);
            }

            if (this.options.placeholderText) {
                this.tagInput.attr('placeholder', this.options.placeholderText);
            }

            if (!this.options.autocomplete.source) {
                this.options.autocomplete.source = function (search, showChoices) {
                    var filter = search.term.toLowerCase(),
                        choices = $.grep(this.options.availableTags, function (element) {
                        // Only match autocomplete options that begin with the search term.
                        // (Case insensitive.)
                            return element.toLowerCase().indexOf(filter) === 0;
                        });

                    if (!this.options.allowDuplicates) {
                        choices = this._subtractArray(choices, this.assignedTags());
                    }

                    showChoices(choices);
                };
            }

            if (this.options.showAutocompleteOnFocus) {
                // eslint-disable-next-line no-unused-vars
                this.tagInput.focus(function (event, ui) {
                    that._showAutocomplete();
                });

                if (typeof this.options.autocomplete.minLength === 'undefined') {
                    this.options.autocomplete.minLength = 0;
                }
            }

            // Bind autocomplete.source callback functions to this context.
            if ($.isFunction(this.options.autocomplete.source)) {
                this.options.autocomplete.source = $.proxy(this.options.autocomplete.source, this);
            }

            // DEPRECATED.
            if ($.isFunction(this.options.tagSource)) {
                this.options.tagSource = $.proxy(this.options.tagSource, this);
            }

            this.tagList
                .addClass('tagit')
                .addClass('ui-widget ui-widget-content ui-corner-all')

                // Create the input field.
                .append($('<li class="tagit-new"></li>').append(this.tagInput))
                .click(function (e) {
                    var target = $(e.target);

                    if (target.hasClass('tagit-label')) {
                        // eslint-disable-next-line vars-on-top, one-var
                        var tag = target.closest('.tagit-choice');

                        if (!tag.hasClass('removed')) {
                            that._trigger('onTagClicked', e, { tag: tag, tagLabel: that.tagLabel(tag) });
                        }
                    } else {
                        // Sets the focus() to the input field, if the user
                        // clicks anywhere inside the UL. This is needed
                        // because the input field needs to be of a small size.
                        that.tagInput.focus();
                    }
                });

            // Single field support.
            // eslint-disable-next-line vars-on-top, one-var
            var addedExistingFromSingleFieldNode = false;

            if (this.options.singleField) {
                if (this.options.singleFieldNode) {
                    // Add existing tags from the input field.
                    // eslint-disable-next-line vars-on-top, one-var
                    var node = $(this.options.singleFieldNode),
                        tags = node.val().split(this.options.singleFieldDelimiter);

                    node.val('');
                    $.each(tags, function (index, tag) {
                        that.createTag(tag, null, true);
                        addedExistingFromSingleFieldNode = true;
                    });
                } else {
                    // Create our single field input after our list.
                    // eslint-disable-next-line
                    this.options.singleFieldNode = $('<input type="hidden" style="display:none;" value="" name="' + this.options.fieldName + '"' + '/' + '>');
                    this.tagList.after(this.options.singleFieldNode);
                }
            }

            // Add existing tags from the list, if any.
            if (!addedExistingFromSingleFieldNode) {
                this.tagList.children('li').each(function () {
                    if (!$(this).hasClass('tagit-new')) {
                        that.createTag($(this).text(), $(this).attr('class'), true);
                        $(this).remove();
                    }
                });
            }

            // Events.
            this.tagInput
                .keydown(function (event) {
                    // Backspace is not detected within a keypress, so it must use keydown.
                    if (event.which === that.options.keyCode.BACKSPACE && that.tagInput.val() === '') {
                        // eslint-disable-next-line vars-on-top, one-var
                        var tag = that._lastTag();

                        if (!that.options.removeConfirmation || tag.hasClass('remove')) {
                            // When backspace is pressed, the last tag is deleted.
                            that.removeTag(tag);
                        } else if (that.options.removeConfirmation) {
                            tag.addClass('remove ui-state-highlight');
                        }
                    } else if (that.options.removeConfirmation) {
                        that._lastTag().removeClass('remove ui-state-highlight');
                    }

                    // Comma/Space/Enter are all valid delimiters for new tags,
                    // except when there is an open quote or if setting allowSpaces = true.
                    // Tab will also create a tag, unless the tag input is empty,
                    // in which case it isn't caught.
                    if (
                        event.which === that.options.keyCode.COMMA && event.shiftKey === false
                        || event.which === that.options.keyCode.ENTER
                        ||
                            event.which === that.options.keyCode.TAB
                            && that.tagInput.val() !== ''

                        ||
                            event.which === that.options.keyCode.SPACE
                            && that.options.allowSpaces !== true
                            && (
                                $.trim(that.tagInput.val()).replace(/^s*/, '').charAt(0) !== '"'
                                ||
                                    $.trim(that.tagInput.val()).charAt(0) === '"'
                                    // eslint-disable-next-line max-len
                                    && $.trim(that.tagInput.val()).charAt($.trim(that.tagInput.val()).length - 1) === '"'
                                    && $.trim(that.tagInput.val()).length - 1 !== 0

                            )

                    ) {
                        // Enter submits the form if there's no text in the input.
                        if (!(event.which === that.options.keyCode.ENTER && that.tagInput.val() === '')) {
                            event.preventDefault();
                        }

                        // Autocomplete will create its own tag from a selection and close automatically.
                        if (!(that.options.autocomplete.autoFocus && that.tagInput.data('autocomplete-open'))) {
                            that.tagInput.autocomplete('close');
                            that.createTag(that._cleanedInput());
                        }
                    } // eslint-disable-next-line no-unused-vars
                }).blur(function (e) {
                    // Create a tag when the element loses focus.
                    // If autocomplete is enabled and suggestion was clicked, don't add it.
                    if (!that.tagInput.data('autocomplete-open')) {
                        that.createTag(that._cleanedInput());
                    }
                });

            // Autocomplete.
            if (this.options.availableTags || this.options.tagSource || this.options.autocomplete.source) {
                // eslint-disable-next-line vars-on-top, one-var
                var autocompleteOptions = {
                    select: function (event, ui) {
                        // START customization
                        var value = ui.item ? ui.item.value : ui.innerText;

                        that.createTag(value);

                        // END customization
                        // Preventing the tag input to be updated with the chosen value.
                        return false;
                    }
                };

                $.extend(autocompleteOptions, this.options.autocomplete);

                // tagSource is deprecated, but takes precedence here since autocomplete.source is set by default,
                // while tagSource is left null by default.
                autocompleteOptions.source = this.options.tagSource || autocompleteOptions.source;
                // eslint-disable-next-line no-unused-vars
                this.tagInput.autocomplete(autocompleteOptions).bind('autocompleteopen.tagit', function (event, ui) {
                    that.tagInput.data('autocomplete-open', true);
                    // eslint-disable-next-line no-unused-vars
                }).bind('autocompleteclose.tagit', function (event, ui) {
                    that.tagInput.data('autocomplete-open', false);
                });

                // START customization
                this.tagInput.autocomplete('widget')
                    .addClass('tagit-autocomplete')
                    .on('click', function (event) {
                        if (event.target.nodeName === 'DIV' && $(event.target).parent('li')) {
                            autocompleteOptions.select(event, event.target);
                            $(event.target).closest('.tagit-autocomplete').hide();
                        }
                    });

                // END customization
            }
        },

        destroy: function () {
            $.Widget.prototype.destroy.call(this);

            this.element.unbind('.tagit');
            this.tagList.unbind('.tagit');

            this.tagInput.removeData('autocomplete-open');

            this.tagList.removeClass([
                'tagit',
                'ui-widget',
                'ui-widget-content',
                'ui-corner-all',
                'tagit-hidden-field'
            ].join(' '));

            if (this.element.is('input')) {
                this.element.removeClass('tagit-hidden-field');
                this.tagList.remove();
            } else {
                this.element.children('li').each(function () {
                    if ($(this).hasClass('tagit-new')) {
                        $(this).remove();
                    } else {
                        $(this).removeClass([
                            'tagit-choice',
                            'ui-widget-content',
                            'ui-state-default',
                            'ui-state-highlight',
                            'ui-corner-all',
                            'remove',
                            'tagit-choice-editable',
                            'tagit-choice-read-only'
                        ].join(' '));

                        $(this).text($(this).children('.tagit-label').text());
                    }
                });

                if (this.singleFieldNode) {
                    this.singleFieldNode.remove();
                }
            }

            return this;
        },

        _cleanedInput: function () {
            // Returns the contents of the tag input, cleaned and ready to be passed to createTag
            return $.trim(this.tagInput.val().replace(/^"(.*)"$/, '$1'));
        },

        _lastTag: function () {
            return this.tagList.find('.tagit-choice:last:not(.removed)');
        },

        _tags: function () {
            return this.tagList.find('.tagit-choice:not(.removed)');
        },

        assignedTags: function () {
            // Returns an array of tag string values
            var that = this,
                tags = [];

            if (this.options.singleField) {
                tags = $(this.options.singleFieldNode).val().split(this.options.singleFieldDelimiter);

                if (tags[0] === '') {
                    tags = [];
                }
            } else {
                this._tags().each(function () {
                    tags.push(that.tagLabel(this));
                });
            }

            return tags;
        },

        _updateSingleTagsField: function (tags) {
            // eslint-disable-next-line max-len
            // Takes a list of tag string values, updates this.options.singleFieldNode.val to the tags delimited by this.options.singleFieldDelimiter
            $(this.options.singleFieldNode).val(tags.join(this.options.singleFieldDelimiter)).trigger('change');
        },

        _subtractArray: function (a1, a2) {
            var result = [];

            // eslint-disable-next-line vars-on-top, one-var
            for (var i = 0; i < a1.length; i++) {
                if ($.inArray(a1[i], a2) === -1) {
                    result.push(a1[i]);
                }
            }

            return result;
        },

        tagLabel: function (tag) {
            // Returns the tag's string label.
            if (this.options.singleField) {
                return $(tag).find('.tagit-label:first').text();
            }

            return $(tag).find('input:first').val();
        },

        _showAutocomplete: function () {
            this.tagInput.autocomplete('search', '');
        },

        _findTagByLabel: function (name) {
            var that = this,
                tag = null;

            // eslint-disable-next-line no-unused-vars,consistent-return
            this._tags().each(function (i) {
                if (that._formatStr(name) === that._formatStr(that.tagLabel(this))) {
                    tag = $(this);

                    return false;
                }
            });

            return tag;
        },

        _isNew: function (name) {
            return !this._findTagByLabel(name);
        },

        _formatStr: function (str) {
            if (this.options.caseSensitive) {
                return str;
            }

            return $.trim(str.toLowerCase());
        },

        _effectExists: function (name) {
            return Boolean($.effects && ($.effects[name] || $.effects.effect && $.effects.effect[name]));
        },

        // eslint-disable-next-line consistent-return
        createTag: function (value, additionalClass, duringInitialization) {
            var that = this;

            // eslint-disable-next-line no-param-reassign
            value = $.trim(value);

            if (this.options.preprocessTag) {
                // eslint-disable-next-line no-param-reassign
                value = this.options.preprocessTag(value);
            }

            if (value === '') {
                return false;
            }

            if (!this.options.allowDuplicates && !this._isNew(value)) {
                // eslint-disable-next-line vars-on-top, one-var
                var existingTag = this._findTagByLabel(value);

                if (this._trigger('onTagExists', null, {
                    existingTag: existingTag,
                    duringInitialization: duringInitialization
                }) !== false) {
                    if (this._effectExists('highlight')) {
                        existingTag.effect('highlight');
                    }
                }

                return false;
            }

            if (this.options.tagLimit && this._tags().length >= this.options.tagLimit) {
                this._trigger('onTagLimitExceeded', null, { duringInitialization: duringInitialization });

                return false;
            }

            // eslint-disable-next-line vars-on-top, one-var
            var label = $(this.options.onTagClicked
                    ? '<a class="tagit-label"></a>'
                    : '<span class="tagit-label"></span>').text(value),

                // Create tag.
                tag = $('<li></li>')
                    .addClass('tagit-choice ui-widget-content ui-state-default ui-corner-all')
                    .addClass(additionalClass)
                    .append(label);

            if (this.options.readOnly) {
                tag.addClass('tagit-choice-read-only');
            } else {
                tag.addClass('tagit-choice-editable');

                // Button for removing the tag.
                // eslint-disable-next-line vars-on-top, one-var
                var removeTagIcon = $('<span></span>')
                        .addClass('ui-icon ui-icon-close'),
                    removeTag = $('<a><span class="text-icon">\xd7</span></a>') // \xd7 is an X
                        .addClass('tagit-close')
                        .append(removeTagIcon)
                        // eslint-disable-next-line no-unused-vars
                        .click(function (e) {
                        // Removes a tag when the little 'x' is clicked.
                            that.removeTag(tag);
                        });

                tag.append(removeTag);
            }

            // Unless options.singleField is set, each tag has a hidden input field inline.
            if (!this.options.singleField) {
                // eslint-disable-next-line vars-on-top, one-var
                var escapedValue = label.html();

                tag.append('<input type="hidden" value="' + escapedValue + '" name="'
                    // eslint-disable-next-line
                    + this.options.fieldName + '" class="tagit-hidden-field"' + '/' + '>');
            }

            if (this._trigger('beforeTagAdded', null, {
                tag: tag,
                tagLabel: this.tagLabel(tag),
                duringInitialization: duringInitialization
            }) === false) {
                // eslint-disable-next-line consistent-return
                return;
            }

            if (this.options.singleField) {
                // eslint-disable-next-line vars-on-top, one-var
                var tags = this.assignedTags();

                tags.push(value);
                this._updateSingleTagsField(tags);
            }

            // DEPRECATED.
            this._trigger('onTagAdded', null, tag);

            this.tagInput.val('');

            // Insert tag.
            this.tagInput.parent().before(tag);

            this._trigger('afterTagAdded', null, {
                tag: tag,
                tagLabel: this.tagLabel(tag),
                duringInitialization: duringInitialization
            });

            if (this.options.showAutocompleteOnFocus && !duringInitialization) {
                setTimeout(function () {
                    that._showAutocomplete();
                }, 0);
            }
        },

        removeTag: function (tag, animate) {
            // eslint-disable-next-line no-param-reassign
            animate = typeof animate === 'undefined' ? this.options.animate : animate;
            // eslint-disable-next-line no-param-reassign
            tag = $(tag);

            // DEPRECATED.
            this._trigger('onTagRemoved', null, tag);

            if (this._trigger('beforeTagRemoved', null, { tag: tag, tagLabel: this.tagLabel(tag) }) === false) {
                return;
            }

            if (this.options.singleField) {
                // eslint-disable-next-line vars-on-top, one-var
                var tags = this.assignedTags(),
                    removedTagLabel = this.tagLabel(tag);

                tags = $.grep(tags, function (el) {
                    return el !== removedTagLabel;
                });
                this._updateSingleTagsField(tags);
            }

            if (animate) {
                tag.addClass('removed'); // Excludes this tag from _tags.
                // eslint-disable-next-line vars-on-top, one-var,max-len,camelcase
                var hide_args = this._effectExists('blind') ? ['blind', { direction: 'horizontal' }, 'fast'] : [ 'fast' ],

                    thisTag = this;

                // eslint-disable-next-line camelcase
                hide_args.push(function () {
                    tag.remove();
                    thisTag._trigger('afterTagRemoved', null, { tag: tag, tagLabel: thisTag.tagLabel(tag) });
                });

                tag.fadeOut('fast').hide.apply(tag, hide_args).dequeue();
            } else {
                tag.remove();
                this._trigger('afterTagRemoved', null, { tag: tag, tagLabel: this.tagLabel(tag) });
            }
        },

        removeTagByLabel: function (tagLabel, animate) {
            var toRemove = this._findTagByLabel(tagLabel);

            if (!toRemove) {
                // eslint-disable-next-line no-throw-literal
                throw 'No such tag exists with the name \'' + tagLabel + '\'';
            }

            this.removeTag(toRemove, animate);
        },

        removeAll: function () {
            // Removes all tags.
            var that = this;

            this._tags().each(function (index, tag) {
                that.removeTag(tag, false);
            });
        }
    });
});
