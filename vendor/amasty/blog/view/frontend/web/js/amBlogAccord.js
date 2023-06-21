define([
    'jquery',
    'collapsible',
    'matchMedia',
    'domReady!'
], function ($) {
    'use strict';

    var helpers = {
        userAgent: /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i,

        domReady: function () {
            var self = this,
                accordionResize = function ($accordions) {
                $accordions.forEach(function (element) {
                    var $accordion = $(element);

                    if (!self.userAgent.test(navigator.userAgent)) {
                        // eslint-disable-next-line no-undef
                        mediaCheck({
                            media: '(min-width: 768px)',
                            entry: function () {
                                $accordion.collapsible('option', 'collapsible', false);
                                $accordion.collapsible('activate');
                            },
                            exit: function () {
                                $accordion.collapsible('option', 'collapsible', true);
                                $accordion.collapsible('deactivate');
                            }
                        });
                    } else {
                        $accordion.addClass('-accordion');
                    }

                    if ($accordion.parent().closest('.amblog-main-content').length) {
                        $accordion.collapsible('activate');
                        $accordion.collapsible('option', 'collapsible', false);
                    }

                    $('[data-amblog-js="content"]').off('click').off('keydown');
                });
            },

             $container = $('[data-amblog-js="accordion"]'),
                $accordions = [],
                accordionOptions = {
                    collapsible: true,
                    header: '[data-amblog-js="heading"]',
                    trigger: '',
                    content: '[data-amblog-js="content"]',
                    openedState: '-active',
                    animate: false
                };

            $container.children('h3').each(function (index, elem) {
                var $this = $(elem),
                    $accordion = $this.collapsible(accordionOptions);

                $accordions.push($accordion);
            });

            accordionResize($accordions);
        }
    };

    helpers.domReady();
});
