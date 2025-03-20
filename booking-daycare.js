$(document).ready(() => {
    let selectedPet = null
    let selectedDate = null
    let selectedPets = [] // Array to store selected pets
    let isLoggedIn = false // Will be set based on server response
  
    // Initially hide pet info and disable sections
    $(".pet-information-dog, .pet-information-cat").hide()
    $(".pet-info h3, .pet-info h6").hide()
    $(".calendar, .checkin-out, .book").addClass("disabled-section")
    $(".book-1").hide()
  
    console.log("Page loaded. Waiting for pet selection...")
  
    // Initialize calendar
    initCalendar()
  
    // Handle pet selection from dropdown
    $("#petSelectionMenu + .dropdown-menu .dropdown-item").click(function () {
      var selectedPetType = $(this).text()
  
      console.log("Pet selected:", selectedPetType)
  
      // Update button text
      $("#petSelectionMenu").text(selectedPetType)
  
      // Hide all pet information first
      $(".pet-information-dog, .pet-information-cat").hide()
  
      // Show the selected pet's information
      if (selectedPetType === "Dog") {
        $(".pet-information-dog").fadeIn()
      } else if (selectedPetType === "Cat") {
        $(".pet-information-cat").fadeIn()
      }
  
      // Enable calendar when a pet is selected
      $(".calendar").removeClass("disabled-section")
  
      // Store selection via AJAX
      $.ajax({
        type: "POST",
        url: window.location.href,
        data: { pet_type: selectedPetType },
        dataType: "json",
        success: (response) => {
          if (response.success) {
            console.log(response.message)
          }
        },
        error: () => {
          console.log("Error sending pet selection via AJAX.")
        },
      })
    })
  
    // Handle pet selection within pet-info section
    $(".pet-info").click(function () {
      const img = $(this).find("img")
  
      if (selectedPet === this) {
        // Deselect pet
        $(this).removeClass("selected")
        swapImage(img)
        $(this).find("h3, h6").fadeOut()
        selectedPet = null
  
        console.log("Pet deselected.")
      } else {
        // Deselect previous pet
        if (selectedPet) {
          swapImage($(selectedPet).find("img"))
          $(selectedPet).removeClass("selected")
          $(selectedPet).find("h3, h6").fadeOut()
        }
  
        // Select new pet
        $(this).addClass("selected")
        swapImage(img)
        $(this).find("h3, h6").fadeIn()
        selectedPet = this
  
        console.log("Pet selected.")
      }
    })
  
    // Function to swap images
    function swapImage(img) {
      const tempSrc = img.attr("src")
      img.attr("src", img.attr("data-selected-src"))
      img.attr("data-selected-src", tempSrc)
    }
  
    // Initialize calendar
    function initCalendar() {
      const date = new Date()
      let currentMonth = date.getMonth()
      let currentYear = date.getFullYear()
  
      updateCalendar(currentMonth, currentYear)
  
      // Previous month button
      $("#prevMonth").click(() => {
        currentMonth--
        if (currentMonth < 0) {
          currentMonth = 11
          currentYear--
        }
        updateCalendar(currentMonth, currentYear)
      })
  
      // Next month button
      $("#nextMonth").click(() => {
        currentMonth++
        if (currentMonth > 11) {
          currentMonth = 0
          currentYear++
        }
        updateCalendar(currentMonth, currentYear)
      })
    }
  
    // Update calendar with new month/year
    function updateCalendar(month, year) {
      const monthNames = [
        "January",
        "February",
        "March",
        "April",
        "May",
        "June",
        "July",
        "August",
        "September",
        "October",
        "November",
        "December",
      ]
      $("#month").text(monthNames[month])
      $("#year").text(year)
  
      const firstDay = new Date(year, month, 1).getDay()
      const daysInMonth = new Date(year, month + 1, 0).getDate()
      const today = new Date()
  
      let html = ""
  
      // Add empty cells for days before the first day of the month
      for (let i = 0; i < firstDay; i++) {
        html += '<div class="day empty"></div>'
      }
  
      // Add days of the month
      for (let i = 1; i <= daysInMonth; i++) {
        const date = new Date(year, month, i)
        const dateStr = formatDate(date)
  
        // Check if date is in the past
        const isPast = date < new Date(today.getFullYear(), today.getMonth(), today.getDate())
  
        // Check if date is today
        const isToday =
          date.getDate() === today.getDate() &&
          date.getMonth() === today.getMonth() &&
          date.getFullYear() === today.getFullYear()
  
        let classes = "day"
        if (isPast) classes += " disabled past"
        if (isToday) classes += " today"
  
        html += `<div class="${classes}" data-date="${dateStr}">${i}</div>`
      }
  
      $("#days").html(html)
  
      // Attach click event to days
      attachDayClickEvent()
    }
  
    // Format date as YYYY-MM-DD
    function formatDate(date) {
      const year = date.getFullYear()
      const month = String(date.getMonth() + 1).padStart(2, "0")
      const day = String(date.getDate()).padStart(2, "0")
      return `${year}-${month}-${day}`
    }
  
    // Attach click event to calendar days
    function attachDayClickEvent() {
      $(".days-grid .day:not(.disabled)").click(function () {
        if ($(".calendar").hasClass("disabled-section")) return
  
        // Remove selected class from all days
        $(".days-grid .day").removeClass("selected")
  
        // Add selected class to clicked day
        $(this).addClass("selected")
  
        // Store selected date
        selectedDate = $(this).data("date")
        console.log("Date selected:", selectedDate)
  
        // Enable time selection
        $(".checkin-out").removeClass("disabled-section")
  
        // Store selected date via AJAX and get available slots
        $.ajax({
          type: "POST",
          url: window.location.href,
          data: { selected_date: selectedDate },
          dataType: "json",
          success: (response) => {
            if (response.success) {
              console.log(response.message)
  
              // Update available slots
              $("#availableSlotCount").text(response.availableSlots)
            }
          },
          error: () => {
            console.log("Error sending date selection via AJAX.")
          },
        })
      })
    }
  
    // Handle check-in time selection
    $(".check-in-time").click(function () {
      if ($(".checkin-out").hasClass("disabled-section")) return
  
      const checkInTime = $(this).text()
      $("#checkInMenu").text(checkInTime)
  
      console.log("Check-in time selected:", checkInTime)
  
      // Send check-in time to server and get default check-out time
      $.ajax({
        type: "POST",
        url: window.location.href,
        data: { check_in_time: checkInTime },
        dataType: "json",
        success: (response) => {
          if (response.success) {
            console.log(response.message)
  
            // Update check-out dropdown with valid options (3+ hours after check-in)
            updateCheckOutOptions(checkInTime, response.defaultCheckOut)
          }
        },
        error: () => {
          console.log("Error sending check-in time via AJAX.")
        },
      })
    })
  
    // Update check-out time options based on check-in time
    function updateCheckOutOptions(checkInTime, defaultCheckOut) {
      // Parse check-in time
      let hour = Number.parseInt(checkInTime.split(":")[0])
      const isPM = checkInTime.includes("PM")
  
      // Convert to 24-hour format for calculation
      if (isPM && hour < 12) hour += 12
      else if (!isPM && hour === 12) hour = 0
  
      // Generate check-out options (at least 3 hours after check-in)
      let options = ""
      for (let i = hour + 3; i <= 19; i++) {
        // 7 PM is the latest check-out
        let displayHour = i
        let period = "AM"
  
        if (i >= 12) {
          period = "PM"
          if (i > 12) displayHour = i - 12
        }
  
        options += `<button class="dropdown-item check-out-time" type="button">${displayHour}:00 ${period}</button>`
      }
  
      // Update dropdown options
      $("#checkOutMenu + .dropdown-menu").html(options)
  
      // Set default check-out time
      $("#checkOutMenu").text(defaultCheckOut)
  
      // Attach click event to new check-out options
      $(".check-out-time").click(function () {
        $("#checkOutMenu").text($(this).text())
  
        // Enable book button
        $(".book").removeClass("disabled-section")
  
        // Store check-out time
        $.ajax({
          type: "POST",
          url: window.location.href,
          data: { check_out_time: $(this).text() },
          dataType: "json",
          success: (response) => {
            if (response.success) {
              console.log(response.message)
            }
          },
          error: () => {
            console.log("Error sending check-out time via AJAX.")
          },
        })
      })
  
      // Trigger click on default check-out time to enable book button
      $(".book").removeClass("disabled-section")
    }
  
    // Handle Book button click
    $(".book").click(function () {
      if ($(this).hasClass("disabled-section")) return
  
      console.log("Book button clicked.")
  
      // Check if user is logged in by attempting to get pets
      $.ajax({
        type: "POST",
        url: window.location.href,
        data: { get_pets: true },
        dataType: "json",
        success: (response) => {
          if (response.success) {
            // User is logged in, proceed with booking
            isLoggedIn = true
            console.log("User is logged in. Loading pet selection...")
  
            // Hide booking options and show pet selection
            $(".main-schedule-options").fadeOut(() => {
              $(".book-1").fadeIn()
  
              // Load user's pets
              loadUserPets()
            })
          } else if (response.requireLogin) {
            // User is not logged in, show login modal
            isLoggedIn = false
            console.log("User is not logged in. Showing login modal...")
            $("#loginModal").modal("show")
          }
        },
        error: () => {
          console.log("Error checking login status.")
        },
      })
    })
  
    // Load user's pets for dropdown
    function loadUserPets() {
      $.ajax({
        type: "POST",
        url: window.location.href,
        data: { get_pets: true },
        dataType: "json",
        success: (response) => {
          if (response.success) {
            console.log("Pets loaded:", response.pets)
  
            // Clear dropdown
            $("#petDropdown").html('<option value="">Select a pet</option>')
  
            // Add pets to dropdown
            response.pets.forEach((pet) => {
              $("#petDropdown").append(`<option value="${pet.pet_id}">${pet.pet_name} (${pet.pet_breed})</option>`)
            })
  
            // Attach change event to pet dropdown
            $("#petDropdown").change(function () {
              const petId = $(this).val()
              if (petId) {
                // Get pet details
                getPetDetails(petId)
              }
            })
          }
        },
        error: () => {
          console.log("Error loading pets.")
        },
      })
    }
  
    // Get pet details when selected from dropdown
    function getPetDetails(petId) {
      $.ajax({
        type: "POST",
        url: window.location.href,
        data: { selected_pet: petId },
        dataType: "json",
        success: (response) => {
          if (response.success) {
            console.log("Pet details:", response.petDetails)
  
            const pet = response.petDetails
  
            // Add pet to table
            addPetToTable(pet)
  
            // Reset dropdown
            $("#petDropdown").val("")
          }
        },
        error: () => {
          console.log("Error getting pet details.")
        },
      })
    }
  
    // Add pet to table
    function addPetToTable(pet) {
      // Check if pet is already in the table
      if (selectedPets.includes(pet.pet_id)) {
        alert("This pet is already selected.")
        return
      }
  
      // Add pet to selected pets array
      selectedPets.push(pet.pet_id)
  
      // Create row for pet
      const row = `
              <tr data-pet-id="${pet.pet_id}">
                  <td>${pet.pet_name}</td>
                  <td>${pet.pet_breed}</td>
                  <td>${pet.pet_age}</td>
                  <td>${pet.pet_gender}</td>
                  <td>${pet.pet_size}</td>
                  <td>₱${pet.service_rate}</td>
                  <td><button class="btn btn-sm btn-danger remove-pet">Remove</button></td>
              </tr>
          `
  
      // Insert row before pet selection row
      $(row).insertBefore("#petSelectionRow")
  
      // Attach click event to remove button
      $(".remove-pet")
        .last()
        .click(function () {
          const petId = $(this).closest("tr").data("pet-id")
  
          // Remove pet from selected pets array
          selectedPets = selectedPets.filter((id) => id !== petId)
  
          // Remove row
          $(this).closest("tr").remove()
        })
    }
  })
  
  