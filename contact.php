<?php 
$PROJECT_ROOT = '/Hotel%20Management%20system'; 
include('includes/header.php'); 
?>

<div class="container contact-page-container">
    
    <section class="contact-header text-center">
        <h1>Contact The Citadel Retreat</h1>
        <p class="lead-text">Our concierge team is available 24/7 to assist with your inquiries, bookings, and special requests.</p>
    </section>

    <section class="contact-grid grid-main">
        
        <div class="contact-form-area card">
            <h2 class="form-title">Send Us a Message</h2>
            <p>We aim to respond to all inquiries within 24 hours.</p>
            
            <form action="<?= $PROJECT_ROOT ?>/bookings/contact_process.php" method="POST" class="contact-form">
                
                <div class="form-group">
                    <label for="name">Your Full Name</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="subject">Subject</label>
                    <input type="text" id="subject" name="subject" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="message">Your Message</label>
                    <textarea id="message" name="message" class="form-control" rows="5" required></textarea>
                </div>

                <button type="submit" class="btn btn-action btn-full-width">
                    Submit Inquiry
                </button>
            </form>
        </div>

        <div class="contact-info-area">
            
            <div class="info-card card">
                <h3>Direct Contacts</h3>
                <div class="contact-detail">
                    <i class="fas fa-phone-alt"></i>
                    <div>
                        <strong>Concierge Service</strong>
                        <p>+19 (555) 777-1111 (24/7)</p>
                    </div>
                </div>
                <div class="contact-detail">
                    <i class="fas fa-envelope"></i>
                    <div>
                        <strong>General Inquiries</strong>
                        <p>info@citadelretreat.com</p>
                    </div>
                </div>
                <div class="contact-detail">
                    <i class="fas fa-map-marker-alt"></i>
                    <div>
                        <strong>Our Address</strong>
                        <p>101 Citadel Blvd, City Center, 90210</p>
                    </div>
                </div>
            </div>

            <div class="map-placeholder">
                <p>Location Map Loading...</p>
                </div>
        </div>
    </section>

</div>

<?php include('includes/footer.php'); ?>