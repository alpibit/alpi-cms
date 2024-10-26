document.addEventListener('DOMContentLoaded', function () {
    class AccordionManager {
        constructor() {
            this.accordions = document.querySelectorAll('.alpi-cms-content-accordion');
            this.animationDuration = 300; // Duration in milliseconds for open/close animation
            this.init();
        }

        init() {
            this.accordions.forEach(accordion => {
                this.setupAccordion(accordion);
            });

            // Handle keyboard navigation
            document.addEventListener('keydown', this.handleKeyboardNavigation.bind(this));
        }

        setupAccordion(accordion) {
            const triggers = accordion.querySelectorAll('.alpi-cms-content-accordion-trigger');

            triggers.forEach((trigger, index) => {
                // Set initial states
                const contentId = trigger.getAttribute('aria-controls');
                const content = document.getElementById(contentId);

                if (!content) {
                    console.error(`Content not found for trigger: ${contentId}`);
                    return;
                }

                // Ensure proper initial ARIA states
                trigger.setAttribute('aria-expanded', 'false');
                content.hidden = true;
                content.style.maxHeight = '0px';
                content.style.transition = `max-height ${this.animationDuration}ms ease-in-out`;

                // Add click handler
                trigger.addEventListener('click', (event) => {
                    this.toggleSection(trigger, accordion);
                    event.preventDefault();
                });

                // Store index for keyboard navigation
                trigger.dataset.index = index;
            });
        }

        toggleSection(trigger, accordion, forceState = null) {
            const isExpanded = forceState !== null ? forceState : trigger.getAttribute('aria-expanded') === 'true';
            const content = document.getElementById(trigger.getAttribute('aria-controls'));

            if (!content) return;

            // Close other sections first
            if (!isExpanded && forceState === null) {
                const otherTriggers = accordion.querySelectorAll('.alpi-cms-content-accordion-trigger');
                otherTriggers.forEach(otherTrigger => {
                    if (otherTrigger !== trigger && otherTrigger.getAttribute('aria-expanded') === 'true') {
                        this.toggleSection(otherTrigger, accordion, true);
                    }
                });
            }

            // Handle the toggle
            trigger.setAttribute('aria-expanded', !isExpanded);

            // Opening animation
            if (!isExpanded) {
                content.hidden = false;
                requestAnimationFrame(() => {
                    // Get the full height of the content
                    const height = content.scrollHeight;
                    content.style.maxHeight = `${height}px`;
                });
            }
            // Closing animation
            else {
                content.style.maxHeight = '0px';
                // Wait for animation to complete before hiding
                setTimeout(() => {
                    if (trigger.getAttribute('aria-expanded') === 'false') {
                        content.hidden = true;
                    }
                }, this.animationDuration);
            }
        }

        handleKeyboardNavigation(event) {
            const trigger = event.target.closest('.alpi-cms-content-accordion-trigger');
            if (!trigger) return;

            const accordion = trigger.closest('.alpi-cms-content-accordion');
            const triggers = Array.from(accordion.querySelectorAll('.alpi-cms-content-accordion-trigger'));
            const currentIndex = parseInt(trigger.dataset.index);

            switch (event.key) {
                case 'ArrowUp':
                    event.preventDefault();
                    this.focusTrigger(triggers, currentIndex - 1);
                    break;
                case 'ArrowDown':
                    event.preventDefault();
                    this.focusTrigger(triggers, currentIndex + 1);
                    break;
                case 'Home':
                    event.preventDefault();
                    this.focusTrigger(triggers, 0);
                    break;
                case 'End':
                    event.preventDefault();
                    this.focusTrigger(triggers, triggers.length - 1);
                    break;
            }
        }

        focusTrigger(triggers, index) {
            const targetIndex = (index + triggers.length) % triggers.length;
            triggers[targetIndex].focus();
        }
    }

    // Initialize accordion functionality
    new AccordionManager();
});