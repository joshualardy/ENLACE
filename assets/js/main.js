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

