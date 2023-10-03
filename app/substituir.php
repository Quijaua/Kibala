<?php

    $vb_montar_menu = true;
    require_once dirname(__FILE__) . "/components/entry_point.php";


    if (isset($_GET['obj']))
        $vs_id_objeto_tela = $_GET['obj'];
    else
    {
        print "Não é possível carregar a listagem. (objeto)";
        exit();
    }

    $vn_objeto_codigo = "";
    if (isset($_GET['cod']))
        $vn_objeto_codigo = $_GET['cod'];
    else
    {
        print "Não é possível ler objeto sem codigo.";
        exit();
    }

    if (isset($_GET['substituir_codigo']))
    {
        $vn_codigo_destino = $_GET['substituir_codigo'];
        
        if ($vn_codigo_destino)
        {
            require_once dirname(__FILE__). "/functions/autenticar_usuario.php";

            if (!$vb_pode_substituir)
                exit();

            $vo_objeto = new $vs_id_objeto_tela($vs_id_objeto_tela);

            if (!$vo_objeto->validar_acesso_registro($vn_objeto_codigo, $va_parametros_controle_acesso))
                exit();

            if (!$vo_objeto->validar_acesso_registro($vn_codigo_destino, $va_parametros_controle_acesso))
                exit();

            $vo_objeto->substituir($vn_objeto_codigo, $vn_codigo_destino);

            $vs_url_retorno = "location:ficha.php?obj=". $vs_id_objeto_tela . "&cod=" . $vn_codigo_destino;
            header($vs_url_retorno);
        }
    }
?>

<!DOCTYPE html>
<html lang="pt-br">

<?php require_once dirname(__FILE__) . "/components/header_html.php"; ?>

<body>

<?php require_once dirname(__FILE__)."/components/sidebar.php"; ?>

<?php 
    if (!$vb_pode_substituir)
        exit();

    $vs_modo = "ficha";
    $vs_visualizacao = "lista";
    
    $va_objeto = array();
    if ($vn_objeto_codigo)
    {
        $vo_objeto = new $vs_id_objeto_tela($vs_id_objeto_tela);
        
        $va_objeto = $vo_objeto->ler($vn_objeto_codigo, $vs_visualizacao);
        $va_visualizacao_lista = $vo_objeto->get_visualizacao($vs_visualizacao);

        if (!count($va_objeto))
        {
            print "Não é possível carregar formulário de cadastro. (Erro ao ler o registro.)";
            exit();
        }

        //var_dump($va_objeto);
    }
?>

<?php
    $vb_substituir_disponivel = true;
    $vs_campo_nome = "substituir";
    $vs_procurar_por = "";

    switch($vs_id_objeto_tela)
    {
        case "agrupamento":
            $vs_campo_nome = "agrupamento";
            $vs_atributo_codigo = "agrupamento_codigo";
            $vs_atributo_nome = "agrupamento_dados_textuais_0_agrupamento_nome";

            break;

        case "atividade_geradora":
            $vs_atributo_codigo = "atividade_geradora_codigo";
            $vs_atributo_nome = "atividade_geradora_nome";

            break;

        case "contexto":
            $vs_atributo_codigo = "contexto_codigo";
            $vs_atributo_nome = "contexto_dados_textuais_0_contexto_nome";

            break;
        
        case "editora":
            $vs_atributo_codigo = "editora_codigo";
            $vs_atributo_nome = "entidade_nome";
            $vs_procurar_por = "entidade_codigo_0_entidade_nome";

            break;

        case "entidade":
            $vs_atributo_codigo = "entidade_codigo";
            $vs_atributo_nome = "entidade_nome";

            break;

        case "especie_documental":
            $vs_atributo_codigo = "especie_documental_codigo";
            $vs_atributo_nome = "especie_documental_dados_textuais_0_especie_documental_nome";

            break;

        case "formato":
            $vs_atributo_codigo = "formato_codigo";
            $vs_atributo_nome = "formato_nome";

            break;

        case "idioma":
            $vs_atributo_codigo = "idioma_codigo";
            $vs_atributo_nome = "idioma_nome";

            break;

        case "localidade":
            $vs_atributo_codigo = "localidade_codigo";
            $vs_atributo_nome = "localidade_nome";

            break;

        case "palavra_chave":
            $vs_atributo_codigo = "palavra_chave_codigo";
            $vs_atributo_nome = "palavra_chave_nome";

            break;

        case "serie":
            $vs_atributo_codigo = "serie_codigo";
            $vs_atributo_nome = "serie_nome";

            break;

        case "tipo_documental":
            $vs_atributo_codigo = "tipo_documental_codigo";
            $vs_atributo_nome = "tipo_documental_nome";

            break;
    
        case "formato_material":
            $vs_atributo_codigo = "formato_material_codigo";
            $vs_atributo_nome = "formato_material_nome";

            break;

        default:
            $vb_substituir_disponivel = false;
    }

    if (!$vs_procurar_por)
        $vs_procurar_por = $vs_atributo_nome;
?>


<div class="wrapper d-flex flex-column min-vh-100 bg-light">
    <?php
        if (!$vb_substituir_disponivel)
        {
            print "O procedimento de substituição não está disponível para este item de cadastro.";
            exit();
        }
    ?>

    <?php require_once dirname(__FILE__)."/components/header.php"; ?>
    <?php require_once dirname(__FILE__)."/components/ler_valor.php"; ?>

    <form method="get" action="substituir.php" id="form_substituir">
        <input type="hidden" name="obj" id="obj" value="<?php print $vs_id_objeto_tela; ?>">
        <input type="hidden" name="cod" id="cod" value="<?php print $vn_objeto_codigo; ?>">

        <div class="body flex-grow-1 px-3">
            <div class="container-lg">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card mb-4">
                            <div class="card-header">Substituir</div>

                            <div class="card-body">

                                <div class="row">
                                    <div class="filter-documents -new col-md-9">
                                    </div>
                                    
                                    <div class="col-md-3 text-right">
                                        <button class="btn btn-primary btn-btn_substituir" type="button" id="btn_substituir">
                                            Substituir
                                        </button>
                                    </div>
                                </div>

                                <br>
                                
                                <!-- FORM-->
                                <div class="row no-margin-side" id="filtro">
                                    <div class="col-12">
            
                                        <?php
                                            $va_campo_visualizacao = array();
                                            $vs_valor_atributo = ler_valor1($vs_atributo_nome, $va_objeto, $va_campo_visualizacao);
                                            
                                            if ($vs_valor_atributo)
                                            {
                                            ?>   
                                                <div class="input-group mb-3">
                                                <div class="form-control cor-interna-edit no-border"> 
                                                    <?php print htmlspecialchars($vs_valor_atributo); ?>
                                                </div>
                                                </div>
                                            <?php
                                            }

                                            $va_parametros_campo = [
                                                "html_autocomplete", 
                                                "nome" => [$vs_campo_nome, "substituir_codigo"],
                                                "label" => "Substituir por", 
                                                "objeto" => $vs_id_objeto_tela,
                                                "atributos" => [$vs_atributo_codigo, $vs_atributo_nome],
                                                "multiplos_valores" => false, 
                                                "procurar_por" => $vs_procurar_por, 
                                                "visualizacao" => "lista",
                                                "permitir_cadastro" => true, 
                                                "campo_salvar" => $vs_atributo_nome,
                                                "excluir" => $vn_objeto_codigo
                                            ];

                                            $vo_combo_selecoes = new html_autocomplete($vs_id_objeto_tela, "substituir");

                                            $va_valores = array();
                                            $vo_combo_selecoes->build($va_valores, $va_parametros_campo);
                                        ?>

                                    </div>
                                </div>
                                <!-- / FORM-->

                                <br>

                            </div>
                        </div>
                    </div>
                    <!-- /.col-->
                </div>
                <!-- /.row-->
            </div>
        </div>
    </form>

</div>
<?php require_once dirname(__FILE__)."/components/footer.php"; ?>

<script>

$(document).on('click', "#btn_substituir", function()
{
    if (confirm('Tem certeza de que deseja substituir este valor?')) 
    {
        $("#form_substituir").submit();
    }
});

</script>

</body>
</html>