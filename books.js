// Initialize booking data object
window.bookingData = {
  pets: [],
  checkInDate: "",
  checkInTime: "",
  checkOutDate: "",
  checkOutTime: "",
}

// Calendar functionality
$(document).ready(() => {
  // Initialize variables
  const currentDate = new Date()
  let currentMonth = currentDate.getMonth()
  let currentYear = currentDate.getFullYear()
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
  const selectedDates = {
    checkIn: null,
    checkOut: null,
  }

  // Check if user is logged in - this will be set in the PHP file
  const isLoggedIn = window.isLoggedIn || false

  // Initially hide pet information sections
  $(".pet-information-dog, .pet-information-cat").hide()

  // Initially hide booking details section
  $(".book-1").hide()

  // Disable sections if not logged in
  if (!isLoggedIn) {
    $(".calendar").addClass("disabled-section")
    $(".checkin-out").addClass("disabled-section")
    $(".book").addClass("disabled-section")
  }

  // Render calendar
  function renderCalendar() {
    // Update month and year display
    $("#month").text(months[currentMonth])
    $("#year").text(currentYear)

    // Get first day of month and total days
    const firstDay = new Date(currentYear, currentMonth, 1).getDay()
    const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate()

    // Clear previous calendar days
    $("#days").empty()

    // Add empty cells for days before the first day of month
    for (let i = 0; i < firstDay; i++) {
      $("#days").append('<div class="day empty"></div>')
    }

    // Create current date for comparison
    const today = new Date()
    today.setHours(0, 0, 0, 0)

    // Add calendar days
    for (let day = 1; day <= daysInMonth; day++) {
      const thisDate = new Date(currentYear, currentMonth, day)
      const formattedDate = thisDate.toISOString().split("T")[0]

      let dayClass = "day"
      const isToday = thisDate.getTime() === today.getTime()
      const isPastDay = thisDate < today

      if (isPastDay || isToday) {
        dayClass += " past-day"
      }

      if (isToday) {
        dayClass += " current-day"
      }

      // Check if this date is selected
      if (
        (selectedDates.checkIn && formattedDate === selectedDates.checkIn.toISOString().split("T")[0]) ||
        (selectedDates.checkOut && formattedDate === selectedDates.checkOut.toISOString().split("T")[0])
      ) {
        dayClass += " selected-date"
      }

      // Add date to calendar
      const dayElement = $(`<div class="${dayClass}" data-date="${formattedDate}">${day}</div>`)

      // Add click handler only for future dates
      if (!dayClass.includes("past-day")) {
        dayElement.on("click", function () {
          handleDateClick(thisDate, $(this))
        })
      }

      $("#days").append(dayElement)
    }

    // Highlight date range if both dates are selected
    highlightDateRange()
  }

  // Modify the updateAvailableSlots function to match the daycare system's approach
  function updateAvailableSlots() {
    if (!selectedDates.checkIn || !selectedDates.checkOut) {
      $(".available-slot").text("Available Slots: Select dates")
      return
    }

    // Get the selected pet type and variant
    let species = ""
    let variant = ""

    if (window.bookingData.pets.length > 0) {
      const pet = window.bookingData.pets[0]

      // Extract species and variant from pet size
      if (pet.size.includes("Dog")) {
        species = "dog"
        if (pet.size.includes("Small")) {
          variant = "small"
        } else if (pet.size.includes("Large")) {
          variant = "large"
        } else {
          variant = "regular"
        }
      } else if (pet.size.includes("Cat")) {
        species = "cat"
        variant = "regular"
      }
    }

    // If no pet is selected yet, just show a message
    if (!species || !variant) {
      $(".available-slot").text("Available Slots: Select a pet type first")
      return
    }

    const checkInDate = selectedDates.checkIn.toISOString().split("T")[0]
    const checkOutDate = selectedDates.checkOut.toISOString().split("T")[0]

    $.ajax({
      type: "POST",
      url: "get-available-slots-hotel.php",
      data: {
        action: "get_hotel_slots",
        check_in_date: checkInDate,
        check_out_date: checkOutDate,
        species: species,
        variant: variant,
      },
      dataType: "json",
      beforeSend: () => {
        // Show loading indicator
        $(".available-slot").html(`<span>Checking availability...</span>`)
      },
      success: (response) => {
        console.log("AJAX success, full response:", response)

        if (response.success) {
          const availableSlots = response.available_slots || 0
          const maxSlots = response.max_slots || 10

          // Update the UI with the available slots count
          $(".available-slot").html(`
          Available Slots: <span class="slot-count">${availableSlots}</span>/${maxSlots}
        `)

          // Add visual indicator based on availability
          if (availableSlots <= 0) {
            $(".available-slot").addClass("no-slots").removeClass("few-slots")
          } else if (availableSlots <= 3) {
            $(".available-slot").addClass("few-slots").removeClass("no-slots")
          } else {
            $(".available-slot").removeClass("few-slots no-slots")
          }
        } else {
          $(".available-slot").html(`Available Slots: <span class="slot-count">Error</span>`)
        }
      },
      error: (xhr, status, error) => {
        console.error("AJAX Error:", error)
        console.error("Status:", status)
        console.error("Response:", xhr.responseText)
        $(".available-slot").html(`Available Slots: <span class="slot-count">Error</span>`)
      },
    })
  }

  function handleDateClick(date, element) {
    if (!selectedDates.checkIn || (selectedDates.checkIn && selectedDates.checkOut)) {
      // Start new selection
      clearDateSelection()
      selectedDates.checkIn = date
      element.addClass("selected-date")

      // Update booking data
      window.bookingData.checkInDate = date.toLocaleDateString("en-US", {
        month: "long",
        day: "numeric",
      })
      window.bookingData.checkOutDate = window.bookingData.checkInDate
    } else {
      // Complete selection
      if (date > selectedDates.checkIn) {
        selectedDates.checkOut = date

        // Update booking data
        window.bookingData.checkOutDate = date.toLocaleDateString("en-US", {
          month: "long",
          day: "numeric",
        })

        // Apply highlighting to dates in between
        $(".day").each(function () {
          const dateStr = $(this).attr("data-date")
          if (!dateStr) return

          const currentDate = new Date(dateStr)
          const currentDateStr = currentDate.toISOString().split("T")[0]
          const checkInDateStr = selectedDates.checkIn.toISOString().split("T")[0]
          const checkOutDateStr = selectedDates.checkOut.toISOString().split("T")[0]

          if (currentDateStr === checkInDateStr || currentDateStr === checkOutDateStr) {
            $(this).removeClass("highlighted").addClass("selected-date")
          } else if (currentDate > selectedDates.checkIn && currentDate < selectedDates.checkOut) {
            $(this).removeClass("selected-date").addClass("highlighted")
          }
        })

        // Update available slots after date range is selected
        updateAvailableSlots()
      }
    }

    // Enable time selection after date selection
    $(".checkin-out").removeClass("disabled-section")

    // Update summary
    updateBookingSummary()
  }

  // Clear date selection
  function clearDateSelection() {
    selectedDates.checkIn = null
    selectedDates.checkOut = null
    $(".day").removeClass("selected-date highlighted")
  }

  // Highlight date range
  function highlightDateRange() {
    if (!selectedDates.checkIn || !selectedDates.checkOut) return

    $(".day").each(function () {
      const dateStr = $(this).attr("data-date")
      if (!dateStr) return

      const date = new Date(dateStr)

      if (date > selectedDates.checkIn && date < selectedDates.checkOut) {
        $(this).addClass("highlighted")
      }
    })
  }

  // Initialize calendar
  renderCalendar()

  // Set up month navigation
  $("#prevMonth").on("click", () => {
    const now = new Date()
    const prevMonth = new Date(currentYear, currentMonth - 1, 1)

    // Don't allow navigating to past months
    if (
      prevMonth.getFullYear() < now.getFullYear() ||
      (prevMonth.getFullYear() === now.getFullYear() && prevMonth.getMonth() < now.getMonth())
    ) {
      return
    }

    currentMonth--
    if (currentMonth < 0) {
      currentMonth = 11
      currentYear--
    }
    renderCalendar()
  })

  $("#nextMonth").on("click", () => {
    currentMonth++
    if (currentMonth > 11) {
      currentMonth = 0
      currentYear++
    }
    renderCalendar()
  })

  // Pet selection logic
  $("#petSelectionMenu + .dropdown-menu .dropdown-item").click(function () {
    var selectedPet = $(this).text()
    $("#petSelectionMenu").text(selectedPet)
    $(".pet-information-dog, .pet-information-cat").hide()

    if (selectedPet === "Dog") {
      $(".pet-information-dog").fadeIn()
    } else if (selectedPet === "Cat") {
      $(".pet-information-cat").fadeIn()
    }

    // Enable calendar after pet selection
    $(".calendar").removeClass("disabled-section")
  })

  // Handle pet info selection
  let selectedPet = null

  $(".pet-info").hover(
    function () {
      $(this).find("h3, h6").fadeIn()
    },
    function () {
      if (!$(this).hasClass("selected")) {
        $(this).find("h3, h6").fadeOut()
      }
    },
  )

  $(".pet-info").click(function () {
    const img = $(this).find("img")
    const petType = $(this).find("h3").text()
    const petPriceMatch = $(this)
      .find("h6")
      .text()
      .match(/₱\s*(\d+)/)
    const petPrice = petPriceMatch ? Number.parseInt(petPriceMatch[1]) : 0

    if (selectedPet === this) {
      // Deselect
      $(this).removeClass("selected")
      swapImage(img)
      $(this).find("h3, h6").fadeOut()
      selectedPet = null

      // Remove from booking data
      const existingPetIndex = window.bookingData.pets.findIndex((p) => p.name === petType)
      if (existingPetIndex >= 0) {
        window.bookingData.pets.splice(existingPetIndex, 1)
      }
    } else {
      // Deselect previous
      if (selectedPet) {
        $(selectedPet).removeClass("selected")
        swapImage($(selectedPet).find("img"))
        $(selectedPet).find("h3, h6").fadeOut()

        // Remove previous pet from booking data
        const prevPetType = $(selectedPet).find("h3").text()
        const existingPetIndex = window.bookingData.pets.findIndex((p) => p.name === prevPetType)
        if (existingPetIndex >= 0) {
          window.bookingData.pets.splice(existingPetIndex, 1)
        }
      }

      // Select new
      $(this).addClass("selected")
      swapImage(img)
      $(this).find("h3, h6").fadeIn()
      selectedPet = this

      // Add to booking data
      window.bookingData.pets.push({
        name: petType,
        size: petType,
        price: petPrice,
      })
    }

    // Update summary
    updateBookingSummary()
  })

  // Function to swap images
  function swapImage(img) {
    const tempSrc = img.attr("src")
    img.attr("src", img.attr("data-selected-src"))
    img.attr("data-selected-src", tempSrc)
  }

  // Handle check-in and check-out time selection
  $(".check-in-time").click(function () {
    const selectedTime = $(this).text()
    $("#checkInMenu").text(selectedTime)
    window.bookingData.checkInTime = selectedTime
    updateBookingSummary()
    checkTimeSelection()
  })

  $(".check-out-time").click(function () {
    const selectedTime = $(this).text()
    $("#checkOutMenu").text(selectedTime)
    window.bookingData.checkOutTime = selectedTime
    updateBookingSummary()
    checkTimeSelection()
  })

  // Check if both times are selected
  function checkTimeSelection() {
    if (window.bookingData.checkInTime && window.bookingData.checkOutTime) {
      $(".book").removeClass("disabled-section")
    }
  }

  // Handle Book button click
  $(".book").click(() => {
    if (!isLoggedIn) {
      alert("Please log in to continue booking.")
      return
    }

    $(".main-schedule-options").fadeOut(() => {
      $(".book-1").fadeIn()

      // Fetch customer pets for the dropdown
      fetchCustomerPets()
    })
  })

  // Handle Back button click
  $(document).on("click", "#backToBookingBtn", () => {
    $(".book-1").fadeOut(() => {
      $(".main-schedule-options").fadeIn()
    })
  })

  // Fetch customer pets
  function fetchCustomerPets() {
    $.ajax({
      type: "POST",
      url: "get-user-pets.php",
      data: { c_id: window.customerId || 0 },
      dataType: "json",
      success: (response) => {
        if (response.success && response.pets.length > 0) {
          // Clear existing options except the first one
          $(".petSelect").find("option:not(:first)").remove()

          // Add the customer's pets to the dropdown
          response.pets.forEach((pet) => {
            const petData = {
              pet_id: pet.pet_id,
              pet_breed: pet.pet_breed,
              pet_age: pet.pet_age,
              pet_gender: pet.pet_gender,
              pet_size: pet.pet_size,
            }

            const option = $("<option>").val(JSON.stringify(petData)).text(pet.pet_name)

            $(".petSelect").append(option)
          })

          // Update available pets in all dropdowns
          updateAvailablePets()
        } else {
          console.log("No pets found or error:", response.message)
        }
      },
      error: (xhr, status, error) => {
        console.error("AJAX Error:", error)
      },
    })
  }

  // Update pet details when selected from dropdown
  window.updatePetDetails = (selectElement) => {
    const selectedOption = $(selectElement).find("option:selected")
    const petName = selectedOption.text()

    if (petName && petName !== "Choose Pet") {
      // Check if this pet is already selected in another row
      let isDuplicate = false

      // Skip the current dropdown when checking for duplicates
      $(".petSelect")
        .not(selectElement)
        .each(function () {
          if ($(this).find("option:selected").text() === petName) {
            isDuplicate = true
            return false // Break the loop
          }
        })

      if (isDuplicate) {
        // Alert the user
        alert("This pet is already selected in another row. Please choose a different pet.")

        // Reset the dropdown to "Choose Pet"
        $(selectElement).val("")

        // Clear row data
        const row = $(selectElement).closest("tr")
        row.find("[data-label='Breed']").text("")
        row.find("[data-label='Age']").text("")
        row.find("[data-label='Gender']").text("")
        row.find("[data-label='Size']").text("")
        row.find("[data-label='Price']").text("₱0.00")

        return
      }

      try {
        // Get pet details from the JSON
        const petDetails = JSON.parse($(selectElement).val())
        const row = $(selectElement).closest("tr")

        // Update row cells
        row.find("[data-label='Breed']").text(petDetails.pet_breed || "")
        row.find("[data-label='Age']").text(petDetails.pet_age ? petDetails.pet_age + "" : "")
        row.find("[data-label='Gender']").text(petDetails.pet_gender || "")
        row.find("[data-label='Size']").text(petDetails.pet_size || "")

        // Set price based on pet size
        let price = 0
        switch (petDetails.pet_size) {
          case "Cat":
            price = 600
            break
          case "Small":
            price = 800
            break
          case "Regular":
            price = 900
            break
          case "Large":
            price = 1000
            break
        }

        row.find("[data-label='Price']").text(`₱${price.toFixed(2)}`)

        // Create pet object
        const pet = {
          name: petName,
          breed: petDetails.pet_breed,
          gender: petDetails.pet_gender,
          age: petDetails.pet_age,
          size: petDetails.pet_size,
          price: price,
        }

        // Check if this pet is already in the array
        const existingPetIndex = window.bookingData.pets.findIndex((p) => p.name === petName)

        if (existingPetIndex >= 0) {
          // Update existing pet
          window.bookingData.pets[existingPetIndex] = pet
        } else {
          // Add new pet
          window.bookingData.pets.push(pet)
        }

        // Update summary
        calculateTotalPrice()
        updateBookingSummary()

        // Update available pets in all dropdowns
        updateAvailablePets()
      } catch (e) {
        console.error("Error parsing pet details:", e)
      }
    } else {
      // Clear row if "Choose Pet" is selected
      const row = $(selectElement).closest("tr")
      row.find("[data-label='Breed']").text("")
      row.find("[data-label='Age']").text("")
      row.find("[data-label='Gender']").text("")
      row.find("[data-label='Size']").text("")
      row.find("[data-label='Price']").text("₱0.00")

      // Remove from booking data if exists
      const oldPetName = row.data("pet-name")
      if (oldPetName) {
        const existingPetIndex = window.bookingData.pets.findIndex((p) => p.name === oldPetName)
        if (existingPetIndex >= 0) {
          window.bookingData.pets.splice(existingPetIndex, 1)
        }
      }

      // Update available pets in all dropdowns
      updateAvailablePets()
    }

    // Store the selected pet name in the row for future reference
    $(selectElement).closest("tr").data("pet-name", petName)
  }

  // Add a function to update available pets in all dropdowns
  function updateAvailablePets() {
    // Get all selected pets
    const selectedPets = []
    $(".petSelect").each(function () {
      const petName = $(this).find("option:selected").text()
      if (petName && petName !== "Choose Pet") {
        selectedPets.push(petName)
      }
    })

    // Update each dropdown
    $(".petSelect").each(function () {
      const currentSelect = $(this)
      const currentSelectedPet = currentSelect.find("option:selected").text()

      // Enable all options first
      currentSelect.find("option").prop("disabled", false)

      // Disable options that are selected in other dropdowns
      selectedPets.forEach((petName) => {
        if (petName !== currentSelectedPet) {
          currentSelect.find(`option:contains("${petName}")`).prop("disabled", true)
        }
      })
    })
  }

  // Update the removePetRow function to also update available pets
  window.removePetRow = (button) => {
    const row = $(button).closest("tr")
    const petName = row.find(".petSelect option:selected").text()

    // Remove from booking data if exists
    if (petName && petName !== "Choose Pet") {
      const existingPetIndex = window.bookingData.pets.findIndex((p) => p.name === petName)
      if (existingPetIndex >= 0) {
        window.bookingData.pets.splice(existingPetIndex, 1)
      }
    }

    // Remove row
    row.remove()

    // Update total price
    calculateTotalPrice()
    updateBookingSummary()

    // Update available pets in all dropdowns
    updateAvailablePets()
  }

  // Add new pet row
  window.addPetRow = () => {
    // Get all options from the first dropdown
    let options = ""
    $(".petSelect")
      .first()
      .find("option")
      .each(function () {
        options += $(this).prop("outerHTML")
      })

    const newRow = `
                      <tr>
                          <td data-label="Name">
                              <select class="petSelect" onchange="updatePetDetails(this)">
                                  ${options}
                              </select>
                          </td>
                          <td data-label="Breed"></td>
                          <td data-label="Age"></td>
                          <td data-label="Gender"></td>
                          <td data-label="Size"></td>
                          <td data-label="Price">₱0.00</td>
                          <td><button type="button" onclick="removePetRow(this)" class="action-btn">(Remove)</button></td>
                      </tr>
                  `

    $("#petTableBody").append(newRow)

    // Update available pets in all dropdowns
    updateAvailablePets()
  }

  // Calculate total price
  function calculateTotalPrice() {
    let totalPrice = 0

    // Calculate number of days between check-in and check-out
    let numberOfDays = 1 // Default to 1 day

    if (selectedDates.checkIn && selectedDates.checkOut) {
      // Calculate the difference in days
      const checkIn = new Date(selectedDates.checkIn)
      const checkOut = new Date(selectedDates.checkOut)
      const timeDiff = Math.abs(checkOut.getTime() - checkIn.getTime())
      numberOfDays = Math.ceil(timeDiff / (1000 * 3600 * 24)) || 1

      // Add 1 because the check-out day is counted
      numberOfDays += 1
    }

    // Calculate price based on selected pets
    if (window.bookingData.pets.length > 0) {
      window.bookingData.pets.forEach((pet) => {
        // Multiply pet price by number of days
        totalPrice += (pet.price || 0) * numberOfDays
      })
    }

    // Update the full amount display (total price)
    $("#summaryFullAmount").text(`₱ ${totalPrice.toFixed(2)}`)

    // Update payment amounts based on selected payment type
    updatePaymentAmounts()

    return totalPrice
  }

  // Function to update payment amounts based on payment type
  function updatePaymentAmounts() {
    const paymentType = $("#paymentTypeSelect").val()
    const fullAmount = Number.parseFloat($("#summaryFullAmount").text().replace("₱", "").trim()) || 0

    let amountToPay = fullAmount
    let remainingBalance = 0

    if (paymentType === "down") {
      // Down payment is 50% of the total
      amountToPay = fullAmount * 0.5
      remainingBalance = fullAmount - amountToPay
    }

    // Update the displayed amounts
    $("#summaryTotalAmount").text(`₱ ${amountToPay.toFixed(2)}`)
    $("#summaryRemainingBalance").text(`₱ ${remainingBalance.toFixed(2)}`)
  }

  // Update booking summary
  function updateBookingSummary() {
    // Update pet details in summary
    if (window.bookingData.pets.length > 0) {
      // If there's only one pet
      if (window.bookingData.pets.length === 1) {
        const pet = window.bookingData.pets[0]
        $("#summaryPetName").text(pet.name)

        // Update the pet details section
        $("#petSummaryDetails").html(`
                              <div class="info-row"><span class="label">Breed:</span><span class="value">${pet.breed || ""}</span></div>
                              <div class="info-row"><span class="label">Gender:</span><span class="value">${pet.gender || ""}</span></div>
                              <div class="info-row"><span class="label">Age:</span><span class="value">${pet.age ? pet.age + " y/o" : ""}</span></div>
                          `)
      }
      // If there are multiple pets
      else {
        // Update the pet name to show count
        $("#summaryPetName").text(`${window.bookingData.pets.length} Pets`)

        // Create a list of all pets with their details
        let petDetailsHtml = ""
        window.bookingData.pets.forEach((pet, index) => {
          petDetailsHtml += `
                                  <div class="pet-summary-item">
                                      <h4>${pet.name}</h4>
                                      <div class="info-row"><span class="label">Breed:</span><span class="value">${pet.breed || ""}</span></div>
                                      <div class="info-row"><span class="label">Gender:</span><span class="value">${pet.gender || ""}</span></div>
                                      <div class="info-row"><span class="label">Age:</span><span class="value">${pet.age ? pet.age + " y/o" : ""}</span></div>
                                      ${index < window.bookingData.pets.length - 1 ? "<hr>" : ""}
                                  </div>
                              `
        })

        // Update the pet details section
        $("#petSummaryDetails").html(petDetailsHtml)
      }
    }

    // Update dates if available
    if (window.bookingData.checkInDate) {
      if (window.bookingData.checkInTime) {
        $("#summaryCheckIn").text(`${window.bookingData.checkInDate}, ${window.bookingData.checkInTime}`)
      } else {
        $("#summaryCheckIn").text(window.bookingData.checkInDate)
      }
    }

    if (window.bookingData.checkOutDate) {
      if (window.bookingData.checkOutTime) {
        $("#summaryCheckOut").text(`${window.bookingData.checkOutDate}, ${window.bookingData.checkOutTime}`)
      } else {
        $("#summaryCheckOut").text(window.bookingData.checkOutDate)
      }
    }

    // Update total price
    calculateTotalPrice()
  }

  // Payment method handling
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

  // Handle payment type change
  $(document).on("change", "#paymentTypeSelect", () => {
    updatePaymentAmounts()
  })

  // Payment form validation - UPDATED with debugging and improved validation
  function validatePaymentForm() {
    const referenceNo = $('input[name="reference_no"]').val().trim()
    const paymentProof = $('input[name="payment_proof"]').prop("files").length

    console.log("Reference No:", referenceNo)
    console.log("Payment Proof Files:", paymentProof)

    // Enable button only if both fields are filled
    if (referenceNo && paymentProof > 0) {
      $("#proceed-to-waiver").prop("disabled", false)
      console.log("Button enabled")
    } else {
      $("#proceed-to-waiver").prop("disabled", true)
      console.log("Button disabled")
    }
  }

  // Attach validation handlers - UPDATED with more robust event binding
  $(document).on("input", 'input[name="reference_no"]', validatePaymentForm)
  $(document).on("change", 'input[name="payment_proof"]', validatePaymentForm)

  // Initialize payment modal - UPDATED to ensure validation runs when modal is shown
  $("#petPaymentModal").on("show.bs.modal", () => {
    // Reset form
    $("#paymentForm")[0].reset()
    $("#proceed-to-waiver").prop("disabled", true)

    // Show default QR code (Maya)
    $("#gcashQR").hide()
    $("#mayaQR").show()

    // Set default payment type to full payment
    $("#paymentTypeSelect").val("full")

    // Update payment amounts
    updatePaymentAmounts()
  })

  // Make sure validation runs after modal is fully shown
  $("#petPaymentModal").on("shown.bs.modal", () => {
    validatePaymentForm()
  })

  // Handle proceed to waiver button - COMPLETELY REWRITTEN
  $("#proceed-to-waiver").on("click", () => {
    console.log("Proceed to waiver clicked")

    // First hide the payment modal completely
    $("#petPaymentModal").modal("hide")

    // Wait for the payment modal to fully hide before showing the waiver form
    setTimeout(() => {
      console.log("Showing waiver form now")
      $("#waiverForm").modal("show")
    }, 500)
  })

  // Add direct access to waiver form for testing
  // Uncomment this line to test if the waiver form works directly
  // $("#waiverForm").modal("show")

  // Handle complete booking button
  $("#complete-booking").on("click", function () {
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
    formData.append("booking_data", JSON.stringify(window.bookingData))

    // Get the transaction number from the payment modal and add it to the form data
    const transactionNo = $(".transaction-no").text().replace("Transaction No. ", "").trim()
    formData.append("transaction_id", transactionNo)

    // Add payment type to the form data
    formData.append("payment_type", $("#paymentTypeSelect").val())

    // Add visible pets data
    formData.append("visible_pets", $("#visiblePetsData").val())

    $.ajax({
      type: "POST",
      url: "process-booking.php",
      data: formData,
      processData: false,
      contentType: false,
      dataType: "json",
      success: (response) => {
        if (response.success) {
          alert("Booking completed successfully!")
          $("#waiverForm").modal("hide")

          // Show the book-1 section
          $(".main-schedule-options").fadeOut(() => {
            $(".book-1").fadeIn()
          })

          // Redirect to confirmation page or refresh after a delay
          setTimeout(() => {
            window.location.href = "book-pet-hotel.php"
          }, 1500)
        } else {
          alert("Error: " + (response.message || "Unknown error"))
          // Re-enable the button if there's an error
          $("#complete-booking").prop("disabled", false).text("Complete Booking")
        }
      },
      error: (xhr, status, error) => {
        console.error("AJAX Error:", error)
        alert("An error occurred while processing your booking. Please try again later.")
        // Re-enable the button if there's an error
        $("#complete-booking").prop("disabled", false).text("Complete Booking")
      },
    })
  })

  // Handle payment button click - MODIFIED TO ONLY FETCH VISIBLE PETS
  $("#proceedToPaymentBtn").on("click", (e) => {
    // Check if dates are selected
    if (!window.bookingData.checkInDate || !window.bookingData.checkOutDate) {
      alert("Please select check-in and check-out dates.")
      return
    }

    // Get only the pets that are currently visible in the table
    const visiblePets = []

    // Loop through each row in the pet table
    $("#petTableBody tr").each(function () {
      const petName = $(this).find(".petSelect option:selected").text()

      // Skip rows where no pet is selected
      if (!petName || petName === "Choose Pet") {
        return
      }

      // Get all the data from the row
      const petData = {
        name: petName,
        breed: $(this).find("[data-label='Breed']").text(),
        age: $(this).find("[data-label='Age']").text(),
        gender: $(this).find("[data-label='Gender']").text(),
        size: $(this).find("[data-label='Size']").text(),
        price: Number.parseFloat($(this).find("[data-label='Price']").text().replace("₱", "").trim()) || 0,
      }

      // Add to visible pets array
      visiblePets.push(petData)
    })

    // Check if any pets are visible in the table
    if (visiblePets.length === 0) {
      alert("Please select at least one pet before proceeding to payment.")
      return
    }

    // Update the booking data with only the visible pets
    window.bookingData.pets = visiblePets

    // Store the visible pets data in the hidden form field
    $("#visiblePetsData").val(JSON.stringify(visiblePets))

    // Update the payment summary with only the visible pets
    updateBookingSummary()

    // Show the payment modal
    $("#petPaymentModal").modal("show")
  })

  // Add a direct test function to check if the waiver form can be shown
  window.testWaiverForm = () => {
    console.log("Testing waiver form")
    $("#waiverForm").modal("show")
  }
})
