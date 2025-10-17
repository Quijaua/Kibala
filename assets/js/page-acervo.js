$('.category-title').click(function() {
/*
  let $this = $(this);
  if ($this.hasClass('-active')) {
    $this.removeClass('-active')
  } else {
    $this.parents('.categorys-holder').find('.category-title').removeClass('-active')
    $this.toggleClass('-active')
  }
 */
 
	$("#grupo").val('');
	$("#subgrupo").val('');
	$("#especie").val('');
	$("#serie").val('');

	$("#material").val('');
	$("#pc").val('');

	vs_secao = $(this).attr("title");
	$("#secao").val(vs_secao);

	if (vs_secao == "arquivo")
	  $("#acervo").val(1);
	else if (vs_secao == "biblioteca")
	  $("#acervo").val(3);

	$("#pag").val("1");
	$("#form_itens").submit();
})