    document.addEventListener("DOMContentLoaded", function () {
    var preloader = document.getElementById("preloader");
    var img = document.querySelector("#preloader img");
    var mainContent = document.querySelector("main");

    var rotationAngle = 0;
    var rotationInterval = 3; 

    var rotateImage = function () {
        rotationAngle += 1; 
        img.style.transform = "rotate(" + rotationAngle + "deg)";

        if (rotationAngle >= 360) {
            clearInterval(rotationIntervalId); 
            preloader.style.opacity = 0;
            setTimeout(function () {
                preloader.style.display = "none";
                document.body.classList.add("loaded");
            }, 1000); 
        }
    };

    var rotationIntervalId = setInterval(rotateImage, (rotationInterval * 1000) / 360);

    var myCarousel = document.getElementById('carouselExampleSlidesOnly');
    var carousel = new bootstrap.Carousel(myCarousel, {
        interval: 3000 // Set interval to 4 seconds (4000 milliseconds)
    });

    var isMouseOver = false;

    myCarousel.addEventListener('mouseover', function () {
        isMouseOver = true;
    });

    myCarousel.addEventListener('mouseout', function () {
        isMouseOver = false;
    });

    // Simulate automatic slide transition even when the mouse is over the carousel
    setInterval(function () {
        if (!isMouseOver) {
            carousel.next();
        }
    }, 4000);

    // Set up initial state for tabs
    $(".tab:not(.tab-active)").hide();

    $('.tab-a').click(function(){  
        // Remove active class from all tabs
        $(".tab-a").removeClass('active-a');

        // Add active class to the clicked tab
        $(this).addClass('active-a');

        // Hide all tabs
        $(".tab").removeClass('tab-active').hide();

        // Show the product group associated with the clicked tab
        $(".tab[data-id='"+$(this).attr('data-id')+"']").addClass('tab-active').show();
    });

    // Initialize slick slider
    $('.slider-nav').slick({
        slidesToShow: 4,
        slidesToScroll: 1,
        dots: true,
        focusOnSelect: true
    });

    
    document.addEventListener("DOMContentLoaded", function() {
        const tabs = document.querySelectorAll('.tab-a');
        const tabContents = document.querySelectorAll('.tab');
    
        tabs.forEach((tab, index) => {
            tab.addEventListener('click', function() {
                // Remove active class from all tabs and tab contents
                tabs.forEach(tab => tab.classList.remove('active-a'));
                tabContents.forEach(content => content.classList.remove('tab-active'));
    
                // Add active class to the clicked tab and corresponding content
                tab.classList.add('active-a');
                tabContents[index].classList.add('tab-active');
            });
        });
    });
}); 










