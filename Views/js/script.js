document.addEventListener('DOMContentLoaded', () => {
    const navLinks = document.querySelectorAll('.nav-link');

    // Set the "Home" link as active by default
    navLinks.forEach(nav => nav.classList.remove('active')); // Ensure all are inactive first
    document.querySelector('a[href="index.php?page=home#home-section"]').classList.add('active');

    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            // Remove 'active' class from all nav links
            navLinks.forEach(nav => nav.classList.remove('active'));

            // Add 'active' class to the clicked nav link
            link.classList.add('active');
        });
    });
});





function redirectToSection() {
    window.location.href = 'index.php';
}




document.addEventListener("DOMContentLoaded", function () {
    const visitorCountElement = document.getElementById("visitor-count");
    const storageKey = "homepage-visit-count";


    // Get the current visit count from localStorage or initialize it
    let visitCount = localStorage.getItem(storageKey);
    visitCount = visitCount ? parseInt(visitCount, 10) : 0;


    // Increment the count
    visitCount += 1;


    // Save the updated count back to localStorage
    localStorage.setItem(storageKey, visitCount);


    // Animate the count
    function animateVisitorCount(targetCount) {
        let startCount = 0;
        const duration = 2000; // Duration of animation in milliseconds
        const increment = Math.ceil(targetCount / (duration / 50)); // Increment value per frame


        const interval = setInterval(() => {
            startCount += increment;
            if (startCount >= targetCount) {
                startCount = targetCount;
                clearInterval(interval);
            }
            visitorCountElement.textContent = startCount.toLocaleString();
        }, 50);
    }


    // Trigger the animation only when the visitor count is in view
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    animateVisitorCount(visitCount);
                    observer.disconnect(); // Stop observing after the animation
                }
            });
        },
        {
            threshold: 0.5, // Trigger when 50% of the element is visible
        }
    );


    observer.observe(visitorCountElement);
});
