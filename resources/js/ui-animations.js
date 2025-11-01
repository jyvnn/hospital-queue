// Small UI animations: add 'animate-in' class to cards and queue items on load
// Respects prefers-reduced-motion

(function () {
    const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function animateElements(selector, stagger = 60) {
        const els = Array.from(document.querySelectorAll(selector));
        els.forEach((el, i) => {
            if (prefersReduced) return;
            el.style.opacity = 0;
            el.style.transform = 'translateY(6px)';
            setTimeout(() => {
                el.classList.add('animate-in');
                el.style.opacity = '';
                el.style.transform = '';
            }, i * stagger);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        // animate top-level cards first
        animateElements('.card', 70);

        // animate patient cards inside queues slightly later
        setTimeout(() => animateElements('.patient-card', 50), 200);
    });
})();
