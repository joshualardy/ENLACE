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

});

