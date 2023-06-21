define(["Magento_Ui/js/form/element/abstract"],
    function (Abstract) {
        return Abstract.extend({
            setData: function(meta) {
                this.value(meta.instance_type);
            }
        });
    }
);
