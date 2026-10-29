// Theme switcher is managed centrally in assets/js/main.js to prevent double-event conflicts

// Registration Form Validation
let form = document.getElementById("registrationForm");

if (form) {

    form.addEventListener("submit", function(event) {

        event.preventDefault();

        let name = document.getElementById("name") ? document.getElementById("name").value.trim() : "";
        let email = document.getElementById("email") ? document.getElementById("email").value.trim() : "";
        let mobile = document.getElementById("mobile") ? document.getElementById("mobile").value.trim() : "";
        let password = document.getElementById("password") ? document.getElementById("password").value : "";
        let confirmPassword = document.getElementById("confirmPassword") ? document.getElementById("confirmPassword").value : "";
        let course = document.getElementById("course") ? document.getElementById("course").value : "";
        let year = document.getElementById("year") ? document.getElementById("year").value : "";
        let termsEl = document.getElementById("terms");
        let terms = termsEl ? termsEl.checked : true;

        let gender = document.querySelector('input[name="gender"]:checked');

        let valid = true;

        // Name validation
        let nameErr = document.getElementById("nameError");
        if (nameErr) {
            if (!/^[A-Za-z ]{2,}$/.test(name)) {
                nameErr.textContent = "Please enter a valid name.";
                valid = false;
            } else {
                nameErr.textContent = "";
            }
        }

        // Email validation
        let emailErr = document.getElementById("emailError");
        if (emailErr) {
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                emailErr.textContent = "Please enter a valid email.";
                valid = false;
            } else {
                emailErr.textContent = "";
            }
        }

        // Mobile validation
        let mobileErr = document.getElementById("mobileError");
        if (mobileErr) {
            if (!/^[0-9]{10}$/.test(mobile)) {
                mobileErr.textContent = "Mobile number must contain 10 digits.";
                valid = false;
            } else {
                mobileErr.textContent = "";
            }
        }

        // Password validation
        let passErr = document.getElementById("passwordError");
        if (passErr) {
            if (!/^(?=.*[A-Za-z])(?=.*\d).{8,}$/.test(password)) {
                passErr.textContent = "Password must be at least 8 characters and contain a letter and number.";
                valid = false;
            } else {
                passErr.textContent = "";
            }
        }

        // Confirm password
        let confErr = document.getElementById("confirmPasswordError");
        if (confErr) {
            if (password !== confirmPassword || confirmPassword === "") {
                confErr.textContent = "Passwords do not match.";
                valid = false;
            } else {
                confErr.textContent = "";
            }
        }

        // Course
        let courseErr = document.getElementById("courseError");
        if (courseErr) {
            if (course === "") {
                courseErr.textContent = "Please select a course.";
                valid = false;
            } else {
                courseErr.textContent = "";
            }
        }

        // Year
        let yearErr = document.getElementById("yearError");
        if (yearErr) {
            if (year === "") {
                yearErr.textContent = "Please select your year.";
                valid = false;
            } else {
                yearErr.textContent = "";
            }
        }

        // Gender
        let genderErr = document.getElementById("genderError");
        if (genderErr) {
            if (!gender) {
                genderErr.textContent = "Please select your gender.";
                valid = false;
            } else {
                genderErr.textContent = "";
            }
        }

        // Terms
        let termsErr = document.getElementById("termsError");
        if (termsErr) {
            if (!terms) {
                termsErr.textContent = "Please accept the terms and conditions.";
                valid = false;
            } else {
                termsErr.textContent = "";
            }
        }

        // Final result
        if (valid) {
            alert("Registration successful!");
            form.reset();
            let strength = document.getElementById("strength");
            if (strength) strength.textContent = "";
        }

    });

    // Password Strength
    let pwdInput = document.getElementById("password");
    if (pwdInput) {
        pwdInput.addEventListener("input", function() {
            let password = this.value;
            let strength = document.getElementById("strength");
            if (!strength) return;

            if (password.length < 6) {
                strength.textContent = "Weak Password";
            } else if (password.length < 8) {
                strength.textContent = "Medium Password";
            } else {
                strength.textContent = "Strong Password";
            }
        });
    }

}
