// Form validation utilities
const FormValidator = {
    patterns: {
        email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
        phone: /^[0-9]{10}$/,
        name: /^[a-zA-Z\s]{2,}$/,
        age: /^[0-9]{1,3}$/,
        numeric: /^[0-9]+$/
    },
    
    validateEmail(email) {
        return this.patterns.email.test(email);
    },
    
    validatePhone(phone) {
        return this.patterns.phone.test(phone.replace(/\D/g, ''));
    },
    
    validateName(name) {
        return name.trim().length >= 2 && /^[a-zA-Z\s]+$/.test(name);
    },
    
    validateAge(age) {
        const num = parseInt(age, 10);
        return num >= 18 && num <= 120;
    },
    
    validateRequired(value) {
        return value.trim().length > 0;
    },
    
    showError(input, message) {
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
        let errorEl = input.nextElementSibling;
        if (!errorEl || !errorEl.classList.contains('error-message')) {
            errorEl = document.createElement('div');
            errorEl.className = 'error-message';
            input.parentElement.appendChild(errorEl);
        }
        errorEl.textContent = message;
        errorEl.classList.add('show');
    },
    
    showSuccess(input) {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        const errorEl = input.nextElementSibling;
        if (errorEl && errorEl.classList.contains('error-message')) {
            errorEl.classList.remove('show');
        }
    },
    
    validateForm(form) {
        const inputs = form.querySelectorAll('[data-validate]');
        let isValid = true;
        
        inputs.forEach(input => {
            const rules = input.getAttribute('data-validate').split(',').map(r => r.trim());
            const value = input.value.trim();
            
            let fieldValid = true;
            for (let rule of rules) {
                let valid = false;
                
                switch(rule) {
                    case 'required':
                        valid = this.validateRequired(value);
                        if (!valid) this.showError(input, 'This field is required');
                        break;
                    case 'email':
                        valid = this.validateEmail(value);
                        if (!valid) this.showError(input, 'Please enter a valid email');
                        break;
                    case 'phone':
                        valid = this.validatePhone(value);
                        if (!valid) this.showError(input, 'Please enter a valid 10-digit phone number');
                        break;
                    case 'name':
                        valid = this.validateName(value);
                        if (!valid) this.showError(input, 'Please enter a valid name (letters and spaces only)');
                        break;
                    case 'age':
                        valid = this.validateAge(value);
                        if (!valid) this.showError(input, 'Age must be between 18 and 120');
                        break;
                    case 'numeric':
                        valid = this.patterns.numeric.test(value);
                        if (!valid) this.showError(input, 'Please enter numbers only');
                        break;
                }
                
                if (!valid) fieldValid = false;
            }
            
            if (fieldValid && value) this.showSuccess(input);
            if (!fieldValid) isValid = false;
        });
        
        return isValid;
    }
};

// Sidebar toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (btn && sidebar) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            sidebar.classList.toggle('collapsed');
            
            if (sidebar.classList.contains('collapsed')) {
                localStorage.setItem('bbms_sidebar', 'collapsed');
                btn.innerHTML = '<i class="bi bi-chevron-right"></i>';
            } else {
                localStorage.removeItem('bbms_sidebar');
                btn.innerHTML = '<i class="bi bi-chevron-left"></i>';
            }
        });
        
        if (localStorage.getItem('bbms_sidebar') === 'collapsed') {
            sidebar.classList.add('collapsed');
            btn.innerHTML = '<i class="bi bi-chevron-right"></i>';
        } else {
            btn.innerHTML = '<i class="bi bi-chevron-left"></i>';
        }
    }
    
    // Form validation on submit
    const forms = document.querySelectorAll('form[data-validate-form]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!FormValidator.validateForm(this)) {
                e.preventDefault();
            }
        });
    });
    
    // Real-time validation
    document.querySelectorAll('[data-validate]').forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value.trim()) {
                FormValidator.validateForm(this.closest('form'));
            }
        });
        
        input.addEventListener('input', function() {
            if (this.classList.contains('is-invalid')) {
                FormValidator.validateForm(this.closest('form'));
            }
        });
    });
    
    // Issue blood form confirmation
    const issueForm = document.querySelector('form.issue-form');
    if (issueForm) {
        issueForm.addEventListener('submit', function(e) {
            const units = parseInt(issueForm.querySelector('input[name="units"]').value || '0', 10);
            if (!confirm('Are you sure you want to issue ' + units + ' unit(s) of blood?')) {
                e.preventDefault();
            }
        });
    }
});