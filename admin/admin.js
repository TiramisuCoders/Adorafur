// ✅ Real-Time Clock Function
function updateClock() {
    const clockElement = document.getElementById("real-time-clock");
    if (!clockElement) return;

    const now = new Date();
    let hours = now.getHours();
    let minutes = now.getMinutes();
    const amPm = hours >= 12 ? "PM" : "AM";

    hours = hours % 12 || 12;
    minutes = minutes.toString().padStart(2, "0");

    clockElement.textContent = `${hours}:${minutes} ${amPm}`;
}

// ✅ Update the date display
function updateDateDisplay() {
    const dateElement = document.querySelector(".date-and-day");
    if (!dateElement) return;

    const now = new Date();
    dateElement.textContent = now.toLocaleDateString("en-US", {
        weekday: "long",
        month: "long",
        day: "numeric",
        year: "numeric",
    });
}

// ✅ Calendar Logic
let currentDate = new Date();

function getWeekDates(date) {
    const startOfWeek = new Date(date);
    startOfWeek.setDate(startOfWeek.getDate() - startOfWeek.getDay() + 1); // Start on Monday

    return Array.from({ length: 7 }, (_, i) => {
        const d = new Date(startOfWeek);
        d.setDate(d.getDate() + i);
        return d;
    });
}

// ✅ Renders the calendar
function renderCalendar() {
    const calendar = document.getElementById("calendar");
    if (!calendar) return;

    const weekDates = getWeekDates(currentDate);
    document.getElementById("week-range").textContent = weekDates[0].toLocaleString("en-US", {
        month: "long",
        year: "numeric",
    });

    calendar.innerHTML = "";
    weekDates.forEach((date) => {
        const dayDiv = document.createElement("div");
        dayDiv.className = "day";

        dayDiv.innerHTML = `
            <div class="day-name">${date.toLocaleDateString("en-US", { weekday: "long" }).toUpperCase()}</div>
            <div class="day-number">${String(date.getDate()).padStart(2, "0")}</div>
        `;

        calendar.appendChild(dayDiv);
    });
}

// ✅ Changes the displayed week
function changeWeek(offset) {
    currentDate.setTime(currentDate.getTime() + offset * 7 * 24 * 60 * 60 * 1000); // Adjust by week
    renderCalendar();
}

// ✅ Fetch & Render Reminders and Tasks
function fetchReminders() {
    fetch("fetch_reminders.php")
        .then((response) => response.json())
        .then((data) => {
            const remindersContainer = document.querySelector(".reminders-section");
            const tasksContainer = document.querySelector(".tasks-section");

            if (!remindersContainer || !tasksContainer) return;

            remindersContainer.innerHTML = '<div class="sidebar-title">REMINDERS</div>';
            tasksContainer.innerHTML = '<div class="sidebar-title">TASKS</div>';

            data.forEach((activity) => {
                const item = document.createElement("div");
                item.className = "sidebar-textbox";

                item.innerHTML = `
                    <div class="sidebar-subtitle">${activity.activity_description}</div>
                    <div class="sidebar-desc">${activity.activity_date} at ${activity.activity_time}</div>
                    <div class="sidebar-line"></div>
                `;

                if (activity.activity_type.toLowerCase() === "reminder") {
                    item.classList.add("reminder-item");
                    remindersContainer.appendChild(item);
                } else if (activity.activity_type.toLowerCase() === "task") {
                    item.classList.add("task-item");
                    tasksContainer.appendChild(item);
                }
            });

            // Add buttons for adding reminders/tasks
            remindersContainer.innerHTML += `
                <div class="sidebar-textbox add-sidebar">
                    <div class="add-sidebar">+ Add Reminder</div>
                    <div class="sidebar-line"></div>
                </div>
                <div class="view-rem" id="viewRemindersBtn">View More</div>
            `;

            tasksContainer.innerHTML += `
                <div class="sidebar-textbox add-sidebar">
                    <div class="add-sidebar">+ Add Task</div>
                    <div class="sidebar-line"></div>
                </div>
                <div class="view-task" id="viewTasksBtn">View More</div>
            `;

            limitVisibleItems(".reminders-section .reminder-item", 2, "#viewRemindersBtn");
            limitVisibleItems(".tasks-section .task-item", 2, "#viewTasksBtn");
        })
        .catch((error) => console.error("Error loading reminders/tasks:", error));
}

// ✅ View More / View Less Logic
function limitVisibleItems(selector, limit, toggleButtonSelector) {
    const items = document.querySelectorAll(selector);
    const toggleButton = document.querySelector(toggleButtonSelector);

    if (!items.length || !toggleButton) return;

    items.forEach((item, index) => (item.style.display = index < limit ? "block" : "none"));

    toggleButton.onclick = () => {
        const isExpanded = toggleButton.dataset.expanded === "true";
        items.forEach((item, index) => {
            item.style.display = isExpanded ? (index < limit ? "block" : "none") : "block";
        });

        toggleButton.textContent = isExpanded ? "View More" : "View Less";
        toggleButton.dataset.expanded = isExpanded ? "false" : "true";
    };
}

// ✅ Open and Close Modal
function openAddActivityForm(type) {
    const modal = document.getElementById("activityModal");
    if (!modal) return;

    document.getElementById("activity_type").value = type;
    document.getElementById("activity-type-title").textContent = type.charAt(0).toUpperCase() + type.slice(1);
    modal.classList.add("open");
}

function closeSidebarModal() {
    document.getElementById("activityModal")?.classList.remove("open");
}

// ✅ Submit Activity
function submitActivity() {
    const description = document.getElementById("activity_description").value;
    const date = document.getElementById("activity_date").value;
    const time = document.getElementById("activity_time").value;
    const type = document.getElementById("activity_type").value;

    if (!description || !date || !time || !type) {
        alert("All fields are required!");
        return;
    }

    const formData = new FormData();
    formData.append("description", description);
    formData.append("date", date);
    formData.append("time", time);
    formData.append("type", type);

    fetch("add_activity.php", {
        method: "POST",
        body: formData,
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                alert("Activity added successfully!");
                closeSidebarModal();
                fetchReminders();
            } else {
                alert("Error adding activity: " + (data.error || "Unknown error"));
            }
        })
        .catch((error) => {
            console.error("Error:", error);
            alert("An error occurred while adding the activity.");
        });
}

// ✅ Event Listeners
document.addEventListener("DOMContentLoaded", () => {
    updateClock();
    setInterval(updateClock, 1000);
    updateDateDisplay();
    fetchReminders();
    renderCalendar();

    document.getElementById("submitActivityBtn")?.addEventListener("click", submitActivity);
});
