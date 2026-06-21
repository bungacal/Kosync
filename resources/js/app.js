document.addEventListener('DOMContentLoaded', () => {
    window.lucide?.createIcons();

    const tenantNotificationButton = document.getElementById('tenantNotificationButton');
    const tenantNotificationMenu = document.getElementById('tenantNotificationMenu');

    tenantNotificationButton?.addEventListener('click', (event) => {
        event.stopPropagation();
        const willOpen = tenantNotificationMenu.hidden;
        tenantNotificationMenu.hidden = !willOpen;
        tenantNotificationButton.setAttribute('aria-expanded', String(willOpen));
    });

    document.addEventListener('click', (event) => {
        if (!tenantNotificationMenu || tenantNotificationMenu.hidden) {
            return;
        }

        if (!tenantNotificationMenu.contains(event.target)) {
            tenantNotificationMenu.hidden = true;
            tenantNotificationButton?.setAttribute('aria-expanded', 'false');
        }
    });

    const ratingModal = document.getElementById('ratingModal');
    const openRatingButtons = document.querySelectorAll('[data-open-rating]');
    const closeRatingButtons = document.querySelectorAll('[data-close-rating]');
    const ratingLabel = document.getElementById('ratingLabel');
    const ratingTexts = {
        1: 'Kurang',
        2: 'Cukup',
        3: 'Lumayan',
        4: 'Baik',
        5: 'Sangat baik',
    };

    const openRatingModal = () => {
        if (!ratingModal) {
            return;
        }

        ratingModal.hidden = false;
        window.lucide?.createIcons();
    };

    const closeRatingModal = () => {
        if (ratingModal) {
            ratingModal.hidden = true;
        }
    };

    openRatingButtons.forEach((button) => {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            openRatingModal();
        });
    });

    closeRatingButtons.forEach((button) => {
        button.addEventListener('click', closeRatingModal);
    });

    ratingModal?.addEventListener('click', (event) => {
        if (event.target === ratingModal) {
            closeRatingModal();
        }
    });

    document.querySelectorAll('input[name="nilai_rating"]').forEach((input) => {
        input.addEventListener('change', () => {
            if (ratingLabel) {
                ratingLabel.textContent = ratingTexts[input.value] ?? 'Baik';
            }
        });
    });

    const params = new URLSearchParams(window.location.search);
    if (ratingModal && (params.has('rating') || ratingModal.querySelector('.alert'))) {
        openRatingModal();
    }
});
