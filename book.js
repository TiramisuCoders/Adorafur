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

// Update the isUserLoggedIn function to check for both session variables
function isUserLoggedIn() {
  // Check if the PHP session has customer_id set
  // This is a client-side approximation - the actual check happens server-side
  return (
    (document.cookie.includes("PHPSESSID=") &&
      typeof window.isLoggedIn !== "undefined" &&
      window.isLoggedIn === true) ||
    (typeof window.customerId !== "undefined" && window.customerId > 0) ||
    (typeof window.customer_id !== "undefined" && window.customer_id > 0)
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
    day.classList.remove("selected-date", "range-date", "highlighted")
  })
}

// Update the highlightDateRange function to use the same logic
function highlightDateRange() {
  if (!window.selectedDates.checkIn || !window.selectedDates.checkOut) return

  // First clear all highlights
  document.querySelectorAll(".day").forEach((dayElement) => {
    dayElement.classList.remove("selected-date", "highlighted")
  })

  // Then apply the correct classes
  document.querySelectorAll(".day").forEach((dayElement) => {
    const dateStr = dayElement.getAttribute("data-date")
    if (!dateStr) return

    const date = new Date(dateStr)

    // Check if this is the start or end date - both should have selected-date class
    if (
      (window.selectedDates.checkIn && date.getTime() === window.selectedDates.checkIn.getTime()) ||
      (window.selectedDates.checkOut && date.getTime() === window.selectedDates.checkOut.getTime())
    ) {
      dayElement.classList.add("selected-date")
    }
    // Check if this is a date in between
    else if (date > window.selectedDates.checkIn && date < window.selectedDates.checkOut) {
      dayElement.classList.add("highlighted")
    }
  })
}

// Update the handleDateClick function to ensure proper highlighting
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
      day: "numeric",
    })
  } else {
    // Complete selection
    if (date > window.selectedDates.checkIn) {
      window.selectedDates.checkOut = date

      // Clear all highlights first
      document.querySelectorAll(".day").forEach((day) => {
        day.classList.remove("selected-date", "highlighted")
      })

      // Find and highlight the first date
      document.querySelectorAll(".day").forEach((dayElement) => {
        const dateStr = dayElement.getAttribute("data-date")
        if (!dateStr) return

        const currentDate = new Date(dateStr)
        if (currentDate.getTime() === window.selectedDates.checkIn.getTime()) {
          dayElement.classList.add("selected-date")
        }
      })

      // Highlight the last date (current selection)
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

// Update the renderCalendar function to handle current date highlighting and disable past dates
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

    // Check if this date is today or in the past
    if (thisDate.getTime() === today.getTime()) {
      // Current date - add underline
      dayElement.classList.add("current-day")
      // Also make it non-selectable
      dayElement.classList.add("past-day")
    } else if (thisDate < today) {
      // Past date - make it gray and non-selectable
      dayElement.classList.add("past-day")
    }

    // Check if this date is selected
    if (window.selectedDates.checkIn && formattedDate === window.selectedDates.checkIn.toISOString().split("T")[0]) {
      dayElement.classList.add("selected-date")
    }
    if (window.selectedDates.checkOut && formattedDate === window.selectedDates.checkOut.toISOString().split("T")[0]) {
      dayElement.classList.add("selected-date")
    }

    // Add click handler only for future dates
    if (!dayElement.classList.contains("past-day")) {
      dayElement.addEventListener("click", () => handleDateClick(thisDate, dayElement))
    }

    daysContainer.appendChild(dayElement)
  }
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

    // Find the end date element and add selected-date class
    $(".day").each(function () {
      const currentDay = Number.parseInt($(this).text())
      if (currentDay === endDate) {
        $(this).addClass("selected-date")
      } else if (currentDay > startDate && currentDay < endDate) {
        $(this).addClass("highlighted")
      }
    })

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

// Update the calculateTotalPrice function to calculate based on pet size and number of days
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
      } else if (pet.size === "Regular") {
        petPrice = 800
      } else if (pet.size === "Large") {
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

// Update the initializeCalendarSelection function to properly handle date selection
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

      // Set as check-in date
      if (dateAttr) {
        const selectedDate = new Date(dateAttr)
        window.selectedDates.checkIn = selectedDate
        window.selectedDates.checkOut = null

        // Format for display
        const formattedDate = selectedDate.toLocaleDateString("en-US", {
          month: "long",
          day: "numeric",
        })

        // Update booking data
        if (!window.bookingData) {
          window.bookingData = {
            pets: [],
            checkInDate: "",
            checkInTime: "",
            checkOutDate: "",
            checkOutTime: "",
          }
        }

        window.bookingData.checkInDate = formattedDate

        // Update display in summary
        if (window.bookingData.checkInTime) {
          $("#summaryCheckIn").text(`${formattedDate}, ${window.bookingData.checkInTime}`)
        } else {
          $("#summaryCheckIn").text(formattedDate)
        }
      }
    }
    // If only start date is selected and clicked day is after start date
    else if (day > startDate) {
      // Set end date
      endDate = day

      // First clear all highlights
      $(".day").removeClass("selected-date highlighted")

      // Find the first date (start date) and last date (end date) elements
      let firstDateElement = null
      let lastDateElement = null

      $(".day").each(function () {
        const currentDay = Number.parseInt($(this).text())
        if (currentDay === startDate) {
          firstDateElement = $(this)
        }
        if (currentDay === endDate) {
          lastDateElement = $(this)
        }
      })

      // Apply selected-date class to first and last date only
      if (firstDateElement) firstDateElement.addClass("selected-date")
      if (lastDateElement) lastDateElement.addClass("selected-date")

      // Apply highlighted class to dates in between
      $(".day").each(function () {
        const currentDay = Number.parseInt($(this).text())
        if (currentDay > startDate && currentDay < endDate) {
          $(this).addClass("highlighted")
        }
      })

      // Set as check-out date
      if (dateAttr) {
        const selectedDate = new Date(dateAttr)
        window.selectedDates.checkOut = selectedDate

        // Format for display
        const formattedDate = selectedDate.toLocaleDateString("en-US", {
          month: "long",
          day: "numeric",
        })

        // Update booking data
        window.bookingData.checkOutDate = formattedDate

        // Update display in summary
        if (window.bookingData.checkOutTime) {
          $("#summaryCheckOut").text(`${formattedDate}, ${window.bookingData.checkOutTime}`)
        } else {
          $("#summaryCheckOut").text(formattedDate)
        }

        // Calculate and update price
        calculateTotalPrice()
      }
    }

    // Update booking data with selected dates
    updateBookingDatesFromCalendar()
  })

  // Handle check-in time selection
  $(document).on("click", ".check-in-time", function () {
    const selectedTime = $(this).text()
    $("#checkInMenu").text(selectedTime)

    // Update booking data
    if (window.bookingData) {
      window.bookingData.checkInTime = selectedTime

      // Update display in summary if check-in date is selected
      if (window.bookingData.checkInDate) {
        $("#summaryCheckIn").text(`${window.bookingData.checkInDate}, ${selectedTime}`)
      }
    }
  })

  // Handle check-out time selection
  $(document).on("click", ".check-out-time", function () {
    const selectedTime = $(this).text()
    $("#checkOutMenu").text(selectedTime)

    // Update booking data
    if (window.bookingData) {
      window.bookingData.checkOutTime = selectedTime

      // Update display in summary if check-out date is selected
      if (window.bookingData.checkOutDate) {
        $("#summaryCheckOut").text(`${window.bookingData.checkOutDate}, ${selectedTime}`)
      }
    }
  })

  // Handle pet selection
  $(document).on("click", ".pet-info", function () {
    const petType = $(this).find("h3").text()
    const petPrice = $(this)
      .find("h6")
      .text()
      .match(/₱\s*(\d+)/)[1]

    // Create pet object
    const pet = {
      name: petType,
      size: petType,
      price: Number.parseInt(petPrice),
    }

    // Check if this pet is already selected
    const existingPetIndex = window.bookingData.pets.findIndex((p) => p.name === petType)

    if (existingPetIndex >= 0) {
      // Remove pet if already selected
      window.bookingData.pets.splice(existingPetIndex, 1)
    } else {
      // Add pet if not selected
      window.bookingData.pets.push(pet)
    }

    // Update summary
    updateBookingSummary()
  })
}

// Add CSS for current day and past days
$(document).ready(() => {
  // Add CSS for current day and past days
  const style = document.createElement("style")
  style.textContent = `
    .day.current-day {
      text-decoration: underline;
      color: #999;
      cursor: not-allowed;
    }
    .day.past-day {
      color: #999;
      cursor: not-allowed;
    }
  `
  document.head.appendChild(style)
})

// Function to update booking dates based on calendar selection
function updateBookingDatesFromCalendar() {
  // Get specifically selected dates (not highlighted ones)
  const selectedStartEnd = $(".day.selected-date")

  if (selectedStartEnd.length > 0) {
    // Get all selected dates and sort them
    const dates = []
    selectedStartEnd.each(function () {
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
      const checkOutDate = dates[dates.length - 1] || checkInDate

      // Update window.selectedDates object
      window.selectedDates.checkIn = checkInDate
      window.selectedDates.checkOut = checkOutDate

      // Format dates for display
      const formattedCheckInDate = checkInDate.toLocaleDateString("en-US", {
        month: "long",
        day: "numeric",
      })

      const formattedCheckOutDate = checkOutDate.toLocaleDateString("en-US", {
        month: "long",
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

// Add a function to update the pet prices in the table based on size
function updatePetPricesInTable() {
  // Get all rows in the pet table
  $("table tr:not(:first-child)").each(function () {
    const sizeCell = $(this).find("td[data-label='Size']") // Size column
    const priceCell = $(this).find("td[data-label='Price']") // Price column

    if (sizeCell.length && priceCell.length) {
      const petSize = sizeCell.text().trim()
      let price = 0

      // Set price based on pet size
      if (petSize === "Cat") {
        price = 500
      } else if (petSize === "Small") {
        price = 700
      } else if (petSize === "Regular") {
        price = 800
      } else if (petSize === "Large") {
        price = 900
      }

      // Update the price cell
      priceCell.text(`₱${price.toFixed(2)}`)

      // Also update the pet object in bookingData if it exists
      if (window.bookingData && window.bookingData.pets) {
        const petName = $(this).find("td[data-label='Name']").text().trim()
        const existingPetIndex = window.bookingData.pets.findIndex((p) => p.name === petName)

        if (existingPetIndex >= 0) {
          window.bookingData.pets[existingPetIndex].price = price
        }
      }
    }
  })

  // Recalculate total price
  calculateTotalPrice()
}

// Update the fetchCustomerPets function to get the customer ID from session
function fetchCustomerPets() {
  // Make an AJAX request to get pets for the logged-in customer
  $.ajax({
    type: "POST",
    url: window.location.href,
    data: { fetch_customer_pets: true },
    dataType: "json",
    success: (response) => {
      if (response.success && response.pets) {
        // Clear existing options except the first one
        const petSelect = $(".petSelect")
        petSelect.find("option:not(:first)").remove()

        // Add the customer's pets to the dropdown
        response.pets.forEach((pet) => {
          const petData = {
            pet_breed: pet.pet_breed,
            pet_age: pet.pet_age,
            pet_gender: pet.pet_gender,
            pet_size: pet.pet_size,
          }

          const option = $("<option>").val(JSON.stringify(petData)).text(pet.pet_name)
          petSelect.append(option)
        })

        // Update prices after loading pets
        updatePetPricesInTable()
      } else {
        console.error("Failed to fetch pets:", response.message || "Unknown error")
        // Show a message to the user that they need to register a pet first
        alert("You don't have any pets registered. Please register a pet first.")
      }
    },
    error: (xhr, status, error) => {
      console.error("AJAX Error:", error)
      alert("Error loading your pets. Please try again later.")
    },
  })
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

  // Check login status on page load
  if (isUserLoggedIn()) {
    // If user is logged in, fetch their pets for the dropdown
    fetchCustomerPets()

    // Update client info in the UI
    updateClientInfo()
  } else {
    // Disable booking sections if not logged in
    $(".calendar").addClass("disabled-section")
    $(".checkin-out").addClass("disabled-section")
    $(".book").addClass("disabled-section")
  }

  // Handle BOOK button click - Check if user is logged in
  $(".book").on("click", (e) => {
    if (!isUserLoggedIn()) {
      e.preventDefault()
      alert("Please log in first to continue booking.")

      // Optionally, show login modal if available
      if ($("#loginModal").length) {
        $("#loginModal").modal("show")
      }
      return
    }

    // If user is logged in, proceed to next section
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

  // Initialize calendar selection
  initializeCalendarSelection()

  // Call the function to update pet prices when the page loads
  updatePetPricesInTable()

  // Also update prices when a pet is selected from dropdown
  $(document).on("change", ".petSelect", () => {
    // Give the DOM time to update with the new pet info
    setTimeout(updatePetPricesInTable, 100)
  })

  // Add event handler for the checkboxes in the Action column
  $(document).on("change", ".pet-checkbox", function () {
    const row = $(this).closest("tr")
    const petName = row.find("td:nth-child(1)").text().trim()
    const petBreed = row.find("td:nth-child(2)").text().trim()
    const petAge = row.find("td:nth-child(3)").text().trim()
    const petGender = row.find("td:nth-child(4)").text().trim()
    const petSize = row.find("td:nth-child(5)").text().trim()
    const petPrice = Number.parseFloat(row.find("td:nth-child(6)").text().replace("₱", "").trim())

    // Create pet object
    const pet = {
      name: petName,
      breed: petBreed,
      age: petAge,
      gender: petGender,
      size: petSize,
      price: petPrice,
    }

    // Check if this pet is already in the bookingData
    const existingPetIndex = window.bookingData.pets.findIndex((p) => p.name === petName)

    if ($(this).is(":checked")) {
      // Add pet if not already in the array
      if (existingPetIndex === -1) {
        window.bookingData.pets.push(pet)
      }
    } else {
      // Remove pet if in the array
      if (existingPetIndex >= 0) {
        window.bookingData.pets.splice(existingPetIndex, 1)
      }
    }

    // Update summary
    updateBookingSummary()
  })

  // Add a function to update client info in the UI
  function updateClientInfo() {
    // Try to get client info from PHP session via AJAX
    $.ajax({
      type: "POST",
      url: window.location.href,
      data: { get_client_info: true },
      dataType: "json",
      success: (response) => {
        if (response.success) {
          // Update client name and email in the UI
          $(".client b").text(response.client_name || "Client name")
          $(".client-email").text(response.client_email || "Client Email")
        }
      },
      error: (xhr, status, error) => {
        console.error("Error fetching client info:", error)
      },
    })
  }
})

