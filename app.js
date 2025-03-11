// Select all FAQ items
const faqs = document.querySelectorAll(".faq");

faqs.forEach((faq) => {
    const icon = faq.querySelector(".icon"); // Select the icon inside each FAQ
    const answer = faq.querySelector(".answer"); // Select the answer inside each FAQ
    
    // Add a click event to toggle visibility of the answer and the icon state
    faq.addEventListener("click", () => {
        // Toggle the 'active' class on the faq
        faq.classList.toggle("active");
        
        // Toggle the max-height of the answer for accordion effect
        if (faq.classList.contains("active")) {
            answer.style.maxHeight = answer.scrollHeight + "px"; // Expand
        } else {
            answer.style.maxHeight = "0"; // Collapse
        }

        // Toggle the plus/minus icon visibility
        icon.classList.toggle('active');
    });
});
