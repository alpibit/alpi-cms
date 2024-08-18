document.addEventListener('DOMContentLoaded', function () {
    const accordions = document.querySelectorAll('.alpi-cms-content-accordion');

    accordions.forEach(accordion => {
        const triggers = accordion.querySelectorAll('.alpi-cms-content-accordion-trigger');

        triggers.forEach(trigger => {
            trigger.addEventListener('click', function () {
                const isExpanded = this.getAttribute('aria-expanded') === 'true';
                const controlledContent = document.getElementById(this.getAttribute('aria-controls'));

                if (!controlledContent) {
                    console.error('Controlled content not found');
                    return;
                }

                this.setAttribute('aria-expanded', !isExpanded);
                controlledContent.hidden = isExpanded;

                if (!isExpanded) {
                    triggers.forEach(otherTrigger => {
                        if (otherTrigger !== trigger) {
                            otherTrigger.setAttribute('aria-expanded', 'false');
                            const otherContent = document.getElementById(otherTrigger.getAttribute('aria-controls'));
                            if (otherContent) {
                                otherContent.hidden = true;
                            }
                        }
                    });
                }
            });
        });
    });
});