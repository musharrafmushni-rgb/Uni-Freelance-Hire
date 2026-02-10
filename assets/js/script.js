document.addEventListener('DOMContentLoaded', function() {
    console.log('UniFreelance Script Loaded');

    // Example Form Validation for Registration
    const regForm = document.querySelector('form[action="register.php"]'); // Might vary if I didn't set action explicitly in previous steps, usually it's empty string so matching by form presence on register page is better.
    
    // Better selector:
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            const requiredInputs = form.querySelectorAll('[required]');
            
            requiredInputs.forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                    input.style.borderColor = 'red';
                } else {
                    input.style.borderColor = '#ddd';
                }
            });

            // Password Match Check
            const pwd = form.querySelector('input[name="password"]');
            const pwdConfirm = form.querySelector('input[name="confirm_password"]');
            if (pwd && pwdConfirm) {
                if (pwd.value !== pwdConfirm.value) {
                    isValid = false;
                    alert("Passwords do not match!");
                    pwdConfirm.style.borderColor = 'red';
                }
            }

            if (!isValid) {
                e.preventDefault();
                alert("Please fill in all required fields correctly.");
            }
        });
    });
});
