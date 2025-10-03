/**
 * Main JavaScript file for Puskesmas Website
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Swiper slider
    const swiper = new Swiper('.mySwiper', {
        // Optional parameters
        loop: true,
        effect: 'fade',
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
        },
        // If we need pagination
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        // Navigation arrows
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
    });

    // Mobile Menu Toggle
    const mobileMenuToggle = document.createElement('div');
    mobileMenuToggle.className = 'mobile-menu-toggle';
    mobileMenuToggle.innerHTML = '<i class="fas fa-bars"></i>';
    document.querySelector('.main-nav .container').prepend(mobileMenuToggle);

    mobileMenuToggle.addEventListener('click', function() {
        document.querySelector('.nav-menu').classList.toggle('active');
        this.classList.toggle('active');
    });

    // Dropdown Toggle for Mobile
    const dropdownItems = document.querySelectorAll('.dropdown');
    
    if (window.innerWidth <= 768) {
        dropdownItems.forEach(item => {
            const link = item.querySelector('a');
            link.addEventListener('click', function(e) {
                e.preventDefault();
                item.classList.toggle('active');
            });
        });
    }

    // Back to Top Button
    const backToTopButton = document.querySelector('.back-to-top');
    
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            backToTopButton.classList.add('active');
        } else {
            backToTopButton.classList.remove('active');
        }
    });

    backToTopButton.addEventListener('click', function(e) {
        e.preventDefault();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // Add active class to current menu item
    const currentLocation = location.pathname;
    const menuItems = document.querySelectorAll('.nav-menu a');
    const menuLength = menuItems.length;
    
    for (let i = 0; i < menuLength; i++) {
        if (menuItems[i].getAttribute('href') === currentLocation) {
            menuItems[i].parentElement.classList.add('active');
        }
    }

    // Sticky Header
    const header = document.querySelector('header');
    const topHeader = document.querySelector('.top-header');
    const mainNav = document.querySelector('.main-nav');
    
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > topHeader.offsetHeight) {
            mainNav.classList.add('sticky');
            document.body.style.paddingTop = mainNav.offsetHeight + 'px';
        } else {
            mainNav.classList.remove('sticky');
            document.body.style.paddingTop = 0;
        }
    });

    // Animation on scroll
    const animatedElements = document.querySelectorAll('.animate');
    
    function checkIfInView() {
        const windowHeight = window.innerHeight;
        const windowTopPosition = window.pageYOffset;
        const windowBottomPosition = windowTopPosition + windowHeight;

        animatedElements.forEach(element => {
            const elementHeight = element.offsetHeight;
            const elementTopPosition = element.offsetTop;
            const elementBottomPosition = elementTopPosition + elementHeight;

            // Check if element is in view
            if (
                (elementBottomPosition >= windowTopPosition) &&
                (elementTopPosition <= windowBottomPosition)
            ) {
                element.classList.add('animated');
            }
        });
    }

    // Run on load
    checkIfInView();
    
    // Run on scroll
    window.addEventListener('scroll', checkIfInView);

    // Add CSS class for sticky navigation
    const style = document.createElement('style');
    style.innerHTML = `
        .main-nav.sticky {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            animation: slideDown 0.5s;
        }
        
        @keyframes slideDown {
            from { transform: translateY(-100%); }
            to { transform: translateY(0); }
        }
        
        .mobile-menu-toggle {
            display: none;
            font-size: 24px;
            color: #fff;
            cursor: pointer;
        }
        
        @media (max-width: 768px) {
            .mobile-menu-toggle {
                display: block;
                padding: 15px 0;
            }
            
            .nav-menu {
                display: none;
                width: 100%;
            }
            
            .nav-menu.active {
                display: block;
            }
            
            .mobile-menu-toggle.active i:before {
                content: '\\f00d';
            }
            
            .animate {
                opacity: 0;
                transform: translateY(30px);
                transition: opacity 0.5s, transform 0.5s;
            }
            
            .animated {
                opacity: 1;
                transform: translateY(0);
            }
        }
    `;
    document.head.appendChild(style);

    // Add animate class to elements
    document.querySelectorAll('.section-header, .service-card, .news-card, .welcome-content, .appointment-content').forEach(el => {
        el.classList.add('animate');
    });
});