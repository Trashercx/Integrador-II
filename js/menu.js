function toggleMobileMenu() {
        const mobileMenu = document.getElementById('mobileMenu');
        const overlay = document.getElementById('mobileMenuOverlay');

        mobileMenu.classList.toggle('active');
        overlay.classList.toggle('active');

        if (mobileMenu.classList.contains('active')) {
            document.body.style.overflow = 'hidden';
            document.documentElement.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
            document.documentElement.style.overflow = '';
        }
    }

    function closeMobileMenu() {
        const mobileMenu = document.getElementById('mobileMenu');
        const overlay = document.getElementById('mobileMenuOverlay');
        const dropdown = document.getElementById('mobileDropdown');

        mobileMenu.classList.remove('active');
        overlay.classList.remove('active');
        if (dropdown) dropdown.style.display = 'none';

        document.body.style.overflow = '';
        document.documentElement.style.overflow = '';
    }

    function toggleMobileDropdown(event) {
        event.preventDefault();
        const dropdown = document.getElementById('mobileDropdown');
        const icon = event.target.querySelector('i') || event.target.parentElement.querySelector('i');

        if (dropdown.style.display === 'none' || dropdown.style.display === '') {
            dropdown.style.display = 'block';
            icon.classList.remove('fa-caret-down');
            icon.classList.add('fa-caret-up');
        } else {
            dropdown.style.display = 'none';
            icon.classList.remove('fa-caret-up');
            icon.classList.add('fa-caret-down');
        }
    }

    window.addEventListener('resize', function () {
        if (window.innerWidth > 768) {
            closeMobileMenu();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeMobileMenu();
        }
    });