var swiper = new Swiper(".mySwiper", {
    effect: "cube",
    grabCursor: true,
    cubeEffect: {
      shadow: true,
      slideShadows: true,
      shadowOffset: 20,
      shadowScale: 0.14,
    },
    loop: true,
    autoplay: {
      delay: 1500,
      disableOnInteraction: false,
    },
    speed: 5000, // Set transition speed to 2000ms (2 seconds)
    pagination: {
      el: ".swiper-pagination",
      clickable: true,
    },
});
