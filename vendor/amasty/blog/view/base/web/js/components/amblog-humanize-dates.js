/**
 * Amasty Blog Humanize Dates Component
 */
define([
    'jquery',
    'mage/translate',
    'domReady!'
], function ($, $t) {
    'use strict';

    function HumanizeDate(dateString) {
        this.now = new Date();

        /**
         * Renders a date in the local timezone, including day of the week.
         * e.g. 'Fri, 22 May 2020'
         */
        this.dateFormatter = new Intl.DateTimeFormat([], {
            'year': 'numeric',
            'month': 'long',
            'day': 'numeric',
            'weekday': 'short'
        });

        /**
         * Renders an HH:MM time in the local timezone, including timezone info.
         * e.g. '12:17 BST'
         */
        this.timeFormatter = new Intl.DateTimeFormat([], {
            'hour': 'numeric',
            'minute': 'numeric',
            'timeZoneName': 'short'
        });

        /**
         * @private
         * @returns {void}
         */
        this._initialize = function () {
            this._prepareNowDate();
            this._processDateString();
            this._setTimeData();
            this._setDate();
        };

        /**
         * Convert current date to UTC.
         * @private
         * @returns {void}
         */
        this._prepareNowDate = function () {
            this.now = new Date(
                this.now.getUTCFullYear(),
                this.now.getUTCMonth(),
                this.now.getUTCDate(),
                this.now.getUTCHours(),
                this.now.getUTCMinutes(),
                this.now.getUTCSeconds(),
                this.now.getUTCMilliseconds()
            );
        };

        /**
         * For iOS devices is need to replace the 'space' with a 'T' to conform to a simplified version of ISO-8601.
         * @private
         * @returns {void}
         */
        this._processDateString = function () {
            this.parsedDate = new Date(Date.parse(dateString.replace(' ', 'T')));
        };

        /**
         * @private
         * @returns {void}
         */
        this._setTimeData = function () {
            var delta = this.now - this.parsedDate,
                seconds = Math.floor(delta / 1000),
                minutes = Math.floor(seconds / 60),
                hours = Math.floor(minutes / 60),
                days = Math.floor(hours / 24),
                months = Math.floor(days / 30),
                years = Math.floor(days / 365);

            this.timeData = {
                seconds: {
                    value: seconds,
                    label: 'second'
                },
                minutes: {
                    value: minutes,
                    label: 'minute'
                },
                hours: {
                    value: hours,
                    label: 'hour'
                },
                days: {
                    value: days,
                    label: 'day'
                },
                months: {
                    value: months,
                    label: 'month'
                },
                years: {
                    value: years,
                    label: 'year'
                }
            };
        };

        /**
         * @private
         * @returns {Boolean}
         */
        this._isToday = function () {
            return this.dateFormatter.format(this.parsedDate) === this.dateFormatter.format(this.now);
        };

        /**
         * @private
         * @returns {Boolean}
         */
        this._isYesterday = function () {
            var yesterday = new Date().setDate(this.now.getDate() - 1);

            return this.dateFormatter.format(this.parsedDate) === this.dateFormatter.format(yesterday);
        };

        /**
         * @private
         * @returns {String}
         */
        this._getFormattedDate = function () {
            var timeData = this.timeData;

            if (timeData.seconds.value < 5) {
                return 'just now';
            }

            if (timeData.seconds.value < 60) {
                return this._getFormattedValue(timeData.seconds);
            }

            if (timeData.minutes.value < 60) {
                return this._getFormattedValue(timeData.minutes);
            }

            if (timeData.hours.value < 24) {
                return this._getFormattedValue(timeData.hours);
            }

            if (timeData.days.value < 30) {
                return this._getFormattedValue(timeData.days);
            }

            if (timeData.months.value < 12) {
                return this._getFormattedValue(timeData.months);
            }

            return this._getFormattedValue(timeData.years);
        };

        /**
         * @private
         * @param {Object} data
         * @returns {String}
         */
        this._getFormattedValue = function (data) {
            var postfix = this._getPostfix(data.value);

            return $t('%1' + ' ' + data.label + postfix + ' ago').replace('%1', data.value);
        };

        /**
         * @private
         * @param {Number} value
         * @returns {String}
         */
        this._getPostfix = function (value) {
            return value === 1 ? '' : 's';
        };

        /**
         * @private
         * @returns {void}
         */
        this._setDate = function () {
            this.formattedDate = this._getFormattedDate();
        };

        /**
         * @public
         * @returns {String}
         */
        this.getDate = function () {
            return this.formattedDate;
        };

        return this._initialize();
    }

    /**
     * Extend Object to return formatted data
     * @returns {String}
     */
    HumanizeDate.prototype.toString = function () {
        return this.getDate();
    };

    return function (dateString, element) {
        var formattedDate;

        // START workaround to force translations
        // to be collected in js-translations.json
        $t('%1 second ago');
        $t('%1 seconds ago');
        $t('%1 minute ago');
        $t('%1 minutes ago');
        $t('%1 day ago');
        $t('%1 days ago');
        $t('%1 month ago');
        $t('%1 months ago');
        $t('%1 year ago');
        $t('%1 years ago');
        // END workaround to force translations
        // to be collected in js-translations.json

        formattedDate = new HumanizeDate(dateString);

        $(element).text($t(formattedDate));
    };
});
