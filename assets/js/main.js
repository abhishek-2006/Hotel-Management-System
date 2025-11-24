// Mobile Menu Toggle
document.addEventListener('DOMContentLoaded', () => {
    const menuToggle = document.querySelector('.menu-toggle');
    const navLinks = document.querySelector('.nav-links');

    if (menuToggle && navLinks) {
        menuToggle.addEventListener('click', () => {
            navLinks.classList.toggle('active');
        });
    }

    // --- Date Validation from index.php ---
    const checkInInput = document.getElementById('check_in_date');
    const checkOutInput = document.getElementById('check_out_date');
    
    if (checkInInput && checkOutInput) {
        const today = new Date().toISOString().split('T')[0];
        
        checkInInput.setAttribute('min', today);
        checkOutInput.setAttribute('min', today);

        checkInInput.addEventListener('change', function() {
            if (checkOutInput.value < this.value) {
                checkOutInput.value = this.value;
            }
            checkOutInput.setAttribute('min', this.value);
        });
    }

    // --- Dynamic Room Loading (Lazy Load / Infinite Scroll) ---

    // Get metadata from rooms.php hidden fields
    const offsetInput = document.getElementById('room-offset');
    const totalInput = document.getElementById('room-total');
    const limitInput = document.getElementById('room-limit');
    const container = document.getElementById('rooms-container');
    const loadingIndicator = document.getElementById('loading-indicator');

    // Check if we are on the rooms page and have dynamic elements
    if (offsetInput && totalInput && limitInput && container && loadingIndicator) {
        let currentOffset = parseInt(offsetInput.value);
        const totalRooms = parseInt(totalInput.value);
        const limit = parseInt(limitInput.value);
        let isLoading = false; // Flag to prevent multiple simultaneous loads

        // Function to load the next set of rooms via AJAX
        const loadMoreRooms = () => {
            if (isLoading || currentOffset >= totalRooms) {
                // Stop loading if already busy or all rooms are loaded
                return;
            }

            isLoading = true;
            loadingIndicator.style.display = 'block';

            // Use Fetch API for modern AJAX request
            fetch('../ajax/load_rooms.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `offset=${currentOffset}&limit=${limit}` // Send current offset
            })
            .then(response => response.text())
            .then(data => {
                if (data.trim() !== "") {
                    // Append new rooms to the container
                    container.insertAdjacentHTML('beforeend', data);
                    currentOffset += limit;
                    offsetInput.value = currentOffset; // Update offset for next request
                } else {
                    // No more rooms left
                    window.removeEventListener('scroll', handleScroll);
                }
            })
            .catch(error => console.error('Error loading rooms:', error))
            .finally(() => {
                isLoading = false;
                loadingIndicator.style.display = 'none';
            });
        };

        // Scroll Handler Function
        const handleScroll = () => {
            // Check if user is near the bottom of the page (e.g., last 1000px)
            const scrollHeight = document.documentElement.scrollHeight;
            const scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
            const clientHeight = document.documentElement.clientHeight;

            if (scrollTop + clientHeight >= scrollHeight - 1000) {
                loadMoreRooms();
            }
        };

        // Attach the scroll event listener
        window.addEventListener('scroll', handleScroll);
        
        // Initial check in case the content doesn't fill the screen
        handleScroll();
    }
});