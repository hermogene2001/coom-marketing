<!-- Banner slider -->
<?php
// Fetch active sliders from the database
$slider_query = "SELECT id, title, description, image_name, order_number 
                FROM banner_sliders 
                WHERE position = 'main' AND is_active = 1 
                ORDER BY order_number ASC";
$slider_result = $conn->query($slider_query);

// Check if we have slider images
$has_sliders = $slider_result && $slider_result->num_rows > 0;
?>

<div class="banner-slider-container">
    <?php if ($has_sliders): ?>
        <div class="banner-slider">
            <?php 
            // Counter for slider indicators
            $slide_count = 0;
            while ($slide = $slider_result->fetch_assoc()): 
                $slide_count++;
            ?>
                <div class="slide" id="slide-<?php echo $slide['id']; ?>">
                    <img src="../uploads/banners/<?php echo htmlspecialchars($slide['image_name']); ?>" 
                         alt="<?php echo htmlspecialchars($slide['title']); ?>" 
                         class="slider-image">
                    <?php if (!empty($slide['title']) || !empty($slide['description'])): ?>
                        <div class="slide-content">
                            <?php if (!empty($slide['title'])): ?>
                                <h2><?php echo htmlspecialchars($slide['title']); ?></h2>
                            <?php endif; ?>
                            <?php if (!empty($slide['description'])): ?>
                                <p><?php echo htmlspecialchars($slide['description']); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
            
            <!-- Navigation arrows -->
            <button class="slider-nav prev" onclick="moveSlide(-1)">&#10094;</button>
            <button class="slider-nav next" onclick="moveSlide(1)">&#10095;</button>
            
            <!-- Slide indicators -->
            <div class="slider-indicators">
                <?php for ($i = 1; $i <= $slide_count; $i++): ?>
                    <span class="indicator" onclick="goToSlide(<?php echo $i; ?>)"></span>
                <?php endfor; ?>
            </div>
        </div>
    <?php else: ?>
        <!-- Fallback if no sliders are available -->
        <img src="/api/placeholder/1200/300" alt="Banner Placeholder" class="banner-image">
    <?php endif; ?>
</div>

<!-- JavaScript for Slider Functionality -->
<script>
let currentSlide = 1;
const totalSlides = <?php echo $has_sliders ? $slide_count : 0; ?>;

// Initialize the slider
document.addEventListener("DOMContentLoaded", function() {
    if (totalSlides > 0) {
        showSlide(currentSlide);
        // Auto-rotate slides every 5 seconds
        setInterval(() => {
            moveSlide(1);
        }, 5000);
    }
});

function moveSlide(n) {
    showSlide(currentSlide += n);
}

function goToSlide(n) {
    showSlide(currentSlide = n);
}

function showSlide(n) {
    const slides = document.querySelectorAll('.slide');
    const indicators = document.querySelectorAll('.indicator');
    
    if (n > slides.length) { currentSlide = 1; }
    if (n < 1) { currentSlide = slides.length; }
    
    // Hide all slides
    for (let i = 0; i < slides.length; i++) {
        slides[i].style.display = "none";
    }
    
    // Remove active class from all indicators
    for (let i = 0; i < indicators.length; i++) {
        indicators[i].classList.remove("active");
    }
    
    // Show the current slide and activate its indicator
    slides[currentSlide - 1].style.display = "block";
    indicators[currentSlide - 1].classList.add("active");
}
</script>

<!-- CSS for Slider -->
<style>
.banner-slider-container {
    display: flex;
    /* flex-direction: column; */
    /* overflow: visible; */
    width: 80%;
    height: 40%;
    max-width: 1200px;
    margin: 0 auto;
    overflow: hidden;
}

.banner-slider {
    position: relative;
}

.slide {
    display: none;
    width: 100%;
    position: relative;
}

.slider-image {
    width: 100%;
    height: auto;
    display: block;
}

.slide-content {
    position: absolute;
    bottom: 20px;
    left: 20px;
    background-color: rgba(0, 0, 0, 0.6);
    color: white;
    padding: 15px;
    max-width: 50%;
    border-radius: 5px;
}

.slide-content h2 {
    margin: 0 0 10px 0;
    font-size: 1.5rem;
}

.slide-content p {
    margin: 0;
    font-size: 1rem;
}

.slider-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background-color: rgba(0, 0, 0, 0.5);
    color: white;
    border: none;
    cursor: pointer;
    padding: 15px;
    font-size: 18px;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
    transition: background-color 0.3s;
}

.slider-nav:hover {
    background-color: rgba(0, 0, 0, 0.8);
}

.prev {
    left: 15px;
}

.next {
    right: 15px;
}

.slider-indicators {
    position: absolute;
    bottom: 15px;
    width: 100%;
    text-align: center;
    z-index: 10;
}

.indicator {
    display: inline-block;
    width: 12px;
    height: 12px;
    margin: 0 5px;
    background-color: rgba(255, 255, 255, 0.5);
    border-radius: 50%;
    cursor: pointer;
    transition: background-color 0.3s;
}

.indicator.active {
    background-color: white;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .slide-content {
        max-width: 80%;
        bottom: 10px;
        left: 10px;
        padding: 10px;
    }
    
    .slide-content h2 {
        font-size: 1.2rem;
    }
    
    .slider-nav {
        padding: 10px;
        font-size: 16px;
        width: 40px;
        height: 40px;
    }
}
</style>