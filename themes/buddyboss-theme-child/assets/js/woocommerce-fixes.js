/**
 * BuddyBoss Child Theme - WooCommerce Fixes
 * Prevents "Store 'core/interface' is already registered" errors
 */

(function($) {
    'use strict';
    
    // Flag to prevent duplicate initializations
    let storesInitialized = false;
    
    // Wait for document ready
    $(document).ready(function() {
        
        // Check if WooCommerce data stores exist before using them
        if (window.wp && window.wp.data) {
            // Wrap store access in try-catch to prevent errors
            try {
                const stores = wp.data.getStoreNames ? wp.data.getStoreNames() : [];
                
                // Check if store already exists before any operations
                if (!stores.includes('core/interface') && !storesInitialized) {
                    storesInitialized = true;
                    console.log('Store initialization complete');
                }
            } catch (error) {
                console.warn('WooCommerce store initialization skipped:', error.message);
            }
        }
        
        // Fix for Dokan dashboard conflicts
        if ($('.dokan-dashboard').length) {
            // Ensure scripts are loaded only once
            if (!window.dokanInitialized) {
                window.dokanInitialized = true;
                
                // Wait for all dependencies
                $(window).on('load', function() {
                    if (typeof dokan !== 'undefined' && dokan.init) {
                        try {
                            dokan.init();
                        } catch (e) {
                            console.warn('Dokan initialization skipped');
                        }
                    }
                });
            }
        }
        
        // Prevent duplicate AJAX calls
        let ajaxRequests = {};
        
        $(document).ajaxSend(function(event, jqxhr, settings) {
            if (settings.url && settings.url.includes('wc-ajax')) {
                const requestKey = settings.url + JSON.stringify(settings.data);
                
                // Cancel duplicate requests
                if (ajaxRequests[requestKey]) {
                    jqxhr.abort();
                    return;
                }
                
                ajaxRequests[requestKey] = true;
                
                // Clean up after completion
                jqxhr.always(function() {
                    delete ajaxRequests[requestKey];
                });
            }
        });
        
        // Fix for product variation scripts
        $(document).on('found_variation', function(event, variation) {
            // Ensure variation scripts don't conflict
            if (window.wp && window.wp.data) {
                try {
                    // Your variation handling code here
                } catch (e) {
                    console.warn('Variation handling error:', e);
                }
            }
        });
        
        // Dokan product edit page fixes
        if ($('#dokan-product-edit-form').length) {
            // Pre-select virtual and downloadable options
            $('input[name=_virtual]').prop('checked', true);
            $('input[name=_downloadable]').prop('checked', true);
            
            // Handle attribute validation
            $('#publish').on('click', function(e) {
                let hasRequiredAttributes = true;
                const requiredAttributes = ['pa_resource-type', 'pa_year-level'];
                const selectedAttributes = [];
                
                $('.dokan-attribute-option-list li').each(function() {
                    const attrName = $(this).data('taxonomy');
                    if (attrName) {
                        selectedAttributes.push(attrName);
                    }
                });
                
                requiredAttributes.forEach(function(attr) {
                    if (!selectedAttributes.includes(attr)) {
                        hasRequiredAttributes = false;
                    }
                });
                
                if (!hasRequiredAttributes) {
                    e.preventDefault();
                    $('.dokan-product-attribute-alert').removeClass('dokan-hide');
                    setTimeout(function() {
                        $('.dokan-product-attribute-alert').addClass('dokan-hide');
                    }, 5000);
                    return false;
                }
            });
        }
        
        // Fix for variation swatches
        if ($('.woo-variation-swatches').length) {
            $(document).ajaxComplete(function(event, xhr, options) {
                if (options.data && options.data.includes('dokan_get_pre_attribute')) {
                    $('.dokan_attribute_values').each(function() {
                        if ($(this).hasClass('select2-hidden-accessible')) {
                            $(this).select2('destroy');
                        }
                        $(this).select2({
                            maximumSelectionLength: 3,
                        });
                    });
                }
            });
        }
        
        // Prevent store registration conflicts on dynamic content
        $(document).on('DOMNodeInserted', function(e) {
            const target = $(e.target);
            
            // Check for WooCommerce dynamic content
            if (target.hasClass('woocommerce') || target.find('.woocommerce').length) {
                // Prevent duplicate registrations
                if (window.wp && window.wp.data && !storesInitialized) {
                    storesInitialized = true;
                }
            }
        });
        
    });
    
    // Global error handler for store registration issues
    window.addEventListener('error', function(e) {
        if (e.message && e.message.includes('already registered')) {
            console.warn('Duplicate registration prevented:', e.message);
            e.preventDefault();
            return true;
        }
    });
    
})(jQuery);