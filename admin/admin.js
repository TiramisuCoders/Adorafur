// Real-Time Clock Function
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
  
  // Update the date display
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
  
  // Calendar Logic
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
  
  // Renders the calendar
  function renderCalendar() {
    const weekDates = getWeekDates(currentDate)
    const firstDayOfWeek = weekDates[0]
  
    document.getElementById("week-range").textContent = firstDayOfWeek.toLocaleString("en-US", {
      month: "long",
      year: "numeric",
    })
  
    const calendar = document.getElementById("calendar")
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
  }
  
  // Changes the displayed week when clicking buttons
  function changeWeek(offset) {
    currentDate.setDate(currentDate.getDate() + offset * 7)
    renderCalendar()
  }
  
  // Fetch & Render Reminders and Tasks
  function fetchReminders() {
    console.log("Fetching reminders...")
  
    fetch("fetch_reminders.php")
      .then((response) => {
        console.log("Response status:", response.status)
        return response.text()
      })
      .then((text) => {
        console.log("Raw response:", text)
        try {
          return JSON.parse(text)
        } catch (e) {
          console.error("JSON parse error:", e)
          throw new Error("Invalid JSON response: " + text)
        }
      })
      .then((data) => {
        console.log("Parsed data:", data)
  
        if (data.error) {
          console.error("Error from server:", data.error)
          return
        }
  
        const remindersContainer = document.querySelector(".reminders-section")
        const tasksContainer = document.querySelector(".tasks-section")
  
        if (!remindersContainer || !tasksContainer) return
  
        // Clear existing content but keep the title
        remindersContainer.innerHTML = '<div class="sidebar-title">REMINDERS</div>'
        tasksContainer.innerHTML = '<div class="sidebar-title">TASKS</div>'
  
        // Add each activity to the appropriate container
        data.forEach((activity) => {
          const item = document.createElement("div")
          item.className = "sidebar-textbox"
  
          item.innerHTML = `
                      <div class="sidebar-subtitle">${activity.activity_description}</div>
                      <div class="sidebar-desc">${activity.formatted_date} at ${activity.activity_time}</div>
                      <div class="sidebar-line"></div>
                  `
  
          if (activity.activity_type.toLowerCase() === "reminder") {
            item.classList.add("reminder-item")
            remindersContainer.appendChild(item)
          } else if (activity.activity_type.toLowerCase() === "task") {
            item.classList.add("task-item")
            tasksContainer.appendChild(item)
          }
        })
  
        // Add the "Add Reminder" button
        const addReminderBtn = document.createElement("div")
        addReminderBtn.className = "sidebar-textbox"
        addReminderBtn.innerHTML = `
                  <div class="add-sidebar">Add Reminder</div>
                  <div class="sidebar-line"></div>
              `
        addReminderBtn.querySelector(".add-sidebar").addEventListener("click", () => {
          openAddActivityForm("reminder")
        })
        remindersContainer.appendChild(addReminderBtn)
  
        // Add View More button for reminders
        const viewRemindersBtn = document.createElement("div")
        viewRemindersBtn.className = "view-rem"
        viewRemindersBtn.id = "viewRemindersBtn"
        viewRemindersBtn.textContent = "View Reminders"
        viewRemindersBtn.dataset.expanded = "false"
        remindersContainer.appendChild(viewRemindersBtn)
  
        // Add the "Add Task" button
        const addTaskBtn = document.createElement("div")
        addTaskBtn.className = "sidebar-textbox"
        addTaskBtn.innerHTML = `
                  <div class="add-sidebar">Add Task</div>
                  <div class="sidebar-line"></div>
              `
        addTaskBtn.querySelector(".add-sidebar").addEventListener("click", () => {
          openAddActivityForm("task")
        })
        tasksContainer.appendChild(addTaskBtn)
  
        // Add View More button for tasks
        const viewTasksBtn = document.createElement("div")
        viewTasksBtn.className = "view-task"
        viewTasksBtn.id = "viewTasksBtn"
        viewTasksBtn.textContent = "View Tasks"
        viewTasksBtn.dataset.expanded = "false"
        tasksContainer.appendChild(viewTasksBtn)
  
        // Set up the view more/less functionality
        limitVisibleItems(".reminders-section .reminder-item", 2, "#viewRemindersBtn")
        limitVisibleItems(".tasks-section .task-item", 2, "#viewTasksBtn")
      })
      .catch((error) => {
        console.error("Error loading reminders/tasks:", error)
      })
  }
  
  // View More / View Less Logic
  function limitVisibleItems(selector, limit, toggleButtonSelector) {
    const items = document.querySelectorAll(selector)
    const toggleButton = document.querySelector(toggleButtonSelector)
  
    if (!items.length || !toggleButton) return
  
    // Initially hide items beyond the limit
    items.forEach((item, index) => {
      item.style.display = index < limit ? "block" : "none"
    })
  
    // Set up toggle button click handler
    toggleButton.addEventListener("click", () => {
      const isExpanded = toggleButton.dataset.expanded === "true"
  
      items.forEach((item, index) => {
        item.style.display = isExpanded ? (index < limit ? "block" : "none") : "block"
      })
  
      toggleButton.textContent = isExpanded ? "View More" : "View Less"
      toggleButton.dataset.expanded = isExpanded ? "false" : "true"
    })
  }
  
  // Open and Close Modal
  function openAddActivityForm(type) {
    const modal = document.getElementById("activityModal")
    if (!modal) return
  
    document.getElementById("activity_type").value = type
    document.getElementById("activity-type-title").textContent = type.charAt(0).toUpperCase() + type.slice(1)
  
    // Set default date to today
    const today = new Date().toISOString().split("T")[0]
    document.getElementById("activity_date").value = today
  
    // Clear other fields
    document.getElementById("activity_description").value = ""
    document.getElementById("activity_time").value = ""
  
    modal.classList.add("open")
  }
  
  function closeSidebarModal() {
    const modal = document.getElementById("activityModal")
    if (modal) {
      modal.classList.remove("open")
    }
  }
  
  // Submit Activity
  function submitActivity() {
    const description = document.getElementById("activity_description").value
    const date = document.getElementById("activity_date").value
    const time = document.getElementById("activity_time").value
    const type = document.getElementById("activity_type").value
  
    if (!description || !date || !time || !type) {
      alert("All fields are required!")
      return
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
        return response.text() // Always get text first
      })
      .then((text) => {
        console.log("Raw response:", text)
        try {
          return JSON.parse(text) // Try to parse as JSON
        } catch (e) {
          console.error("JSON parse error:", e)
          throw new Error("Invalid JSON response: " + text)
        }
      })
      .then((data) => {
        console.log("Parsed data:", data)
        if (data.success) {
          alert("Activity added successfully!")
          closeSidebarModal()
          fetchReminders() // Refresh the reminders list
        } else {
          alert("Error adding activity: " + (data.error || "Unknown error"))
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        alert("An error occurred while adding the activity. Error: " + error.message)
      })
  }
  
  // Event Listeners
  document.addEventListener("DOMContentLoaded", () => {
    updateClock()
    setInterval(updateClock, 1000)
    updateDateDisplay()
    renderCalendar()
    fetchReminders()
  
    // Submit button event listener
    const submitBtn = document.getElementById("submitActivityBtn")
    if (submitBtn) {
      submitBtn.addEventListener("click", submitActivity)
    }
  
    // Add event listeners for "Add Reminder" and "Add Task" buttons
    document.addEventListener("click", (event) => {
      if (event.target.classList.contains("add-sidebar")) {
        const type = event.target.textContent.includes("Reminder") ? "reminder" : "task"
        openAddActivityForm(type)
      }
    })
  
    // Set up notification modal
    const modal = document.getElementById("notificationModal")
    const btn = document.getElementById("notificationButton")
    const span = document.getElementsByClassName("close")[0]
  
    if (btn && modal && span) {
      btn.onclick = () => {
        modal.style.display = "block"
      }
  
      span.onclick = () => {
        modal.style.display = "none"
      }
  
      window.onclick = (event) => {
        if (event.target == modal) {
          modal.style.display = "none"
        }
      }
    }
  })
  
  