// ✅ Real-Time Clock Function
function updateClock() {
  const clockElement = document.getElementById("real-time-clock")
  if (!clockElement) return

  const now = new Date()
  let hours = now.getHours()
  let minutes = now.getMinutes()
  const amPm = hours >= 12 ? "PM" : "AM"

  hours = hours % 12 || 12
  minutes = minutes < 10 ? "0" + minutes : minutes

  clockElement.textContent = `${hours}:${minutes} ${amPm}`
}

// ✅ Update the date display
function updateDateDisplay() {
  const dateElement = document.querySelector(".date-and-day")
  if (!dateElement) return

  const now = new Date()
  const formattedDate = now.toLocaleDateString("en-US", {
    weekday: "long",
    month: "long",
    day: "numeric",
    year: "numeric",
  })

  dateElement.textContent = formattedDate
}

// ✅ Calendar Logic
const currentDate = new Date()

function getWeekDates(date) {
  const startOfWeek = new Date(date)
  const dayOfWeek = startOfWeek.getDay()
  startOfWeek.setDate(startOfWeek.getDate() - dayOfWeek + (dayOfWeek === 0 ? -6 : 1))

  return Array.from({ length: 7 }, (_, i) => {
    const d = new Date(startOfWeek)
    d.setDate(d.getDate() + i)
    return d
  })
}

function formatDateToISO(date) {
  return date.toISOString().split("T")[0]
}

// ✅ Renders the calendar
function renderCalendar() {
  const weekDates = getWeekDates(currentDate)
  const firstDayOfWeek = weekDates[0]

  const weekRangeElement = document.getElementById("week-range")
  if (weekRangeElement) {
    weekRangeElement.textContent = firstDayOfWeek.toLocaleString("en-US", {
      month: "long",
      year: "numeric",
    })
  }

  const calendar = document.getElementById("calendar")
  if (!calendar) return

  calendar.innerHTML = ""

  weekDates.forEach((date) => {
    const dayDiv = document.createElement("div")
    dayDiv.className = "day"

    const dayText = document.createElement("div")
    dayText.className = "day-name"
    dayText.textContent = date.toLocaleDateString("en-US", { weekday: "long" }).toUpperCase()

    const dateText = document.createElement("div")
    dateText.className = "day-number"
    dateText.textContent = date.getDate().toString().padStart(2, "0")

    dayDiv.appendChild(dayText)
    dayDiv.appendChild(dateText)
    calendar.appendChild(dayDiv)
  })

  // Fetch and display bookings after rendering the calendar
  fetchBookingsForWeek()
}

// ✅ Changes the displayed week when clicking buttons
function changeWeek(offset) {
  currentDate.setDate(currentDate.getDate() + offset * 7)
  renderCalendar()
}

// Update the fetchReminders function to better handle current and future tasks
function fetchReminders() {
  console.log("Fetching current and future reminders...")

  // Show loading state
  const remindersContainer = document.querySelector(".reminders-section")
  const tasksContainer = document.querySelector(".tasks-section")

  if (remindersContainer && tasksContainer) {
    remindersContainer.innerHTML =
      '<div class="sidebar-title">REMINDERS</div><div class="sidebar-textbox">Loading...</div>'
    tasksContainer.innerHTML = '<div class="sidebar-title">TASKS</div><div class="sidebar-textbox">Loading...</div>'
  }

  fetch("fetch_reminders.php", {
    method: "GET",
    headers: {
      "Cache-Control": "no-cache",
    },
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`)
      }
      return response.json()
    })
    .then((responseData) => {
      console.log("Response received:", responseData)

      if (!responseData.success) {
        console.error("Error:", responseData.error || "Unknown error")
        throw new Error(responseData.error || "Failed to fetch reminders")
      }

      const data = responseData.data || []

      if (!remindersContainer || !tasksContainer) {
        console.error("Container elements not found")
        return
      }

      // Clear existing content
      remindersContainer.innerHTML = '<div class="sidebar-title">REMINDERS</div>'
      tasksContainer.innerHTML = '<div class="sidebar-title">TASKS</div>'

      // Filter reminders and tasks
      const reminders = data.filter((item) => item.activity_type.toLowerCase() === "reminder")
      const tasks = data.filter((item) => item.activity_type.toLowerCase() === "task")

      // Group activities by date for better organization
      const today = new Date().toISOString().split("T")[0]
      const tomorrow = new Date()
      tomorrow.setDate(tomorrow.getDate() + 1)
      const tomorrowStr = tomorrow.toISOString().split("T")[0]

      // Add reminders with proper class for visibility control
      if (reminders.length === 0) {
        const noReminders = document.createElement("div")
        noReminders.className = "sidebar-textbox"
        noReminders.innerHTML = '<div class="sidebar-subtitle">No upcoming reminders</div>'
        remindersContainer.appendChild(noReminders)
      } else {
        // Group reminders by date category (Today, Tomorrow, Future)
        const todayReminders = reminders.filter((item) => item.activity_date === today)
        const tomorrowReminders = reminders.filter((item) => item.activity_date === tomorrowStr)
        const futureReminders = reminders.filter((item) => item.activity_date > tomorrowStr)

        // Add today's reminders
        if (todayReminders.length > 0) {
          const todayHeader = document.createElement("div")
          todayHeader.className = "sidebar-date-header"
          todayHeader.textContent = "Today"
          remindersContainer.appendChild(todayHeader)

          todayReminders.forEach((activity) => {
            const item = createActivityItem(activity, "reminder-item")
            remindersContainer.appendChild(item)
          })
        }

        // Add tomorrow's reminders
        if (tomorrowReminders.length > 0) {
          const tomorrowHeader = document.createElement("div")
          tomorrowHeader.className = "sidebar-date-header"
          tomorrowHeader.textContent = "Tomorrow"
          remindersContainer.appendChild(tomorrowHeader)

          tomorrowReminders.forEach((activity) => {
            const item = createActivityItem(activity, "reminder-item")
            remindersContainer.appendChild(item)
          })
        }

        // Add future reminders
        if (futureReminders.length > 0) {
          const futureHeader = document.createElement("div")
          futureHeader.className = "sidebar-date-header"
          futureHeader.textContent = "Upcoming"
          remindersContainer.appendChild(futureHeader)

          futureReminders.forEach((activity) => {
            const item = createActivityItem(activity, "reminder-item")
            remindersContainer.appendChild(item)
          })
        }
      }

      // Add tasks with proper class for visibility control
      if (tasks.length === 0) {
        const noTasks = document.createElement("div")
        noTasks.className = "sidebar-textbox"
        noTasks.innerHTML = '<div class="sidebar-subtitle">No upcoming tasks</div>'
        tasksContainer.appendChild(noTasks)
      } else {
        // Group tasks by date category (Today, Tomorrow, Future)
        const todayTasks = tasks.filter((item) => item.activity_date === today)
        const tomorrowTasks = tasks.filter((item) => item.activity_date === tomorrowStr)
        const futureTasks = tasks.filter((item) => item.activity_date > tomorrowStr)

        // Add today's tasks
        if (todayTasks.length > 0) {
          const todayHeader = document.createElement("div")
          todayHeader.className = "sidebar-date-header"
          todayHeader.textContent = "Today"
          tasksContainer.appendChild(todayHeader)

          todayTasks.forEach((activity) => {
            const item = createActivityItem(activity, "task-item")
            tasksContainer.appendChild(item)
          })
        }

        // Add tomorrow's tasks
        if (tomorrowTasks.length > 0) {
          const tomorrowHeader = document.createElement("div")
          tomorrowHeader.className = "sidebar-date-header"
          tomorrowHeader.textContent = "Tomorrow"
          tasksContainer.appendChild(tomorrowHeader)

          tomorrowTasks.forEach((activity) => {
            const item = createActivityItem(activity, "task-item")
            tasksContainer.appendChild(item)
          })
        }

        // Add future tasks
        if (futureTasks.length > 0) {
          const futureHeader = document.createElement("div")
          futureHeader.className = "sidebar-date-header"
          futureHeader.textContent = "Upcoming"
          tasksContainer.appendChild(futureHeader)

          futureTasks.forEach((activity) => {
            const item = createActivityItem(activity, "task-item")
            tasksContainer.appendChild(item)
          })
        }
      }

      // Add the "Add Reminder" button
      const addReminderBtn = document.createElement("div")
      addReminderBtn.className = "sidebar-textbox add-sidebar"
      addReminderBtn.innerHTML = `
        <div class="add-sidebar">+ Add Reminder</div>
        <div class="sidebar-line"></div>
      `
      remindersContainer.appendChild(addReminderBtn)

      // Add "View More" button for reminders if there are more than 3
      if (reminders.length > 3) {
        const viewMoreBtn = document.createElement("div")
        viewMoreBtn.className = "view-rem"
        viewMoreBtn.id = "viewRemindersBtn"
        viewMoreBtn.textContent = "View More"
        viewMoreBtn.dataset.expanded = "false"
        remindersContainer.appendChild(viewMoreBtn)
      }

      // Add the "Add Task" button
      const addTaskBtn = document.createElement("div")
      addTaskBtn.className = "sidebar-textbox add-sidebar"
      addTaskBtn.innerHTML = `
        <div class="add-sidebar">+ Add Task</div>
        <div class="sidebar-line"></div>
      `
      tasksContainer.appendChild(addTaskBtn)

      // Add "View More" button for tasks if there are more than 3
      if (tasks.length > 3) {
        const viewMoreBtn = document.createElement("div")
        viewMoreBtn.className = "view-task"
        viewMoreBtn.id = "viewTasksBtn"
        viewMoreBtn.textContent = "View More"
        viewMoreBtn.dataset.expanded = "false"
        tasksContainer.appendChild(viewMoreBtn)
      }

      // Set up the view more/less functionality
      limitVisibleItems(".reminders-section .reminder-item", 3, "#viewRemindersBtn")
      limitVisibleItems(".tasks-section .task-item", 3, "#viewTasksBtn")

      // Attach event listeners to the add buttons using event delegation
      attachAddButtonListeners()

      console.log("Sidebar updated successfully with current and future activities")
    })
    .catch((error) => {
      console.error("Error loading reminders/tasks:", error)

      // Show error message in the sidebar
      if (remindersContainer && tasksContainer) {
        remindersContainer.innerHTML =
          '<div class="sidebar-title">UPCOMING REMINDERS</div><div class="sidebar-textbox error">Error loading reminders</div>'
        tasksContainer.innerHTML =
          '<div class="sidebar-title">UPCOMING TASKS</div><div class="sidebar-textbox error">Error loading tasks</div>'

        // Still add the buttons so users can add new items
        const addReminderBtn = document.createElement("div")
        addReminderBtn.className = "sidebar-textbox add-sidebar"
        addReminderBtn.innerHTML = `
          <div class="add-sidebar">+ Add Reminder</div>
          <div class="sidebar-line"></div>
        `
        remindersContainer.appendChild(addReminderBtn)

        const addTaskBtn = document.createElement("div")
        addTaskBtn.className = "sidebar-textbox add-sidebar"
        addTaskBtn.innerHTML = `
          <div class="add-sidebar">+ Add Task</div>
          <div class="sidebar-line"></div>
        `
        tasksContainer.appendChild(addTaskBtn)

        attachAddButtonListeners()
      }
    })
}

// Helper function to create an activity item with proper styling
function createActivityItem(activity, itemClass) {
  const item = document.createElement("div")
  item.className = `sidebar-textbox ${itemClass}`

  const dateText = activity.formatted_date || activity.activity_date
  const timeText = activity.formatted_time || activity.activity_time

  // Check if the activity is for today
  const today = new Date().toISOString().split("T")[0]
  const isToday = activity.activity_date === today

  // Use span with class for styling
  const dateDisplay = isToday ? '<span class="today-text">Today</span>' : dateText

  item.innerHTML = `
    <div class="sidebar-subtitle">${activity.activity_description || "Untitled"}</div>
    <div class="sidebar-desc">at ${timeText} on ${dateDisplay}</div>
    <div class="sidebar-line"></div>
  `

  return item
}

// Add some CSS for the new date headers
document.addEventListener("DOMContentLoaded", () => {
  // Remove this code that adds styles dynamically
  /*
  const style = document.createElement('style');
  style.textContent = `
    .sidebar-date-header {
      font-size: 0.9rem;
      font-weight: bold;
      margin: 10px 0 5px 5px;
      color: #555;
    }
    
    .sidebar-title {
      margin-bottom: 10px;
    }
  `;
  document.head.appendChild(style);
  */
})

// Helper function to attach event listeners to add buttons
function attachAddButtonListeners() {
  // Use event delegation instead of direct binding
  document.querySelectorAll(".add-sidebar").forEach((button) => {
    button.addEventListener("click", function () {
      const type = this.textContent.includes("Reminder") ? "reminder" : "task"
      openAddActivityForm(type)
    })
  })
}

// ✅ View More / View Less Logic
function limitVisibleItems(selector, limit, toggleButtonSelector) {
  const items = document.querySelectorAll(selector)
  const toggleButton = document.querySelector(toggleButtonSelector)

  if (!items.length || !toggleButton) return

  items.forEach((item, index) => {
    item.style.display = index < limit ? "block" : "none"
  })

  toggleButton.addEventListener("click", () => {
    const isExpanded = toggleButton.dataset.expanded === "true"

    items.forEach((item, index) => {
      item.style.display = isExpanded ? (index < limit ? "block" : "none") : "block"
    })

    toggleButton.textContent = isExpanded ? "View More" : "View Less"
    toggleButton.dataset.expanded = isExpanded ? "false" : "true"
  })
}

// ✅ Open and Close Modal
function openAddActivityForm(type) {
  const modal = document.getElementById("activityModal")
  if (!modal) return

  // Set default date to today
  const today = new Date()
  const formattedDate = today.toISOString().split("T")[0]
  document.getElementById("activity_date").value = formattedDate

  // Set default time to current time
  const hours = String(today.getHours()).padStart(2, "0")
  const minutes = String(today.getMinutes()).padStart(2, "0")
  document.getElementById("activity_time").value = `${hours}:${minutes}`

  document.getElementById("activity_type").value = type.charAt(0).toUpperCase() + type.slice(1)
  document.getElementById("activity-type-title").textContent = type.charAt(0).toUpperCase() + type.slice(1)

  // Clear description field
  document.getElementById("activity_description").value = ""

  modal.classList.add("open")
}

function closeSidebarModal() {
  const modal = document.getElementById("activityModal")
  if (modal) {
    modal.classList.remove("open")
  }
}

// ✅ Submit Activity with improved error handling
function submitActivity() {
  const description = document.getElementById("activity_description").value.trim()
  const date = document.getElementById("activity_date").value
  const time = document.getElementById("activity_time").value
  const type = document.getElementById("activity_type").value

  if (!description || !date || !time) {
    alert("All fields are required!")
    return
  }

  // Disable the submit button to prevent multiple submissions
  const submitBtn = document.getElementById("submitActivityBtn")
  if (submitBtn) {
    submitBtn.disabled = true
    submitBtn.textContent = "Adding..."
  }

  const formData = new FormData()
  formData.append("description", description)
  formData.append("date", date)
  formData.append("time", time)
  formData.append("type", type)

  console.log("Submitting activity:", { description, date, time, type })

  fetch("add_activity.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => {
      console.log("Response status:", response.status)
      if (!response.ok) {
        return response.text().then((text) => {
          throw new Error(`HTTP error! status: ${response.status}, body: ${text}`)
        })
      }
      return response.json()
    })
    .then((data) => {
      console.log("Response data:", data)
      if (data.success) {
        alert("Activity added successfully!")
        closeSidebarModal()
        fetchReminders() // Refresh the sidebar
      } else {
        alert("Error adding activity: " + (data.error || "Unknown error"))
      }
    })
    .catch((error) => {
      console.error("Error:", error)
      alert("An error occurred while adding the activity. Error: " + error.message)
    })
    .finally(() => {
      // Re-enable the submit button
      if (submitBtn) {
        submitBtn.disabled = false
        submitBtn.textContent = "Add"
      }
    })
}

// Function to fetch bookings for the current week
function fetchBookingsForWeek() {
  const weekDates = getWeekDates(currentDate)
  const startDate = formatDateToISO(weekDates[0])
  const endDate = formatDateToISO(weekDates[6])

  console.log(`Fetching bookings from ${startDate} to ${endDate}`)

  // Show loading indicator in each day cell
  document.querySelectorAll(".day").forEach((day) => {
    const loadingDiv = document.createElement("div")
    loadingDiv.className = "booking-loading"
    loadingDiv.textContent = "Loading bookings..."
    loadingDiv.style.fontSize = "12px"
    loadingDiv.style.color = "#888"
    loadingDiv.style.marginTop = "10px"
    day.appendChild(loadingDiv)
  })

  fetch(`fetch_bookings.php?start_date=${startDate}&end_date=${endDate}`, {
    method: "GET",
    headers: {
      "Cache-Control": "no-cache",
    },
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`)
      }
      return response.json()
    })
    .then((responseData) => {
      console.log("Bookings response:", responseData)

      // Remove loading indicators
      document.querySelectorAll(".booking-loading").forEach((el) => el.remove())

      if (responseData.success) {
        displayBookingsInCalendar(responseData.data, weekDates)
      } else {
        console.error("Error fetching bookings:", responseData.error)
        throw new Error(responseData.error || "Failed to fetch bookings")
      }
    })
    .catch((error) => {
      console.error("Error loading bookings:", error)

      // Remove loading indicators
      document.querySelectorAll(".booking-loading").forEach((el) => el.remove())

      // Show error message in each day cell
      document.querySelectorAll(".day").forEach((day) => {
        const errorDiv = document.createElement("div")
        errorDiv.className = "booking-error"
        errorDiv.textContent = "Could not load bookings"
        errorDiv.style.fontSize = "12px"
        errorDiv.style.color = "#d32f2f"
        errorDiv.style.marginTop = "10px"
        day.appendChild(errorDiv)
      })
    })
}

// Function to display bookings in the calendar
function displayBookingsInCalendar(bookingsData, weekDates) {
  // Clear any existing booking elements
  document.querySelectorAll(".bookings-container").forEach((el) => el.remove())

  // Loop through each day in the week
  weekDates.forEach((date, index) => {
    const dateStr = formatDateToISO(date)
    const dayElement = document.querySelectorAll(".day")[index]

    if (!dayElement) return

    // Check if there are bookings for this date
    const bookingsForDay = bookingsData[dateStr] || []

    if (bookingsForDay.length > 0) {
      // Create a container for bookings
      const bookingsContainer = document.createElement("div")
      bookingsContainer.className = "bookings-container"

      // Add each booking
      bookingsForDay.forEach((booking) => {
        // Create a simple booking element
        const bookingElement = document.createElement("div")
        bookingElement.className = `booking-item booking-status-${booking.booking_status.toLowerCase()} booking-service-${booking.service_name.toLowerCase().replace(/\s+/g, "-")}`

        // Add the booking content
        bookingElement.innerHTML = `
          <div class="booking-header">
            <div class="booking-pet-name">${booking.pet_name}</div>
          </div>
          <div class="booking-times">
            ${booking.formatted_check_in_time} - ${booking.formatted_check_out_time}
          </div>
          <div class="booking-status">${booking.booking_status}</div>
        `

        bookingsContainer.appendChild(bookingElement)
      })

      dayElement.appendChild(bookingsContainer)
    }
  })
}

// ✅ Event Listeners - Improved initialization
document.addEventListener("DOMContentLoaded", () => {
  console.log("DOM fully loaded")

  // Initialize clock
  updateClock()
  setInterval(updateClock, 1000)

  // Initialize calendar with bookings
  renderCalendar()

  console.log("Initialization complete")
})
