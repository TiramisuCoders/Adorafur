// Add jQuery import
const $ = jQuery
// Declare variables
const currentDate = new Date()
let startDate = null
let endDate = null

// Add months array for calendar display
const months = [
  "JANUARY",
  "FEBRUARY",
  "MARCH",
  "APRIL",
  "MAY",
  "JUNE",
  "JULY",
  "AUGUST",
  "SEPTEMBER",
  "OCTOBER",
  "NOVEMBER",
  "DECEMBER",
]

// Store selected dates globally
window.selectedDates = {
  checkIn: null,
  checkOut: null,
}

// Function to check if user is logged in
function isUserLoggedIn() {
  // Check if the PHP session has customer_id set
  // This is a client-side approximation - the actual check happens server-side
  return (
    document.cookie.includes("PHPSESSID=") && typeof window.isLoggedIn !== "undefined" && window.isLoggedIn === true
  )
}

// Function to validate pet selection
function hasPetSelected() {
  return window.bookingData && window.bookingData.pets && window.bookingData.pets.length > 0
}

// Function to validate date selection
function hasDateSelected() {
  return window.bookingData && window.bookingData.checkInDate && window.bookingData.checkOutDate
}

// Declare functions
function resetHighlights() {
  console.log("resetHighlights function called")
  const days = document.querySelectorAll(".day")
  days.forEach((day) => {
    day.classList.remove("selected-date", "range-date")
  })
}

function highlightDateRange() {
  if (!window.selectedDates.checkIn || !window.selectedDates.checkOut) return

  document.querySelectorAll(".day").forEach((dayElement) => {
    const dateStr = dayElement.getAttribute("data-date")
    if (!dateStr) return

    const date = new Date(dateStr)
    if (date > window.selectedDates.checkIn && date < window.selectedDates.checkOut) {
      dayElement.classList.add("highlighted")
    }
  })
}

// Function to generate and render the calendar
function renderCalendar() {
  const year = currentDate.getFullYear()
  const month = currentDate.getMonth()

  // Update month and year display
  document.getElementById("month").textContent = `${months[month]} ${year}`

  // Get first day of month and total days
  const firstDay = new Date(year, month, 1).getDay()
  const daysInMonth = new Date(year, month + 1, 0).getDate()

  // Clear previous calendar days
  const daysContainer = document.getElementById("days")
  daysContainer.innerHTML = ""

  // Add empty cells for days before the first day of month
  for (let i = 0; i < firstDay; i++) {
    const emptyDay = document.createElement("div")
    emptyDay.classList.add("day", "empty")
    daysContainer.appendChild(emptyDay)
  }

  // Create current date for comparison
  const today = new Date()
  today.setHours(0, 0, 0, 0)

  // Add calendar days
  for (let day = 1; day <= daysInMonth; day++) {
    const dayElement = document.createElement("div")
    dayElement.classList.add("day")
    dayElement.textContent = day

    // Create date object for this day
    const thisDate = new Date(year, month, day)
    // Add data-date attribute in YYYY-MM-DD format
    const formattedDate = thisDate.toISOString().split("T")[0]
    dayElement.setAttribute("data-date", formattedDate)

    // Check if this date is selected
    if (window.selectedDates.checkIn && formattedDate === window.selectedDates.checkIn.toISOString().split("T")[0]) {
      dayElement.classList.add("selected-date")
    }
    if (window.selectedDates.checkOut && formattedDate === window.selectedDates.checkOut.toISOString().split("T")[0]) {
      dayElement.classList.add("selected-date")
    }

    // Add click handler
    dayElement.addEventListener("click", () => handleDateClick(thisDate, dayElement))

    daysContainer.appendChild(dayElement)
  }
}

// Function to handle date clicks
function handleDateClick(date, element) {
  if (!window.selectedDates.checkIn || (window.selectedDates.checkIn && window.selectedDates.checkOut)) {
    // Start new selection
    clearDateSelection()
    window.selectedDates.checkIn = date
    element.classList.add("selected-date")

    // Update booking data
    window.bookingData.checkInDate = date.toLocaleDateString("en-US", {
      month: "long",
      day: "numeric",
    })
  } else {
    // Complete selection
    if (date > window.selectedDates.checkIn) {
      window.selectedDates.checkOut = date
      element.classList.add("selected-date")

      // Update booking data
      window.bookingData.checkOutDate = date.toLocaleDateString("en-US", {
        month: "long",
        day: "numeric",
      })

      // Highlight dates in between
      highlightDateRange()
    }
  }

  // Update summary
  updateBookingSummary()
}

// Function to clear date selection
function clearDateSelection() {
  window.selectedDates.checkIn = null
  window.selectedDates.checkOut = null
  document.querySelectorAll(".day").forEach((day) => {
    day.classList.remove("selected-date", "highlighted")
  })
}

// Update the handleDateSelection function
function handleDateSelection(day, dayElement) {
  const selectedDate = new Date(currentDate.getFullYear(), currentDate.getMonth(), day)
  if (selectedDate < new Date().setHours(0, 0, 0, 0)) return // Prevent past selection

  if (!startDate || (startDate && endDate)) {
    // Reset selection
    startDate = day
    endDate = null
    resetHighlights()
    dayElement.classList.add("selected-date")

    // Update the booking data with check-in date
    const formattedDate = selectedDate.toLocaleDateString("en-US", {
      month: "long",
      day: "numeric",
    })
    window.bookingData.checkInDate = formattedDate
    window.bookingData.checkOutDate = formattedDate // Set same date for single-day booking

    updateBookingSummary()
  } else if (day > startDate) {
    // Set endDate and highlight range
    endDate = day
    highlightDateRange()

    // Update the booking data with check-out date
    const checkOutDate = new Date(currentDate.getFullYear(), currentDate.getMonth(), day)
    const formattedDate = checkOutDate.toLocaleDateString("en-US", {
      month: "long",
      day: "numeric",
    })
    window.bookingData.checkOutDate = formattedDate

    updateBookingSummary()
  }
}

// Update the updateBookingSummary function
function updateBookingSummary() {
  // Update dates in summary
  if (window.selectedDates.checkIn) {
    const checkInStr = window.selectedDates.checkIn.toLocaleDateString("en-US", {
      month: "long",
      day: "numeric",
    })
    $("#summaryCheckIn").text(
      checkInStr + (window.bookingData.checkInTime ? `, ${window.bookingData.checkInTime}` : ""),
    )
  }

  if (window.selectedDates.checkOut) {
    const checkOutStr = window.selectedDates.checkOut.toLocaleDateString("en-US", {
      month: "long",
      day: "numeric",
    })
    $("#summaryCheckOut").text(
      checkOutStr + (window.bookingData.checkOutTime ? `, ${window.bookingData.checkOutTime}` : ""),
    )
  }

  // Update pet details
  if (window.bookingData && window.bookingData.pets.length > 0) {
    let petDetailsHtml = ""
    window.bookingData.pets.forEach((pet, index) => {
      petDetailsHtml += `
                <div class="pet-summary-item">
                    <h4>${pet.name}</h4>
                    <div class="info-row"><span class="label">Breed:</span><span class="value">${pet.breed}</span></div>
                    <div class="info-row"><span class="label">Gender:</span><span class="value">${pet.gender}</span></div>
                    <div class="info-row"><span class="label">Age:</span><span class="value">${pet.age} years old</span></div>
                </div>
            `
    })

    $("#summaryPetName").text(`${window.bookingData.pets.length} Pet${window.bookingData.pets.length > 1 ? "s" : ""}`)
    $("#petSummaryDetails").html(petDetailsHtml)
  }

  // Calculate and update total price
  calculateTotalPrice()
}

// Update payment method handling
$(document).on("change", 'input[name="payment_method"]', function () {
  const selectedPayment = $(this).val()

  if (selectedPayment === "GCash") {
    $("#gcashQR").show()
    $("#mayaQR").hide()
  } else {
    $("#gcashQR").hide()
    $("#mayaQR").show()
  }
})

// Update form validation
function validatePaymentForm() {
  console.log("Validating payment form")
  const referenceNo = $('input[name="reference_no"]').val().trim()
  const paymentProof = $('input[name="payment_proof"]').prop("files").length

  console.log("Reference No:", referenceNo, "Payment Proof:", paymentProof)

  // Enable button only if both fields are filled
  if (referenceNo && paymentProof > 0) {
    console.log("Enabling proceed-to-waiver button")
    $("#proceed-to-waiver").prop("disabled", false)
  } else {
    console.log("Disabling proceed-to-waiver button")
    $("#proceed-to-waiver").prop("disabled", true)
  }
}

// Attach validation handlers with more specific selectors
$(document).on("input", 'input[name="reference_no"]', validatePaymentForm)
$(document).on("change", 'input[name="payment_proof"]', validatePaymentForm)

// Initialize payment modal
$("#petPaymentModal").on("show.bs.modal", () => {
  // Reset form
  $("#paymentForm")[0].reset()
  $("#proceed-to-waiver").prop("disabled", true)

  // Show default QR code (Maya)
  $("#gcashQR").hide()
  $("#mayaQR").show()

  // Update summary
  updateBookingSummary()
})

// Example calculateTotalPrice function (replace with your actual implementation)
function calculateTotalPrice() {
  let totalPrice = 0

  // Calculate number of days between check-in and check-out
  let numberOfDays = 1 // Default to 1 day

  if (window.selectedDates.checkIn && window.selectedDates.checkOut) {
    // Calculate the difference in days
    const checkIn = new Date(window.selectedDates.checkIn)
    const checkOut = new Date(window.selectedDates.checkOut)
    const timeDiff = Math.abs(checkOut.getTime() - checkIn.getTime())
    numberOfDays = Math.ceil(timeDiff / (1000 * 3600 * 24)) || 1
  }

  // Calculate price based on pet type and size
  if (window.bookingData && window.bookingData.pets && window.bookingData.pets.length > 0) {
    window.bookingData.pets.forEach((pet) => {
      let petPrice = 0

      // Set price based on pet size
      if (pet.size === "Cat") {
        petPrice = 500
      } else if (pet.size === "Small") {
        petPrice = 700
      } else if (pet.size === "Medium" || pet.size === "Regular") {
        petPrice = 800
      } else if (pet.size === "Large" || pet.size === "XL" || pet.size === "XXL") {
        petPrice = 900
      }

      // Multiply by number of days
      totalPrice += petPrice * numberOfDays
    })
  }

  // Update the total price display
  $("#summaryTotalAmount").text(`₱ ${totalPrice.toFixed(2)}`)
  $("#summaryRemainingBalance").text(`₱ ${totalPrice.toFixed(2)}`)

  return totalPrice
}

// Add this function to properly handle date selection from the calendar
function initializeCalendarSelection() {
  // When the calendar is rendered, attach click handlers to the days
  $(document).on("click", ".day:not(.past-day)", function () {
    const day = Number.parseInt($(this).text())
    const dateAttr = $(this).attr("data-date")

    // If no start date is selected or both start and end dates are selected
    if (!startDate || (startDate && endDate)) {
      // Reset selection
      startDate = day
      endDate = null
      $(".day").removeClass("selected-date highlighted")
      $(this).addClass("selected-date")
    }
    // If only start date is selected and clicked day is after start date
    else if (day > startDate) {
      // Set end date
      endDate = day

      // Highlight the range
      $(".day").each(function () {
        const currentDay = Number.parseInt($(this).text())
        if (currentDay >= startDate && currentDay <= endDate) {
          if (currentDay === startDate || currentDay === endDate) {
            $(this).addClass("selected-date")
          } else {
            $(this).addClass("highlighted")
          }
        }
      })
    }

    // Update booking data with selected dates
    updateBookingDatesFromCalendar()
  })
}

// Function to update booking dates based on calendar selection
function updateBookingDatesFromCalendar() {
  const selectedDays = $(".day.selected-date, .day.highlighted")

  if (selectedDays.length > 0) {
    // Get all selected dates and sort them
    const dates = []
    selectedDays.each(function () {
      const dateAttr = $(this).attr("data-date")
      if (dateAttr) {
        dates.push(new Date(dateAttr))
      }
    })

    // Sort dates chronologically
    dates.sort((a, b) => a - b)

    // First date is check-in, last date is check-out
    if (dates.length > 0) {
      const checkInDate = dates[0]
      const checkOutDate = dates[dates.length - 1]

      // Format dates for display
      const formattedCheckInDate = checkInDate.toLocaleDateString("en-US", {
        month: "long",
        day: "numeric",
      })

      const formattedCheckOutDate = checkOutDate.toLocaleDateString("en-US", {
        month: "long",
        day: "numeric",
      })

      // Initialize bookingData if it doesn't exist
      if (!window.bookingData) {
        window.bookingData = {
          pets: [],
          checkInDate: "",
          checkInTime: "",
          checkOutDate: "",
          checkOutTime: "",
        }
      }

      // Update booking data
      window.bookingData.checkInDate = formattedCheckInDate
      window.bookingData.checkOutDate = formattedCheckOutDate

      // Update summary if times are selected
      if (window.bookingData.checkInTime) {
        $("#summaryCheckIn").text(`${formattedCheckInDate}, ${window.bookingData.checkInTime}`)
      }

      if (window.bookingData.checkOutTime) {
        $("#summaryCheckOut").text(`${formattedCheckOutDate}, ${window.bookingData.checkOutTime}`)
      }
    }
  }
}

// Add month navigation handlers
$(document).ready(() => {
  // Initialize bookingData if it doesn't exist
  if (!window.bookingData) {
    window.bookingData = {
      pets: [],
      checkInDate: "",
      checkInTime: "",
      checkOutDate: "",
      checkOutTime: "",
    }
  }

  // Initialize calendar
  renderCalendar()

  // Set up month navigation
  $("#prevMonth").on("click", () => {
    currentDate.setMonth(currentDate.getMonth() - 1)
    renderCalendar()
  })

  $("#nextMonth").on("click", () => {
    currentDate.setMonth(currentDate.getMonth() + 1)
    renderCalendar()
  })

  // Handle BOOK button click
  $(".book").on("click", (e) => {
    if (!isUserLoggedIn()) {
      e.preventDefault()
      alert("Please log in first to continue booking.")
      return
    }

    $(".main-schedule-options").fadeOut(() => {
      $(".book-1").fadeIn()
    })
  })

  // Handle Proceed to Payment button click
  $(".payment-btn").on("click", (e) => {
    if (!hasPetSelected()) {
      e.preventDefault()
      alert("Please select a pet first.")
      return
    }

    if (!hasDateSelected()) {
      e.preventDefault()
      alert("Please select check-in and check-out dates.")
      return
    }

    // If all validations pass, show payment modal
    $("#petPaymentModal").modal("show")
  })

  // Fix for complete booking button
  $(document).on("click", "#complete-booking", function (e) {
    e.preventDefault()
    console.log("Complete booking button clicked")

    // Check if waiver checkboxes are checked
    if (!$("#waiverForm-checkbox1").prop("checked") || !$("#waiverForm-checkbox2").prop("checked")) {
      alert("You must agree to the terms and conditions to complete your booking.")
      return
    }

    // Show processing notification
    alert("Your booking is being processed. Please wait...")

    // Disable the button to prevent multiple submissions
    $(this).prop("disabled", true).text("Processing...")

    // Get the payment form data
    var formData = new FormData($("#paymentForm")[0])
    formData.append("complete_booking", "true")

    // Add booking data to form
    if (window.bookingData) {
      formData.append("booking_data", JSON.stringify(window.bookingData))
    }

    $.ajax({
      type: "POST",
      url: window.location.href,
      data: formData,
      processData: false,
      contentType: false,
      dataType: "json",
      success: (response) => {
        if (response.success) {
          alert("Booking completed successfully!")
          $("#waiverForm").modal("hide")
          // Redirect to confirmation page
          window.location.href = "booking-confirmation.php"
        } else {
          alert("Error: " + (response.message || "Unknown error"))
          // Re-enable the button if there's an error
          $("#complete-booking").prop("disabled", false).text("Complete Booking")
        }
      },
      error: (xhr, status, error) => {
        console.error("AJAX Error:", error)

        // Show detailed error message
        let errorMessage = "An error occurred while processing your booking."

        if (xhr.responseJSON && xhr.responseJSON.message) {
          errorMessage += " Details: " + xhr.responseJSON.message
        } else if (xhr.status === 401) {
          errorMessage = "You must be logged in to complete this booking."
        } else if (xhr.status === 400) {
          errorMessage = "Invalid booking data. Please check your selections."
        } else if (xhr.status === 500) {
          errorMessage = "Server error. Please try again later or contact support."
        } else {
          errorMessage += " Error code: " + xhr.status + " - " + error
        }

        alert(errorMessage)

        // Re-enable the button if there's an error
        $("#complete-booking").prop("disabled", false).text("Complete Booking")
      },
    })
  })

  // Ensure the proceed-to-waiver button works correctly
  $(document).on("click", "#proceed-to-waiver", (e) => {
    console.log("Proceed to waiver button clicked")
    // Close payment modal
    $("#petPaymentModal").modal("hide")
    // Show waiver modal
    setTimeout(() => {
      $("#waiverForm").modal("show")
    }, 500) // Small delay to ensure first modal is closed
  })
})

