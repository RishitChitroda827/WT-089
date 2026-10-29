/**
 * StudentHub - Main Frontend Interactivity (Practical 4)
 * Covers: Light/Dark Theme Switcher (localStorage), Notification Banner,
 * Collapsible FAQ, Modal Popup, Image/Content Slider, Mobile Menu
 */

document.addEventListener("DOMContentLoaded", function () {
    // 1. LIGHT / DARK THEME SWITCHER WITH LOCALSTORAGE PERSISTENCE (Practical 4)
    function applyPortalTheme(theme) {
        const isDark = (theme === "dark");
        if (isDark) {
            document.body.classList.add("dark-mode");
            document.body.classList.add("dark");
        } else {
            document.body.classList.remove("dark-mode");
            document.body.classList.remove("dark");
        }
        const buttons = document.querySelectorAll("#themeToggleBtn, #themeBtn, .theme-toggle-trigger");
        buttons.forEach(function (btn) {
            btn.textContent = isDark ? "Light Mode" : "Dark Mode";
        });
    }

    const currentSavedTheme = localStorage.getItem("studenthub_theme") || localStorage.getItem("theme") || "light";
    applyPortalTheme(currentSavedTheme);

    // Global click listener for theme toggle buttons
    document.addEventListener("click", function (e) {
        const trigger = e.target.closest("#themeToggleBtn, #themeBtn, .theme-toggle-trigger");
        if (trigger) {
            e.preventDefault();
            e.stopPropagation();
            const isDarkNow = document.body.classList.contains("dark-mode") || document.body.classList.contains("dark");
            const targetTheme = isDarkNow ? "light" : "dark";
            localStorage.setItem("studenthub_theme", targetTheme);
            localStorage.setItem("theme", targetTheme);
            applyPortalTheme(targetTheme);
        }
    });

    // 2. DISMISSIBLE NOTIFICATION BANNER
    const banner = document.getElementById("notificationBanner");
    const bannerCloseBtn = document.getElementById("closeBannerBtn");

    if (bannerCloseBtn && banner) {
        // Check if previously dismissed in this session
        if (sessionStorage.getItem("banner_dismissed") === "true") {
            banner.style.display = "none";
        }
        bannerCloseBtn.addEventListener("click", function () {
            banner.style.display = "none";
            sessionStorage.setItem("banner_dismissed", "true");
        });
    }

    // 3. COLLAPSIBLE FAQ ACCORDION
    const accordionToggles = document.querySelectorAll(".accordion-toggle");
    accordionToggles.forEach(function (btn) {
        btn.addEventListener("click", function () {
            const content = this.nextElementSibling;
            const isExpanded = content.classList.contains("show");

            // Close other items in the same accordion group (optional clean UX)
            const parent = this.closest(".accordion-group");
            if (parent) {
                parent.querySelectorAll(".accordion-content").forEach(c => c.classList.remove("show"));
                parent.querySelectorAll(".accordion-toggle").forEach(t => t.setAttribute("aria-expanded", "false"));
            }

            if (!isExpanded) {
                content.classList.add("show");
                this.setAttribute("aria-expanded", "true");
            } else {
                content.classList.remove("show");
                this.setAttribute("aria-expanded", "false");
            }
        });
    });

    // 4. CONTENT / IMAGE SLIDER
    const slider = document.querySelector(".college-slider");
    if (slider) {
        const slides = slider.querySelectorAll(".slider-slide");
        let currentSlide = 0;

        function showSlide(index) {
            slides.forEach((s, i) => {
                s.classList.toggle("active", i === index);
            });
        }

        const prevBtn = slider.querySelector(".slider-btn.prev");
        const nextBtn = slider.querySelector(".slider-btn.next");

        if (prevBtn) {
            prevBtn.addEventListener("click", function () {
                currentSlide = (currentSlide - 1 + slides.length) % slides.length;
                showSlide(currentSlide);
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener("click", function () {
                currentSlide = (currentSlide + 1) % slides.length;
                showSlide(currentSlide);
            });
        }

        // Auto advance every 6 seconds
        setInterval(function () {
            if (slider.matches(':hover')) return; // pause on hover
            currentSlide = (currentSlide + 1) % slides.length;
            showSlide(currentSlide);
        }, 6000);
    }
});

// 5. GLOBAL MODAL POPUP HELPERS (Practical 4)
window.openCustomModal = function (modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add("show");
        document.body.style.overflow = "hidden";
    }
};

window.closeCustomModal = function (modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove("show");
        document.body.style.overflow = "";
    }
};

// Close modal when clicking outside dialog
document.addEventListener("click", function (e) {
    if (e.target.classList && e.target.classList.contains("custom-modal-backdrop")) {
        e.target.classList.remove("show");
        document.body.style.overflow = "";
    }
});
