$('.tooltip-btn').click(function() {

  let $this = $(this);
  if ($this.hasClass('--active')) {
    $this.removeClass('--active')
  } else {
    $this.parents('.single--details').find('.tooltip-btn').removeClass('--active')
    $this.toggleClass('--active')
  }
})

$(document).mouseup(function(e) {
    var container = $(".tooltip-btn");

    if (!container.is(e.target) && container.has(e.target).length === 0) {
      $('.tooltip-btn').removeClass('--active')
    }
});