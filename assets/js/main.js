
// img Vertial || Horizontal
$(window).on("load", function() {

  function verifyImg(img) {
    // let widthImg = $(img).width();
    // let heightImg = $(img).height();
  
    // if (widthImg > heightImg) {
    //   console.log($(img));
    //   $(img).addClass('--horizontal')
    // }

    $(img).each(function(i, el){
      let widthImg = $(el).width();
      let heightImg = $(el).height();
    
      if (widthImg > heightImg) {
        $(el).addClass('--horizontal')
      }
    })
  }


  verifyImg($('main img'))

  
});




// Menu
const menubtn = $('.menu-btn')
const btnSubmenu = $('.menu__link.--has-child')

menubtn.click(function() {
  $('body').toggleClass('menu-open')
})

btnSubmenu.click(function() {
  $(this).toggleClass('-show')
  $(btnSubmenu).find('.submenu').slideToggle('fast')
})


// Linha do tempo
const btnActiveLinhadoTempo = $('.linha-do-tempo--title')

btnActiveLinhadoTempo.click(function(){
  let $this = $(this);
  if ($this.hasClass('--active')) {
    $this.removeClass('--active')
  } else 
  {

jQuery.ajaxSetup({async:false});

	vn_contexto_codigo = $(this).attr("id");
    vs_url_carrossel = "carrossel.php?contexto="+vn_contexto_codigo;
    
    $.get(vs_url_carrossel).done(function(data, status)
	{	
		$("#carr_"+vn_contexto_codigo).html(data);
		
		 $('#carrossel_cards_'+vn_contexto_codigo).slick({
		  slidesToShow: 5,
		  slidesToScroll: 5,
		  prevArrow: '<span class="material-symbols-outlined slick-prev">arrow_back_ios</span>',
		  nextArrow: '<span class="material-symbols-outlined slick-next">arrow_forward_ios</span>',
		  // centerMode: true,
		  centerPadding: 30,
      infinite: true,
		  responsive: [
			{
			  breakpoint: 1280,
			  settings: {
				slidesToShow: 4,
				slidesToScroll: 4,
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
	});
	
	$('main .card-img img').each(function(i, el)
	{
		$(el).on('load', function()
		{
			let widthImg = $(el).width();
			let heightImg = $(el).height();
		
			if (widthImg > heightImg) 
			{
				$(el).addClass('--horizontal')
			}
		});		  
	})
	
    $this.parents('.linha-do-tempo').find('.linha-do-tempo--title').removeClass('--active')
    $this.toggleClass('--active')
  }

  // //SCROLL
  // let elmnt = document.getElementById($(this).parent().attr("id"));
  // elmnt.scrollIntoView({ behavior: 'smooth' });
  $([document.documentElement, document.body]).animate({
    scrollTop: $this.parent().offset().top - 100
  }, 300);
});


const btnDesc = $('.btn-desc')

btnDesc.click(function(){
  $(this).toggleClass('--active')
  $(this).parents('.linha-do-tempo--content').find('.linha-do-tempo--desc').toggleClass('--active')
});

// Filtro Mobile
let btnFilter = $('.filter-open-mobile')

btnFilter.click(function() {
  $('body').toggleClass('filter-open')
})




const timeLineDesc = $('.linha-do-tempo--desc')

$(timeLineDesc).each((i, element) => {
  let $elm = $(element)

  if ($elm.text().split(' ').length < 100) {
    $elm.parents('.linha-do-tempo--content').addClass('-min-words')
  }

});


const select = $('.filter-form-select, .form-select-acervo')

$(select).each((i, element) => {
  let $elm = $(element)
  let $list = $elm.find('.select-list')
  let $title = $elm.find('.select-title')

  $title.click(function() {
    $elm.toggleClass('-active')
  })

  $list.find('span').click(function() {
    $title.find('small').text($(this).text())
    $elm.removeClass('-active')
  })

});




function isScrolledIntoView(elem) {
    var docViewTop = $(window).scrollTop();
    var docViewBottom = docViewTop + $(window).height() + 60;

    if ($(elem).offset() === undefined) {
      return false;
    }
    var elemTop = $(elem).offset().top;
    var elemBottom = elemTop + $(elem).height();

    return ((elemBottom <= docViewBottom) && (elemTop >= docViewTop));
}

$(window).scroll(function() {
  if (isScrolledIntoView($('.box-wrapper'))) {
    $('.colecao-slideshow').addClass('-congelar')
  } else {
  $('.colecao-slideshow').removeClass('-congelar')
  }
  //console.log('saa', isScrolledIntoView($('.box-wrapper')));
});


function shareLink(mean, text, url) {

    const baseUrl = window.location.origin;
    const shareUrl = baseUrl + "/" + url;
    const message = "Acervo Sueli Carneiro - " + text + ": " + shareUrl.replace(/&/g, '%26');

    if (mean === 'whatsapp') {
        window.open('https://api.whatsapp.com/send?text='+message, '_blank');
    }

    if (mean === 'facebook') {
        window.open('https://www.facebook.com/sharer/sharer.php?href='+message, '_blank');
    }

    if (mean === 'twitter') {
        window.open('https://twitter.com/intent/tweet?text='+message, '_blank');
    }

    if (mean === 'telegram') {
        window.open('https://t.me/share/url?url='+message, '_blank');
    }

    if (mean === 'email') {
        window.open('mailto:?subject='+message, '_blank');
    }

    if (mean === 'copy') {
        copyToClipboard(message);
    }

}

function copyToClipboard(text) {
    const input = document.createElement('input');
    input.setAttribute('value', text);
    document.body.appendChild(input);
    input.select();
    const result = document.execCommand('copy');
    document.body.removeChild(input);

    if (result) {
        const copyMessage = document.getElementById('copy-message');
        copyMessage.style.display = 'block';
        setTimeout(() => {
            copyMessage.style.display = 'none';
        }, 1500);
    }
}