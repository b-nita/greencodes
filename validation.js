// Wait for DOM to load
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('contactForm');
    
    if (form) {
        // Real-time validation as user types
        setupRealTimeValidation();
        
        // Form submission handler
        form.addEventListener('submit', handleFormSubmit);
    }
});

// ========================
// CORE FUNCTIONS
// ========================

/**
 * Handles form submission with validation
 */
async function handleFormSubmit(e) {
    e.preventDefault();
    clearErrors();

    const form = e.target;
    const formData = new FormData(form);
    const isValid = validateFormFields(formData);

    if (!isValid) return;

    try {
        // Send to backend (PHP/Node.js/Formspree)
        const response = await fetch(form.action, {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (!response.ok) {
            displayServerErrors(result.errors);
            throw new Error(result.message || 'Submission failed');
        }

        // Success!
        form.reset();
        showSuccess(result.message || 'Message sent successfully!');
        
    } catch (error) {
        console.error('Error:', error);
        showError('Submission failed. Please try again.');
    }
}

/**
 * Validates all form fields
 */
function validateFormFields(formData) {
    let isValid = true;
    const name = formData.get('name').trim();
    const email = formData.get('email').trim();
    const message = formData.get('message').trim();

    // Name validation
    if (name.length < 3) {
        showError('nameError', 'Name must be at least 3 characters');
        isValid = false;
    }

    // Email validation
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        showError('emailError', 'Please enter a valid email');
        isValid = false;
    }

    // Message validation
    if (message.length < 10) {
        showError('messageError', 'Message must be at least 10 characters');
        isValid = false;
    }

    return isValid;
}

// ========================
// HELPER FUNCTIONS
// ========================

function setupRealTimeValidation() {
    document.getElementById('name').addEventListener('input', function() {
        if (this.value.length > 0 && this.value.length < 3) {
            showError('nameError', 'Name too short');
        } else {
            hideError('nameError');
        }
    });

    // Add similar listeners for email/message...
}

function clearErrors() {
    document.querySelectorAll('.error').forEach(el => {
        el.style.display = 'none';
    });
}

function showError(elementId, message) {
    const element = document.getElementById(elementId);
    if (element) {
        element.textContent = message;
        element.style.display = 'block';
    }
}

function hideError(elementId) {
    const element = document.getElementById(elementId);
    if (element) element.style.display = 'none';
}

function displayServerErrors(errors) {
    if (!errors) return;
    Object.entries(errors).forEach(([field, message]) => {
        showError(`${field}Error`, message);
    });
}

function showSuccess(message) {
    alert(message); // Replace with modal/toast in production
    // document.getElementById('successMessage').textContent = message;
}

function showError(message) {
    alert(message); // Replace with modal/toast in production
    // document.getElementById('errorMessage').textContent = message;
}