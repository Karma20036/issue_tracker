document.addEventListener('DOMContentLoaded', () => {
    const signupForm = document.getElementById('signupForm');
    const loginForm = document.getElementById('loginForm');

    if (signupForm) {
        signupForm.addEventListener('submit', (e) => {
            const email = signupForm.email.value;
            const mobileNumber = signupForm.mobile_number.value;
            const role = signupForm.role.value;

            if (!validateEmail(email)) {
                alert('Please enter a valid email address.');
                e.preventDefault();
            }

            if (!validateMobileNumber(mobileNumber)) {
                alert('Mobile number should not exceed 10 digits.');
                e.preventDefault();
            }

            if (!validateRole(role)) {
                alert('Please select a valid user role.');
                e.preventDefault();
            }
        });
    }

    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            const email = loginForm.email.value;

            if (!validateEmail(email)) {
                alert('Please enter a valid email address.');
                e.preventDefault();
            }
        });
    }

    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(String(email).toLowerCase());
    }

    function validateMobileNumber(number) {
        return number.length <= 10 && /^\d+$/.test(number);
    }

    function validateRole(role) {
        const validRoles = ['Customer', 'Organization Admin', 'Global Admin'];
        return validRoles.includes(role);
    }
});
