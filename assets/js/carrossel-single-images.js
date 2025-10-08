$(document).ready(function(){
  $('.carrossel-single-images').slick({
    slidesToShow: 3,
    slidesToScroll: 3,
    prevArrow: '<span class="material-symbols-outlined slick-prev">arrow_back_ios</span>',
    nextArrow: '<span class="material-symbols-outlined slick-next">arrow_forward_ios</span>',
    responsive: [
      {
        breakpoint: 769,
        settings: {
          slidesToShow: 2,
          slidesToScroll: 2
        }
      },
      // {
      //   breakpoint: 550,
      //   settings: {
      //     slidesToShow: 1,
      //     slidesToScroll: 1
      //   }
      // }
    ]
  });
});