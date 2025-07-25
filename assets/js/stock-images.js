jQuery(document).ready(function($) {
    'use strict';
    
    // Stock Images functionality
    var StockImages = {
        currentPage: 1,
        currentQuery: '',
        isLoading: false,
        modalMode: false,
        
        init: function() {
            try {
                console.log('StockImages: Initializing...');
                this.bindEvents();
                // this.initSearchInterface(); // Commented out to disable search on main Media Library page
                this.initMediaModal();
                console.log('StockImages: Initialization complete');
            } catch (error) {
                console.error('StockImages: Error during initialization:', error);
            }
        },
        
        bindEvents: function() {
            // Search form submission
            $(document).on('submit', '#stock-search-form', function(e) {
                e.preventDefault();
                StockImages.search();
            });
            
            // Modal search button
            $(document).on('click', '#stock-modal-search-btn', function(e) {
                e.preventDefault();
                StockImages.modalSearch();
            });
            
            // Load more button
            $(document).on('click', '#stock-load-more', function(e) {
                e.preventDefault();
                StockImages.loadMore();
            });
            
            // Modal load more button
            $(document).on('click', '#stock-modal-load-more', function(e) {
                e.preventDefault();
                StockImages.modalLoadMore();
            });
            
            // Source selector change - trigger new search
            $(document).on('change', '#stock-source-select', function() {
                if (StockImages.currentQuery) {
                    StockImages.currentPage = 1;
                    StockImages.performSearch();
                }
            });
            
            // Modal source selector change - trigger new search
            $(document).on('change', '#stock-modal-source-select', function() {
                if (StockImages.currentQuery) {
                    StockImages.currentPage = 1;
                    StockImages.performModalSearch();
                }
            });
            
            // Import image button
            $(document).on('click', '.stock-import-btn', function(e) {
                e.preventDefault();
                var imageData = $(this).data('image');
                var selectedSize = $(this).data('size');
                
                // If imageData is a string, decode and parse it
                if (typeof imageData === 'string') {
                    try {
                        // First decode the URI component, then parse JSON
                        imageData = JSON.parse(decodeURIComponent(imageData));
                    } catch (err) {
                        console.error('Error parsing image data:', err);
                        StockImages.showError('Invalid image data.');
                        return;
                    }
                }
                StockImages.importImage(imageData, selectedSize);
            });
            
            // Search input with debounce
            var searchTimeout;
            $(document).on('input', '#stock-search-input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    StockImages.search();
                }, 500);
            });
            
            // Modal search input with debounce
            $(document).on('input', '#stock-modal-search-input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    StockImages.modalSearch();
                }, 500);
            });
        },
        
        /*
        initSearchInterface: function() {
            // Add search interface to media library
            if ($('#wp-media-grid').length) {
                this.addSearchInterface();
            }
        },
        */
        
        initMediaModal: function() {
            var observer = null;
            var injected = false;

            function injectIfNeeded() {
                if ($('.media-menu').length && $('.media-frame-content').length && !$('.media-menu-item[data-panel="stock-images"]').length) {
                    StockImages.addMediaModalTab();
                    StockImages.createModalContent();
                    injected = true;
                    if (observer) observer.disconnect();
                }
            }

            // Listen for Add Media button clicks
            $(document).on('click', '.insert-media, .editor-insert-media, .block-editor-inserter__media-button, .components-button, .edit-post-header-toolbar__insert-media-button, .block-editor-media-placeholder__button, .block-editor-block-toolbar__media-button, .block-editor-inserter__toggle, .block-editor-inserter__media-upload', function() {
                injected = false;
                if (observer) observer.disconnect();
                observer = new MutationObserver(function() {
                    if (!injected) injectIfNeeded();
                });
                observer.observe(document.body, { childList: true, subtree: true });
            });

            // Also handle tab switching
            $(document).on('click', '.media-menu-item[data-panel="stock-images"]', function(e) {
                e.preventDefault();
                StockImages.showStockImagesPanel();
            });

            // Refresh media library when user clicks the Media Library tab in the modal
            $(document).on('click', '.media-menu-item#menu-item-browse', function() {
                if (wp.media && wp.media.frame && wp.media.frame.content.get().collection) {
                    wp.media.frame.content.get().collection.props.set({ignore: (+ new Date())});
                    wp.media.frame.content.get().options.selection.reset();
                }
            });
        },
        
        createModalContent: function() {
            // Create the stock images panel if it doesn't exist
            if ($('#stock-images-panel').length === 0) {
                var panelHtml = `
                    <div id="stock-images-panel" class="media-panel" style="display: none;">
                        <div class="media-panel-header">
                            <h3>${stockImagesAjax.strings.stock_images || 'Stock Images'}</h3>
                        </div>
                        <div class="media-panel-content">
                            <div class="stock-modal-search">
                                <select id="stock-modal-source-select" class="stock-source-select">
                                    ${this.getConfiguredApiOptions()}
                                </select>
                                <input type="text" id="stock-modal-search-input" placeholder="${stockImagesAjax.strings.search_placeholder || 'Search for images...'}" class="stock-search-input">
                                <button type="button" id="stock-modal-search-btn" class="button button-primary">${stockImagesAjax.strings.search_button || 'Search'}</button>
                            </div>
                            <div id="stock-modal-results" class="stock-modal-results"></div>
                            <div id="stock-modal-load-more-container" class="stock-modal-load-more" style="display: none;">
                                <button id="stock-modal-load-more" class="button">${stockImagesAjax.strings.load_more || 'Load More'}</button>
                            </div>
                        </div>
                    </div>
                `;
                
                // Add to media modal content area
                $('.media-frame-content').append(panelHtml);
            }
        },
        
        getConfiguredApiOptions: function() {
            // This will be populated by PHP via wp_localize_script
            if (window.stockImagesAjax && window.stockImagesAjax.configured_apis) {
                var options = '';
                window.stockImagesAjax.configured_apis.forEach(function(api) {
                    options += `<option value="${api.value}">${api.label}</option>`;
                });
                return options;
            }
            
            // Fallback to all options if not provided
            return `
                <option value="unsplash">Unsplash</option>
                <option value="pexels">Pexels</option>
                <option value="pixabay">Pixabay</option>
            `;
        },
        
        addMediaModalTab: function() {
            // Add tab to media modal if it doesn't exist
            if ($('.media-menu-item[data-panel="stock-images"]').length === 0) {
                var tabHtml = '<a href="#stock-images-panel" class="media-menu-item" data-panel="stock-images">' + 
                             (stockImagesAjax.strings.stock_images || 'Stock Images') + '</a>';
                $('.media-menu').append(tabHtml);
            }
        },
        
        showStockImagesPanel: function() {
            this.modalMode = true;
            
            // Hide other panels
            $('.media-panel').hide();
            $('.media-menu-item').removeClass('active');
            
            // Show stock images panel
            $('#stock-images-panel').show();
            $('.media-menu-item[data-panel="stock-images"]').addClass('active');
            
            // Clear previous results
            $('#stock-modal-results').empty();
            $('#stock-modal-load-more-container').hide();
        },
        
        addSearchInterface: function() {
            var searchHtml = `
                <div id="stock-search-container" class="stock-search-container">
                    <div class="stock-search-header">
                        <h3>${stockImagesAjax.strings.searching || 'Search Stock Images'}</h3>
                        <div class="stock-search-form-wrapper">
                            <select id="stock-source-select" class="stock-source-select">
                                <option value="unsplash">Unsplash</option>
                                <option value="pexels">Pexels</option>
                            </select>
                            <input type="text" id="stock-search-input" placeholder="Search for images..." class="stock-search-input">
                            <button type="submit" class="button button-primary stock-search-btn">Search</button>
                        </div>
                    </div>
                    <div id="stock-results" class="stock-results"></div>
                    <div id="stock-load-more-container" class="stock-load-more-container" style="display: none;">
                        <button id="stock-load-more" class="button">Load More</button>
                    </div>
                </div>
            `;
            
            // Insert after the media library toolbar
            $('.wp-filter').after(searchHtml);
        },
        
        search: function() {
            var query = $('#stock-search-input').val().trim();
            
            if (!query) {
                this.clearResults();
                return;
            }
            
            this.currentQuery = query;
            this.currentPage = 1;
            this.performSearch();
        },
        
        modalSearch: function() {
            var query = $('#stock-modal-search-input').val().trim();
            
            if (!query) {
                this.clearModalResults();
                return;
            }
            
            this.currentQuery = query;
            this.currentPage = 1;
            this.performModalSearch();
        },
        
        loadMore: function() {
            if (this.isLoading) return;
            
            this.currentPage++;
            this.performSearch(true);
        },
        
        modalLoadMore: function() {
            if (this.isLoading) return;
            
            this.currentPage++;
            this.performModalSearch(true);
        },
        
        performSearch: function(append) {
            if (this.isLoading) return;
            
            this.isLoading = true;
            this.showLoading(append);
            
            var selectedSource = $('#stock-source-select').val() || 'unsplash';
            var currentPage = this.currentPage; // Store reference to current page
            
            $.ajax({
                url: stockImagesAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'stk_img_its_search',
                    nonce: stockImagesAjax.nonce,
                    query: this.currentQuery,
                    page: currentPage,
                    source: selectedSource
                },
                success: function(response) {
                    StockImages.isLoading = false;
                    StockImages.hideLoading();
                    
                    if (response.success && response.data.results && response.data.results.length) {
                        StockImages.displayResults(response.data, append);
                        
                        if (response.data.total_pages > currentPage) {
                            $('#stock-load-more-container').show();
                        } else {
                            $('#stock-load-more-container').hide();
                        }
                    } else {
                        if (!append) {
                            $('#stock-results').html('<div class="no-results"><p>' + (stockImagesAjax.strings.no_results || 'No images found.') + '</p></div>');
                        }
                        $('#stock-load-more-container').hide();
                    }
                },
                error: function(xhr, status, error) {
                    StockImages.isLoading = false;
                    StockImages.hideLoading();
                    StockImages.showError('Error searching ' + selectedSource + '. Please try again.');
                    $('#stock-load-more-container').hide();
                }
            });
        },
        
        performModalSearch: function(append) {
            if (this.isLoading) return;
            
            this.isLoading = true;
            this.showModalLoading(append);
            
            var selectedSource = $('#stock-modal-source-select').val() || 'unsplash';
            
            $.ajax({
                url: stockImagesAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'stk_img_its_search',
                    nonce: stockImagesAjax.nonce,
                    query: this.currentQuery,
                    page: this.currentPage,
                    source: selectedSource
                },
                success: function(response) {
                    if (response.success) {
                        StockImages.displayModalResults(response.data, append);
                    } else {
                        StockImages.showModalError(response.data);
                    }
                },
                error: function(xhr, status, error) {
                    $root.find('#stock-modal-spinner').hide();
                    $root.find('#stock-modal-message').html('<span style="color:red;">Error searching ' + selectedSource + '.</span>');
                    $root.find('#stock-modal-load-more-container').hide();
                },
                complete: function() {
                    StockImages.isLoading = false;
                    StockImages.hideModalLoading();
                }
            });
        },
        
        displayResults: function(data, append) {
            var resultsContainer = $('#stock-results');
            var html = '';
            var currentPage = this.currentPage; // Store reference to current page
            
            if (!append) {
                resultsContainer.empty();
            }
            
            if (data.results && data.results.length > 0) {
                data.results.forEach(function(image) {
                    html += StockImages.createImageCard(image);
                });
                
                if (append) {
                    resultsContainer.append(html);
                } else {
                    resultsContainer.html(html);
                }
                
                // Bind import handlers for new images
                StockImages.bindImportHandlers();
                
                // Show load more button if there are more pages
                if (data.total_pages > currentPage) {
                    $('#stock-load-more-container').show();
                } else {
                    $('#stock-load-more-container').hide();
                }
            } else {
                if (!append) {
                    resultsContainer.html('<p class="no-results">' + (stockImagesAjax.strings.no_results || 'No images found.') + '</p>');
                }
                $('#stock-load-more-container').hide();
            }
        },
        
        displayModalResults: function(data, append) {
            var resultsContainer = $('#stock-modal-results');
            var html = '';
            var currentPage = this.currentPage; // Store reference to current page
            
            if (!append) {
                resultsContainer.empty();
            }
            
            if (data.results && data.results.length > 0) {
                data.results.forEach(function(image) {
                    html += StockImages.createImageCard(image);
                });
                
                if (append) {
                    resultsContainer.append(html);
                } else {
                    resultsContainer.html(html);
                }
                
                // Bind import handlers for new images
                StockImages.bindImportHandlers();
                
                // Show load more button if there are more pages
                if (data.total_pages > currentPage) {
                    $('#stock-modal-load-more-container').show();
                } else {
                    $('#stock-modal-load-more-container').hide();
                }
            } else {
                if (!append) {
                    resultsContainer.html('<p class="no-results">' + (stockImagesAjax.strings.no_results || 'No images found.') + '</p>');
                }
                $('#stock-modal-load-more-container').hide();
            }
        },
        
        createImageCard: function(image) {
            // Determine source-specific size descriptions
            var source = image.source || 'unsplash';
            var smallSize = source === 'pexels' ? '350px' : source === 'pixabay' ? '640px' : '350px';
            var mediumSize = source === 'pexels' ? '1200px' : source === 'pixabay' ? '1280px' : '700px';
            var fullSize = source === 'pexels' ? 'Original' : source === 'pixabay' ? '1920px' : '1920px';
            
            // Properly encode the image data to handle quotes and special characters
            var encodedImageData = encodeURIComponent(JSON.stringify(image));
            
            return `
                <div class="stock-image-card" data-image-id="${image.id}">
                    <div class="stock-image-wrapper">
                        <img src="${image.urls.small}" alt="${image.alt_description || 'Stock image'}" class="stock-image">
                        <div class="stock-image-overlay">
                            <div class="stock-download-buttons">
                                <button class="stock-import-btn stock-import-circle" data-image="${encodedImageData}" data-size="small" aria-label="Import small image (${smallSize})">
                                    <span class="stock-size-label">S</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                    </svg>
                                </button>
                                <button class="stock-import-btn stock-import-circle" data-image="${encodedImageData}" data-size="medium" aria-label="Import medium image (${mediumSize})">
                                    <span class="stock-size-label">M</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                    </svg>
                                </button>
                                <button class="stock-import-btn stock-import-circle" data-image="${encodedImageData}" data-size="full" aria-label="Import full image (${fullSize})">
                                    <span class="stock-size-label">L</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="stock-image-info">
                        <p class="stock-photographer">
                            Photo by <a href="${image.user.links.html}" target="_blank">${image.user.name}</a>
                        </p>
                        <p class="stock-description">${image.description || image.alt_description || ''}</p>
                    </div>
                </div>
            `;
        },
        
        importImage: function(imageData, selectedSize) {
            // Find the specific button that was clicked based on image ID and size
            var button = $('.stock-import-btn[data-image*="' + imageData.id + '"][data-size="' + selectedSize + '"]');
            button.prop('disabled', true);
            // Add spinner overlay
            if (!button.find('.stock-import-spinner').length) {
                button.append('<span class="stock-import-spinner" style="margin-left: -24px; margin-top: 24px; position: absolute;"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10" stroke="#fff" stroke-width="4" opacity="0.2"/><path d="M22 12a10 10 0 0 1-10 10" stroke="#fff" stroke-width="4" stroke-linecap="round"><animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="1s" repeatCount="indefinite"/></path></svg></span>');
                button.css('position', 'relative');
            }
            
            // Get the appropriate image URL based on selected size
            var imageUrl = imageData.urls.regular; // Default to regular size
            if (selectedSize === 'small' && imageData.urls.small) {
                imageUrl = imageData.urls.small;
            } else if (selectedSize === 'full' && imageData.urls.full) {
                imageUrl = imageData.urls.full;
            }
            
            var ajaxData = {
                action: 'stk_img_its_import',
                nonce: stockImagesAjax.nonce,
                image_url: imageUrl,
                image_data: imageData,
                selected_size: selectedSize
            };
            
            $.ajax({
                url: stockImagesAjax.ajaxurl,
                type: 'POST',
                data: ajaxData,
                success: function(response) {
                    if (response.success) {
                        // Change only the clicked button to success state
                        StockImages.changeButtonToSuccess(button);
                        
                        StockImages.showSuccess(stockImagesAjax.strings.imported || 'Image imported successfully!');
                        
                        // If in modal mode, add to media library and close modal
                        if (StockImages.modalMode) {
                            StockImages.addToMediaLibraryAndClose(response.data);
                        } else {
                            // Refresh the media library grid if not in modal and collection exists
                            if (wp.media && wp.media.frame && wp.media.frame.content.get().collection) {
                                wp.media.frame.content.get().collection.props.set({ignore: (+ new Date())});
                                wp.media.frame.content.get().options.selection.reset();
                            }
                            // Add to media library grid if available
                            StockImages.addToMediaGrid(response.data);
                        }
                        
                        // Refresh stats and recent imports
                        StockImages.refreshStats();
                        StockImages.refreshRecentImports();
                    } else {
                        StockImages.showError(response.data);
                    }
                },
                error: function(xhr, status, error) {
                    StockImages.showError('AJAX error: ' + error);
                },
                complete: function() {
                    // Only re-enable button if there was an error (success case is handled above)
                    if (!button.hasClass('stock-import-success')) {
                        button.prop('disabled', false);
                        button.find('.stock-import-spinner').remove();
                    }
                }
            });
        },
        
        changeButtonToSuccess: function(button) {
            // Remove spinner
            button.find('.stock-import-spinner').remove();
            
            // Change button to success state
            button.removeClass('stock-import-circle').addClass('stock-import-success');
            button.prop('disabled', true);
            
            // Replace content with check icon
            button.html('<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z" fill="#fff"/></svg>');
            
            // Add success animation
            button.css({
                'background': '#28a745',
                'border-color': '#28a745',
                'transform': 'scale(1.1)',
                'transition': 'all 0.3s ease'
            });
            
            // Reset scale after animation
            setTimeout(function() {
                button.css('transform', 'scale(1)');
            }, 300);
        },
        
        addToMediaLibraryAndClose: function(data) {
            // Add the imported image to the media library selection
            if (wp.media && wp.media.frame) {
                // Create a new attachment model
                var attachment = wp.media.model.Attachment.get(data.attachment_id);
                
                // Add to selection
                wp.media.frame.content.get().options.selection.add(attachment);
                
                // Close the modal
                wp.media.frame.close();
            }
        },
        
        addToMediaGrid: function(data) {
            // This method can be used to add the imported image to the media grid
            // Implementation depends on the specific WordPress version and media library structure
        },
        
        showLoading: function(append) {
            if (this.modalMode) {
                $('#stock-modal-spinner').show();
            } else {
                if (!append) {
                    $('#stock-results').html('<div class="loading"><p>' + (stockImagesAjax.strings.searching || 'Searching...') + '</p></div>');
                }
            }
        },
        
        showModalLoading: function(append) {
            var loadingHtml = '<div class="stock-loading">' + (stockImagesAjax.strings.searching || 'Searching...') + '</div>';
            
            if (append) {
                $('#stock-modal-results').append(loadingHtml);
            } else {
                $('#stock-modal-results').html(loadingHtml);
            }
        },
        
        hideLoading: function() {
            if (this.modalMode) {
                $('#stock-modal-spinner').hide();
            }
        },
        
        hideModalLoading: function() {
            $('#stock-modal-results .stock-loading').remove();
        },
        
        showSuccess: function(message) {
            this.showNotice(message, 'success');
        },
        
        showError: function(message) {
            if (this.modalMode) {
                $('#stock-modal-message').html('<span style="color:red;">' + message + '</span>');
            } else {
                $('#stock-results').html('<div class="error"><p>' + message + '</p></div>');
            }
        },
        
        showModalError: function(message) {
            $('#stock-modal-results').html('<p class="no-results">' + message + '</p>');
        },
        
        showNotice: function(message, type) {
            var noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
            var noticeHtml = `
                <div class="notice ${noticeClass} is-dismissible">
                    <p>${message}</p>
                    <button type="button" class="notice-dismiss">
                        <span class="screen-reader-text">Dismiss this notice.</span>
                    </button>
                </div>
            `;
            
            $('.wp-header-end').after(noticeHtml);
            
            // Auto-dismiss after 5 seconds
            setTimeout(function() {
                $('.notice').fadeOut();
            }, 5000);
        },
        
        clearResults: function() {
            $('#stock-results').empty();
            $('#stock-load-more-container').hide();
        },
        
        clearModalResults: function() {
            $('#stock-modal-results').empty();
            $('#stock-modal-load-more-container').hide();
        },
        
        refreshStats: function() {
            // Refresh the stats section if it exists
            var statsContainer = $('.stock-stats');
            if (statsContainer.length) {
                $.ajax({
                    url: stockImagesAjax.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'stk_img_its_get_stats',
                        nonce: stockImagesAjax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            // Update the stats numbers
                            $('.stock-stat-number[data-stat="total"]').text(response.data.total_imported);
                            $('.stock-stat-number[data-stat="month"]').text(response.data.this_month);
                            $('.stock-stat-number[data-stat="downloads"]').text(response.data.total_downloads);
                        }
                    }
                });
            }
        },
        
        refreshRecentImports: function() {
            // Refresh the recent imports section if it exists
            var recentContainer = $('.stock-recent-imports');
            if (recentContainer.length) {
                $.ajax({
                    url: stockImagesAjax.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'stk_img_its_get_recent',
                        nonce: stockImagesAjax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            recentContainer.html(response.data.html);
                        }
                    }
                });
            }
        },
        
        bindImportHandlers: function() {
            // Import handler for new markup
            $('.stock-import-btn').off('click').on('click', function(e) {
                e.preventDefault();
                var imageData = $(this).data('image');
                var selectedSize = $(this).data('size');
                if (typeof imageData === 'string') {
                    try {
                        // First decode the URI component, then parse JSON
                        imageData = JSON.parse(decodeURIComponent(imageData));
                    } catch (err) {
                        console.error('Error parsing image data:', err);
                        $('#stock-results').html('<div class="error"><p>Invalid image data.</p></div>');
                        return;
                    }
                }
                StockImages.importImage(imageData, selectedSize);
            });
        }
    };
    
    // Initialize the Stock Images
    StockImages.init();

    var l10n = wp.media.view.l10n;

    // Extending the MediaFrame.Select prototype to add custom tabs and content
    var frame = wp.media.view.MediaFrame.Select;
    wp.media.view.MediaFrame.Select = frame.extend({
        initialize: function () {
            frame.prototype.initialize.apply(this, arguments);

            var State = wp.media.controller.State.extend({
                insert: function () {
                    console.log("Stock Images Insert");
                    this.frame.close();
                }
            });

            this.states.add([
                new State({
                    id: "stk_img_its_tab",
                    search: false,
                    title: "Stock Images"
                })
            ]);

            // On render
            this.on("content:render:stk_img_its_tab", this.renderStockImagesTabContent, this);
        },
        browseRouter: function (routerView) {
            routerView.set({
                upload: {
                    text: l10n.uploadFilesTitle,
                    priority: 20
                },
                browse: {
                    text: l10n.mediaLibraryTitle,
                    priority: 40
                },
                stk_img_its_tab: {
                    text: "Stock Images",
                    priority: 60
                }
            });
        },
        renderStockImagesTabContent: function () {
            var StockImagesTabContent = wp.Backbone.View.extend({
                tagName: "div",
                className: "stock-images-tab-content",
                template: wp.template("stock-images-tab-template"),
                active: false,
                toolbar: null,
                frame: null,
                render: function() {
                    this.$el.html(this.template());
                    // Inject Unsplash search UI
                    renderStockImagesUI(this.$el);
                    return this;
                }
            });

            var view = new StockImagesTabContent();
            this.content.set(view);
        }
    });

    // Add the custom template to the page (just a root div)
    $('body').append('<script type="text/html" id="tmpl-stock-images-tab-template"><div id="stock-images-modal-root"></div></script>');

    // --- Stock Images UI logic (supports both Unsplash and Pexels) ---
    function renderStockImagesUI($root) {
        // Track modal search state
        $root.data('stockPage', 1);
        $root.data('stockQuery', '');
        $root.data('stockTotalPages', 1);
        $root.data('stockSource', 'unsplash');

        // Get configured APIs
        var configuredApis = window.stockImagesAjax && window.stockImagesAjax.configured_apis ? window.stockImagesAjax.configured_apis : [];
        var hasConfiguredApis = configuredApis.length > 0;
        
        var sourceOptions = '';
        if (hasConfiguredApis) {
            configuredApis.forEach(function(api) {
                sourceOptions += '<option value="' + api.value + '">' + api.label + '</option>';
            });
        } else {
            sourceOptions = '<option value="unsplash">Unsplash</option><option value="pexels">Pexels</option><option value="pixabay">Pixabay</option>';
        }

        var html = '' +
            '<div class="stock-modal-search">' +
            '  <select id="stock-modal-source-select" class="stock-source-select"' + (hasConfiguredApis ? '' : ' disabled') + '>' +
            sourceOptions +
            '  </select>' +
            '  <input type="text" id="stock-modal-search-input" placeholder="Search for images..." class="stock-search-input"' + (hasConfiguredApis ? '' : ' disabled') + '>' +
            '  <button type="submit" id="stock-modal-search-btn" class="button button-primary"' + (hasConfiguredApis ? '' : ' disabled') + '>Search</button>' +
            '  <span id="stock-modal-spinner" style="display:none;margin-left:10px;">Searching...</span>' +
            '</div>' +
            '<div id="stock-modal-results">' +
            (hasConfiguredApis ? '' : '<p>Please configure at least one API key in the settings to start searching.</p>') +
            '</div>' +
            '<div id="stock-modal-load-more-container" class="stock-load-more-container" style="display:none;">' +
            '  <button id="stock-modal-load-more" class="button">Load More</button>' +
            '</div>' +
            '<div id="stock-modal-message" style="margin-top:10px;"></div>';
        $root.find('#stock-images-modal-root').html(html);

        // Only bind events if APIs are configured
        if (hasConfiguredApis) {
            // Source selector change handler
            $root.find('#stock-modal-source-select').on('change', function() {
                var selectedSource = $(this).val();
                $root.data('stockSource', selectedSource);
                
                // If there's an active query, trigger a new search
                var currentQuery = $root.data('stockQuery');
                if (currentQuery) {
                    $root.data('stockPage', 1);
                    performModalSearch($root, currentQuery, 1, false);
                }
            });

            // Search handler
            $root.find('#stock-modal-search-btn').on('click', function(e) {
                e.preventDefault();
                var query = $root.find('#stock-modal-search-input').val().trim();
                if (!query) return;
                
                $root.data('stockQuery', query);
                $root.data('stockPage', 1);
                performModalSearch($root, query, 1, false);
            });

            // Search input with debounce
            var searchTimeout;
            $root.find('#stock-modal-search-input').on('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    var query = $root.find('#stock-modal-search-input').val().trim();
                    if (!query) {
                        $root.find('#stock-modal-results').empty();
                        $root.find('#stock-modal-load-more-container').hide();
                        return;
                    }
                    $root.data('stockQuery', query);
                    $root.data('stockPage', 1);
                    performModalSearch($root, query, 1, false);
                }, 500);
            });

            // Load More handler
            $root.on('click', '#stock-modal-load-more', function(e) {
                e.preventDefault();
                var query = $root.data('stockQuery');
                var page = $root.data('stockPage') + 1;
                var totalPages = $root.data('stockTotalPages') || 1;
                if (!query || page > totalPages) return;
                
                performModalSearch($root, query, page, true);
            });
        }
    }

    function performModalSearch($root, query, page, append) {
        var selectedSource = $root.find('#stock-modal-source-select').val() || 'unsplash';
        
        $root.find('#stock-modal-spinner').show();
        $root.find('#stock-modal-message').empty();
        
        if (!append) {
            $root.find('#stock-modal-results').empty();
        }
        
        $.ajax({
            url: stockImagesAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'stk_img_its_search',
                nonce: stockImagesAjax.nonce,
                query: query,
                page: page,
                source: selectedSource
            },
            success: function(response) {
                $root.find('#stock-modal-spinner').hide();
                if (response.success && response.data.results && response.data.results.length) {
                    renderModalResults($root, response.data.results, append);
                    $root.data('stockTotalPages', response.data.total_pages || 1);
                    $root.data('stockPage', page);
                    
                    if (page < (response.data.total_pages || 1)) {
                        $root.find('#stock-modal-load-more-container').show();
                    } else {
                        $root.find('#stock-modal-load-more-container').hide();
                    }
                } else {
                    if (!append) {
                        $root.find('#stock-modal-results').html('<p>No images found.</p>');
                    }
                    $root.find('#stock-modal-load-more-container').hide();
                }
            },
            error: function(xhr, status, error) {
                $root.find('#stock-modal-spinner').hide();
                $root.find('#stock-modal-message').html('<span style="color:red;">Error searching ' + selectedSource + '.</span>');
                $root.find('#stock-modal-load-more-container').hide();
            }
        });
    }

    function renderModalResults($root, results, append) {
        var html = '';
        results.forEach(function(img) {
            html += StockImages.createImageCard(img);
        });
        
        if (append) {
            $root.find('#stock-modal-results').append(html);
        } else {
            $root.find('#stock-modal-results').html(html);
        }

        // Import handler for new markup
        $root.find('.stock-import-btn').off('click').on('click', function(e) {
            e.preventDefault();
            var imageData = $(this).data('image');
            var selectedSize = $(this).data('size');
            if (typeof imageData === 'string') {
                try {
                    // First decode the URI component, then parse JSON
                    imageData = JSON.parse(decodeURIComponent(imageData));
                } catch (err) {
                    console.error('Error parsing image data:', err);
                    $root.find('#stock-modal-message').html('<span style="color:red;">Invalid image data.</span>');
                    return;
                }
            }
            StockImages.importImage(imageData, selectedSize);
        });
    }
}); 