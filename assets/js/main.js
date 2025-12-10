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
            };
            reader.readAsDataURL(file);
        });
    }

    // Initialize photo preview for offering-service form
    initPhotoPreview('profile_photo', 'photo-preview', 'service-photo-preview-img');

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

});

