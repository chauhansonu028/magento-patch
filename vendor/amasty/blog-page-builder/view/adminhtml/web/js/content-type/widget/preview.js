function _inheritsLoose(subClass, superClass) {
    subClass.prototype = Object.create(superClass.prototype);
    subClass.prototype.constructor = subClass;
    subClass.__proto__ = superClass;
}

define(["jquery", "knockout", "mage/translate", "Magento_PageBuilder/js/widget-initializer", "Magento_PageBuilder/js/config", "Magento_PageBuilder/js/content-type-menu/hide-show-option", "Magento_PageBuilder/js/utils/object", "Magento_PageBuilder/js/content-type/preview"], function (_jquery, _knockout, _translate, _widgetInitializer, _config, _hideShowOption, _object, _preview) {

    var Preview =
        function (_preview2) {
            "use strict";

            _inheritsLoose(Preview, _preview2);

            function Preview(parent, config, observableUpdater) {
                var self;

                self = _preview2.call(this, parent, config, observableUpdater) || this;
                self.displayingWidgetPreview = _knockout.observable(false);
                self.loading = _knockout.observable(false);
                self.messages = {
                    NOT_SELECTED: (0, _translate)("Empty Widget"),
                    UNKNOWN_ERROR: (0, _translate)("An unknown error occurred. Please try again.")
                };
                self.placeholderText = _knockout.observable(self.messages.NOT_SELECTED);
                return self;
            }

            var _proto = Preview.prototype;

            _proto.retrieveOptions = function retrieveOptions() {
                var options = _preview2.prototype.retrieveOptions.call(this);

                options.hideShow = new _hideShowOption({
                    preview: this,
                    icon: _hideShowOption.showIcon,
                    title: _hideShowOption.showText,
                    action: this.onOptionVisibilityToggle,
                    classes: ["hide-show-content-type"],
                    sort: 40
                });
                return options;
            };

            _proto.processWidgetData = function processWidgetData(data) {
                this.displayPreviewPlaceholder(data);

                if (data.instance_type || data.html) {
                    this.processRequest(data);
                }
            };

            _proto.afterObservablesUpdated = function afterObservablesUpdated() {
                _preview2.prototype.afterObservablesUpdated.call(this);

                var data = this.contentType.dataStore.getState(); // Only load if something changed

                this.processWidgetData(data);
            };

            _proto.displayPreviewPlaceholder = function displayPreviewPlaceholder(data, identifierName) {
                if (this.lastRenderedHtml && this.lastWidget === data.instance_type) {
                    this.data.main.html(this.lastRenderedHtml);
                }
                this.showBlockPreview(false);
            };

            _proto.processRequest = function processRequest(data) {
                var self = this,
                    url = _config.getConfig("preview_url"),
                    identifier = (0, _object.get)(data, "instance_id"),
                    requestConfig = {
                        method: "POST",
                        data: {
                            role: this.config.name,
                            instance_id: data.instance_id,
                            directive: this.data.main.html()
                        }
                    };

                this.loading(true);

                _jquery.ajax(url, requestConfig)
                    .done(function (response) {
                        if (!response.data.content) {
                            self.showBlockPreview(false);
                            self.placeholderText(self.messages.UNKNOWN_ERROR);

                            return;
                        }

                        self.displayLabel(self.config.label);

                        if (response.data.content) {
                            self.showBlockPreview(true);

                            self.data.main.html(response.data.content);

                            _jquery(self.wrapperElement).find('.amblog-slick-slider').trigger('contentUpdated');
                        }
                        self.lastWidget = parseInt(identifier.toString(), 10);
                        self.lastRenderedHtml = response.data.content;
                    }).fail(function () {
                    self.showBlockPreview(false);

                    self.placeholderText(self.messages.UNKNOWN_ERROR);
                }).always(function () {
                    self.loading(false);
                });
            };

            _proto.showBlockPreview = function showBlockPreview(isShow) {
                this.displayingWidgetPreview(isShow);
            };

            return Preview;
        }(_preview);

    return Preview;
});
