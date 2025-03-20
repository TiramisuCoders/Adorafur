<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Contact.css">
    <!--<script src="script.js" defer></script>-->
    <title>Contact Adorafur</title>
    <link rel="icon" type="image/png" href="Header-Pics/logo.png">
</head> 

<body>

<?php 
$activePage = 'contact';
include 'header.php'; ?>


<div class = "main">
    <div class="contact">

        <img class="contact-us-img" src="elements/cont_pic.png" />

        <!-- contact text -->
        <div class="contact-text1">
            <span>

                <span class="contact-span1">
                    <br />
                </span>

                <span class="contact-span2">
                    A happy place where your furbabies
                    <br />
                    can play and make new friends
                    <br />
                </span>

                <span class="contact-span3">
                    Pet Hotel | Grooming
                    <br />
                </span>
                
        <!--contact text2 -->
            <div class = "contact-text2">
                <div class = "gmail">
                    <img class="gmail-icon" src="elements/gmail-icon.png" />
                        <div class="gmail-info">
                            adorafurph@gmail.com
                        </div>
                </div>

                <div class="number">
                    <img class="num-icon" src="elements/num-icon.png" />
                        <div class="contact-num">
                            09477 617 2478
                        </div>
                </div>
            </div>

    <!-- google maps-->
    <div class="g-maps-container">
        <div class="g-maps">
        <iframe 
            class="map-iframe"
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3860.5571336833946!2d121.03208259999998!3d14.624286300000001!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397b153531cd2ef%3A0xbd0463a253f32bfb!2sAdorafur%20Happy%20Stay!5e0!3m2!1sfil!2sph!4v1738089085635!5m2!1sfil!2sph"
            allowfullscreen=""
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade">
        </iframe>

        </div>
            <!-- social media links -->
    <div class = "socmed-links">

<a href="https://www.instagram.com/adorafurph/" target="_blank">
    <img class="ig-icon" src="elements/ig-icon.png" />
</a>

<a href="https://www.facebook.com/AdorafurPH/" target="_blank">
    <img class="fb-icon" src="elements/fb-icon.png" />
</a>
</div>
    </div>
            </span>
        </div>


    <!-- faqs container -->
    <div class="faqs">
        <div class="faqs-img-container" id = "faqs">
            <img class="faqs-img" src="elements/faqs2.png" alt="FAQs Image">
            <h2 class="faqs-title">Frequently Asked Questions</h2>
        </div>
    </div>

        <div class="faqs-space"></div>

        <!-- faqs accordion -->
         <section>

            <div class="faq">
                <div class="question">
                    <h3>Q1.  What are the health and vaccination requirements for pets staying at Adorafur Happy Stay ?</h3>

                    <svg class="icon" width="25" height="30" viewBox="0 0 42 42">
                        <!-- Plus sign path -->
                        <path class="plus" d="M3 21H39" stroke="black" stroke-width="8" stroke-linecap="round" stroke-linejoin="round"/>
                        <path class="plus" d="M21 3V39" stroke="black" stroke-width="8" stroke-linecap="round" stroke-linejoin="round"/>

                        <!-- Minus sign path -->
                        <path class="minus" d="M3 12H39" stroke="black" stroke-width="8" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>

                </div>
                <div class="answer">
                    <p>
                    All pets must be in good health, vaccinated against Distemper, Canine Adenovirus-2, Canine Parvovirus-2, and Rabies, 
                    and must be on flea prevention (e.g., Frontline, Advantage, or Revolution). If a pet has external parasites, we will treat them at 
                    the owner’s expense to ensure a clean environment for all pets.
                    </p>
                </div>
            </div>

            <div class="faq">
                <div class="question">
                    <h3>Q2. Can pets with aggression or behavioral issues stay at the facility ?</h3>

                    <svg class="icon" width="25" height="30" viewBox="0 0 42 42">
                        <!-- Plus sign path -->
                        <path class="plus" d="M3 21H39" stroke="black" stroke-width="8" stroke-linecap="round" stroke-linejoin="round"/>
                        <path class="plus" d="M21 3V39" stroke="black" stroke-width="8" stroke-linecap="round" stroke-linejoin="round"/>

                        <!-- Minus sign path -->
                        <path class="minus" d="M3 12H39" stroke="black" stroke-width="8" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>

                </div>
                <div class="answer">
                    <p>
                    We welcome sociable pets but reserve the right to refuse pets with aggression. Please 
                    inform us of any known triggers or aggressive behaviors (e.g., food or territorial aggression) 
                    so we can make accommodations if possible. In some cases, we may need to use restraints or isolation 
                    or the safety of all pets.
                    </p>
                </div>
            </div>

            <div class="faq">
                <div class="question">
                    <h3>Q3. Are there any risks associated with boarding my pet ?</h3>

                    <svg class="icon" width="25" height="30" viewBox="0 0 42 42">
                        <!-- Plus sign path -->
                        <path class="plus" d="M3 21H39" stroke="black" stroke-width="8" stroke-linecap="round" stroke-linejoin="round"/>
                        <path class="plus" d="M21 3V39" stroke="black" stroke-width="8" stroke-linecap="round" stroke-linejoin="round"/>

                        <!-- Minus sign path -->
                        <path class="minus" d="M3 12H39" stroke="black" stroke-width="8" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                
                </div>
                <div class="answer">
                    <p>
                    We take every measure to ensure safety; however, there are inherent risks in a cageless 
                    daycare or boarding environment, including minor injuries, illness, or occasional behavioral incidents. 
                    By using our services, owners acknowledge and accept these risks, and we are not liable for injuries or illnesses 
                    caused by pets during their stay.
                    </p>
                </div>
            </div>

            <div class="faq">
                <div class="question">
                    <h3>Q4. What if I’m unable to pick up my pet on time?</h3>

                    <svg class="icon" width="25" height="30" viewBox="0 0 42 42">
                        <!-- Plus sign path -->
                        <path class="plus" d="M3 21H39" stroke="black" stroke-width="8" stroke-linecap="round" stroke-linejoin="round"/>
                        <path class="plus" d="M21 3V39" stroke="black" stroke-width="8" stroke-linecap="round" stroke-linejoin="round"/>

                        <!-- Minus sign path -->
                        <path class="minus" d="M3 12H39" stroke="black" stroke-width="8" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                
                </div>
                <div class="answer">
                    <p>
                    We take every measure to ensure safety; however, there are inherent risks in a cageless daycare 
                    or boarding environment, including minor injuries, illness, or occasional behavioral incidents. 
                    By using our services, owners acknowledge and accept these risks, and we are not liable for injuries or 
                    illnesses caused by pets during their stay.
                    </p>
                </div>
            </div>

            <div class="faq">
                <div class="question">
                    <h3>Q5. What if my pet causes damage or injury ?</h3>

                    <svg class="icon" width="25" height="30" viewBox="0 0 42 42">
                        <!-- Plus sign path -->
                        <path class="plus" d="M3 21H39" stroke="black" stroke-width="8" stroke-linecap="round" stroke-linejoin="round"/>
                        <path class="plus" d="M21 3V39" stroke="black" stroke-width="8" stroke-linecap="round" stroke-linejoin="round"/>

                        <!-- Minus sign path -->
                        <path class="minus" d="M3 12H39" stroke="black" stroke-width="8" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>

                </div>
                <div class="answer">
                    <p>
                    Pet owners are responsible for any property damage (e.g., to kennels, fencing) caused by their pets, 
                    as well as for medical costs for injuries inflicted on other pets or staff.
                    </p>
                </div>
            </div>

         </section>
         <script src="app.js"></script>
    <!-- contact div ender -->
    </div>
</div>
</body>
</html>
