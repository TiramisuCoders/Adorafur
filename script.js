let slideIndex = 0;
const slides = document.getElementsByClassName("slide"); // Get all slides
const totalSlides = slides.length;

function showSlides() {
    // Remove 'active' and 'previous' classes from all slides
    for (let i = 0; i < totalSlides; i++) {
        slides[i].classList.remove("active");
        slides[i].classList.remove("previous");
    }

    // Increment slideIndex and loop back if it exceeds totalSlides
    slideIndex++;
    if (slideIndex >= totalSlides) {
        slideIndex = 0; // Restart from the first slide
    }

    // Set the current slide to 'active' and the previous slide to 'previous'
    const currentSlide = slides[slideIndex];
    const previousSlide = slides[(slideIndex - 1 + totalSlides) % totalSlides];

    currentSlide.classList.add("active");
    previousSlide.classList.add("previous");

    // Change slide every 3 seconds
    setTimeout(showSlides, 5000);
}

// Start the slideshow
showSlides();