/**
 * ==========================================================================
 * UtilajePro — about.js
 * --------------------------------------------------------------------------
 * Logica specifică paginii despre-noi.html:
 *   - animații simple de apariție la scroll pentru blocurile informative,
 *     statistici și membrii echipei, folosind IntersectionObserver.
 * Depinde de js/main.js.
 * ==========================================================================
 */

(function () {
    "use strict";

    document.addEventListener("DOMContentLoaded", () => {
        initScrollRevealAnimations();
    });

    function initScrollRevealAnimations() {
        const targets = document.querySelectorAll(".about-block, .stat-card, .team-member");
        if (targets.length === 0) return;

        // Respectă preferința utilizatorului de reducere a mișcării.
        if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
            targets.forEach((el) => el.classList.add("is-visible"));
            return;
        }

        targets.forEach((el) => el.classList.add("scroll-reveal"));

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add("is-visible");
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15 });

        targets.forEach((el) => observer.observe(el));
    }
})();
