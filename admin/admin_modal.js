// Initialize Supabase client
const SUPABASE_URL = "https://ygbwanzobuielhttdzsw.supabase.co"
const SUPABASE_ANON_KEY =
  "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InlnYndhbnpvYnVpZWxodHRkenN3Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDM1MTY3NTMsImV4cCI6MjA1OTA5Mjc1M30.bIaP_7rfHyne5PQ_Wmt8qdMYFDzurdnEAUR7U2bxbDQ"

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

// Function to create admin in Supabase and then in the database
async function createAdmin() {
  console.log("createAdmin function called")

  if (!validateForm()) {
    console.log("Form validation failed")
    return
  }

  const adminName = document.getElementById("admin_name").value
  const email = document.getElementById("email").value
  const password = document.getElementById("password").value
  const position = document.getElementById("admin_position").value

  try {
    console.log("Creating user in Supabase Auth")

    // Create Supabase client
    const supabase = window.supabase.createClient(SUPABASE_URL, SUPABASE_ANON_KEY)

    // First, create the user in Supabase Auth
    const { data: authData, error: authError } = await supabase.auth.signUp({
      email: email,
      password: password,
      options: {
        data: {
          name: adminName,
          position: position,
          full_name: adminName, // Add full_name for display name
        },
        emailRedirectTo: window.location.origin + "/admin/admin_login.php",
      },
    })

    console.log("Supabase Auth response:", authData)

    if (authError) {
      console.error("Supabase Auth error:", authError)
      throw new Error(authError.message)
    }

    if (!authData.user || !authData.user.id) {
      throw new Error("Failed to create user in Supabase Auth")
    }

    // Now insert the admin data into your custom admin table using AJAX
    console.log("Inserting admin data into database with Supabase UID:", authData.user.id)
    const formData = new FormData()
    formData.append("action", "create_admin")
    formData.append("admin_name", adminName)
    formData.append("admin_email", email)
    formData.append("admin_position", position)
    formData.append("supabase_uid", authData.user.id)
    formData.append("password", password) // Add the plain text password

    try {
      const response = await fetch("admin_ajax.php", {
        method: "POST",
        body: formData,
      })

      // Check if response is OK
      if (!response.ok) {
        throw new Error(`Server responded with status: ${response.status}`)
      }

      // Try to parse JSON response
      const responseText = await response.text()
      let result

      try {
        result = JSON.parse(responseText)
      } catch (parseError) {
        console.error("Failed to parse JSON response:", responseText)
        throw new Error(`Invalid JSON response: ${responseText.substring(0, 100)}...`)
      }

      console.log("Database insert response:", result)

      if (!result.success) {
        throw new Error(result.message || "Failed to create admin in database")
      }

      // Success - reload the page or show success message
      alert("Admin created successfully! Check email for confirmation link.")
      window.location.href = "admin_profile.php?success=1"
    } catch (fetchError) {
      console.error("Fetch error:", fetchError)
      throw new Error(`Error communicating with server: ${fetchError.message}`)
    }
  } catch (error) {
    console.error("Error creating admin:", error)
    // Display error message
    alert("Error creating admin: " + error.message)
  }
}

// Add event listeners when the DOM is fully loaded
document.addEventListener("DOMContentLoaded", () => {
  console.log("DOM fully loaded")

  // Add event listener to create admin button
  const createAdminBtn = document.getElementById("createAdminBtn")
  if (createAdminBtn) {
    console.log("Create Admin button found, adding event listener")
    createAdminBtn.addEventListener("click", createAdmin)
  } else {
    console.error("Create Admin button not found")
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

// Add console log to check if the script is loaded
console.log("admin_modal.js loaded")
