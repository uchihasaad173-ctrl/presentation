document.addEventListener('DOMContentLoaded', () => {
    /* --- Navbar Scroll Effect --- */
    const navbar = document.getElementById('navbar');

    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });

    /* --- Mobile Menu Toggle --- */
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const navLinks = document.getElementById('nav-links');
    const navLinksItems = document.querySelectorAll('.nav-links a');

    mobileMenuBtn.addEventListener('click', () => {
        navLinks.classList.toggle('active');
        mobileMenuBtn.classList.toggle('active');

        // Toggle icon
        const icon = mobileMenuBtn.querySelector('i');
        if (navLinks.classList.contains('active')) {
            icon.classList.remove('fa-bars');
            icon.classList.add('fa-times');
        } else {
            icon.classList.remove('fa-times');
            icon.classList.add('fa-bars');
        }
    });

    // Close mobile menu when a link is clicked
    navLinksItems.forEach(link => {
        link.addEventListener('click', () => {
            navLinks.classList.remove('active');
            mobileMenuBtn.classList.remove('active');
            const icon = mobileMenuBtn.querySelector('i');
            icon.classList.remove('fa-times');
            icon.classList.add('fa-bars');
        });
    });

    /* --- Smooth Scrolling --- */
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;

            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                const headerOffset = 80; // Height of sticky header
                const elementPosition = targetElement.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });

    /* --- Intersection Observer for Animations --- */
    const animatedElements = document.querySelectorAll('.fade-in, .slide-up');

    const animationObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                // Optional: Stop observing once animated
                // observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.15,
        rootMargin: "0px 0px -50px 0px"
    });

    animatedElements.forEach(el => {
        animationObserver.observe(el);
    });

    /* --- Lightbox Functionality --- */
    const galleryItems = document.querySelectorAll('.gallery-item');
    const lightbox = document.getElementById('lightbox');
    const lightboxImg = document.getElementById('lightbox-img');
    const lightboxClose = document.querySelector('.lightbox-close');

    galleryItems.forEach(item => {
        item.addEventListener('click', () => {
            const imgSrc = item.querySelector('img').getAttribute('src');
            lightboxImg.setAttribute('src', imgSrc);
            lightbox.classList.add('active');
        });
    });

    const closeLightbox = () => {
        lightbox.classList.remove('active');
    };

    lightboxClose.addEventListener('click', closeLightbox);

    // Close lightbox on outside click
    lightbox.addEventListener('click', (e) => {
        if (e.target === lightbox) {
            closeLightbox();
        }
    });

    // Close lightbox on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && lightbox.classList.contains('active')) {
            closeLightbox();
        }
    });

    /* --- Preloader --- */
    const preloader = document.getElementById('preloader');
    if (preloader) {
        window.addEventListener('load', () => {
            setTimeout(() => {
                preloader.classList.add('hidden');
            }, 800); // Small artificial delay for aesthetics
        });
    }

    /* --- Custom Cursor --- */
    const customCursor = document.getElementById('custom-cursor');
    if (customCursor) {
        document.addEventListener('mousemove', (e) => {
            customCursor.style.left = e.clientX + 'px';
            customCursor.style.top = e.clientY + 'px';
        });

        const hoverElements = document.querySelectorAll('a, button, .playlist-item, .gallery-item, .ctrl-btn');
        hoverElements.forEach(el => {
            el.addEventListener('mouseenter', () => {
                customCursor.classList.add('hovering');
            });
            el.addEventListener('mouseleave', () => {
                customCursor.classList.remove('hovering');
            });
        });
    }

    /* --- Real Audio Player & Playlist Functionality --- */
    const playBtn = document.querySelector('.play-btn');
    if (playBtn) {
        const albumArt = document.getElementById('current-album-art');
        const progressBar = document.getElementById('progress-indicator');
        const progressBarContainer = document.querySelector('.progress-bar');
        const currentTimeEl = document.querySelector('.current-time');
        const durationEl = document.getElementById('current-track-duration');
        const trackTitleEl = document.getElementById('current-track-title');
        const trackArtistEl = document.getElementById('current-track-artist');
        const playlistItems = document.querySelectorAll('.playlist-item');

        let currentAudio = document.getElementById('audio-player-1');
        if (!currentAudio) currentAudio = new Audio(); // Fallback if no audio tag

        const formatTime = (seconds) => {
            if (isNaN(seconds) || !isFinite(seconds)) return '0:00';
            const mins = Math.floor(seconds / 60);
            const secs = Math.floor(seconds % 60).toString().padStart(2, '0');
            return `${mins}:${secs}`;
        };

        const updatePlayerUI = () => {
            if (currentAudio.duration) {
                const percentage = (currentAudio.currentTime / currentAudio.duration) * 100;
                progressBar.style.width = percentage + '%';
                currentTimeEl.textContent = formatTime(currentAudio.currentTime);
            }
        };

        const equalizer = document.getElementById('equalizer');

        const togglePlay = () => {
            if (currentAudio.paused) {
                currentAudio.play().then(() => {
                    playBtn.classList.add('playing');
                    albumArt.classList.add('playing');
                    if(equalizer) equalizer.classList.add('playing');
                }).catch(e => console.log('Audio play failed:', e));
            } else {
                currentAudio.pause();
                playBtn.classList.remove('playing');
                albumArt.classList.remove('playing');
                if(equalizer) equalizer.classList.remove('playing');
            }
        };

        playBtn.addEventListener('click', togglePlay);

        currentAudio.addEventListener('timeupdate', updatePlayerUI);
        currentAudio.addEventListener('ended', () => {
            playBtn.classList.remove('playing');
            albumArt.classList.remove('playing');
            if(equalizer) equalizer.classList.remove('playing');
            progressBar.style.width = '0%';
        });

        // Seek functionality
        progressBarContainer.addEventListener('click', (e) => {
            if (currentAudio.duration) {
                const clickX = e.clientX - progressBarContainer.getBoundingClientRect().left;
                const width = progressBarContainer.clientWidth;
                const clickPercentage = clickX / width;
                currentAudio.currentTime = clickPercentage * currentAudio.duration;
            }
        });

        // Playlist Item Click
        playlistItems.forEach(item => {
            item.addEventListener('click', () => {
                // Pause current before switching
                currentAudio.pause();
                currentAudio.removeEventListener('timeupdate', updatePlayerUI);
                playBtn.classList.remove('playing');
                if(equalizer) equalizer.classList.remove('playing');

                // Remove active class from all
                playlistItems.forEach(el => el.classList.remove('active'));
                item.classList.add('active');

                // Update play icons
                playlistItems.forEach(el => {
                    const icon = el.querySelector('.play-icon i');
                    icon.className = 'fas fa-play';
                });
                item.querySelector('.play-icon i').className = 'fas fa-music';

                // Get new audio element
                const audioId = item.getAttribute('data-audio-id');
                currentAudio = document.getElementById(audioId);
                currentAudio.currentTime = 0;
                currentAudio.addEventListener('timeupdate', updatePlayerUI);

                // Update Player Meta UI
                albumArt.src = item.getAttribute('data-src');
                trackTitleEl.textContent = item.getAttribute('data-title');
                trackArtistEl.textContent = item.getAttribute('data-artist');
                durationEl.textContent = item.getAttribute('data-duration');

                // Reset progress UI
                progressBar.style.width = '0%';
                currentTimeEl.textContent = '0:00';

                // Autoplay
                togglePlay();

                // Optional: Bounce animation on player wrapper to show switch
                const playerWrapper = document.querySelector('.player-container');
                playerWrapper.style.transform = 'scale(0.98)';
                setTimeout(() => {
                    playerWrapper.style.transform = 'scale(1)';
                }, 150);
            });
        });

        // Ensure transition is set for scale bounce
        const playerContainer = document.querySelector('.player-container');
        if (playerContainer) {
            playerContainer.style.transition = 'transform 0.15s ease';
        }
    }

    /* --- Gallery Filtering Logic --- */
    const filterBtns = document.querySelectorAll('.filter-btn');
    const gallerySections = document.querySelectorAll('.gallery-event-section');

    if (filterBtns.length > 0) {
        filterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                // Update active state
                filterBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                const filterValue = btn.getAttribute('data-filter');

                gallerySections.forEach(section => {
                    const category = section.getAttribute('data-category');
                    if (filterValue === 'all' || category === filterValue) {
                        section.style.display = 'block';
                        // Small timeout to allow display:block to reflow
                        setTimeout(() => {
                            section.style.opacity = '1';
                        }, 10);
                    } else {
                        section.style.opacity = '0';
                        setTimeout(() => {
                            // Only hide if it's still supposed to be hidden (user didn't click quickly)
                            if (section.style.opacity === '0') {
                                section.style.display = 'none';
                            }
                        }, 300); // Wait for transition
                    }
                });
            });
        });
    }

    // Trigger initial intersection observer check for above-the-fold elements
    setTimeout(() => {
        const event = new Event('scroll');
        window.dispatchEvent(event);
    }, 100);
});
