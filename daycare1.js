// Initialize booking data object
window.bookingData = {
  pets: [],
  date: "",
  dropOffTime: "",
  pickUpTime: "",
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
  let selectedDate = null

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
      if (selectedDate && formattedDate === selectedDate.toISOString().split("T")[0]) {
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
  }

  // Handle date click
  function handleDateClick(date, element) {
    // Clear previous selection
    $(".day").removeClass("selected-date")

    // Select new date
    selectedDate = date
    element.addClass("selected-date")

    // Update booking data
    window.bookingData.date = date.toLocaleDateString("en-US", {
      month: "long",
      day: "numeric",
      year: "numeric",
    })

    // Enable time selection after date selection
    $(".checkin-out").removeClass("disabled-section")

    // Update summary
    updateBookingSummary()
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

  // Handle drop-off and pick-up time selection
  $(".check-in-time").click(function () {
    const selectedTime = $(this).text()
    $("#checkInMenu").text(selectedTime)
    window.bookingData.dropOffTime = selectedTime

    // Get the selected hour for automatic check-out time
    const timeComponents = selectedTime.split(":")
    let hour = Number.parseInt(timeComponents[0])
    const isPM = selectedTime.includes("PM")
    const isAM = selectedTime.includes("AM")

    // Convert to 24-hour format for calculation
    if (isPM && hour !== 12) {
      hour += 12
    } else if (isAM && hour === 12) {
      hour = 0
    }

    // Calculate check-out time (3 hours later)
    let checkoutHour = hour + 3
    let checkoutPeriod = isPM ? "PM" : "AM"

    // Handle period change
    if (checkoutHour >= 12) {
      checkoutPeriod = "PM"
      if (checkoutHour > 12) {
        checkoutHour -= 12
      }
    }

    // Format the checkout time
    const checkoutTime = `${checkoutHour}:00 ${checkoutPeriod}`

    // Find and select the matching checkout time in the dropdown
    let matchFound = false
    $(".check-out-time").each(function () {
      if ($(this).text() === checkoutTime) {
        // Simulate click on this option
        $(this).click()
        matchFound = true
        return false // Break the loop
      }
    })

    // If no exact match found, select the next available time
    if (!matchFound) {
      let nextTimeFound = false
      $(".check-out-time").each(function () {
        const thisTime = $(this).text()
        const thisHour = Number.parseInt(thisTime.split(":")[0])
        const thisIsPM = thisTime.includes("PM")

        let thisHour24 = thisHour
        if (thisIsPM && thisHour !== 12) {
          thisHour24 += 12
        } else if (!thisIsPM && thisHour === 12) {
          thisHour24 = 0
        }

        // If this time is after the calculated checkout time
        if (
          (thisIsPM && !checkoutPeriod.includes("PM")) ||
          (thisIsPM === checkoutPeriod.includes("PM") && thisHour >= checkoutHour)
        ) {
          $(this).click()
          nextTimeFound = true
          return false // Break the loop
        }
      })

      // If still no time found, select the last available time
      if (!nextTimeFound) {
        $(".check-out-time").last().click()
      }
    }

    updateBookingSummary()
  })

  // Remove the manual check-out time selection logic since it's now automated
  $(".check-out-time").click(function () {
    const selectedTime = $(this).text()
    $("#checkOutMenu").text(selectedTime)
    window.bookingData.pickUpTime = selectedTime
    updateBookingSummary()
    checkTimeSelection()
  })

  // Check if both times are selected
  function checkTimeSelection() {
    if (window.bookingData.dropOffTime && window.bookingData.pickUpTime) {
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

        // Set price based on pet size - Daycare prices
        let price = 0
        switch (petDetails.pet_size) {
          case "Cat":
            price = 300
            break
          case "Small":
            price = 400
            break
          case "Regular":
            price = 450
            break
          case "Large":
            price = 500
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
  window.calculateTotalPrice = () => {
    let totalPrice = 0

    // Calculate price based on selected pets (for daycare, it's just a single day)
    if (window.bookingData.pets.length > 0) {
      window.bookingData.pets.forEach((pet) => {
        totalPrice += pet.price || 0
      })
    }

    // Update the total price display
    $("#summaryTotalAmount").text(`₱ ${totalPrice.toFixed(2)}`)
    $("#summaryRemainingBalance").text(`₱ ${totalPrice.toFixed(2)}`)

    return totalPrice
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
                    <div class="info-row"><span class="label">Age:</span><span class="value">${pet.age ? pet.age : ""}</span></div>
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
                            <div class="info-row"><span class="label">Age:</span><span class="value">${pet.age ? pet.age : ""}</span></div>
                            ${index < window.bookingData.pets.length - 1 ? "<hr>" : ""}
                        </div>
                    `
        })

        // Update the pet details section
        $("#petSummaryDetails").html(petDetailsHtml)
      }
    }

    // Update date if available
    if (window.bookingData.date) {
      // Update drop-off time display
      if (window.bookingData.dropOffTime) {
        $("#summaryCheckIn").text(`${window.bookingData.date}, ${window.bookingData.dropOffTime}`)
      } else {
        $("#summaryCheckIn").text(window.bookingData.date)
      }

      // Update pick-up time display
      if (window.bookingData.pickUpTime) {
        $("#summaryCheckOut").text(`${window.bookingData.date}, ${window.bookingData.pickUpTime}`)
      } else {
        $("#summaryCheckOut").text(window.bookingData.date)
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

  // Payment form validation
  function validatePaymentForm() {
    const referenceNo = $('input[name="reference_no"]').val().trim()
    const paymentProof = $('input[name="payment_proof"]').prop("files").length

    // Enable button only if both fields are filled
    if (referenceNo && paymentProof > 0) {
      $("#proceed-to-waiver").prop("disabled", false)
    } else {
      $("#proceed-to-waiver").prop("disabled", true)
    }
  }

  // Attach validation handlers
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
  })

  // Handle proceed to waiver button
  $("#proceed-to-waiver").on("click", () => {
    $("#petPaymentModal").modal("hide")
    setTimeout(() => {
      $("#waiverForm").modal("show")
    }, 500)
  })

  // Handle complete booking button
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
        // Redirect to confirmation page or refresh
        window.location.href = "book-pet-hotel.php"
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
    // Check if date is selected
    if (!window.bookingData.date) {
      alert("Please select a date for daycare.")
      return
    }

    // Check if times are selected
    if (!window.bookingData.dropOffTime || !window.bookingData.pickUpTime) {
      alert("Please select drop-off and pick-up times.")
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
})

