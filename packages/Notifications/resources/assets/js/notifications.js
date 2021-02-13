(function ($) {
    var selector = '[data-type="notification"]';
    var canLog = typeof(console.log) != 'undefined';
    var required = ['level', 'message'];
    var config = {
        levels : {},
        classes: {},
        icons  : {},
        timeout: 3,
    };
    
    /**
     * A shortcut for logging messages, while also checking that console.log exists.
     * @param message
     */
    function log(message) {
        if(canLog) {
            console.log(message);
        }
    }
    
    /**
     * Load the configuration into the internal variable.
     */
    function loadConfig() {
        if(typeof(NotificationConfig) != 'object') {
            log('Cannot load configuration');
            return;
        }
        config = NotificationConfig;
    }
    
    /**
     * Make a new notification object and add it to the correct bag.
     * @param params
     * @returns {jQuery}
     */
    $.notify = function (params) {
        // Check the parameters
        if(typeof(params) != 'object') {
            log('Invalid argument supplied - must be an object.');
            return this;
        }
        for(var i in required) {
            if(typeof(params[required[i]]) == 'undefined') {
                log('Invalid argument supplied - parameter "' + required[i] + '" required.');
                return this;
            }
        }
        if(config.levels.indexOf(params.level) == -1) {
            log('Invalid argument supplied - unknown level "' + params.level + '".');
            return this;
        }
        
        // Set the attributes
        var close = 'auto';
        if(typeof(params.permanent) == 'boolean' && params.permanent) {
            close = 'manual';
        } else if(typeof(params.close) == 'boolean' && !params.close) {
            close = 'none';
        }
        var attributes = $.extend(true, {}, {
            'data-close': close
        }, params.attributes || {});
        
        // Make the element
        var notification = $('<div class="notification" />')
            .addClass(config.classes[params.level])
            .attr(attributes)
            .attr('data-type', 'notification')
            .attr('data-close-class', config['close-class'])
            .append('<span class="icon ' + config.icons[params.level] + '"></span>')
            .append(typeof(params.title) == 'string' ? '<h1>' + params.title + '</h1>' : '')
            .append('<p>' + params.message.replace(/(?:\r\n|\r|\n)/g, '</p><p>') + '</p>');
        
        // Add it to the correct bag
        var bag = $('[data-type="notification-bag"][data-bag="' + (params.bag || 'default') + '"]:first');
        if(bag.length != 1) {
            log('Could not find bag "' + (params.bag || 'default') + '" to add notification to.');
            return this;
        }
        bag.prepend(notification);
        
        // Initialise
        notification.trigger('initialise');
        
        // Animate showing
        notification.trigger('show');
        
        // Allow chaining
        return this;
    };
    
    /**
     * Event for initialising a notification.
     */
    $('body').on('initialise', selector, function () {
        var notification = $(this);
        if(!notification.hasClass('notification-initialised')) {
            var close = notification.data('close') || 'auto';
            if(close == 'manual') {
                notification.append(
                    $('<button type="button" class="close-notification"><span class="' + notification.data('closeClass') + '"></span></button>')
                )
                    .addClass('has-close-button');
            } else if(close == 'auto') {
                setTimeout(function () {
                    notification.trigger('close');
                }, config.timeout * 1000);
            }
            
            notification.addClass('notification-initialised');
        }
    });
    
    /**
     * Event for closing the notification.
     */
    $('body').on('close', selector, function () {
        var notification = $(this);
        notification.animate({
            opacity: '0'
        }, 100, function () {
            notification.slideUp(100, function () {
                notification.remove();
            });
        });
    });
    
    /**
     * Animate showing a notification.
     */
    $('body').on('show', selector, function () {
        var notification = $(this);
        notification.css('opacity', '0')
            .animate({
                opacity: '1'
            }, 100);
    });
    
    /**
     * Set up closing the notification by clicking the close button.
     */
    $('body').on('click', selector + ' > button.close-notification', function () {
        $(this).parent()
            .trigger('close');
    });
    
    // Initialise all existing notifications on page load.
    $(document).ready(function () {
        loadConfig();
        $(selector).trigger('initialise');
    });
})(jQuery);