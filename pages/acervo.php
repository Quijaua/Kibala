<?php
// --- Configurações de paginação ---
$itens_por_pagina = 12;
$pagina_atual = isset($_GET['pag']) && $_GET['pag'] > 0 ? (int)$_GET['pag'] : 1;
$offset = ($pagina_atual - 1) * $itens_por_pagina;

// --- Ordenação ---
$acervo = strtolower($_GET['acervo'] ?? '');
$secao = strtolower($_GET['secao'] ?? '');
$grupo = strtolower($_GET['grupo'] ?? '');
$sort = strtolower($_GET['sort'] ?? 'data');
$ord = strtolower($_GET['ord'] ?? 'asc');
$ord = ($ord === 'desc') ? 'desc' : 'asc';

$campo_ordem = match ($sort) {
    'tit' => 'a.identificador',
    'data' => 'a.data_inicial',
    default => 'a.identificador',
};

// --- Filtros ---
$filtros = [];
$params = [];
$joins = "";

// Filtro por acervo
if (!empty($_GET['acervo'])) {
    $filtros[] = 'a.acervo_codigo = :acervo';
    $params[':acervo'] = $_GET['acervo'];
}

// Filtro por grupo
if (!empty($_GET['grupo'])) {
    $sql = "SELECT codigo FROM agrupamento WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$_GET['grupo']]);
    $grupo = $stmt->fetch(PDO::FETCH_ASSOC);

    $joins .= "
        LEFT JOIN documento doc ON a.codigo = doc.item_acervo_codigo
        LEFT JOIN agrupamento agrup ON doc.agrupamento_codigo = agrup.codigo
        LEFT JOIN agrupamento_dados_textuais agrup_text ON doc.agrupamento_codigo = agrup_text.agrupamento_codigo
    ";
    $filtros[] = '(agrup_text.agrupamento_codigo = :grupo OR agrup.agrupamento_superior_codigo = :grupo)';
    $params[':grupo'] = $grupo['codigo'];
}

// Filtro por grupo
if (!empty($_GET['subgrupo'])) {
    $sql = "SELECT codigo FROM agrupamento WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$_GET['subgrupo']]);
    $subgrupo = $stmt->fetch(PDO::FETCH_ASSOC);

    $joins .= "
        LEFT JOIN documento doc ON a.codigo = doc.item_acervo_codigo
        LEFT JOIN agrupamento agrup ON doc.agrupamento_codigo = agrup.codigo
        LEFT JOIN agrupamento_dados_textuais agrup_text ON doc.agrupamento_codigo = agrup_text.agrupamento_codigo
    ";
    $filtros[] = 'agrup_text.agrupamento_codigo = :subgrupo';
    $params[':subgrupo'] = $subgrupo['codigo'];
}

// Filtro por título/descrição
if (!empty($_GET['especie'])) {
    $joins .= "
        LEFT JOIN documento doc1 ON a.codigo = doc1.item_acervo_codigo
        LEFT JOIN documento_especie_documental especie ON doc1.codigo = especie.documento_codigo
    ";
    $filtros[] = 'especie.especie_documental_codigo = :especie';
    $params[':especie'] = $_GET['especie'];
}

// Filtro por título/descrição
if (!empty($_GET['s'])) {
    $filtros[] = '(a.identificador LIKE :titulo OR a.titulo LIKE :titulo OR d.descricao LIKE :titulo)';
    $params[':titulo'] = '%' . $_GET['s'] . '%';
}

// Filtro por título/descrição
if (!empty($_GET['tit'])) {
    $filtros[] = '(a.identificador LIKE :titulo OR a.titulo LIKE :titulo OR d.descricao LIKE :titulo)';
    $params[':titulo'] = '%' . $_GET['tit'] . '%';
}

// Filtro por autoria / agentes
if (!empty($_GET['ent'])) {
    // adiciona os JOINs necessários
    $joins .= " 
        LEFT JOIN item_acervo_entidade iae ON iae.item_acervo_codigo = a.codigo
        LEFT JOIN entidade ent ON iae.entidade_codigo = ent.codigo
    ";
    $filtros[] = 'ent.nome LIKE :entidade';
    $params[':entidade'] = '%' . $_GET['ent'] . '%';
}

// Filtro por ano inicial
if (!empty($_GET['ai']) && is_numeric($_GET['ai'])) {
    $filtros[] = 'YEAR(a.data_inicial) >= :ano_inicial';
    $params[':ano_inicial'] = $_GET['ai'];
}

// Filtro por ano final
if (!empty($_GET['af']) && is_numeric($_GET['af'])) {
    $filtros[] = 'YEAR(a.data_final) <= :ano_final';
    $params[':ano_final'] = $_GET['af'];
}

// Monta o WHERE dinamicamente
$where = $filtros ? 'WHERE ' . implode(' AND ', $filtros) : '';

// --- Contar total de registros com filtro ---
$sql_total = "SELECT COUNT(DISTINCT a.codigo)
              FROM item_acervo a
              LEFT JOIN acervo d ON a.acervo_codigo = d.codigo
              $joins
              $where";

$stmt_total = $conn->prepare($sql_total);
foreach ($params as $chave => $valor) {
    $stmt_total->bindValue($chave, $valor);
}
$stmt_total->execute();
$total_registros = $stmt_total->fetchColumn();
$total_paginas = ceil($total_registros / $itens_por_pagina);

// --- Consulta principal com filtros e paginação ---
$sql = "SELECT DISTINCT
            a.*,
            b.codigo AS codigo_documento,
            c.codigo AS codigo_livro,
            c.cutter_pha,
            c.numero_paginas,
            d.codigo AS acervo_codigo,
            d.identificador AS acervo_identificador,
            d.nome AS acervo_nome,
            d.sigla AS acervo_sigla,
            d.setor_sistema_codigo,
            f.nome AS subcategoria,
            g.nome AS categoria,
            h.recurso_sistema_padrao_codigo,
            i.titulo AS item_acervo_documento
        FROM item_acervo a
        LEFT JOIN documento b ON a.codigo = b.item_acervo_codigo
        LEFT JOIN livro c ON a.codigo = c.item_acervo_codigo
        LEFT JOIN acervo d ON a.acervo_codigo = d.codigo
        LEFT JOIN agrupamento e ON b.agrupamento_codigo = e.codigo
        LEFT JOIN agrupamento_dados_textuais f ON e.codigo = f.agrupamento_codigo
        LEFT JOIN agrupamento_dados_textuais g ON e.agrupamento_superior_codigo = g.agrupamento_codigo
        LEFT JOIN setor_sistema h ON d.setor_sistema_codigo = h.codigo
        LEFT JOIN item_acervo_dados_textuais i ON a.codigo = i.item_acervo_codigo
        $joins
        $where
        ORDER BY $campo_ordem $ord
        LIMIT :offset, :limit";

$stmt = $conn->prepare($sql);

foreach ($params as $chave => $valor) {
    $stmt->bindValue($chave, $valor);
}

$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $itens_por_pagina, PDO::PARAM_INT);
$stmt->execute();
$itens = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- Busca os acervos para o filtro lateral ---
$sql = "SELECT 
            a.codigo,
            a.nome,
            a.sigla,
            a.quantidade_itens,
            i.nome AS instituicao_nome
        FROM acervo a
        LEFT JOIN instituicao i ON a.instituicao_codigo = i.codigo
        ORDER BY a.nome ASC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$acervos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="page-acervo">
    <section class="section-default">
        <div class="topo">
            <div class="container">
                <div class="info">
                    <h2 class="title">Acervo</h2>
                    <div class="breadcrumb --default">
                        <a href="./" title="Home" class="breadcrumb--link">Home</a><a class='breadcrumb--link'> / </a><a href="acervo" title="Explore" class="breadcrumb--link">Explore</a>
                    </div>
                </div>
            </div>
        </div>            

        <div class="container">
            <div class="categorys-holder">
                <div class="category-content">
                    <strong class="category-title<?= $acervo == 1 ? ' -active' : ''; ?>" title="arquivo">Arquivo Sueli Carneiro</strong>

                    <?php
                        $sql = "SELECT 
                                    b.id,
                                    c.nome
                                FROM acervo a
                                INNER JOIN agrupamento b 
                                    ON a.codigo = b.acervo_codigo
                                    AND b.agrupamento_superior_codigo IS NULL
                                INNER JOIN agrupamento_dados_textuais c 
                                    ON b.codigo = c.agrupamento_codigo 
                                    AND c.idioma_codigo = 1
                                WHERE a.nome = 'Arquivo Sueli Carneiro'
                                ORDER BY c.nome ASC";

                        $stmt = $conn->prepare($sql);
                        $stmt->execute();
                        $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>

                    <div class="category-buttons">
                        <?php if ($categorias): ?>
                            <?php foreach ($categorias as $data): ?>
                                <a href="#" class="btn category grupo" title="<?= $data['id']; ?>"><?= $data['nome']; ?></a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="category-content">
                    <strong class="category-title<?= $acervo == 3 ? ' -active' : ''; ?>" title="biblioteca">Biblioteca Sueli Carneiro</strong>

                    <?php
                        $sql = "SELECT 
                                    b.id,
                                    c.nome
                                FROM acervo a
                                INNER JOIN agrupamento b 
                                    ON a.codigo = b.acervo_codigo
                                    AND b.agrupamento_superior_codigo IS NULL
                                INNER JOIN agrupamento_dados_textuais c 
                                    ON b.codigo = c.agrupamento_codigo 
                                    AND c.idioma_codigo = 1
                                WHERE a.nome = 'Biblioteca Sueli Carneiro'
                                ORDER BY c.nome ASC";

                        $stmt = $conn->prepare($sql);
                        $stmt->execute();
                        $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>

                    <div class="category-buttons">
                        <?php if ($categorias): ?>
                            <?php foreach ($categorias as $data): ?>
                                <a href="#" class="btn category grupo" title="<?= $data['id']; ?>"><?= $data['nome']; ?></a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <button class="btn filter-mobile filter-open-mobile">FILTROS AVANÇADOS</button>
        </div>
    </section>
</main>

<?php
    $sql = "SELECT 
                b.codigo,
                b.id,
                c.nome
            FROM acervo a
            INNER JOIN agrupamento b 
                ON a.codigo = b.acervo_codigo
                AND b.agrupamento_superior_codigo IS NULL
            INNER JOIN agrupamento_dados_textuais c 
                ON b.codigo = c.agrupamento_codigo 
                AND c.idioma_codigo = 1
            ORDER BY c.nome ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $grupos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sql = "SELECT 
                b.codigo,
                b.id,
                c.nome
            FROM acervo a
            INNER JOIN agrupamento b 
                ON a.codigo = b.acervo_codigo
                AND b.agrupamento_superior_codigo IS NOT NULL
            INNER JOIN agrupamento_dados_textuais c 
                ON b.codigo = c.agrupamento_codigo 
                AND c.idioma_codigo = 1
            ORDER BY c.nome ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $subgrupos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sql = "SELECT 
                a.especie_documental_codigo AS codigo,
                a.nome
            FROM especie_documental_dados_textuais a 
            ORDER BY a.nome ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $especies = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<form id="form_itens" method="get" action="">
    <input type="hidden" id="sort" name="sort" value="<?= $sort; ?>">
    <input type="hidden" id="ord" name="ord" value="<?= $ord; ?>">

    <section class="order-page">
        <div class="container">
            <div class="order">
                <strong class="order-quantity"><?= $total_registros; ?> iten(s)</strong>

                <!-- Botões de filtro -->
                <button type="button" class="order-descending" title="Ordenar por data">
                    <i class="<?= ($sort == 'data' && $ord == 'asc') ? 'cil-sort-ascending' : 'cil-sort-descending' ?>"></i>
                </button>

                <button type="button" class="order-az" title="Ordenar por título">
                    <i class="<?= ($sort == 'tit' && $ord == 'asc') ? 'cil-sort-alpha-up' : 'cil-sort-alpha-down' ?>"></i>
                </button>
            </div>

            <?php if ($total_paginas > 1): ?>

            <div class="pagination">
                <ul class="pagination-list">
                    <?php for ($i = 1; $i <= min($total_paginas, 5); $i++): ?>
                    <li class="<?= $i == $pagina_atual ? '--active' : '' ?>">
                        <a href="#" title="<?= $i ?>" class="--pag"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>

                    <?php if ($total_paginas > 5): ?>
                    <li class="ellipsis">...</li>
                    <li><a href="#" title="<?= $total_paginas ?>" class="--pag"><?= $total_paginas ?></a></li>
                    <?php endif; ?>
                </ul>

                <div class="pagination-arrows">
                    <?php if ($pagina_atual > 1): ?>
                    <span class="material-symbols-outlined pagination-arrow --prev">arrow_back_ios</span>
                    <?php endif; ?>
                    <?php if ($pagina_atual < $total_paginas): ?>
                    <span class="material-symbols-outlined pagination-arrow --next">arrow_forward_ios</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="go-page">
                <label for="pag" class="go-page-label">Página</label>
                <div class="go-page-form">
                    <input id="pag" name="pag" type="number" min="1" max="<?= $total_paginas ?>" value="<?= $pagina_atual ?>">
                    <button type="button" class="btn pag">Ir</button>  
                </div>
            </div>
            
            <?php endif; ?>

        </div>
    </section>


<script>
$(document).on('change', "#acervo", function(event)
{
    event.preventDefault();

	$("#secao").val('');
    $("#grupo").val('');
    $("#subgrupo").val('');
    $("#especie").val('');
    $("#serie").val('');

    $("#pag").val("1");
    $("#form_itens").submit();
});

$(document).on('click', ".category-title", function(e){
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
});

$(document).on('click', ".grupo", function(event)
{
    event.preventDefault();

    vs_grupo = $(this).attr("title");

    $("#acervo").val(1);
    $("#grupo").val(vs_grupo);
    $("#subgrupo").val('');

    $("#material").val('');
    $("#pc").val('');

    $("#pag").val("1");
    $("#form_itens").submit();
});

$(document).on('click', ".--prev", function(e){
    e.preventDefault();
    let pag = parseInt($("#pag").val()) - 1;
    $("#pag").val(pag);
    $("#form_itens").submit();
});

$(document).on('click', ".--next", function(e){
    e.preventDefault();
    let pag = parseInt($("#pag").val()) + 1;
    $("#pag").val(pag);
    $("#form_itens").submit();
});

$(document).on('click', ".--pag", function(e){
    e.preventDefault();
    $("#pag").val($(this).text().trim());
    $("#form_itens").submit();
});

$(document).on('click', ".pag", function(e){
    e.preventDefault();
    let pag = parseInt($("#pag").val());
    $("#pag").val(pag);
    $("#form_itens").submit();
});

// Filtros de ordenação
$(document).on('click', ".order-descending", function(e) {
    e.preventDefault();
    $("#sort").val("data");
    $("#ord").val($("#ord").val() === "asc" ? "desc" : "asc");
    $("#form_itens").submit();
});

$(document).on('click', ".order-az", function(e) {
    e.preventDefault();
    $("#sort").val("tit");
    $("#ord").val($("#ord").val() === "asc" ? "desc" : "asc");
    $("#form_itens").submit();
});

$(document).on('click', '.btn-limpar', function () {
    window.location.href = window.location.pathname;
});
</script>



        <div class="container">
            <div class="acervo-page">

                <?php if (!isset($_GET['acervo'])): ?>
                <input type="hidden" id="secao" name="secao" value="<?= $secao; ?>">
                <input type="hidden" id="grupo" name="grupo" value="<?= $grupo; ?>">
                <?php endif; ?>

                <aside class="filter">
                    <h3 class="filter-title">
                        Filtros
                        <button class="filter-btn-close filter-open-mobile">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </h3>

                    <div class="filter-form-selects">
                        <div class="filter-form-select">
                            <select name="acervo" id="acervo" class="select-filter">
                                <option value="">Acervo</option>
                                <?php foreach ($acervos as $acervo): ?>
                                    <option value="<?= $acervo['codigo']; ?>" <?= ($_GET['acervo'] ?? '') == $acervo['codigo'] ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($acervo['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button class="btn"><span class="material-symbols-outlined">expand_more</span></button>
                        </div>
                        

                        <?php if (isset($_GET['acervo']) && !empty($_GET['acervo'])): ?>
                        <div class="filter-form-select">
                            <select name="grupo" id="grupo" class="select-filter">
                                <option value="">Grupo</option>
                                <?php foreach ($grupos as $grupo): ?>
                                    <option value="<?= $grupo['id']; ?>" <?= ($_GET['grupo'] ?? '') == $grupo['id'] ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($grupo['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button class="btn "><span class="material-symbols-outlined">expand_more</span></button>
                        </div>                           

                        <div class="filter-form-select">
                            <select name="subgrupo" id="subgrupo" class="select-filter">
                                <option value="">Subgrupo</option>
                                <?php foreach ($subgrupos as $subgrupo): ?>
                                    <option value="<?= $subgrupo['id']; ?>" <?= ($_GET['subgrupo'] ?? '') == $subgrupo['id'] ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($subgrupo['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button class="btn "><span class="material-symbols-outlined">expand_more</span></button>
                        </div>

                        <div class="filter-form-select">
                            <select name="especie" id="especie" class="select-filter">
                                <option value="">Espécie documental</option>
                                <?php foreach ($especies as $especie): ?>
                                    <option value="<?= $especie['codigo']; ?>" <?= ($_GET['especie'] ?? '') == $especie['codigo'] ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($especie['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button class="btn "><span class="material-symbols-outlined">expand_more</span></button>
                        </div>
                        <?php endif; ?>


                    </div>

                    <div class="filter-search">
                        <strong class="filter-title">Busca</strong>

                        <div action="#" class="form full">
                            <input type="text" name="tit" id="tit" placeholder="Título / Descrição"
                                value="<?= htmlspecialchars($_GET['tit'] ?? ''); ?>">

                            <input type="text" name="ent" id="ent" placeholder="Autoria / Agentes"
                                value="<?= htmlspecialchars($_GET['ent'] ?? ''); ?>">

                            <div class="filter-date-group">
                                <div class="filter-date">
                                    <input type="text" class="ano" name="ai" id="ai" placeholder="Ano inicial" maxlength="4"
                                        value="<?= htmlspecialchars($_GET['ai'] ?? ''); ?>">
                                    <input type="text" class="ano" name="af" id="af" placeholder="Ano final" maxlength="4"
                                        value="<?= htmlspecialchars($_GET['af'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="btn-filtro">
                                <button type="submit" class="btn">Ir</button>
							    <button type="button" class="btn btn-limpar">Limpar</button>
                            </div>
                        </div>
                    </div>
                </aside>    






                
                <main class="library">

                    <?php if (!$itens): ?>

                        <div class="alert-null">
                            Nenhum resultado encontrado
                        </div>

                    <?php else: ?>
                        <div class="cards-wrapper">

                            <?php
                            // =========================
                            // LOOP PARA LISTAR OS ITENS
                            // =========================
                            foreach ($itens as $dado):

                                // 🔹 Busca autores e tipos de autor
                                $sql = "SELECT 
                                            b.nome AS autor, 
                                            c.nome AS tipo_autor
                                        FROM item_acervo_entidade a
                                        INNER JOIN entidade b ON a.entidade_codigo = b.codigo
                                        INNER JOIN tipo_autor c ON a.tipo_autor_codigo = c.codigo
                                        WHERE a.item_acervo_codigo = ?";
                                $stmt = $conn->prepare($sql);
                                $stmt->execute([$dado['codigo']]);
                                $entidades = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                $autores = [];
                                foreach ($entidades as $linha) {
                                    $autores[] = "{$linha['autor']} ({$linha['tipo_autor']})";
                                }
                                $autoria = !empty($autores) ? implode(', ', $autores) : '';

                                // 🔹 Formata data (só ano)
                                $ano = '';
                                if (!empty($dado['data_inicial'])) {
                                    $ano = date('Y', strtotime($dado['data_inicial']));
                                } else {
                                    $ano = 's.d.';
                                }

                                // 🔹 Monta o caminho do link amigável
                                $identificador = $dado['acervo_identificador'] ?? '';
                                $tipoItem = (strpos($identificador, 'asc_') !== false) ? 'arquivo' : 'biblioteca';

                                // 🔹 Monta identificador formatado com sigla do acervo
                                $sigla = !empty($dado['acervo_sigla']) ? $dado['acervo_sigla'] : ''; // ex: BSC
                                $codigo_formatado = str_pad($dado['codigo'], 6, '0', STR_PAD_LEFT);
                                $identificador_formatado = ($sigla ? strtoupper($sigla) . '_' : '') . $codigo_formatado;

                                // 🔹 Monta o caminho do link amigável
                                $identificador = $dado['acervo_identificador'] ?? '';
                                $tipoItem = (strpos(strtolower($sigla), 'asc') !== false) ? 'arquivo' : 'biblioteca';
                                $link = INCLUDE_PATH . "item/{$tipoItem}/$identificador_formatado";

                                // --- Busca imagem ---
                                $cod = ($dado['setor_sistema_codigo'] == 1) ? $dado['codigo_documento'] : $dado['codigo_livro'];

                                // Seleciona todos os "sobre" que têm o mesmo id mais de uma vez
                                $sql = "
                                    SELECT 
                                        a.*
                                    FROM representante_digital a
                                    WHERE 
                                        a.tipo = 1
                                    AND
                                        a.recurso_sistema_codigo = ?
                                    AND
                                        a.registro_codigo = ?
                                    ORDER BY a.sequencia ASC
                                    LIMIT 1
                                ";
                                $stmt = $conn->prepare($sql);
                                $stmt->execute([$dado['recurso_sistema_padrao_codigo'], $cod]);
                                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                                // 🔹 Imagem
                                $imagem = (isset($result) && !empty($result['path'])) ? INCLUDE_FILE_PATH . "?file={$result['path']}&size=original" : INCLUDE_PATH . 'assets/img/sem-imagem.png';
                            ?>

                            <!-- CARD -->
                            <a href="<?= $link ?>" class="card <?= (isset($dado['codigo_livro']) && !empty($dado['codigo_livro'])) ? '--dark' : '' ?>">
                                <div class="card-img">
                                    <img src="<?= $imagem ?>" alt="<?= $dado['item_acervo_documento'] ?? $result['legenda'] ?? "Arranjo Arquivo Sueli Carneiro"; ?>">
                                </div>

                                <h4 class="card-title"><?= htmlspecialchars($dado['identificador']) ?></h4>

                                <div class="card-content">

                                    <?php if (!empty($autoria)): ?>
                                        <strong>Autoria</strong>
                                        <span><?= htmlspecialchars($autoria) ?></span>
                                    <?php endif; ?>

                                    <strong>Data</strong>
                                    <span><?= htmlspecialchars($ano) ?></span>

                                    <?php if (!empty($dado['categoria'])): ?>
                                        <strong>Grupo/Subgrupo</strong>
                                        <span><?= htmlspecialchars($dado['categoria']) ?><?= !empty($dado['subcategoria']) ? ' > ' . htmlspecialchars($dado['subcategoria']) : '' ?></span>
                                    <?php endif; ?>

                                </div>
                            </a>

                            <?php endforeach; ?>

                        </div>
                    <?php endif; ?>
                </main>
            </div>
        </div>

        
<section class="order-page">
    <div class="container">
        <div class="order">
            <strong class="order-quantity"><?= $total_registros; ?> iten(s)</strong>

            <!-- Botões de filtro -->
            <button type="button" class="order-descending" title="Ordenar por data">
                <i class="<?= ($sort == 'data' && $ord == 'asc') ? 'cil-sort-ascending' : 'cil-sort-descending' ?>"></i>
            </button>

            <button type="button" class="order-az" title="Ordenar por título">
                <i class="<?= ($sort == 'tit' && $ord == 'asc') ? 'cil-sort-alpha-up' : 'cil-sort-alpha-down' ?>"></i>
            </button>
        </div>

        <?php if ($total_paginas > 1): ?>

            <div class="pagination">
                <ul class="pagination-list">
                    <?php for ($i = 1; $i <= min($total_paginas, 5); $i++): ?>
                    <li class="<?= $i == $pagina_atual ? '--active' : '' ?>">
                        <a href="#" title="<?= $i ?>" class="--pag"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>

                    <?php if ($total_paginas > 5): ?>
                    <li class="ellipsis">...</li>
                    <li><a href="#" title="<?= $total_paginas ?>" class="--pag"><?= $total_paginas ?></a></li>
                    <?php endif; ?>
                </ul>

                <div class="pagination-arrows">
                    <?php if ($pagina_atual > 1): ?>
                    <span class="material-symbols-outlined pagination-arrow --prev">arrow_back_ios</span>
                    <?php endif; ?>
                    <?php if ($pagina_atual < $total_paginas): ?>
                    <span class="material-symbols-outlined pagination-arrow --next">arrow_forward_ios</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="go-page">
                <label for="pag" class="go-page-label">Página</label>
                <div class="go-page-form">
                    <input id="pag" name="pag" type="number" min="1" max="<?= $total_paginas ?>" value="<?= $pagina_atual ?>">
                    <button type="button" class="btn pag">Ir</button>  
                </div>
            </div>

        <?php endif; ?>
    </div>
</section>

<script>


$(document).on('click', ".paginf", function(event)
{
    event.preventDefault();
       
    vn_pagina = $("#paginf").val();
    $("#pag").val(vn_pagina);
    
    $("#form_itens").submit();
}
);


</script>    </form>