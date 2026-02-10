/**
 * Global JavaScript for UniFreelance
 * Handles button states and common UI interactions
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Form Submission Loading State
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function (e) {
            // Find the submit button in this form
            const submitBtn = form.querySelector('button[type="submit"]');

            if (submitBtn && !submitBtn.classList.contains('no-loading')) {
                // Prevent multiple clicks
                // Use setTimeout to allow the form data (including the button value) to be captured
                setTimeout(() => {
                    submitBtn.disabled = true;
                    submitBtn.classList.add('loading');
                }, 0);
            }
        });
    });

    // 2. Accessibility: Focus states for buttons
    // 2. Accessibility: Focus states removed to prevent interaction issues
});
