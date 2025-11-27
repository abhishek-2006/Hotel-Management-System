<?php 
error_reporting(E_ALL);
$PROJECT_ROOT = '/Hotel%20Management%20system'; 
include('includes/header.php'); 
?>

<div class="container about-page-container">
    
    <section class="about-hero text-center">
        <h1>Welcome to The Citadel Retreat</h1>
        <p class="lead-text">Your exclusive sanctuary in the city. We are defined by impeccable security, unparalleled luxury, and the commitment to making your stay truly memorable.</p>
    </section>

    <section class="story-section">
        <h2 class="text-center">A Legacy of Secure Luxury</h2>
        <div class="story-content grid-2">
            <div>
                <p>The Citadel Retreat was established for discerning travelers who demand both supreme comfort and absolute privacy. Our architecture is inspired by timeless strength, yet our service is entirely modern. We offer a true retreat where you can conduct business or simply relax, knowing every detail of your security and comfort has been expertly handled.</p>
                <p>We combine cutting-edge technology with traditional, personalized hospitality. Our integrated booking system ensures your access to our exclusive rooms and dining is instant, seamless, and protected from the moment you make a reservation.</p>
            </div>
                <div class="image-box" id="image-box">
                    <img src="assets/images/about.jpg" alt="About The Citadel Retreat"/>
            </div>
        </div>
    </section>

    <section class="values-section">
        <h2 class="text-center">Our Core Pillars</h2>
        <div class="values-grid grid-3">
            <div class="card value-card">
                <h3><i class="fas fa-shield-alt"></i> Absolute Privacy</h3>
                <p>We guarantee a level of security and discretion unmatched in urban hospitality, making our retreat your personal haven.</p>
            </div>
            <div class="card value-card">
                <h3><i class="fas fa-gem"></i> Impeccable Standards</h3>
                <p>Every room, every meal, and every interaction reflects the highest global standards of luxury and quality.</p>
            </div>
            <div class="card value-card">
                <h3><i class="fas fa-utensils"></i> Exclusive Dining</h3>
                <p>Access our signature restaurants, offering bespoke menus and private dining options, reservable through your personalized dashboard.</p>
            </div>
        </div>
    </section>

    <section class="cta-section text-center">
        <h2>Secure Your Experience at The Citadel Retreat</h2>
        <a href="<?= $PROJECT_ROOT ?>/rooms.php" class="btn btn-action">View Exclusive Rooms</a>
        <a href="<?= $PROJECT_ROOT ?>/contact.php" class="btn btn-secondary">Request Concierge Service</a>
    </section>

</div>

<?php include('includes/footer.php'); ?>