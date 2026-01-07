// Main JavaScript file

document.addEventListener('DOMContentLoaded', function() {
    // Enhanced city search functionality
    const cityInput = document.getElementById('ville');
    if (cityInput) {
        // Make the city input more searchable
        cityInput.addEventListener('input', function(e) {
            const value = e.target.value.toLowerCase();
            const datalist = document.getElementById('belgian-cities');
            const options = datalist.querySelectorAll('option');
            
            // Highlight matching cities (optional visual feedback)
            options.forEach(option => {
                if (option.value.toLowerCase().includes(value) || value === '') {
                    option.style.display = '';
                } else {
                    option.style.display = 'none';
                }
            });
        });

        // Allow typing to filter and select
        cityInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const datalist = document.getElementById('belgian-cities');
                const options = Array.from(datalist.querySelectorAll('option'));
                const value = this.value.toLowerCase();
                
                // Find first matching option
                const match = options.find(opt => 
                    opt.value.toLowerCase().startsWith(value)
                );
                
                if (match) {
                    this.value = match.value;
                }
            }
        });
    }

    // Hover dropdown menu for user menu
    const userMenuDropdown = document.getElementById('userMenuDropdown');
    if (userMenuDropdown) {
        const dropdownItem = userMenuDropdown.closest('.dropdown-hover');
        const dropdownMenu = dropdownItem ? dropdownItem.querySelector('.dropdown-menu') : null;
        
        if (dropdownItem && dropdownMenu) {
            let hoverTimeout;
            
            // Show on hover
            dropdownItem.addEventListener('mouseenter', function() {
                clearTimeout(hoverTimeout);
                const bsDropdown = new bootstrap.Dropdown(userMenuDropdown);
                bsDropdown.show();
            });
            
            // Hide when mouse leaves
            dropdownItem.addEventListener('mouseleave', function() {
                hoverTimeout = setTimeout(function() {
                    const bsDropdown = bootstrap.Dropdown.getInstance(userMenuDropdown);
                    if (bsDropdown) {
                        bsDropdown.hide();
                    }
                }, 200); // Small delay to allow moving to dropdown menu
            });
            
            // Keep open when hovering over dropdown menu
            if (dropdownMenu) {
                dropdownMenu.addEventListener('mouseenter', function() {
                    clearTimeout(hoverTimeout);
                });
                
                dropdownMenu.addEventListener('mouseleave', function() {
                    hoverTimeout = setTimeout(function() {
                        const bsDropdown = bootstrap.Dropdown.getInstance(userMenuDropdown);
                        if (bsDropdown) {
                            bsDropdown.hide();
                        }
                    }, 200);
                });
            }
        }
    }

    // Blur text animation for ENLACE title
    const heroTitle = document.getElementById('hero-title-enlace');
    if (heroTitle) {
        const text = heroTitle.textContent;
        const letters = text.split('');
        
        // Wrap each letter in a span
        heroTitle.innerHTML = letters.map((letter, index) => {
            if (letter === ' ') {
                return '<span class="blur-letter" style="display: inline-block;">&nbsp;</span>';
            }
            return `<span class="blur-letter" style="display: inline-block; will-change: transform, filter, opacity;" data-index="${index}">${letter}</span>`;
        }).join('');

        // Intersection Observer to trigger animation when in view
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        animateBlurText(heroTitle);
                        observer.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.1, rootMargin: '0px' }
        );

        observer.observe(heroTitle);
    }

    function animateBlurText(element) {
        const letters = element.querySelectorAll('.blur-letter');
        const delay = 150; // delay between each letter in ms
        const stepDuration = 600; // duration of each step in ms
        const totalSteps = 2;

        letters.forEach((letter, index) => {
            const letterDelay = index * delay;
            
            setTimeout(() => {
                // Step 1: Blur 5px, opacity 0.5, slight movement
                setTimeout(() => {
                    letter.style.filter = 'blur(5px)';
                    letter.style.opacity = '0.5';
                    letter.style.transform = 'translateY(5px)';
                }, 0);

                // Step 2: No blur, full opacity, final position
                setTimeout(() => {
                    letter.style.filter = 'blur(0px)';
                    letter.style.opacity = '1';
                    letter.style.transform = 'translateY(0)';
                }, stepDuration);

                // Set initial state
                letter.style.filter = 'blur(10px)';
                letter.style.opacity = '0';
                letter.style.transform = 'translateY(-50px)';
                letter.style.transition = `all ${stepDuration}ms cubic-bezier(0.4, 0, 0.2, 1)`;
            }, letterDelay);
        });
    }

    // Profile photo preview handler (reusable)
    function initPhotoPreview(inputId, previewId, previewClass = 'service-photo-preview-img') {
        const photoInput = document.getElementById(inputId);
        const photoPreview = document.getElementById(previewId);
        
        if (!photoInput || !photoPreview) {
            return;
        }
        
        photoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) {
                return;
            }
            
            // Check if it's an image
            if (!file.type.match('image.*')) {
                alert('Veuillez sélectionner une image.');
                this.value = '';
                return;
            }
            
            // Check file size (max 5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('L\'image est trop grande. Taille maximum : 5MB.');
                this.value = '';
                return;
            }
            
            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                photoPreview.innerHTML = '<img src="' + e.target.result + '" alt="Photo de profil" class="' + previewClass + '">';
                // Add class to indicate image is loaded
                photoPreview.classList.add('has-image');
            };
            reader.readAsDataURL(file);
        });
    }

    // Initialize photo preview for offering-service form
    initPhotoPreview('profile_photo', 'photo-preview', 'service-photo-preview-img');
    
    // Initialize photo preview for seeking-service form
    initPhotoPreview('seeking_profile_photo', 'seeking-photo-preview', 'seeking-photo-preview-img');

    // Profile edit form functionality
    const editBtn = document.getElementById('edit-profile-btn');
    const editFormWrapper = document.getElementById('profile-edit-form-wrapper');
    const closeBtn = document.getElementById('close-edit-form');
    const cancelBtn = document.getElementById('cancel-edit-form');
    
    function closeEditForm() {
        if (editFormWrapper) {
            editFormWrapper.style.display = 'none';
        }
    }
    
    if (editBtn && editFormWrapper) {
        editBtn.addEventListener('click', function() {
            editFormWrapper.style.display = 'block';
            editFormWrapper.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    }
    
    if (closeBtn) {
        closeBtn.addEventListener('click', closeEditForm);
    }
    
    if (cancelBtn) {
        cancelBtn.addEventListener('click', closeEditForm);
    }
    
    // Profile edit photo preview
    const editPhotoInput = document.getElementById('edit_profile_photo');
    if (editPhotoInput) {
        editPhotoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) {
                return;
            }
            
            if (!file.type.match('image.*')) {
                alert('Veuillez sélectionner une image.');
                this.value = '';
                return;
            }
            
            if (file.size > 5 * 1024 * 1024) {
                alert('L\'image est trop grande. Taille maximum : 5MB.');
                this.value = '';
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewSection = document.querySelector('.profile-edit-photo-section');
                if (previewSection) {
                    let previewImg = previewSection.querySelector('.profile-edit-photo-preview-new');
                    if (!previewImg) {
                        previewImg = document.createElement('img');
                        previewImg.className = 'profile-edit-photo-preview-new';
                        previewSection.insertBefore(previewImg, editPhotoInput);
                    }
                    previewImg.src = e.target.result;
                    previewImg.style.display = 'block';
                }
            };
            reader.readAsDataURL(file);
        });
    }

    // Add production form toggle
    const addProductionBtn = document.getElementById('add-production-btn');
    const cancelAddProduction = document.getElementById('cancel-add-production');
    const addProductionFormWrapper = document.getElementById('add-production-form-wrapper');

    if (addProductionBtn && addProductionFormWrapper) {
        addProductionBtn.addEventListener('click', function() {
            const isHidden = addProductionFormWrapper.style.display === 'none' || addProductionFormWrapper.style.display === '';
            addProductionFormWrapper.style.display = isHidden ? 'block' : 'none';
            if (isHidden) {
                addProductionFormWrapper.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    }

    if (cancelAddProduction && addProductionFormWrapper) {
        cancelAddProduction.addEventListener('click', function() {
            addProductionFormWrapper.style.display = 'none';
        });
    }

    // Contact Modal
    const contactBtn = document.getElementById('contact-btn');
    const contactModal = document.getElementById('contact-modal');
    const contactModalOverlay = document.getElementById('contact-modal-overlay');
    const contactModalClose = document.getElementById('contact-modal-close');

    function openContactModal() {
        if (contactModal) {
            contactModal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeContactModal() {
        if (contactModal) {
            contactModal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    if (contactBtn) {
        contactBtn.addEventListener('click', openContactModal);
    }

    if (contactModalOverlay) {
        contactModalOverlay.addEventListener('click', closeContactModal);
    }

    if (contactModalClose) {
        contactModalClose.addEventListener('click', closeContactModal);
    }

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && contactModal && contactModal.classList.contains('active')) {
            closeContactModal();
        }
    });

    // Form validation and error highlighting for service registration forms
    function highlightFormErrors() {
        if (typeof formErrors !== 'undefined' && formErrors.length > 0) {
            formErrors.forEach(function(fieldName) {
                const field = document.getElementById(fieldName);
                const errorMsg = document.getElementById(fieldName + '-error');
                
                if (field) {
                    field.classList.add('is-invalid');
                    field.focus();
                }
                
                if (errorMsg) {
                    errorMsg.style.display = 'block';
                }
            });
        }
    }

    // Highlight errors on page load
    highlightFormErrors();

    // Form submission handler with spinner
    const offeringForm = document.querySelector('.service-form');
    const seekingForm = document.querySelector('.service-form'); // Same class, will be detected by button ID
    
    function setupFormSubmission(form, submitBtnId) {
        if (!form) return;
        
        const submitBtn = document.getElementById(submitBtnId);
        if (!submitBtn) return;
        
        form.addEventListener('submit', function(e) {
            // Client-side validation
            let isValid = true;
            const errors = [];
            
            // Validate biographie
            const biographie = form.querySelector('#biographie');
            if (biographie && (!biographie.value || biographie.value.trim() === '')) {
                e.preventDefault();
                biographie.classList.add('is-invalid');
                const errorMsg = document.getElementById('biographie-error');
                if (errorMsg) errorMsg.style.display = 'block';
                isValid = false;
                errors.push('biographie');
            } else if (biographie) {
                biographie.classList.remove('is-invalid');
                const errorMsg = document.getElementById('biographie-error');
                if (errorMsg) errorMsg.style.display = 'none';
            }
            
            // Validate genre
            const genre = form.querySelector('#genre');
            if (genre && (!genre.value || genre.value.trim() === '')) {
                e.preventDefault();
                genre.classList.add('is-invalid');
                const errorMsg = document.getElementById('genre-error');
                if (errorMsg) errorMsg.style.display = 'block';
                isValid = false;
                errors.push('genre');
            } else if (genre) {
                genre.classList.remove('is-invalid');
                const errorMsg = document.getElementById('genre-error');
                if (errorMsg) errorMsg.style.display = 'none';
            }
            
            // Validate filters/music genres based on form type
            const filters = form.querySelectorAll('input[name="filters[]"]:checked');
            const musicGenres = form.querySelectorAll('input[name="music_genres[]"]:checked');
            
            // Determine form type by checking which checkboxes exist
            const hasFiltersInputs = form.querySelector('input[name="filters[]"]') !== null;
            const hasMusicGenresInputs = form.querySelector('input[name="music_genres[]"]') !== null;
            
            if (hasFiltersInputs) {
                // Offering service - validate filters
                if (filters.length === 0) {
                    e.preventDefault();
                    const errorMsg = document.getElementById('filters-error');
                    if (errorMsg) errorMsg.style.display = 'block';
                    isValid = false;
                    errors.push('filters');
                } else {
                    const errorMsg = document.getElementById('filters-error');
                    if (errorMsg) errorMsg.style.display = 'none';
                }
            } else if (hasMusicGenresInputs) {
                // Seeking service - validate music genres
                if (musicGenres.length === 0) {
                    e.preventDefault();
                    const errorMsg = document.getElementById('music_genres-error');
                    if (errorMsg) errorMsg.style.display = 'block';
                    isValid = false;
                    errors.push('music_genres');
                } else {
                    const errorMsg = document.getElementById('music_genres-error');
                    if (errorMsg) errorMsg.style.display = 'none';
                }
            }
            
            if (!isValid) {
                // Scroll to first error
                if (errors.length > 0) {
                    const firstErrorField = document.getElementById(errors[0]);
                    if (firstErrorField) {
                        firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
                return false;
            }
            
            // Show spinner and disable button only if validation passes
            const btnText = submitBtn.querySelector('.btn-text');
            const spinner = submitBtn.querySelector('.spinner-border');
            
            if (btnText) btnText.textContent = 'Traitement...';
            if (spinner) spinner.classList.remove('d-none');
            
            // Don't disable the button - let it submit naturally
            // The form will submit and the page will reload/redirect
        });
    }
    
    // Setup form submission for both offering and seeking forms
    if (offeringForm) {
        setupFormSubmission(offeringForm, 'offering-submit-btn');
    }
    
    if (seekingForm) {
        setupFormSubmission(seekingForm, 'seeking-submit-btn');
    }
    
    // Remove error highlighting on input
    function setupFieldValidation(fieldId, errorId) {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('input', function() {
                this.classList.remove('is-invalid');
                const errorMsg = document.getElementById(errorId);
                if (errorMsg) errorMsg.style.display = 'none';
            });
        }
    }
    
    // Setup validation for all fields
    setupFieldValidation('biographie', 'biographie-error');
    setupFieldValidation('genre', 'genre-error');
    
    // Setup validation for checkboxes
    const filterCheckboxes = document.querySelectorAll('input[name="filters[]"], input[name="music_genres[]"]');
    filterCheckboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const filterErrorMsg = document.getElementById('filters-error');
            const genreErrorMsg = document.getElementById('music_genres-error');
            if (filterErrorMsg) filterErrorMsg.style.display = 'none';
            if (genreErrorMsg) genreErrorMsg.style.display = 'none';
        });
    });

});

// Announcements Page Functionality
document.addEventListener('DOMContentLoaded', function() {
    // Add Announcement Form Toggle
    const addAnnonceBtn = document.getElementById('add-annonce-btn');
    const addAnnonceFormWrapper = document.getElementById('add-annonce-form-wrapper');
    const closeAnnonceForm = document.getElementById('close-annonce-form');
    const cancelAnnonceForm = document.getElementById('cancel-annonce-form');

    if (addAnnonceBtn && addAnnonceFormWrapper) {
        addAnnonceBtn.addEventListener('click', function() {
            addAnnonceFormWrapper.style.display = 'block';
            document.body.style.overflow = 'hidden';
        });
    }

    if (closeAnnonceForm && addAnnonceFormWrapper) {
        closeAnnonceForm.addEventListener('click', function() {
            addAnnonceFormWrapper.style.display = 'none';
            document.body.style.overflow = '';
        });
    }

    if (cancelAnnonceForm && addAnnonceFormWrapper) {
        cancelAnnonceForm.addEventListener('click', function() {
            addAnnonceFormWrapper.style.display = 'none';
            document.body.style.overflow = '';
        });
    }

    // Announcement Modal
    const annonceCards = document.querySelectorAll('.annonce-card');
    const annonceModalOverlay = document.getElementById('annonce-modal-overlay');
    const annonceModalBody = document.getElementById('annonce-modal-body');
    const closeAnnonceModal = document.getElementById('close-annonce-modal');

    // Escape HTML to prevent XSS
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Escape URL attribute
    function escapeUrl(url) {
        if (!url) return '#';
        // Basic URL validation - only allow http, https, and relative URLs
        try {
            const parsed = new URL(url, window.location.origin);
            if (parsed.protocol === 'http:' || parsed.protocol === 'https:' || url.startsWith('/')) {
                return url.replace(/"/g, '&quot;').replace(/'/g, '&#x27;');
            }
        } catch (e) {
            // If URL parsing fails, check if it's a relative URL
            if (url.startsWith('/') || url.startsWith('#')) {
                return url.replace(/"/g, '&quot;').replace(/'/g, '&#x27;');
            }
        }
        return '#';
    }

    // Load announcement details
    function loadAnnonceDetails(annonceId) {
        const card = document.querySelector(`.annonce-card[data-annonce-id="${annonceId}"]`);
        if (!card) return;

        const title = card.querySelector('.annonce-card-title')?.textContent || '';
        const description = card.querySelector('.annonce-card-description')?.textContent || '';
        const location = card.querySelector('.annonce-card-location span')?.textContent || '';
        const image = card.querySelector('.annonce-card-image img')?.src || '';
        const imagePlaceholder = card.querySelector('.annonce-card-placeholder');
        
        // Get author data from data attributes - these should be escaped on server side
        const authorId = card.getAttribute('data-author-id');
        const authorName = card.getAttribute('data-author-name') || 'Utilisateur';
        const authorUrl = card.getAttribute('data-author-url') || '#';

        // Create modal using DOM methods instead of innerHTML to prevent XSS
        if (!annonceModalBody) return;
        
        // Clear previous content
        annonceModalBody.innerHTML = '';
        
        // Create image container
        const imageContainer = document.createElement('div');
        imageContainer.className = 'annonce-modal-image';
        
        if (image) {
            const img = document.createElement('img');
            img.src = escapeUrl(image);
            img.alt = escapeHtml(title);
            imageContainer.appendChild(img);
        } else if (imagePlaceholder) {
            const placeholder = document.createElement('div');
            placeholder.className = 'annonce-modal-image-placeholder';
            placeholder.innerHTML = '<svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 16L8.586 11.414C9.367 10.633 10.633 10.633 11.414 11.414L16 16M14 14L15.586 12.414C16.367 11.633 17.633 11.633 18.414 12.414L22 16M18 8V6C18 4.895 17.105 4 16 4H8C6.895 4 6 4.895 6 6V18C6 19.105 6.895 20 8 20H16C17.105 20 18 19.105 18 18V16M18 8H20C21.105 8 22 8.895 22 10V18C22 19.105 21.105 20 20 20H12C10.895 20 10 19.105 10 18V10C10 8.895 10.895 8 12 8H14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
            imageContainer.appendChild(placeholder);
        }
        annonceModalBody.appendChild(imageContainer);
        
        // Create details container
        const detailsContainer = document.createElement('div');
        detailsContainer.className = 'annonce-modal-details';
        
        // Title
        const titleEl = document.createElement('h2');
        titleEl.className = 'annonce-modal-title';
        titleEl.textContent = title;
        detailsContainer.appendChild(titleEl);
        
        // Description
        const descEl = document.createElement('p');
        descEl.className = 'annonce-modal-description';
        descEl.textContent = description;
        detailsContainer.appendChild(descEl);
        
        // Author
        const authorEl = document.createElement('div');
        authorEl.className = 'annonce-modal-author';
        authorEl.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
        const authorLink = document.createElement('a');
        authorLink.href = escapeUrl(authorUrl);
        authorLink.className = 'annonce-modal-author-link';
        authorLink.textContent = authorName;
        authorEl.appendChild(authorLink);
        detailsContainer.appendChild(authorEl);
        
        // Location
        if (location) {
            const locationEl = document.createElement('div');
            locationEl.className = 'annonce-modal-location';
            locationEl.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M21 10C21 17 12 23 12 23C12 23 3 17 3 10C3 7.61305 3.94821 5.32387 5.63604 3.63604C7.32387 1.94821 9.61305 1 12 1C14.3869 1 16.6761 1.94821 18.364 3.63604C20.0518 5.32387 21 7.61305 21 10Z" stroke="currentColor" stroke-width="1.5"/><circle cx="12" cy="10" r="3" stroke="currentColor" stroke-width="1.5"/></svg>';
            const locationSpan = document.createElement('span');
            locationSpan.textContent = location;
            locationEl.appendChild(locationSpan);
            detailsContainer.appendChild(locationEl);
        }
        
        annonceModalBody.appendChild(detailsContainer);
    }

    // Open modal when clicking on a card
    annonceCards.forEach(function(card) {
        card.addEventListener('click', function(e) {
            if (e.target.closest('.annonce-bookmark-btn')) {
                return;
            }
            
            const annonceId = card.getAttribute('data-annonce-id');
            if (annonceId && annonceModalOverlay) {
                loadAnnonceDetails(annonceId);
                annonceModalOverlay.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        });
    });

    // Close modal
    if (closeAnnonceModal && annonceModalOverlay) {
        closeAnnonceModal.addEventListener('click', function() {
            annonceModalOverlay.style.display = 'none';
            document.body.style.overflow = '';
        });
    }

    // Close modal when clicking on overlay
    if (annonceModalOverlay) {
        annonceModalOverlay.addEventListener('click', function(e) {
            if (e.target === annonceModalOverlay) {
                annonceModalOverlay.style.display = 'none';
                document.body.style.overflow = '';
            }
        });
    }

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && annonceModalOverlay && annonceModalOverlay.style.display === 'flex') {
            annonceModalOverlay.style.display = 'none';
            document.body.style.overflow = '';
        }
    });

    // Favorites functionality
    function toggleFavorite(button) {
        if (!button) return;
        
        const itemType = button.getAttribute('data-item-type');
        const itemId = button.getAttribute('data-item-id');
        
        if (!itemType || !itemId) return;
        
        // Disable button during request
        button.disabled = true;
        const originalHTML = button.innerHTML;
        
        // Show loading state
        button.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        
        // Make AJAX request
        if (typeof enlaceAjax === 'undefined') {
            alert('Erreur de configuration. Veuillez rafraîchir la page.');
            button.disabled = false;
            button.innerHTML = originalHTML;
            return;
        }
        
        if (!enlaceAjax.favorites_nonce) {
            alert('Erreur de sécurité. Veuillez rafraîchir la page.');
            button.disabled = false;
            button.innerHTML = originalHTML;
            return;
        }
        
        jQuery.ajax({
            url: enlaceAjax.ajaxurl || ajaxurl || '/wp-admin/admin-ajax.php',
            type: 'POST',
            data: {
                action: 'toggle_favorite',
                nonce: enlaceAjax.favorites_nonce,
                item_type: itemType,
                item_id: itemId
            },
            success: function(response) {
                if (response.success) {
                    // Update button state
                    const isFavorited = response.data.is_favorited;
                    button.classList.toggle('favorited', isFavorited);
                    
                    // Update SVG fill
                    const svg = button.querySelector('svg');
                    if (svg) {
                        svg.setAttribute('fill', isFavorited ? 'currentColor' : 'none');
                    }
                    
                    // Update aria-label
                    button.setAttribute('aria-label', isFavorited ? 'Retirer des favoris' : 'Ajouter aux favoris');
                    
                    // If it's a remove button in favorites list, remove the item
                    if (button.classList.contains('favorite-remove-btn')) {
                        button.closest('.favorite-user-item, .favorite-annonce-item')?.remove();
                        
                        // Check if favorites list is now empty
                        const favoritesSection = document.querySelector('.profile-favorites-section');
                        if (favoritesSection) {
                            const remainingItems = favoritesSection.querySelectorAll('.favorite-user-item, .favorite-annonce-item');
                            if (remainingItems.length === 0) {
                                const emptyMsg = document.createElement('p');
                                emptyMsg.className = 'favorites-empty';
                                emptyMsg.textContent = 'Aucun favori pour le moment. Ajoutez des utilisateurs ou des annonces à vos favoris !';
                                favoritesSection.appendChild(emptyMsg);
                            }
                        }
                    }
                } else {
                    alert(response.data.message || 'Erreur lors de l\'opération');
                    button.innerHTML = originalHTML;
                }
                button.disabled = false;
            },
            error: function() {
                alert('Erreur de connexion. Veuillez réessayer.');
                button.disabled = false;
                button.innerHTML = originalHTML;
            }
        });
    }
    
    // Handle bookmark buttons on annonces
    document.addEventListener('click', function(e) {
        // Check for annonce bookmark button
        const annonceBtn = e.target.closest('.annonce-bookmark-btn');
        if (annonceBtn) {
            e.preventDefault();
            e.stopPropagation();
            toggleFavorite(annonceBtn);
            return;
        }
        
        // Handle favorite buttons on user profiles
        const profileBtn = e.target.closest('.btn-profile-favorite');
        if (profileBtn) {
            e.preventDefault();
            e.stopPropagation();
            toggleFavorite(profileBtn);
            return;
        }
        
        // Handle remove favorite buttons
        const removeBtn = e.target.closest('.favorite-remove-btn');
        if (removeBtn) {
            e.preventDefault();
            e.stopPropagation();
            toggleFavorite(removeBtn);
            return;
        }
    });
    
    // Production Comments Functionality
    function toggleComments(button) {
        const productionId = button.getAttribute('data-production-id');
        const commentsContainer = document.getElementById('comments-' + productionId);
        
        if (!commentsContainer) return;
        
        const isVisible = commentsContainer.style.display !== 'none';
        commentsContainer.style.display = isVisible ? 'none' : 'block';
        
        // Load comments if not already loaded
        if (!isVisible && commentsContainer.querySelector('.production-comments-list').children.length === 0) {
            loadComments(productionId);
        }
    }
    
    function loadComments(productionId) {
        if (typeof enlaceAjax === 'undefined' || !enlaceAjax.production_comments_nonce) {
            return;
        }
        
        jQuery.ajax({
            url: enlaceAjax.ajaxurl || ajaxurl || '/wp-admin/admin-ajax.php',
            type: 'POST',
            data: {
                action: 'get_production_comments',
                nonce: enlaceAjax.production_comments_nonce,
                production_id: productionId
            },
            success: function(response) {
                if (response.success && response.data.comments) {
                    const commentsList = document.getElementById('comments-list-' + productionId);
                    if (commentsList) {
                        renderComments(commentsList, response.data.comments, productionId);
                    }
                }
            },
            error: function() {
                // Silent fail - comments will not load
            }
        });
    }
    
    function renderComments(container, comments, productionId) {
        if (comments.length === 0) {
            container.innerHTML = '<p class="production-comments-empty">Aucun commentaire pour le moment.</p>';
            return;
        }
        
        const currentUserId = typeof wpApiSettings !== 'undefined' ? wpApiSettings.currentUserId : 0;
        const productionOwnerId = container.closest('.production-item')?.querySelector('.add-production-comment-form')?.getAttribute('data-production-owner-id') || 0;
        
        let html = '';
        comments.forEach(function(comment) {
            const canDelete = comment.is_own || (currentUserId == productionOwnerId);
            html += '<div class="production-comment-item" data-comment-id="' + comment.id + '">';
            html += '<div class="production-comment-avatar">';
            if (comment.user_photo) {
                html += '<img src="' + comment.user_photo + '" alt="' + comment.user_name + '">';
            } else {
                html += '<div class="production-comment-avatar-placeholder">';
                html += '<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z" stroke="currentColor" stroke-width="1.5"/></svg>';
                html += '</div>';
            }
            html += '</div>';
            html += '<div class="production-comment-content">';
            html += '<div class="production-comment-header">';
            html += '<span class="production-comment-author">' + comment.user_name + '</span>';
            html += '<span class="production-comment-date">' + getTimeAgo(comment.created_at) + '</span>';
            if (canDelete) {
                html += '<button class="production-comment-delete" data-comment-id="' + comment.id + '" aria-label="Supprimer">';
                html += '<svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2"/></svg>';
                html += '</button>';
            }
            html += '</div>';
            html += '<p class="production-comment-text">' + escapeHtml(comment.comment).replace(/\n/g, '<br>') + '</p>';
            html += '</div>';
            html += '</div>';
        });
        container.innerHTML = html;
    }
    
    function getTimeAgo(dateString) {
        const now = new Date();
        const date = new Date(dateString);
        const diff = Math.floor((now - date) / 1000);
        
        if (diff < 60) return 'à l\'instant';
        if (diff < 3600) return 'il y a ' + Math.floor(diff / 60) + ' min';
        if (diff < 86400) return 'il y a ' + Math.floor(diff / 3600) + ' h';
        if (diff < 604800) return 'il y a ' + Math.floor(diff / 86400) + ' j';
        return date.toLocaleDateString('fr-FR');
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Handle comment toggle
    document.addEventListener('click', function(e) {
        if (e.target.closest('.production-comments-toggle')) {
            e.preventDefault();
            e.stopPropagation();
            const button = e.target.closest('.production-comments-toggle');
            toggleComments(button);
        }
    });
    
    // Handle add comment
    document.addEventListener('submit', function(e) {
        if (e.target.closest('.add-production-comment-form')) {
            e.preventDefault();
            const form = e.target.closest('.add-production-comment-form');
            const productionId = form.getAttribute('data-production-id');
            const productionOwnerId = form.getAttribute('data-production-owner-id');
            const textarea = form.querySelector('.production-comment-input');
            const comment = textarea.value.trim();
            
            if (!comment) return;
            
            if (typeof enlaceAjax === 'undefined' || !enlaceAjax.production_comments_nonce) {
                alert('Erreur de configuration. Veuillez rafraîchir la page.');
                return;
            }
            
            const submitBtn = form.querySelector('.production-comment-submit');
            const originalHTML = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            
            jQuery.ajax({
                url: enlaceAjax.ajaxurl || ajaxurl || '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: {
                    action: 'add_production_comment',
                    nonce: enlaceAjax.production_comments_nonce,
                    production_id: productionId,
                    production_owner_id: productionOwnerId,
                    comment: comment
                },
                success: function(response) {
                    if (response.success) {
                        textarea.value = '';
                        const commentsList = document.getElementById('comments-list-' + productionId);
                        if (commentsList) {
                            // Add new comment to list
                            const comment = response.data.comment;
                            const commentHtml = '<div class="production-comment-item" data-comment-id="' + comment.id + '">' +
                                '<div class="production-comment-avatar">' +
                                (comment.user_photo ? '<img src="' + comment.user_photo + '" alt="' + comment.user_name + '">' : 
                                '<div class="production-comment-avatar-placeholder"><svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z" stroke="currentColor" stroke-width="1.5"/></svg></div>') +
                                '</div>' +
                                '<div class="production-comment-content">' +
                                '<div class="production-comment-header">' +
                                '<span class="production-comment-author">' + comment.user_name + '</span>' +
                                '<span class="production-comment-date">à l\'instant</span>' +
                                '<button class="production-comment-delete" data-comment-id="' + comment.id + '" aria-label="Supprimer">' +
                                '<svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2"/></svg>' +
                                '</button>' +
                                '</div>' +
                                '<p class="production-comment-text">' + escapeHtml(comment.comment).replace(/\n/g, '<br>') + '</p>' +
                                '</div>' +
                                '</div>';
                            
                            // Remove empty message if exists
                            const emptyMsg = commentsList.querySelector('.production-comments-empty');
                            if (emptyMsg) emptyMsg.remove();
                            
                            commentsList.insertAdjacentHTML('beforeend', commentHtml);
                            
                            // Update comment count
                            const toggleBtn = form.closest('.production-comments-container').previousElementSibling;
                            if (toggleBtn) {
                                const countSpan = toggleBtn.querySelector('.comments-count');
                                if (countSpan) {
                                    const newCount = parseInt(countSpan.textContent) + 1;
                                    countSpan.textContent = newCount;
                                    const labelSpan = toggleBtn.querySelector('.comments-label');
                                    if (labelSpan) {
                                        labelSpan.textContent = newCount == 1 ? 'commentaire' : 'commentaires';
                                    }
                                }
                            }
                        }
                    } else {
                        alert(response.data.message || 'Erreur lors de l\'ajout du commentaire.');
                    }
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalHTML;
                },
                error: function() {
                    alert('Erreur de connexion. Veuillez réessayer.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalHTML;
                }
            });
        }
    });
    
    // Handle delete comment
    document.addEventListener('click', function(e) {
        if (e.target.closest('.production-comment-delete')) {
            e.preventDefault();
            e.stopPropagation();
            const button = e.target.closest('.production-comment-delete');
            const commentId = button.getAttribute('data-comment-id');
            const commentItem = button.closest('.production-comment-item');
            
            if (!confirm('Êtes-vous sûr de vouloir supprimer ce commentaire ?')) {
                return;
            }
            
            if (typeof enlaceAjax === 'undefined' || !enlaceAjax.production_comments_nonce) {
                alert('Erreur de configuration.');
                return;
            }
            
            button.disabled = true;
            
            jQuery.ajax({
                url: enlaceAjax.ajaxurl || ajaxurl || '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: {
                    action: 'delete_production_comment',
                    nonce: enlaceAjax.production_comments_nonce,
                    comment_id: commentId
                },
                success: function(response) {
                    if (response.success) {
                        commentItem.remove();
                        
                        // Update comment count
                        const productionItem = commentItem.closest('.production-item');
                        if (productionItem) {
                            const toggleBtn = productionItem.querySelector('.production-comments-toggle');
                            if (toggleBtn) {
                                const countSpan = toggleBtn.querySelector('.comments-count');
                                if (countSpan) {
                                    const newCount = Math.max(0, parseInt(countSpan.textContent) - 1);
                                    countSpan.textContent = newCount;
                                    const labelSpan = toggleBtn.querySelector('.comments-label');
                                    if (labelSpan) {
                                        labelSpan.textContent = newCount == 1 ? 'commentaire' : 'commentaires';
                                    }
                                }
                            }
                            
                            // Show empty message if no comments left
                            const commentsList = productionItem.querySelector('.production-comments-list');
                            if (commentsList && commentsList.querySelectorAll('.production-comment-item').length === 0) {
                                commentsList.innerHTML = '<p class="production-comments-empty">Aucun commentaire pour le moment.</p>';
                            }
                        }
                    } else {
                        alert(response.data.message || 'Erreur lors de la suppression.');
                        button.disabled = false;
                    }
                },
                error: function() {
                    alert('Erreur de connexion.');
                    button.disabled = false;
                }
            });
        }
    });
    
    // Production Edit Functionality
    document.addEventListener('click', function(e) {
        // Handle edit button click
        if (e.target.closest('.production-edit')) {
            e.preventDefault();
            e.stopPropagation();
            const button = e.target.closest('.production-edit');
            const productionId = button.getAttribute('data-production-id');
            const editForm = document.getElementById('edit-production-' + productionId);
            const productionItem = button.closest('.production-item');
            
            if (editForm) {
                const isVisible = editForm.style.display !== 'none';
                editForm.style.display = isVisible ? 'none' : 'block';
                
                if (!isVisible) {
                    // Scroll to form
                    editForm.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            }
        }
        
        // Handle cancel edit button
        if (e.target.closest('.cancel-edit-production')) {
            e.preventDefault();
            e.stopPropagation();
            const button = e.target.closest('.cancel-edit-production');
            const productionId = button.getAttribute('data-production-id');
            const editForm = document.getElementById('edit-production-' + productionId);
            
            if (editForm) {
                editForm.style.display = 'none';
            }
        }
    });
    
    // Profile Tabs Navigation - No page reload
    const profileTabsNav = document.querySelector('.profile-tabs-nav');
    if (profileTabsNav) {
        const profileTabs = profileTabsNav.querySelectorAll('.profile-tab');
        const profileTabContents = document.querySelectorAll('.profile-tab-content');
        
        // Get current tab from URL or default to 'profile'
        function getCurrentTab() {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get('tab') || 'profile';
        }
        
        // Update URL without reload
        function updateURL(tab) {
            const url = new URL(window.location);
            if (tab === 'profile') {
                url.searchParams.delete('tab');
            } else {
                url.searchParams.set('tab', tab);
            }
            window.history.pushState({ tab: tab }, '', url);
        }
        
        // Switch tab function
        function switchTab(tabName) {
            // Update active tab button
            profileTabs.forEach(function(tab) {
                if (tab.getAttribute('data-tab') === tabName) {
                    tab.classList.add('active');
                } else {
                    tab.classList.remove('active');
                }
            });
            
            // Show/hide tab content
            profileTabContents.forEach(function(content) {
                if (content.getAttribute('data-tab-content') === tabName) {
                    content.classList.add('active');
                } else {
                    content.classList.remove('active');
                }
            });
            
            // Update URL
            updateURL(tabName);
        }
        
        // Add click handlers to tab buttons
        profileTabs.forEach(function(tab) {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                const tabName = this.getAttribute('data-tab');
                if (tabName) {
                    switchTab(tabName);
                }
            });
        });
        
        // Handle browser back/forward buttons
        window.addEventListener('popstate', function(e) {
            const tab = getCurrentTab();
            switchTab(tab);
        });
        
        // Initialize: show correct tab on load
        const initialTab = getCurrentTab();
        switchTab(initialTab);
    }
    
    // Recommendations Filters and Sort
    const recommendationsSection = document.querySelector('.recommendations-section');
    if (recommendationsSection) {
        const filterBtns = recommendationsSection.querySelectorAll('.filter-btn');
        const sortSelect = recommendationsSection.querySelector('#recommendations-sort');
        const recommendationItems = recommendationsSection.querySelectorAll('.recommendation-item');
        const recommendationsList = recommendationsSection.querySelector('.recommendations-list');
        
        // Get current user city from profile info (if available)
        let currentUserCity = '';
        const profileVille = document.querySelector('.info-card-value');
        if (profileVille && profileVille.textContent.trim()) {
            currentUserCity = profileVille.textContent.trim().toLowerCase();
        }
        
        // Filter functionality
        filterBtns.forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const filter = this.getAttribute('data-filter');
                
                // Update active state
                filterBtns.forEach(function(b) {
                    b.classList.remove('active');
                });
                this.classList.add('active');
                
                // Filter items with fade effect
                recommendationItems.forEach(function(item) {
                    let show = true;
                    
                    if (filter === 'local') {
                        const itemCity = item.getAttribute('data-city') || '';
                        if (!itemCity || itemCity.toLowerCase() !== currentUserCity) {
                            show = false;
                        }
                    } else if (filter === 'genres') {
                        const hasGenres = item.querySelector('.recommendation-genres');
                        if (!hasGenres) {
                            show = false;
                        }
                    }
                    // 'all' shows everything
                    
                    if (show) {
                        item.style.display = '';
                        item.style.opacity = '1';
                    } else {
                        item.style.opacity = '0';
                        setTimeout(function() {
                            item.style.display = 'none';
                        }, 200);
                    }
                });
            });
        });
        
        // Sort functionality
        if (sortSelect && recommendationsList) {
            sortSelect.addEventListener('change', function() {
                const sortValue = this.value;
                const items = Array.from(recommendationItems);
                
                // Filter visible items only
                const visibleItems = items.filter(function(item) {
                    return item.style.display !== 'none';
                });
                
                // Sort items
                visibleItems.sort(function(a, b) {
                    if (sortValue === 'score') {
                        const scoreA = parseInt(a.getAttribute('data-score')) || 0;
                        const scoreB = parseInt(b.getAttribute('data-score')) || 0;
                        return scoreB - scoreA;
                    } else if (sortValue === 'name') {
                        const nameA = a.getAttribute('data-name') || '';
                        const nameB = b.getAttribute('data-name') || '';
                        return nameA.localeCompare(nameB);
                    }
                    return 0;
                });
                
                // Re-append sorted visible items
                visibleItems.forEach(function(item) {
                    recommendationsList.appendChild(item);
                });
            });
        }
    }
    
    // Settings Navigation Tabs - URL-based navigation
    const settingsNavItems = document.querySelectorAll('.settings-nav-item');
    const settingsTabContents = document.querySelectorAll('.settings-tab-content');
    
    settingsNavItems.forEach(function(item) {
        item.addEventListener('click', function() {
            const tabName = this.getAttribute('data-settings-tab');
            const currentUrl = new URL(window.location);
            currentUrl.searchParams.set('section', tabName);
            window.history.pushState({ section: tabName }, '', currentUrl);
            
            // Update active nav item
            settingsNavItems.forEach(function(navItem) {
                navItem.classList.remove('active');
            });
            this.classList.add('active');
            
            // Show/hide tab content
            settingsTabContents.forEach(function(content) {
                if (content.getAttribute('data-settings-content') === tabName) {
                    content.classList.add('active');
                } else {
                    content.classList.remove('active');
                }
            });
        });
    });
    
    // Handle browser back/forward for settings
    if (settingsNavItems.length > 0) {
        window.addEventListener('popstate', function(e) {
            const urlParams = new URLSearchParams(window.location.search);
            const section = urlParams.get('section') || 'profile';
            
            settingsNavItems.forEach(function(navItem) {
                if (navItem.getAttribute('data-settings-tab') === section) {
                    navItem.classList.add('active');
                } else {
                    navItem.classList.remove('active');
                }
            });
            
            settingsTabContents.forEach(function(content) {
                if (content.getAttribute('data-settings-content') === section) {
                    content.classList.add('active');
                } else {
                    content.classList.remove('active');
                }
            });
        });
    }

    // Vanta.TRUNK background animation (login and register pages only)
    const vantaElement = document.getElementById('vanta-background');
    if (vantaElement) {
        // Wait for Vanta scripts to load
        if (typeof VANTA !== 'undefined' && VANTA.TRUNK) {
            initVantaTrunk();
        } else {
            // If scripts aren't loaded yet, wait for them
            window.addEventListener('load', function() {
                if (typeof VANTA !== 'undefined' && VANTA.TRUNK) {
                    initVantaTrunk();
                }
            });
        }
    }
});

// Vanta.TRUNK initialization function
function initVantaTrunk() {
    const vantaElement = document.getElementById('vanta-background');
    if (vantaElement && typeof VANTA !== 'undefined' && VANTA.TRUNK) {
        // Destroy any existing instance
        if (window.vantaEffect) {
            window.vantaEffect.destroy();
        }
        
        window.vantaEffect = VANTA.TRUNK({
            el: "#vanta-background",
            mouseControls: true,
            touchControls: true,
            gyroControls: false,
            minHeight: window.innerHeight,
            minWidth: window.innerWidth,
            scale: 1.00,
            scaleMobile: 1.00,
            spacing: 10.00,
            chaos: 10.00
        });
        
        // Force canvas to cover full viewport
        setTimeout(function() {
            const canvas = vantaElement.querySelector('canvas');
            if (canvas) {
                canvas.style.position = 'fixed';
                canvas.style.top = '0';
                canvas.style.left = '0';
                canvas.style.width = '100vw';
                canvas.style.height = '100vh';
                canvas.style.zIndex = '0';
                canvas.style.maxWidth = '100%';
                canvas.style.maxHeight = '100%';
            }
        }, 100);
        
        // Also handle window resize to ensure full coverage
        window.addEventListener('resize', function() {
            if (window.vantaEffect && vantaElement) {
                const canvas = vantaElement.querySelector('canvas');
                if (canvas) {
                    canvas.style.width = window.innerWidth + 'px';
                    canvas.style.height = window.innerHeight + 'px';
                }
                // Reinitialize with new dimensions
                window.vantaEffect.resize();
            }
        });
    }

    // ============================================
    // NAVBAR AUTO-ADAPTIVE COLOR
    // Adapte la couleur du texte de la navbar selon le fond
    // ============================================
    function getBackgroundColor(element) {
        const style = window.getComputedStyle(element);
        let bgColor = style.backgroundColor;
        
        // Si le fond est transparent, chercher dans les parents
        if (bgColor === 'rgba(0, 0, 0, 0)' || bgColor === 'transparent') {
            const parent = element.parentElement;
            if (parent && parent !== document.body) {
                return getBackgroundColor(parent);
            }
            // Si toujours transparent, retourner la couleur par défaut
            return 'rgb(244, 246, 250)'; // --bg-main clair par défaut
        }
        
        return bgColor;
    }

    function rgbToLuminance(rgb) {
        // Extraire les valeurs RGB
        const match = rgb.match(/\d+/g);
        if (!match || match.length < 3) return 0.5;
        
        const r = parseInt(match[0]);
        const g = parseInt(match[1]);
        const b = parseInt(match[2]);
        
        // Convertir en valeurs 0-1
        const [rNorm, gNorm, bNorm] = [r, g, b].map(val => {
            val = val / 255;
            return val <= 0.03928 ? val / 12.92 : Math.pow((val + 0.055) / 1.055, 2.4);
        });
        
        // Calculer la luminance relative
        return 0.2126 * rNorm + 0.7152 * gNorm + 0.0722 * bNorm;
    }

    function updateNavbarColor() {
        const header = document.querySelector('.main-header');
        if (!header) return;
        
        // Sur la page d'accueil avec hero-section, forcer le mode sombre (texte clair)
        if (document.body.classList.contains('home')) {
            const heroSection = document.querySelector('.hero-section');
            if (heroSection) {
                // Vérifier si on est toujours sur le hero (pas scrollé)
                const heroRect = heroSection.getBoundingClientRect();
                const scrollPosition = window.scrollY || window.pageYOffset;
                
                // Si on est en haut de la page ou sur le hero (moins de 50px scroll)
                if (scrollPosition < 50 || (heroRect.top < 200 && heroRect.bottom > 0)) {
                    // On est sur le hero - texte clair
                    header.classList.remove('navbar-on-light');
                    header.classList.add('navbar-on-dark');
                    return;
                }
            } else {
                // Pas de hero-section trouvé, mais on est sur home - forcer sombre par défaut
                header.classList.remove('navbar-on-light');
                header.classList.add('navbar-on-dark');
                return;
            }
        }
        
        // Obtenir la position de la navbar
        const headerRect = header.getBoundingClientRect();
        const centerY = headerRect.top + headerRect.height / 2;
        const centerX = headerRect.left + headerRect.width / 2;
        
        // Obtenir l'élément sous la navbar
        const elementBelow = document.elementFromPoint(centerX, centerY + headerRect.height);
        if (!elementBelow) {
            // Si pas d'élément, vérifier s'il y a une image de fond
            const bodyStyle = window.getComputedStyle(document.body);
            const bgImage = bodyStyle.backgroundImage;
            if (bgImage && bgImage !== 'none') {
                // Image de fond détectée - supposer sombre par défaut
                header.classList.remove('navbar-on-light');
                header.classList.add('navbar-on-dark');
            }
            return;
        }
        
        // Trouver l'élément avec un fond visible
        let currentElement = elementBelow;
        let bgColor = null;
        let bgImage = null;
        let attempts = 0;
        
        while (currentElement && attempts < 15) {
            const style = window.getComputedStyle(currentElement);
            bgColor = style.backgroundColor;
            bgImage = style.backgroundImage;
            
            // Si on a une image de fond, considérer comme sombre (généralement le cas)
            if (bgImage && bgImage !== 'none' && !bgColor) {
                header.classList.remove('navbar-on-light');
                header.classList.add('navbar-on-dark');
                return;
            }
            
            // Si on a une couleur de fond non transparente
            if (bgColor && bgColor !== 'rgba(0, 0, 0, 0)' && bgColor !== 'transparent') {
                break;
            }
            
            currentElement = currentElement.parentElement;
            attempts++;
        }
        
        if (!bgColor || bgColor === 'rgba(0, 0, 0, 0)' || bgColor === 'transparent') {
            // Si on ne trouve pas de couleur, vérifier le body
            const bodyStyle = window.getComputedStyle(document.body);
            bgColor = bodyStyle.backgroundColor;
            if (!bgColor || bgColor === 'rgba(0, 0, 0, 0)') {
                // Par défaut sur fond clair si rien trouvé
                header.classList.remove('navbar-on-dark');
                header.classList.add('navbar-on-light');
                return;
            }
        }
        
        // Calculer la luminance
        const luminance = rgbToLuminance(bgColor);
        
        // Si luminance > 0.5, fond clair, sinon fond sombre
        if (luminance > 0.5) {
            header.classList.remove('navbar-on-dark');
            header.classList.add('navbar-on-light');
        } else {
            header.classList.remove('navbar-on-light');
            header.classList.add('navbar-on-dark');
        }
    }

    // Initialiser immédiatement si on est sur la page d'accueil
    if (document.body && document.body.classList.contains('home')) {
        const header = document.querySelector('.main-header');
        if (header) {
            header.classList.remove('navbar-on-light');
            header.classList.add('navbar-on-dark');
        }
    }
    
    // Mettre à jour au chargement
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(updateNavbarColor, 100);
        });
    } else {
        setTimeout(updateNavbarColor, 100);
    }

    // Mettre à jour au scroll avec debounce
    let scrollTimeout;
    window.addEventListener('scroll', function() {
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(updateNavbarColor, 50);
    }, { passive: true });

    // Mettre à jour au resize
    window.addEventListener('resize', function() {
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(updateNavbarColor, 100);
    });

    // ============================================
    // SYSTÈME DE CONTRASTE ADAPTATIF GLOBAL
    // Adapte tous les textes selon leur fond
    // ============================================
    
    function getElementBackgroundColor(element) {
        const style = window.getComputedStyle(element);
        let bgColor = style.backgroundColor;
        
        // Si transparent, chercher dans les parents
        if (bgColor === 'rgba(0, 0, 0, 0)' || bgColor === 'transparent') {
            const parent = element.parentElement;
            if (parent && parent !== document.body && parent !== document.documentElement) {
                return getElementBackgroundColor(parent);
            }
            // Retourner la couleur par défaut du body
            const bodyStyle = window.getComputedStyle(document.body);
            return bodyStyle.backgroundColor || 'rgb(244, 246, 250)';
        }
        
        return bgColor;
    }

    function updateElementTextColor(element) {
        if (!element || element.classList.contains('skip-text-adaptive')) {
            return;
        }

        const bgColor = getElementBackgroundColor(element);
        if (!bgColor) return;

        const luminance = rgbToLuminance(bgColor);
        const isLight = luminance > 0.5;

        // Appliquer la classe appropriée
        if (isLight) {
            element.classList.remove('text-on-dark');
            element.classList.add('text-on-light');
            element.setAttribute('data-bg', 'light');
        } else {
            element.classList.remove('text-on-light');
            element.classList.add('text-on-dark');
            element.setAttribute('data-bg', 'dark');
        }
    }

    function updateAllTextColors() {
        // Sélecteurs pour les éléments contenant du texte
        const selectors = [
            'section',
            '.container',
            '.card',
            '.info-card',
            '.annonce-card',
            '.pour-qui-card',
            '.decouvrir-user-card',
            '.hero-content',
            '.section-content',
            '[class*="card"]',
            '[class*="panel"]',
            '[class*="box"]',
            'article',
            'div[style*="background"]'
        ];

        // Éléments à ignorer
        const skipSelectors = [
            '.main-header',
            '.dropdown-menu',
            'script',
            'style',
            'noscript'
        ];

        // Récupérer tous les éléments pertinents
        const allElements = new Set();
        
        selectors.forEach(selector => {
            try {
                document.querySelectorAll(selector).forEach(el => {
                    // Vérifier si l'élément doit être ignoré
                    let shouldSkip = false;
                    skipSelectors.forEach(skipSelector => {
                        if (el.matches(skipSelector) || el.closest(skipSelector)) {
                            shouldSkip = true;
                        }
                    });
                    
                    if (!shouldSkip && el.offsetParent !== null) {
                        allElements.add(el);
                    }
                });
            } catch (e) {
                // Ignorer les sélecteurs invalides
            }
        });

        // Mettre à jour chaque élément
        allElements.forEach(element => {
            updateElementTextColor(element);
        });

        // Mettre à jour aussi les éléments de texte directs
        document.querySelectorAll('p, h1, h2, h3, h4, h5, h6, span, a, li, td, th').forEach(element => {
            if (!element.closest('.skip-text-adaptive') && element.offsetParent !== null) {
                const parent = element.closest('section, .card, .container, [class*="card"], [class*="panel"]');
                if (parent) {
                    // Utiliser la classe du parent
                    if (parent.classList.contains('text-on-light')) {
                        element.classList.add('text-on-light');
                        element.setAttribute('data-bg', 'light');
                    } else if (parent.classList.contains('text-on-dark')) {
                        element.classList.add('text-on-dark');
                        element.setAttribute('data-bg', 'dark');
                    }
                } else {
                    updateElementTextColor(element);
                }
            }
        });
    }

    // Initialiser au chargement
    function initAdaptiveText() {
        // Attendre que le DOM soit complètement chargé
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(updateAllTextColors, 100);
            });
        } else {
            setTimeout(updateAllTextColors, 100);
        }

        // Utiliser Intersection Observer pour mettre à jour les éléments visibles
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    updateElementTextColor(entry.target);
                }
            });
        }, {
            rootMargin: '50px',
            threshold: 0.1
        });

        // Observer les sections et cards
        setTimeout(() => {
            document.querySelectorAll('section, .card, .info-card, .annonce-card').forEach(el => {
                observer.observe(el);
            });
        }, 500);

        // Mettre à jour au scroll (debounced)
        let updateTimeout;
        window.addEventListener('scroll', function() {
            clearTimeout(updateTimeout);
            updateTimeout = setTimeout(() => {
                // Mettre à jour uniquement les éléments visibles
                document.querySelectorAll('section:not(.skip-text-adaptive), .card:not(.skip-text-adaptive)').forEach(el => {
                    const rect = el.getBoundingClientRect();
                    if (rect.top < window.innerHeight && rect.bottom > 0) {
                        updateElementTextColor(el);
                    }
                });
            }, 150);
        }, { passive: true });

        // Mettre à jour au resize
        window.addEventListener('resize', function() {
            clearTimeout(updateTimeout);
            updateTimeout = setTimeout(updateAllTextColors, 200);
        });
    }

    // Initialiser le système adaptatif
    initAdaptiveText();
}

