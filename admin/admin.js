function updateClock() {
  const clockElement = document.getElementById("real-time-clock");
  if (!clockElement) return; // Prevents errors if the element is missing

  const now = new Date();
  let hours = now.getHours();
  let minutes = now.getMinutes();
  let amPm = hours >= 12 ? 'PM' : 'AM';

  hours = hours % 12 || 12; 
  minutes = minutes < 10 ? '0' + minutes : minutes;

  clockElement.textContent = `${hours}:${minutes} ${amPm}`;
}

document.addEventListener("DOMContentLoaded", () => {
  updateClock(); 
  setInterval(updateClock, 1000);
});

// Calendar Logic
let currentDate = new Date();

const events = [
    { date: "2025-03-04"},
    { date: "2025-03-06"},
    { date: "2025-03-07"}
];

function getWeekDates(date) {
    const startOfWeek = new Date(date);
    const dayOfWeek = startOfWeek.getDay(); // 0 = Sunday, 1 = Monday, etc.
    startOfWeek.setDate(startOfWeek.getDate() - dayOfWeek + (dayOfWeek === 0 ? -6 : 1)); // Adjust to Monday

    return Array.from({ length: 7 }, (_, i) => {
        const d = new Date(startOfWeek);
        d.setDate(d.getDate() + i);
        return d;
    });
}

function formatDate(date) {
    return date.toISOString().split("T")[0]; // Converts to "YYYY-MM-DD"
}

function renderCalendar() {
    const weekDates = getWeekDates(currentDate);
    document.getElementById("week-range").textContent =
        `${weekDates[0].toDateString()} - ${weekDates[6].toDateString()}`;

    const calendar = document.getElementById("calendar");
    calendar.innerHTML = "";

    weekDates.forEach(date => {
        const dayDiv = document.createElement("div");
        dayDiv.className = "day";
        dayDiv.innerHTML = `<strong>${date.toDateString()}</strong>`;

        const formattedDate = formatDate(date);

        events.forEach(event => {
            if (event.date === formattedDate) {
                const eventDiv = document.createElement("div");
                eventDiv.className = "event";
                eventDiv.textContent = "Event"; // Placeholder title
                dayDiv.appendChild(eventDiv);
            }
        });

        calendar.appendChild(dayDiv);
    });
}

function changeWeek(offset) {
    currentDate.setDate(currentDate.getDate() + offset * 7);
    renderCalendar();
}

document.addEventListener("DOMContentLoaded", () => {
    renderCalendar();
});
