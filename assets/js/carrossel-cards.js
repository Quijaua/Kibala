$(document).ready(function(){

  //if ($('.carrossel-cards .card').length >= 5) {
    $('.carrossel-cards').slick({
      slidesToShow: 5,
      slidesToScroll: 5,
      prevArrow: '<span class="material-symbols-outlined slick-prev">arrow_back_ios</span>',
      nextArrow: '<span class="material-symbols-outlined slick-next">arrow_forward_ios</span>',
      // centerMode: true,
      centerPadding: 30,
      responsive: [
        {
          breakpoint: 1280,
          settings: {
            slidesToShow: 4,
            slidesToScroll: 4,
            infinite: true,
          }
        },
        {
          breakpoint: 900,
          settings: {
            slidesToShow: 3,
            slidesToScroll: 3
          }
        },
        {
          breakpoint: 769,
          settings: {
            slidesToShow: 2,
            slidesToScroll: 2
          }
        },
        {
          breakpoint: 550,
          settings: {
            slidesToShow: 1,
            slidesToScroll: 1
          }
        }
      ]
    });
  //}
});