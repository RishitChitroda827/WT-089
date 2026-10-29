/**
 * StudentHub - Registration Form Validation & Password Strength (Practical 5)
 * Regular expressions, real-time feedback, user-friendly errors
 */

document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("registrationForm");
    if (!form) return;

    const nameInput = document.getElementById("reg_name");
    const enrollInput = document.getElementById("reg_enrollment");
    const emailInput = document.getElementById("reg_email");
    const mobileInput = document.getElementById("reg_mobile");
    const passInput = document.getElementById("reg_password");
    const confirmPassInput = document.getElementById("reg_confirm_password");
    const courseSelect = document.getElementById("reg_course");
    const yearSelect = document.getElementById("reg_year");
    const termsCheck = document.getElementById("reg_terms");

    // Real-time password strength meter
    if (passInput) {
        passInput.addEventListener("input", function () {
            checkPasswordStrength(this.value);
            if (confirmPassInput && confirmPassInput.value) {
                validateConfirmPassword();
            }
        });
    }

    if (confirmPassInput) {
        confirmPassInput.addEventListener("input", validateConfirmPassword);
    }

    if (nameInput) nameInput.addEventListener("blur", validateName);
    if (emailInput) emailInput.addEventListener("blur", validateEmail);
    if (mobileInput) mobileInput.addEventListener("blur", validateMobile);

    function setError(input, errorElementId, message) {
        const errorEl = document.getElementById(errorElementId);
        if (errorEl) errorEl.textContent = message;
        if (input) {
            if (message) {
                input.classList.add("is-invalid");
                input.classList.remove("is-valid");
            } else {
                input.classList.remove("is-invalid");
                input.classList.add("is-valid");
            }
        }
    }

    function validateName() {
        if (!nameInput) return true;
        const val = nameInput.value.trim();
        const regex = /^[A-Za-z ]{2,50}$/;
        if (val === "") {
            setError(nameInput, "nameError", "Full Name is required.");
            return false;
        } else if (!regex.test(val)) {
            setError(nameInput, "nameError", "Name should only contain letters and spaces (min 2 characters).");
            return false;
        }
        setError(nameInput, "nameError", "");
        return true;
    }

    function validateEmail() {
        if (!emailInput) return true;
        const val = emailInput.value.trim();
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (val === "") {
            setError(emailInput, "emailError", "Email address is required.");
            return false;
        } else if (!regex.test(val)) {
            setError(emailInput, "emailError", "Please enter a valid email address (e.g., student@charusat.edu.in).");
            return false;
        }
        setError(emailInput, "emailError", "");
        return true;
    }

    function validateMobile() {
        if (!mobileInput) return true;
        const val = mobileInput.value.trim();
        const regex = /^[6-9][0-9]{9}$/;
        if (val === "") {
            setError(mobileInput, "mobileError", "Mobile number is required.");
            return false;
        } else if (!regex.test(val)) {
            setError(mobileInput, "mobileError", "Please enter a valid 10-digit Indian mobile number starting with 6-9.");
            return false;
        }
        setError(mobileInput, "mobileError", "");
        return true;
    }

    function validateConfirmPassword() {
        if (!confirmPassInput || !passInput) return true;
        if (confirmPassInput.value === "") {
            setError(confirmPassInput, "confirmPasswordError", "Please confirm your password.");
            return false;
        } else if (confirmPassInput.value !== passInput.value) {
            setError(confirmPassInput, "confirmPasswordError", "Passwords do not match.");
            return false;
        }
        setError(confirmPassInput, "confirmPasswordError", "");
        return true;
    }

    function checkPasswordStrength(val) {
        const bar = document.getElementById("strengthBar");
        const label = document.getElementById("strengthText");
        if (!bar || !label) return;

        if (!val) {
            bar.className = "strength-bar";
            bar.style.width = "0%";
            label.textContent = "";
            return;
        }

        let score = 0;
        if (val.length >= 8) score++;
        if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
        if (/[0-9]/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        if (score <= 1) {
            bar.className = "strength-bar weak";
            label.className = "strength-text weak";
            label.textContent = "Weak Password (add letters, digits & min 8 characters)";
        } else if (score === 2 || score === 3) {
            bar.className = "strength-bar medium";
            label.className = "strength-text medium";
            label.textContent = "Medium Password (good, but consider special characters)";
        } else {
            bar.className = "strength-bar strong";
            label.className = "strength-text strong";
            label.textContent = "Strong Password";
        }
    }

    // Submit handler
    form.addEventListener("submit", function (e) {
        let isValid = true;

        if (!validateName()) isValid = false;
        if (!validateEmail()) isValid = false;
        if (!validateMobile()) isValid = false;

        // Enrollment
        if (enrollInput) {
            const val = enrollInput.value.trim();
            if (val === "") {
                setError(enrollInput, "enrollError", "Enrollment number is required.");
                isValid = false;
            } else {
                setError(enrollInput, "enrollError", "");
            }
        }

        // Password length check
        if (passInput) {
            if (passInput.value.length < 6) {
                setError(passInput, "passwordError", "Password must be at least 6 characters long.");
                isValid = false;
            } else {
                setError(passInput, "passwordError", "");
            }
        }

        if (!validateConfirmPassword()) isValid = false;

        // Course select
        if (courseSelect && courseSelect.value === "") {
            setError(courseSelect, "courseError", "Please select your course/branch.");
            isValid = false;
        } else if (courseSelect) {
            setError(courseSelect, "courseError", "");
        }

        // Year select
        if (yearSelect && yearSelect.value === "") {
            setError(yearSelect, "yearError", "Please select your current year.");
            isValid = false;
        } else if (yearSelect) {
            setError(yearSelect, "yearError", "");
        }

        // Gender radio
        const genderChecked = form.querySelector('input[name="gender"]:checked');
        const genderErr = document.getElementById("genderError");
        if (!genderChecked) {
            if (genderErr) genderErr.textContent = "Please select your gender.";
            isValid = false;
        } else if (genderErr) {
            genderErr.textContent = "";
        }

        // Terms
        if (termsCheck && !termsCheck.checked) {
            const termsErr = document.getElementById("termsError");
            if (termsErr) termsErr.textContent = "You must accept the terms and conditions to register.";
            isValid = false;
        } else {
            const termsErr = document.getElementById("termsError");
            if (termsErr) termsErr.textContent = "";
        }

        if (!isValid) {
            e.preventDefault();
            // Scroll to first invalid field
            const firstInvalid = form.querySelector(".is-invalid");
            if (firstInvalid) firstInvalid.focus();
        }
    });
});
