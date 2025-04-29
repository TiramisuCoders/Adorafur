// ✅ Real-Time Clock Function
function updateClock() {
  const clockElement = document.getElementById("real-time-clock")
  if (!clockElement) {
    console.error("Clock element not found")
    return
  }

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
  if (!dateElement) {
    // This element might not exist in all pages, so we'll just return silently
    return
  }

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
  try {
    console.log("Rendering calendar...")
    const weekDates = getWeekDates(currentDate)
    const firstDayOfWeek = weekDates[0]
    const lastDayOfWeek = weekDates[6]

    // Format month and year for display
    const monthYear = firstDayOfWeek.toLocaleString("en-US", {
      month: "long",
      year: "numeric",
    })

    const weekRangeElement = document.getElementById("week-range")
    if (weekRangeElement) {
      weekRangeElement.textContent = monthYear
    } else {
      console.error("Week range element not found")
    }

    const calendar = document.getElementById("calendar")
    if (!calendar) {
      console.error("Calendar element not found")
      return
    }

    calendar.innerHTML = ""

    // Create the days of the week
    weekDates.forEach((date) => {
      const dayDiv = document.createElement("div")
      dayDiv.className = "day"
      dayDiv.dataset.date = formatDateToISO(date) // Add date as data attribute for easier reference

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
  } catch (error) {
    console.error("Error rendering calendar:", error)
    // Try to show an error message on the page
    const calendar = document.getElementById("calendar")
    if (calendar) {
      calendar.innerHTML =
        "<div class='error-message'>Error rendering calendar. Please check console for details.</div>"
    }
  }
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

  // Simple direct path - we're in the admin directory
  fetch("fetch_reminders.php", {
    method: "GET",
    headers: {
      "Cache-Control": "no-cache",
    },
    credentials: "same-origin",
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`)
      }
      return response.json()
    })
    .then((responseData) => {
      console.log("Reminders response received")

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

      // Add "View More" button for reminders if there are 4 or more
      if (reminders.length >= 4) {
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

      // Add "View More" button for tasks if there are 4 or more
      if (tasks.length >= 4) {
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
          '<div class="sidebar-title">REMINDERS</div><div class="sidebar-textbox error">Error loading reminders</div>'
        tasksContainer.innerHTML =
          '<div class="sidebar-title">TASKS</div><div class="sidebar-textbox error">Error loading tasks</div>'

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

// Change the createActivityItem function to use an eye icon instead of X
function createActivityItem(activity, itemClass) {
  const item = document.createElement("div")
  item.className = `sidebar-textbox ${itemClass}`

  // Add completed class if the activity is marked as completed
  if (activity.completed) {
    item.classList.add("completed-activity")
  }

  // Store the activity ID as a data attribute
  item.dataset.activityId = activity.activity_id

  const dateText = activity.formatted_date || activity.activity_date
  const timeText = activity.formatted_time || activity.activity_time

  // Check if the activity is for today
  const today = new Date().toISOString().split("T")[0]
  const isToday = activity.activity_date === today

  // Use span with class for styling
  const dateDisplay = isToday ? '<span class="today-text">Today</span>' : dateText

  // Create the activity content with checkbox and hide button (eye icon)
  item.innerHTML = `
    <div class="activity-content">
      <input type="checkbox" class="activity-checkbox" ${activity.completed ? "checked" : ""}>
      <div class="activity-text">
        <div class="sidebar-subtitle">${activity.activity_description || "Untitled"}</div>
        <div class="sidebar-desc">at ${timeText} on ${dateDisplay}</div>
      </div>
      <span class="activity-delete" title="Hide"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg></span>
    </div>
    <div class="sidebar-line"></div>
  `

  // Add event listener for checkbox
  const checkbox = item.querySelector(".activity-checkbox")
  if (checkbox) {
    checkbox.addEventListener("change", function () {
      const completed = this.checked
      updateActivityStatus(activity.activity_id, completed)

      // Update UI immediately
      if (completed) {
        item.classList.add("completed-activity")
      } else {
        item.classList.remove("completed-activity")
      }
    })
  }

  // Add event listener for hide button
  const hideBtn = item.querySelector(".activity-delete")
  if (hideBtn) {
    hideBtn.addEventListener("click", (e) => {
      e.stopPropagation()
      console.log("Hide button clicked for activity ID:", activity.activity_id)
      openHideConfirmation(activity.activity_id)
    })
  }

  return item
}

// Function to update activity status (completed/not completed)
function updateActivityStatus(activityId, completed) {
  console.log(`Updating activity ${activityId} to ${completed ? "completed" : "not completed"}`)

  const formData = new FormData()
  formData.append("activity_id", activityId)
  formData.append("action", "update")
  formData.append("completed", completed ? "true" : "false")

  fetch("update_activity.php", {
    method: "POST",
    body: formData,
    credentials: "same-origin",
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`)
      }
      return response.json()
    })
    .then((data) => {
      console.log("Update response:", data)
      if (!data.success) {
        console.error("Error updating activity:", data.error)
        alert("Failed to update activity status. Please try again.")
      }
    })
    .catch((error) => {
      console.error("Error:", error)
      alert("An error occurred while updating the activity: " + error.message)
    })
}

// Rename the function from deleteActivity to hideActivity
function hideActivity(activityId) {
  console.log("Hiding activity ID:", activityId)

  const formData = new FormData()
  formData.append("activity_id", activityId)
  formData.append("action", "hide")

  fetch("update_activity.php", {
    method: "POST",
    body: formData,
    credentials: "same-origin",
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`)
      }
      return response.json()
    })
    .then((data) => {
      console.log("Hide response:", data)
      // Always consider it a success and refresh the sidebar
      fetchReminders()
      closeHideConfirmation()

      // Show a success message
      const successMessage = document.createElement("div")
      successMessage.className = "success-message"
      successMessage.textContent = "Item hidden successfully"
      successMessage.style.position = "fixed"
      successMessage.style.bottom = "20px"
      successMessage.style.right = "20px"
      successMessage.style.backgroundColor = "#4CAF50"
      successMessage.style.color = "white"
      successMessage.style.padding = "10px 20px"
      successMessage.style.borderRadius = "4px"
      successMessage.style.zIndex = "1000"
      successMessage.style.boxShadow = "0 2px 5px rgba(0,0,0,0.2)"

      document.body.appendChild(successMessage)

      // Remove the success message after 3 seconds
      setTimeout(() => {
        successMessage.style.opacity = "0"
        successMessage.style.transition = "opacity 0.5s"
        setTimeout(() => {
          document.body.removeChild(successMessage)
        }, 500)
      }, 3000)
    })
    .catch((error) => {
      console.error("Error:", error)
      // Don't show an alert, just log the error and close the modal
      console.log("Error hiding activity, but we'll proceed anyway:", error.message)
      closeHideConfirmation()
      fetchReminders() // Still refresh to ensure UI is in sync with database
    })
}

// Delete confirmation modal functions
let currentActivityId = null

// Rename the function from openDeleteConfirmation to openHideConfirmation
function openHideConfirmation(activityId) {
  console.log("Opening hide confirmation for activity ID:", activityId)
  currentActivityId = activityId
  const modal = document.getElementById("deleteModal")
  if (modal) {
    modal.classList.add("open")

    // Update the message to include the activity description
    const activityItems = document.querySelectorAll(`[data-activity-id="${activityId}"]`)
    if (activityItems.length > 0) {
      const activityItem = activityItems[0]
      const description = activityItem.querySelector(".sidebar-subtitle")?.textContent || "this item"

      const titleElement = modal.querySelector(".delete-modal-title")
      if (titleElement) {
        titleElement.textContent = "Confirm Hide"
      }

      const messageElement = modal.querySelector(".delete-modal-message")
      if (messageElement) {
        messageElement.textContent = `Are you sure you want to hide "${description}"?`
      }
    }
  } else {
    console.error("Hide modal element not found")
  }
}

// Rename the function from closeDeleteConfirmation to closeHideConfirmation
function closeHideConfirmation() {
  const modal = document.getElementById("deleteModal")
  if (modal) {
    modal.classList.remove("open")
    currentActivityId = null
  }
}

// Update the attachAddButtonListeners function to use the new hide functionality
function attachAddButtonListeners() {
  // Use event delegation instead of direct binding
  document.querySelectorAll(".add-sidebar").forEach((button) => {
    button.addEventListener("click", function () {
      const type = this.textContent.includes("Reminder") ? "reminder" : "task"
      openAddActivityForm(type)
    })
  })

  // Add event listeners for hide confirmation buttons
  const confirmDeleteBtn = document.getElementById("confirmDeleteBtn")
  if (confirmDeleteBtn) {
    confirmDeleteBtn.addEventListener("click", () => {
      if (currentActivityId) {
        hideActivity(currentActivityId)
      }
    })
  } else {
    console.error("Confirm hide button not found")
  }

  const cancelDeleteBtn = document.getElementById("cancelDeleteBtn")
  if (cancelDeleteBtn) {
    cancelDeleteBtn.addEventListener("click", closeHideConfirmation)
  } else {
    console.error("Cancel hide button not found")
  }
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
  if (!modal) {
    console.error("Modal element not found")
    return
  }

  // Set default date to today
  const today = new Date()
  const formattedDate = today.toISOString().split("T")[0]

  const dateInput = document.getElementById("activity_date")
  if (dateInput) {
    dateInput.value = formattedDate
  }

  // Set default time to current time
  const hours = String(today.getHours()).padStart(2, "0")
  const minutes = String(today.getMinutes()).padStart(2, "0")

  const timeInput = document.getElementById("activity_time")
  if (timeInput) {
    timeInput.value = `${hours}:${minutes}`
  }

  const typeInput = document.getElementById("activity_type")
  if (typeInput) {
    typeInput.value = type.charAt(0).toUpperCase() + type.slice(1)
  }

  const titleSpan = document.getElementById("activity-type-title")
  if (titleSpan) {
    titleSpan.textContent = type.charAt(0).toUpperCase() + type.slice(1)
  }

  // Clear description field
  const descInput = document.getElementById("activity_description")
  if (descInput) {
    descInput.value = ""
  }

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
  const description = document.getElementById("activity_description")?.value.trim() || ""
  const date = document.getElementById("activity_date")?.value || ""
  const time = document.getElementById("activity_time")?.value || ""
  const type = document.getElementById("activity_type")?.value || ""

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

  // Simple direct path - we're in the admin directory
  fetch("add_activity.php", {
    method: "POST",
    body: formData,
    credentials: "same-origin",
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

// Function to fetch bookings for the current week - IMPROVED VERSION
function fetchBookingsForWeek() {
  try {
    console.log("Starting fetchBookingsForWeek...")
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

    // Simple direct path - we're in the admin directory
    const fetchUrl = `fetch_bookings.php?start_date=${startDate}&end_date=${endDate}`
    console.log("Fetching bookings from:", fetchUrl)

    fetch(fetchUrl, {
      method: "GET",
      headers: {
        "Cache-Control": "no-cache",
      },
      credentials: "same-origin", // Important for session cookies
    })
      .then((response) => {
        console.log("Bookings response status:", response.status)
        if (!response.ok) {
          return response.text().then((text) => {
            console.error("Response text:", text)
            throw new Error(`HTTP error! Status: ${response.status}`)
          })
        }
        return response.json()
      })
      .then((responseData) => {
        console.log("Bookings response received:", responseData)

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
  } catch (error) {
    console.error("Exception in fetchBookingsForWeek:", error)
  }
}

// Function to display bookings in the calendar - IMPROVED VERSION
function displayBookingsInCalendar(bookingsData, weekDates) {
  try {
    console.log("Displaying bookings:", bookingsData)

    // Clear any existing booking elements
    document.querySelectorAll(".bookings-container").forEach((el) => el.remove())

    // Check if bookingsData is empty
    if (!bookingsData || Object.keys(bookingsData).length === 0) {
      console.log("No bookings found for the selected week")
      return
    }

    // Loop through each day in the week
    weekDates.forEach((date, index) => {
      const dateStr = formatDateToISO(date)
      const dayElement = document.querySelectorAll(".day")[index]

      if (!dayElement) {
        console.error(`Day element not found for index ${index}`)
        return
      }

      // Check if there are bookings for this date
      const bookingsForDay = bookingsData[dateStr] || []

      console.log(`Bookings for ${dateStr}: ${bookingsForDay.length}`)

      if (bookingsForDay.length > 0) {
        // Create a container for bookings
        const bookingsContainer = document.createElement("div")
        bookingsContainer.className = "bookings-container"

        // Add each booking
        bookingsForDay.forEach((booking) => {
          try {
            const bookingElement = createBookingElement(booking)
            bookingsContainer.appendChild(bookingElement)
          } catch (error) {
            console.error("Error creating booking element:", error)
          }
        })

        dayElement.appendChild(bookingsContainer)
      }
    })
  } catch (error) {
    console.error("Exception in displayBookingsInCalendar:", error)
  }
}

// Function to create a booking element - IMPROVED VERSION
function createBookingElement(booking) {
  try {
    if (!booking) {
      console.error("Invalid booking data")
      return document.createElement("div") // Return empty div to prevent errors
    }

    console.log("Creating booking element for:", booking)

    // Get service type for styling and display
    const serviceType = booking.service_name ? booking.service_name.toLowerCase() : "unknown"
    const serviceVariant = booking.service_variant ? booking.service_variant : ""

    // Create the booking element with service-specific class
    const bookingElement = document.createElement("div")

    // Build class name safely
    let className = "booking-item"

    if (booking.booking_status) {
      className += ` booking-status-${booking.booking_status.toLowerCase()}`
    }

    if (serviceType && serviceType !== "unknown") {
      className += ` booking-service-${serviceType.replace(/\s+/g, "-")}`
    }

    bookingElement.className = className

    if (booking.booking_id) {
      bookingElement.dataset.bookingId = booking.booking_id
    }

    if (serviceType) {
      bookingElement.dataset.serviceType = serviceType
    }

    // Create the content with simplified service information
    bookingElement.innerHTML = `
      <div class="booking-header">
        <div class="booking-pet-name">${booking.pet_name || "Unknown Pet"}</div>
        <div class="service-badge" title="${booking.service_name || ""}${booking.service_variant ? " - " + booking.service_variant : ""}">
          ${getServiceShortName(serviceType)}
        </div>
      </div>
      <div class="booking-times">
        <span class="booking-check-in">${booking.formatted_check_in_time || "N/A"}</span> - 
        <span class="booking-check-out">${booking.formatted_check_out_time || "N/A"}</span>
      </div>
      <div class="booking-status">${booking.booking_status || "Unknown"}</div>
    `

    // Add click event to show booking details
    bookingElement.addEventListener("click", () => {
      alert(
        `Booking Details:\nPet: ${booking.pet_name}\nService: ${booking.service_name} (${booking.service_variant})\nStatus: ${booking.booking_status}`,
      )
    })

    return bookingElement
  } catch (error) {
    console.error("Exception in createBookingElement:", error)
    return document.createElement("div") // Return empty div to prevent errors
  }
}

// Helper function to get shortened service name for display
function getServiceShortName(serviceType) {
  if (!serviceType) return "Service"

  switch (serviceType.toLowerCase()) {
    case "pet hotel":
      return "Hotel"
    case "pet daycare":
      return "Daycare"
    default:
      return serviceType.charAt(0).toUpperCase() + serviceType.slice(1)
  }
}

// Update the DOMContentLoaded event listener to use the new hide functionality
document.addEventListener("DOMContentLoaded", () => {
  try {
    console.log("DOM fully loaded - Starting initialization")

    // Initialize clock
    updateClock()
    setInterval(updateClock, 1000)
    console.log("Clock initialized")

    // Initialize date display
    updateDateDisplay()
    console.log("Date display initialized")

    // Fetch reminders and tasks
    fetchReminders()
    console.log("Reminders fetch initiated")

    // Initialize calendar with bookings
    renderCalendar()
    console.log("Calendar rendering initiated")

    // Add event listener for submit button
    const submitBtn = document.getElementById("submitActivityBtn")
    if (submitBtn) {
      submitBtn.addEventListener("click", submitActivity)
      console.log("Submit button listener attached")
    } else {
      console.error("Submit button not found")
    }

    // Add event listener for close button
    const closeBtn = document.querySelector(".close-btn")
    if (closeBtn) {
      closeBtn.addEventListener("click", closeSidebarModal)
      console.log("Close button listener attached")
    } else {
      console.error("Close button not found")
    }

    // Add event listeners for hide confirmation buttons
    const confirmDeleteBtn = document.getElementById("confirmDeleteBtn")
    if (confirmDeleteBtn) {
      confirmDeleteBtn.addEventListener("click", () => {
        if (currentActivityId) {
          hideActivity(currentActivityId)
        }
      })
      console.log("Confirm hide button listener attached")
    }

    const cancelDeleteBtn = document.getElementById("cancelDeleteBtn")
    if (cancelDeleteBtn) {
      cancelDeleteBtn.addEventListener("click", closeHideConfirmation)
      console.log("Cancel hide button listener attached")
    }

    console.log("Initialization complete")
  } catch (error) {
    console.error("Error during initialization:", error)

    // Try to show an error message on the page
    const clockElement = document.getElementById("real-time-clock")
    if (clockElement) {
      clockElement.textContent = "Error loading page"
      clockElement.style.color = "red"
    }
  }
})

// Add this line at the end to check if the script loaded
console.log("admin.js loaded successfully at " + new Date().toLocaleTimeString())
