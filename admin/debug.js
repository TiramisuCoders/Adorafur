// Add this file to your project to help debug issues
// Include it in your HTML with: <script src="debug.js"></script>

// Function to check if all required elements exist
function checkRequiredElements() {
    const elements = [
      { id: "real-time-clock", name: "Clock" },
      { id: "week-range", name: "Week Range" },
      { id: "calendar", name: "Calendar" },
      { id: "activityModal", name: "Activity Modal" },
      { id: "activity_description", name: "Activity Description Input" },
      { id: "activity_date", name: "Activity Date Input" },
      { id: "activity_time", name: "Activity Time Input" },
      { id: "activity_type", name: "Activity Type Input" },
      { id: "submitActivityBtn", name: "Submit Button" },
    ]
  
    console.group("Element Check")
    let allFound = true
  
    elements.forEach((element) => {
      const el = document.getElementById(element.id)
      if (el) {
        console.log(`✅ ${element.name} found`)
      } else {
        console.error(`❌ ${element.name} NOT found (id: ${element.id})`)
        allFound = false
      }
    })
  
    // Check for class-based elements
    const classElements = [
      { class: ".reminders-section", name: "Reminders Section" },
      { class: ".tasks-section", name: "Tasks Section" },
      { class: ".day", name: "Calendar Days" },
    ]
  
    classElements.forEach((element) => {
      const els = document.querySelectorAll(element.class)
      if (els && els.length > 0) {
        console.log(`✅ ${element.name} found (${els.length} elements)`)
      } else {
        console.error(`❌ ${element.name} NOT found (class: ${element.class})`)
        allFound = false
      }
    })
  
    console.log(allFound ? "✅ All required elements found" : "❌ Some elements are missing")
    console.groupEnd()
  
    return allFound
  }
  
  // Function to check API endpoints
  function checkApiEndpoints() {
    const baseUrl = window.location.pathname.includes("/admin/") ? "./" : "./admin/"
    const endpoints = [
      { url: baseUrl + "fetch_reminders.php", name: "Fetch Reminders" },
      { url: baseUrl + "add_activity.php", name: "Add Activity" },
      { url: baseUrl + "fetch_bookings.php?start_date=2023-01-01&end_date=2023-01-07", name: "Fetch Bookings" },
    ]
  
    console.group("API Endpoint Check")
  
    endpoints.forEach((endpoint) => {
      console.log(`Testing ${endpoint.name} endpoint: ${endpoint.url}`)
  
      fetch(endpoint.url, { method: "HEAD" })
        .then((response) => {
          if (response.ok) {
            console.log(`✅ ${endpoint.name} endpoint exists`)
          } else {
            console.error(`❌ ${endpoint.name} endpoint returned ${response.status}`)
          }
        })
        .catch((error) => {
          console.error(`❌ ${endpoint.name} endpoint error:`, error)
        })
    })
  
    console.groupEnd()
  }
  
  // Function to display environment info
  function showEnvironmentInfo() {
    console.group("Environment Info")
    console.log("URL:", window.location.href)
    console.log("Path:", window.location.pathname)
    console.log("Host:", window.location.host)
    console.log("User Agent:", navigator.userAgent)
    console.groupEnd()
  }
  
  // Run checks when the page loads
  document.addEventListener("DOMContentLoaded", () => {
    console.log("🔍 Running diagnostic checks...")
    showEnvironmentInfo()
    checkRequiredElements()
    checkApiEndpoints()
  })
  