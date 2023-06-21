define(["Magento_PageBuilder/js/mass-converter/widget-directive-abstract"], function(WidgetDirectiveAbstract) {
    var AmBlogDirective = function() {
        WidgetDirectiveAbstract.apply(this, arguments);
    };

    AmBlogDirective.prototype = Object.create(WidgetDirectiveAbstract.prototype);
    AmBlogDirective.prototype.constructor = AmBlogDirective;

    AmBlogDirective.prototype.fromDom = function(data, config) {
        var attributes = WidgetDirectiveAbstract.prototype.fromDom.call(this, data, config);
        data.instance_id = attributes.instance_id;
        data.instance_type = attributes.type;
        delete attributes.instance_id;
        delete attributes.type;

        data.widget_parameters = JSON.stringify(attributes);

        return data;
    };

    AmBlogDirective.prototype.toDom = function(data, config) {
        if (!data.instance_id) {
            return data;
        }

        var attributes = {
            type: data.instance_type,
            instance_id: data.instance_id
        };
        if (data.widget_parameters) {
            attributes = Object.assign(attributes, JSON.parse(data.widget_parameters));
        }
        data[config.html_variable] = this.buildDirective(attributes);

        return data;
    };

    return AmBlogDirective;
});
