<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="home.css">
    <script src="script.js" defer></script>
    <link rel="icon" type="image/png" href="Header-Pics/logo.png">
    <title>Adorafur Home</title>
</head>
<body>
    
        <?php include 'header.php'; ?>
   


    <div class="home">
    <div class="gradient">
        <div class="rectangle-4"></div>
        <div class="rectangle-7"></div>
    </div>

    
        
        <!-- Slideshow Section -->
        <div class="home-1">
            <div class="welcome-to-text">
                <div class="welcome-text">WELCOME TO</div>
                <div class="welcome-text">ADORAFUR HAPPY STAY!</div>
                <a href = "#second-scroll"><img class="scroll_button" src="Home-Pics/scroll_button.png"/></a>
            </div>

            <div class="slideshow-container">
                <!-- Slide 1 -->
                <div class="slide active">
                    <img src="Home-Pics/slideshow1.png" alt="Slide 1">
                </div>
                <!-- Slide 2 -->
                <div class="slide">
                    <img src="Home-Pics/slideshow2.png" alt="Slide 2">
                </div>
                <!-- Slide 3 -->
                <div class="slide">
                    <img src="Home-Pics/slideshow3.png" alt="Slide 3">
                </div>
            </div>
        </div>

        <!-- Second Scroll Section -->s
        <div class="second-scroll" >

            <div class="group-holder" id = "second-scroll"></div>

            <div class="second-scroll-title" id = "second-scroll-title">
                <div class="explore-the-care-options">EXPLORE THE CARE OPTIONS</div>
                <br />
                <div class="we-offer">WE OFFER</div>
                </div>
                
                <div class="container1">
                    <!-- Daycare Card -->
                    <button class="booking-card" onclick="window.location.href='booking-daycare.php'" style="background: url('Home-Pics/daycare.png') center; background-size: cover;">
                        <div class="booking-title">DAYCARE</div>
                        <div class="booking-desc">
                            Unleash fun and care where your pets play, stay, and thrive at our daycare service!
                        </div>
                    </button>

                    <!-- Pet Hotel Button -->
                    <button class="booking-card" onclick="window.location.href='book-pet-hotel.php'" style="background: url('Home-Pics/pet_hotel.png') center; background-size: cover;">
                        <div class="booking-title">PET HOTEL</div>
                        <div class="booking-desc">
                            Home away from home—where your pets enjoy luxury, love, and plenty of play!
                        </div>
                    </button>

                </div><!-- containter1 -->
            </div><!-- second scroll title -->
        </div><!-- second scroll -->

        <div class="incluPerks" id = "inclusions">
            <div class="inclusAndPerks1">
                <div class="iap-text">
                    <img src="hand.png" alt="" class="hand">
                    <h1 class="iap"> INCLUSION AND PERKS </h1>
                    <p class="iap-caption"> Looking for the best care for your pet? Check out the amazing inclusions and perks that make their stay with us unforgettable!</p>
                    <div class="learnmore">
                        <a href="#learnm"><h2 class="lm"> Learn More </h2></a>
                    </div>
                </div>
                <div class="iap-img">
                    <img src="Home-Pics/woof.png" alt="" class="woof">
                </div>
            </div>

            <div id="learnm" class="inclusAndPerks2">
                <div class="inclusions">
                    <div class="inclu-img">
                        <img src="Home-Pics/meow.png" alt="" class="meow">
                    </div>
                    <div class="inclu-text">
                        <h6 class="inclus"> INCLUSIONS </h6>
                        <div class="inclu1">
                            <div class="inclu1-1">
                                <img src="Home-Pics/daily_video_icon.png" alt="">
                                <p>Daily video and photo updates</p>
                            </div>
                            <div class="inclu1-2">
                                <img src="Home-Pics/ac_icon.png" alt="">
                                <p>Airconditioned facility</p>
                            </div>
                        </div>
                        <div class="inclu2">
                            <div class="inclu2-1">
                                <img src="Home-Pics/vit_and_med_icon.png" alt="">
                                <p>24/7 Pet Attendant Supervision</p>
                            </div>
                            <div class="inclu2-2">
                                <img src="Home-Pics/play_access_icon.png" alt="">
                                <p>Access to play care area</p>
                            </div>
                        </div>
                        <div class="inclu3">
                            <div class="inclu3-1">
                                <img src="Home-Pics/free_roam_icon.png" alt="">
                                <p>Free roam play with friends of the same sixe (not applicable to cats) </p>
                            </div>
                            <div class="inclu3-2">
                                <img src="Home-Pics/vit_and_med_icon.png" alt="">
                                <p>Vitamin and Medicin administrations (as needed)</p>
                            </div>
                        </div>
                    </div>
                </div>
            
               

                <div class="perks">
                    <div class="perks1"><img src="Home-Pics/PERKS2.png" alt=""></div>
                    <div class="perk-text">
                        <h6 class="perk">PERKS</h6>
                        <p>Personalized attention and care:</p>
                        <ul>
                            <li>Energy levels</li>
                            <li>Sensitivities to playtime</li>
                            <li>Naptime preferences</li>
                            <li>Feeding schedules</li>
                        </ul>

                        <p>Grooming services:</p>
                        <ul>
                            <li>Wash away the grime after a fun playtime!</li>
                        </ul>

                        <p>Socialization opportunities:</p>
                        <ul>
                            <li>Your pet's chance to make new friends!</li>
                        </ul>

                        <div class="bn">
                        <a href="#second-scroll"><h6 class="bnow">Book Now!</h6> </a>
                        </div>

                    </div>

                    <div class="perks2"><img src="Home-Pics/PERKS1.png" alt=""></div>
                </div>
            </div>
        </div>

        <div class = "third-section">
                <!-- pictures -->
                <img class="yellow_doodle" src="Home-Pics/yellow_doodle.png" id = "third-section-pics"/>
                <img class="paws_up1" src="Home-Pics/paws_up1.png" id = "third-section-pics"/>
                <img class="join_meow" src="Home-Pics/join_meow.png" id = "third-section-pics"/>

                <img class="yellow_doodle2" src="Home-Pics/yellow_doodle2.png" id = "third-section-pics"/>

                <!-- //pictures -->


            <div class = "third-section-1">
                <div class = "third-section-title" id = "third-sec-titledesc">
                    DISCOVER OUR EXCLUSIVE MEMBERSHIP OFFERS
                </div><!-- third-section-title -->
                <center>
                    <div class = "third-section-desc" id = "third-sec-titledesc" style = "text-shadow: 4px 4px 12px #e9e0d9, 2px 2px 10px #e9e0d9;
">
                Choose from Gold, Silver, or Regular memberships to enjoy tailored perks and savings on your pet’s stay at our luxury hotel. Take a look today and find the perfect plan for your furry friend!
                    </div>
                </center><!-- third-section-desc -->
            </div><!-- third-section-1 -->


            <div class = "third-section-2">
                <div class = "cards">
                <center><img class="member-pic" src="Home-Pics/1.png"/>
                    <div class = "member-title" style = "color:#077208;">REGULAR</div>
                <div class = "member-desc">
                    <ul>
                    <li>Access to basic pet hotel and daycare reservations</li>
                    <li>Pay-as-you-go pricing with no discounts.</li>
                    </ul>

                <div class = "member-price">₱ xxx.xx</div>
                
                </div><!-- member desc--></center>
                </div><!-- cards1 -->

                <div class = "cards">
                <center><img class="member-pic" src="Home-Pics/2.png" id = "member-pic-1"/>
                    <div class = "member-title" style = "color: #838383;">SILVER</div>
                <div class = "member-desc">
                    <ul>
                    <li>5% discount on all reservations (hotel stays, daycare, and services).</li>
                    <li>Priority booking during holidays and weekends.</li>
                    </ul>

                <div class = "member-price" style = "margin-top: 40px">₱ xxx.xx</div>
                
                </div><!-- member desc--></center>
                </div><!-- cards2 -->

                <div class = "cards">
                <center><img class="member-pic" src="Home-Pics/3.png" id = "member-pic-1"/>
                    <div class = "member-title" style = "color:#cca200;;">GOLD</div>
                <div class = "member-desc">
                    <ul>
                    <li>20% discount on all reservations and services.</li>
                    <li>Complimentary daycare service for one day each month.</li>
                    </ul>

                <div class = "member-price" style = "margin-top: 40px">₱ xxx.xx</div>
                
                </div><!-- member desc--></center>
                </div><!-- cards3 -->

                <div class = "cards">
                <center><img class="member-pic" src="Home-Pics/4.png" id = "member-pic-1"/>
                    <div class = "member-title" style = "color: #7590af;">PLATINUM</div>
                <div class = "member-desc">
                    <ul>
                    <li>Access to basic pet hotel and daycare reservations</li>
                    <li>Pay-as-you-go pricing with no discounts.</li>
                    </ul>

                <div class = "member-price">₱ xxx.xx</div>
                
                </div><!-- member desc--></center>
                </div><!-- cards4 -->

            </div><!-- third section 2-->


        </div><!--third-section-->
    </div><!--Home-->

    <div class="time-sec">  
        <img src="Home-Pics/second-cloud.png" alt="">      
        <div class="service-box daycare">
            <div class="content">
                <div class="icon-box">
                    <img src="Home-Pics/sun.png" class="icon" />
                    <span class="hover-text1">7AM 12PM</span>
                </div>
                <div class="text-content">
                    <h2>DAYCARE</h2>
                    <p class="default-txt">Our daycare service is the perfect spot for your pet to have a blast while you're away. No overnight stays, just pure daytime enjoyment!</p>
                </div>
            </div>
        </div>

        <div class="service-box hotel">
            <div class="content">
                <div class="icon-box">
                    <img src="Home-Pics/moon.png"  class="icon"/>
                    <span class="hover-text1">24/7</span>

                </div>
                <div class="text-content">
                    <h2>PET HOTEL</h2>
                    <p class="default-txt">From playful days to peaceful nights, we're here to make sure they feel right at home, around the clock!</p>
                </div>
            </div>
        </div>
    </div>        


</body>

</html>