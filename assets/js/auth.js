// ===================================================
// CampusHub - Authentication JavaScript
// Covers: Variables, Functions, Events, DOM Manipulation
// ===================================================

// ===== VARIABLES =====
const MKU_DOMAIN = '@mku.ac.ke';

// ===== FUNCTION 1: Auto-generate MKU email from admission number =====
function generateEmail() {
    const admInput = document.getElementById('admission_number');
    const emailInput = document.getElementById('mku_email');
    const emailStatus = document.getElementById('email-status');

    if (!admInput || !emailInput) return;

    admInput.addEventListener('input', function() {
        const raw = this.value.trim();
        // Remove slashes, lowercase — Variables in action
        const clean = raw.replace(/\//g, '').toLowerCase();

        if (clean.length >= 8) {
            // Auto-fill email
            emailInput.value = clean + MKU_DOMAIN;
            emailInput.style.borderColor = '#4caf50';
            emailInput.style.background = '#f0fff4';
            if (emailStatus) {
                emailStatus.innerHTML = '<i class="bi bi-check-circle-fill" style="color:#4caf50;"></i> Valid MKU email generated';
                emailStatus.style.color = '#4caf50';
            }
        } else {
            emailInput.value = '';
            emailInput.style.borderColor = '';
            emailInput.style.background = '';
            if (emailStatus) emailStatus.innerHTML = '';
        }
    });
}

// ===== FUNCTION 2: Real-time email validation =====
function validateEmail() {
    const emailInput = document.getElementById('mku_email');
    const emailStatus = document.getElementById('email-status');
    if (!emailInput) return;

    emailInput.addEventListener('input', function() {
        const val = this.value.trim().toLowerCase();
        const mkuPattern = /^[a-z0-9]+@mku\.ac\.ke$/;

        if (val === '') {
            this.style.borderColor = '';
            this.style.background = '';
            if (emailStatus) emailStatus.innerHTML = '';
        } else if (mkuPattern.test(val)) {
            this.style.borderColor = '#4caf50';
            this.style.background = '#f0fff4';
            if (emailStatus) {
                emailStatus.innerHTML = '<i class="bi bi-check-circle-fill"></i> Valid MKU email ✓';
                emailStatus.style.color = '#4caf50';
            }
        } else {
            this.style.borderColor = '#f44336';
            this.style.background = '#fff5f5';
            if (emailStatus) {
                emailStatus.innerHTML = '<i class="bi bi-x-circle-fill"></i> Must end with @mku.ac.ke';
                emailStatus.style.color = '#f44336';
            }
        }
    });
}

// ===== FUNCTION 3: Password strength checker =====
function initPasswordStrength() {
    const pwd = document.getElementById('password');
    const bar = document.getElementById('pwd-strength');
    const label = document.getElementById('pwd-label');
    const reqList = document.getElementById('pwd-requirements');

    if (!pwd) return;

    // Requirements checklist — DOM Manipulation
    const requirements = [
        { id: 'req-length',  test: v => v.length >= 6,              text: 'At least 6 characters' },
        { id: 'req-upper',   test: v => /[A-Z]/.test(v),            text: 'One uppercase letter' },
        { id: 'req-number',  test: v => /[0-9]/.test(v),            text: 'One number' },
        { id: 'req-special', test: v => /[^A-Za-z0-9]/.test(v),     text: 'One special character (!@#$)' },
    ];

    // Build requirements list in DOM
    if (reqList) {
        reqList.innerHTML = requirements.map(r =>
            `<div id="${r.id}" class="pwd-req">
                <i class="bi bi-x-circle-fill" style="color:#ddd;"></i>
                <span>${r.text}</span>
            </div>`
        ).join('');
    }

    pwd.addEventListener('input', function() {
        const val = this.value;
        let strength = 0;

        // Check each requirement — Events in action
        requirements.forEach(r => {
            const passed = r.test(val);
            if (passed) strength++;
            const el = document.getElementById(r.id);
            if (el) {
                el.querySelector('i').style.color = passed ? '#4caf50' : '#ddd';
                el.querySelector('i').className = passed
                    ? 'bi bi-check-circle-fill'
                    : 'bi bi-x-circle-fill';
                el.style.color = passed ? '#4caf50' : '#aaa';
            }
        });

        // Update strength bar
        const colors = ['#f44336', '#ff9800', '#ffc107', '#4caf50'];
        const labels = ['Weak', 'Fair', 'Good', 'Strong'];
        const percent = (strength / requirements.length) * 100;

        if (bar) {
            bar.style.width = percent + '%';
            bar.style.background = colors[strength - 1] || '#eee';
        }
        if (label) {
            label.textContent = val.length > 0 ? (labels[strength - 1] || '') : '';
            label.style.color = colors[strength - 1] || '#aaa';
        }
    });
}

// ===== FUNCTION 4: Show/Hide password toggle =====
function initPasswordToggle() {
    document.querySelectorAll('.pwd-toggle').forEach(btn => {
        btn.addEventListener('click', function() {
            // DOM Manipulation — changing element type
            const input = this.closest('.auth-input-wrap').querySelector('input');
            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'bi bi-eye';
            }
        });
    });
}

// ===== FUNCTION 5: Role card selection =====
function initRoleCards() {
    document.querySelectorAll('.role-option').forEach(option => {
        option.addEventListener('click', function() {
            // DOM Manipulation — hide/show selected state
            document.querySelectorAll('.role-option').forEach(o => o.classList.remove('selected'));
            this.classList.add('selected');
            this.querySelector('input[type="radio"]').checked = true;
        });
    });
}

// ===== FUNCTION 6: Login form validation =====
function initLoginValidation() {
    const form = document.getElementById('loginForm');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        let valid = true;

        // Email validation
        const email = document.getElementById('login-email');
        const emailMsg = document.getElementById('login-email-msg');
        if (email && email.value.trim() === '') {
            emailMsg.textContent = 'Please enter your email.';
            email.style.borderColor = '#f44336';
            valid = false;
        } else if (email) {
            emailMsg.textContent = '';
            email.style.borderColor = '#4caf50';
        }

        // Password validation
        const password = document.getElementById('login-password');
        const pwdMsg = document.getElementById('login-pwd-msg');
        if (password && password.value.trim() === '') {
            pwdMsg.textContent = 'Please enter your password.';
            password.style.borderColor = '#f44336';
            valid = false;
        } else if (password) {
            pwdMsg.textContent = '';
            password.style.borderColor = '#4caf50';
        }

        if (!valid) e.preventDefault();
    });
}

// ===== FUNCTION 7: Live text preview (DOM Manipulation Task 2) =====
function initLivePreview() {
    const nameInput = document.getElementById('full_name');
    const preview = document.getElementById('name-preview');
    if (!nameInput || !preview) return;

    nameInput.addEventListener('input', function() {
        // Live DOM update
        preview.textContent = this.value
            ? 'Hello, ' + this.value + '! 👋'
            : '';
    });
}

// ===== FUNCTION 8: Alert demo (JavaScript Alerts Task 1) =====
function showWelcomeAlert() {
    const btn = document.getElementById('demo-alert-btn');
    if (!btn) return;
    btn.addEventListener('click', function() {
        alert('Welcome to CampusHub! 🎓\nBuy, Sell and Support Student Businesses.');
    });
}

// ===== INIT — Run all functions on page load =====
document.addEventListener('DOMContentLoaded', function() {
    generateEmail();
    validateEmail();
    initPasswordStrength();
    initPasswordToggle();
    initRoleCards();
    initLoginValidation();
    initLivePreview();
    showWelcomeAlert();
});