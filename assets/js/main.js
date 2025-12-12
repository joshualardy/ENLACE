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

    // Load announcement details
    function loadAnnonceDetails(annonceId) {
        const card = document.querySelector(`.annonce-card[data-annonce-id="${annonceId}"]`);
        if (!card) return;

        const title = card.querySelector('.annonce-card-title')?.textContent || '';
        const description = card.querySelector('.annonce-card-description')?.textContent || '';
        const location = card.querySelector('.annonce-card-location span')?.textContent || '';
        const image = card.querySelector('.annonce-card-image img')?.src || '';
        const imagePlaceholder = card.querySelector('.annonce-card-placeholder');
        
        // Get author data from data attributes
        const authorId = card.getAttribute('data-author-id');
        const authorName = card.getAttribute('data-author-name') || 'Utilisateur';
        const authorUrl = card.getAttribute('data-author-url') || '#';

        let imageHtml = '';
        if (image) {
            imageHtml = `<img src="${image}" alt="${title}">`;
        } else if (imagePlaceholder) {
            imageHtml = '<div class="annonce-modal-image-placeholder"><svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 16L8.586 11.414C9.367 10.633 10.633 10.633 11.414 11.414L16 16M14 14L15.586 12.414C16.367 11.633 17.633 11.633 18.414 12.414L22 16M18 8V6C18 4.895 17.105 4 16 4H8C6.895 4 6 4.895 6 6V18C6 19.105 6.895 20 8 20H16C17.105 20 18 19.105 18 18V16M18 8H20C21.105 8 22 8.895 22 10V18C22 19.105 21.105 20 20 20H12C10.895 20 10 19.105 10 18V10C10 8.895 10.895 8 12 8H14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></div>';
        }

        const modalContent = `
            <div class="annonce-modal-image">
                ${imageHtml}
            </div>
            <div class="annonce-modal-details">
                <h2 class="annonce-modal-title">${title}</h2>
                <p class="annonce-modal-description">${description}</p>
                <div class="annonce-modal-author">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <a href="${authorUrl}" class="annonce-modal-author-link">${authorName}</a>
                </div>
                ${location ? `<div class="annonce-modal-location">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M21 10C21 17 12 23 12 23C12 23 3 17 3 10C3 7.61305 3.94821 5.32387 5.63604 3.63604C7.32387 1.94821 9.61305 1 12 1C14.3869 1 16.6761 1.94821 18.364 3.63604C20.0518 5.32387 21 7.61305 21 10Z" stroke="currentColor" stroke-width="1.5"/>
                        <circle cx="12" cy="10" r="3" stroke="currentColor" stroke-width="1.5"/>
                    </svg>
                    <span>${location}</span>
                </div>` : ''}
            </div>
        `;

        if (annonceModalBody) {
            annonceModalBody.innerHTML = modalContent;
        }
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
            console.error('AJAX configuration not available');
            alert('Erreur de configuration. Veuillez rafraîchir la page.');
            button.disabled = false;
            button.innerHTML = originalHTML;
            return;
        }
        
        if (!enlaceAjax.favorites_nonce) {
            console.error('Favorites nonce not available');
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
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                console.error('Response:', xhr.responseText);
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
            console.log('Annonce bookmark clicked', annonceBtn);
            toggleFavorite(annonceBtn);
            return;
        }
        
        // Handle favorite buttons on user profiles
        const profileBtn = e.target.closest('.btn-profile-favorite');
        if (profileBtn) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Profile favorite clicked', profileBtn);
            toggleFavorite(profileBtn);
            return;
        }
        
        // Handle remove favorite buttons
        const removeBtn = e.target.closest('.favorite-remove-btn');
        if (removeBtn) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Remove favorite clicked', removeBtn);
            toggleFavorite(removeBtn);
            return;
        }
    });
    
    // Debug: Log available buttons on page load
    console.log('Favorites system initialized');
    console.log('Annonce bookmark buttons:', document.querySelectorAll('.annonce-bookmark-btn').length);
    console.log('Profile favorite buttons:', document.querySelectorAll('.btn-profile-favorite').length);
    console.log('enlaceAjax available:', typeof enlaceAjax !== 'undefined');
    if (typeof enlaceAjax !== 'undefined') {
        console.log('favorites_nonce available:', !!enlaceAjax.favorites_nonce);
    }
    
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
            console.error('Production comments nonce not available');
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
            error: function(xhr, status, error) {
                console.error('Error loading comments:', error);
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
                error: function(xhr, status, error) {
                    console.error('Error adding comment:', error);
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
});

