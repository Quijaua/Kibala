$(document).ready(function(){
  $('.carrossel-big-image').slick({
    slidesToShow: 2,
    slidesToScroll: 2,
    prevArrow: '<span class="material-symbols-outlined slick-prev">arrow_back_ios</span>',
    nextArrow: '<span class="material-symbols-outlined slick-next">arrow_forward_ios</span>',
    responsive: [
      {
        breakpoint: 769,
        settings: {
          slidesToShow: 1,
          slidesToScroll: 1
        }
      },
    ]
  });
});