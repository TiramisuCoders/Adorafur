// Function to open the modal
function openModal() {
    document.getElementById("createAdminModal").style.display = "block"
  }
  
  // Function to close the modal
  function closeModal() {
    document.getElementById("createAdminModal").style.display = "none"
  }
  
  // Close the modal when clicking outside of it
  window.onclick = (event) => {
    const modal = document.getElementById("createAdminModal")
    if (event.target === modal) {
      closeModal()
    }
  }
  
  // Toggle password visibility
  function togglePasswordVisibility(inputId, iconId) {
    const passwordInput = document.getElementById(inputId)
    const icon = document.getElementById(iconId)
  
    if (passwordInput.type === "password") {
      passwordInput.type = "text"
      icon.className = "fa fa-eye-slash" // Change to eye-slash icon when password is visible
    } else {
      passwordInput.type = "password"
      icon.className = "fa fa-eye" // Change to eye icon when password is hidden
    }
  }
  
  // Form validation
  function validateForm() {
    let isValid = true
    const adminName = document.getElementById("admin_name").value
    const email = document.getElementById("email").value
    const password = document.getElementById("password").value
    const repeatPassword = document.getElementById("repeatPassword").value
    const position = document.getElementById("admin_position").value
  
    // Reset error messages
    document.querySelectorAll(".error-message").forEach((el) => (el.textContent = ""))
  
    // Validate name
    if (adminName.trim() === "") {
      document.getElementById("name_error").textContent = "Name is required"
      isValid = false
    } else if (!/^[a-zA-Z\s'-]+$/.test(adminName)) {
      document.getElementById("name_error").textContent = "Name must only contain letters, spaces, apostrophes, or dashes"
      isValid = false
    }
  
    // Validate email
    if (email.trim() === "") {
      document.getElementById("email_error").textContent = "Email is required"
      isValid = false
    } else if (!/^\S+@\S+\.\S+$/.test(email)) {
      document.getElementById("email_error").textContent = "Invalid email format"
      isValid = false
    }
  
    // Validate password
    if (password.trim() === "") {
      document.getElementById("password_error").textContent = "Password is required"
      isValid = false
    } else {
      const passwordErrors = []
  
      if (password.length < 8 || password.length > 12) {
        passwordErrors.push("Password must be between 8 and 12 characters")
      }
  
      if (!/[A-Z]/.test(password)) {
        passwordErrors.push("Password must contain at least 1 uppercase letter")
      }
  
      if (!/\d/.test(password)) {
        passwordErrors.push("Password must contain at least 1 number")
      }
  
      if (!/[\W_]/.test(password)) {
        passwordErrors.push("Password must contain at least 1 special character")
      }
  
      if (passwordErrors.length > 0) {
        document.getElementById("password_error").textContent = passwordErrors[0]
        isValid = false
      }
    }
  
    // Validate confirm password
    if (password !== repeatPassword) {
      document.getElementById("repeat_password_error").textContent = "Passwords do not match"
      isValid = false
    }
  
    // Validate position
    if (position.trim() === "") {
      document.getElementById("position_error").textContent = "Position is required"
      isValid = false
    }
  
    return isValid
  }
  
  // Add event listeners when the DOM is fully loaded
  document.addEventListener("DOMContentLoaded", () => {
    // Add event listener to form submission
    const form = document.getElementById("createAdminForm")
    if (form) {
      form.addEventListener("submit", (event) => {
        if (!validateForm()) {
          event.preventDefault()
        }
      })
    }
  
    // Add event listeners for password toggle buttons
    const passwordToggle = document.getElementById("passwordToggle")
    if (passwordToggle) {
      // Remove any existing event listeners to prevent duplication
      passwordToggle.replaceWith(passwordToggle.cloneNode(true))
  
      // Get the fresh element and add the event listener
      document.getElementById("passwordToggle").addEventListener("click", () => {
        togglePasswordVisibility("password", "passwordEyeIcon")
      })
    }
  
    const repeatPasswordToggle = document.getElementById("repeatPasswordToggle")
    if (repeatPasswordToggle) {
      // Remove any existing event listeners to prevent duplication
      repeatPasswordToggle.replaceWith(repeatPasswordToggle.cloneNode(true))
  
      // Get the fresh element and add the event listener
      document.getElementById("repeatPasswordToggle").addEventListener("click", () => {
        togglePasswordVisibility("repeatPassword", "repeatPasswordEyeIcon")
      })
    }
  })
  