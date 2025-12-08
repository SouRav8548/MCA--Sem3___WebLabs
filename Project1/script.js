/* --- JAVASCRIPT --- */

// 1. PLAIN JAVASCRIPT: Form Validation
document.addEventListener("DOMContentLoaded", function() {
    
    const form = document.getElementById('registrationForm');
    
    form.addEventListener('submit', function(e) {
        // Prevent form submission until validated
        e.preventDefault();
        
        let isValid = true;
        
        // Get values
        const email = document.getElementById('email').value;
        const mobile = document.getElementById('mobile').value;
        const address = document.getElementById('address').value;

        // --- Email Validation ---
        // Regex: Standard pattern for email
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(email)) {
            alert("Invalid Email Format!");
            isValid = false;
        }

        // --- Mobile Validation ---
        // Regex: Exactly 10 digits
        const mobilePattern = /^\d{10}$/;
        if (!mobilePattern.test(mobile)) {
            alert("Mobile number must be exactly 10 digits.");
            isValid = false;
        }

        // --- Address Validation ---
        // Regex: Alphanumeric and spaces only (No special symbols like @, #, $, /)
        const addressPattern = /^[a-zA-Z0-9\s]+$/;
        if (!addressPattern.test(address)) {
            alert("Address cannot contain special characters or symbols.");
            isValid = false;
        }

        // If all validations pass
        if (isValid) {
            alert("Form Submitted Successfully!");
            form.reset();
        }
    });
});

// 2. JQUERY: Interactive Features
$(document).ready(function() {

    // --- Feature A: Random Background Color on Button Click ---
    $('#bgBtn').click(function() {
        // Generate random RGB values
        var r = Math.floor(Math.random() * 256);
        var g = Math.floor(Math.random() * 256);
        var b = Math.floor(Math.random() * 256);
        
        // Apply to body
        $('body').css('background-color', 'rgb(' + r + ',' + g + ',' + b + ')');
    });

    // --- Feature B: Dynamic Cover Picture Change (Interval) ---
    const images = [
        "https://picsum.photos/800/250?random=1",
        "https://picsum.photos/800/250?random=2",
        "https://picsum.photos/800/250?random=3",
        "https://picsum.photos/800/250?random=4"
    ];
    
    let currentIndex = 0;

    // Change image every 3 seconds (3000ms)
    setInterval(function() {
        currentIndex = (currentIndex + 1) % images.length;
        
        // FadeOut, Change Source, FadeIn for smooth effect
        $('#bannerImage').fadeOut(500, function() {
            $(this).attr('src', images[currentIndex]).fadeIn(500);
        });
        
    }, 3000);
});