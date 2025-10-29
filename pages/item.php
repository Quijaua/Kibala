<?php
    // Limpa o parâmetro recebido (ex: asc_000123 → 000123)
    $codigo = $param;
    $param = preg_replace('/\D+/', '', $param);

    $sql = "SELECT
                a.*,
                b.descricao,
                c.codigo AS codigo_livro,
                c.cutter_pha,
                c.numero_paginas,
                d.codigo AS codigo_documento,
                e.codigo AS acervo_codigo,
                e.identificador AS acervo_identificador,
                e.nome AS acervo_nome,
                e.setor_sistema_codigo,
                g.nome AS subcategoria,
                h.nome AS categoria,
                i.recurso_sistema_padrao_codigo,
                j.titulo AS item_acervo_documento,
                k.nome AS genero_documental
            FROM item_acervo a
            LEFT JOIN item_acervo_dados_textuais b ON a.codigo = b.item_acervo_codigo
            LEFT JOIN livro c ON a.codigo = c.item_acervo_codigo
            LEFT JOIN documento d ON a.codigo = d.item_acervo_codigo
            LEFT JOIN acervo e ON a.acervo_codigo = e.codigo
            LEFT JOIN agrupamento f ON d.agrupamento_codigo = f.codigo
            LEFT JOIN agrupamento_dados_textuais g ON f.codigo = g.agrupamento_codigo
            LEFT JOIN agrupamento_dados_textuais h ON f.agrupamento_superior_codigo = h.agrupamento_codigo
            LEFT JOIN setor_sistema i ON e.setor_sistema_codigo = i.codigo
            LEFT JOIN item_acervo_dados_textuais j ON a.codigo = j.item_acervo_codigo
            LEFT JOIN genero_documental k ON d.genero_documental_codigo = k.codigo
            WHERE a.codigo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$param]);
    $dado = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$dado) {
        header('Location: ' . INCLUDE_PATH);
    }

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

    $dado['autores'] = implode(', ', $autores);

    $sql = "SELECT
                c.nome
            FROM livro_editora a
            INNER JOIN editora b ON a.editora_codigo = b.codigo
            INNER JOIN entidade c ON b.entidade_codigo = c.codigo
            WHERE a.livro_codigo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$dado['codigo_livro']]);
    $editoras = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $dado['editoras'] = array_column($editoras, 'nome');

    $sql = "SELECT
                b.nome
            FROM item_acervo_suporte a
            INNER JOIN suporte b ON a.suporte_codigo = b.codigo
            WHERE a.item_acervo_codigo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$dado['codigo']]);
    $suportes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $dado['suportes'] = array_column($suportes, 'nome');

    $sql = "SELECT
            b.nome AS especie_nome,
            c.nome AS tipo_documental
        FROM documento_especie_documental a
        INNER JOIN especie_documental_dados_textuais b ON a.especie_documental_codigo = b.especie_documental_codigo
        INNER JOIN tipo_documental c ON a.tipo_documental_codigo = c.codigo
        WHERE a.documento_codigo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$dado['codigo_documento']]);
    $especiesDocumentais = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $especies = [];
    foreach ($especiesDocumentais as $linha) {
        $especies[] = "{$linha['especie_nome']} ({$linha['tipo_documental']})";
    }

    $dado['especies'] = implode(', ', $especies);

    $sql = "SELECT 
            b.nome,
            a.funcao_entidade
        FROM item_acervo_entidade a
        INNER JOIN entidade b ON a.entidade_codigo = b.codigo
        WHERE a.item_acervo_codigo = ? 
        AND a.tipo_autor_codigo != ''
        GROUP BY 
            a.item_acervo_codigo, 
            a.entidade_codigo, 
            a.funcao_entidade";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$dado['codigo']]);
    $acervoEntidades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $entidades = [];
    foreach ($acervoEntidades as $linha) {
        $entidades[] = (!empty($linha['funcao_entidade'])) 
            ? "{$linha['nome']} ({$linha['funcao_entidade']})" 
            : "{$linha['nome']}";
    }

    $dado['entidades'] = implode(', ', $entidades);

    $sql = "SELECT
            b.nome,
            c.nome AS contexto_superior_nome
        FROM contexto a
        INNER JOIN contexto_dados_textuais b ON a.codigo = b.contexto_codigo
        LEFT JOIN contexto_dados_textuais c ON a.contexto_superior_codigo = c.contexto_codigo
        WHERE a.acervo_codigo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$dado['acervo_codigo']]);
    $itemContextos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $contextos = [];
    foreach ($itemContextos as $linha) {
        $contextos[] = (!empty($linha['contexto_superior_nome'])) ? "{$linha['contexto_superior_nome']} > {$linha['nome']}" : $linha['nome'];
    }

    $dado['contextos'] = implode(', ', $contextos);

    $sql = "SELECT
                b.nome
            FROM documento_formato a
            INNER JOIN formato b ON a.formato_codigo = b.codigo
            WHERE a.documento_codigo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$dado['codigo_documento']]);
    $formatos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $dado['formatos'] = array_column($formatos, 'nome');

    if ($dado['setor_sistema_codigo'] == 1) { // Apenas para documento
        $sql = "SELECT
                    b.nome,
                    a.descricao
                FROM item_acervo_estado_conservacao a
                INNER JOIN estado_conservacao b ON a.estado_conservacao_codigo = b.codigo
                WHERE a.item_acervo_codigo = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$dado['codigo']]);
        $conservacao = $stmt->fetch(PDO::FETCH_ASSOC);
    
        $dado['estado_conservacao'] = $conservacao['nome'] . (!empty($conservacao['descricao']) ? " - {$conservacao['descricao']}" : "");
    }

    $sql = "SELECT
                b.nome
            FROM item_acervo_localidade a
            INNER JOIN localidade b ON a.localidade_codigo = b.codigo
            WHERE a.item_acervo_codigo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$dado['codigo']]);
    $localidades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $dado['localidades'] = array_column($localidades, 'nome');

    $sql = "SELECT
                b.nome
            FROM item_acervo_idioma a
            INNER JOIN idioma b ON a.idioma_codigo = b.codigo
            WHERE a.item_acervo_codigo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$dado['codigo']]);
    $idiomas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $dado['idiomas'] = array_column($idiomas, 'nome');

    $sql = "SELECT
                b.nome
            FROM item_acervo_palavra_chave a
            INNER JOIN palavra_chave b ON a.palavra_chave_codigo = b.codigo
            WHERE a.item_acervo_codigo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$dado['codigo']]);
    $palavras_chave = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $dado['palavras_chave'] = array_column($palavras_chave, 'nome');
?>

<?php
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
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$dado['recurso_sistema_padrao_codigo'], $cod]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sem_imagem = INCLUDE_PATH . "assets/img/sem-imagem.png";
?>

<main>
    <div class="single--top --livro">
        <h1 class="title">
			<?= $dado['identificador']; ?>
            <span><?= $codigo ?></span>
		</h1>

        <div class="breadcrumb">
            <a href="<?= INCLUDE_PATH; ?>" title="Home" class="breadcrumb--link">Home</a>
            <a class='breadcrumb--link'> / </a>
            <a href="<?= INCLUDE_PATH; ?>acervo" title="Acervo" class="breadcrumb--link">Acervo</a>
            <a class='breadcrumb--link'> / </a>
            <?php if (isset($dado['acervo_codigo']) && !empty($dado['acervo_codigo'])): ?>
            <a href="<?= INCLUDE_PATH; ?>acervo?acervo=<?= $dado['acervo_codigo']; ?>" title="<?= $dado['acervo_identificador']; ?>" class="breadcrumb--link"><?= $dado['acervo_nome']; ?></a>
            <a class='breadcrumb--link'> / </a>
            <?php endif; ?>
             <?php if (isset($dado['categoria']) && !empty($dado['categoria'])): ?>
            <a href="<?= INCLUDE_PATH; ?>acervo?acervo=<?= $dado['acervo_codigo']; ?>" title="<?= $dado['categoria']; ?>" class="breadcrumb--link"><?= $dado['categoria']; ?></a>
            <a class='breadcrumb--link'> / </a>
            <?php endif; ?>
            <?php if (isset($dado['subcategoria']) && !empty($dado['subcategoria'])): ?>
            <a href="<?= INCLUDE_PATH; ?>acervo?acervo=<?= $dado['acervo_codigo']; ?>" title="<?= $dado['subcategoria']; ?>" class="breadcrumb--link"><?= $dado['subcategoria']; ?></a>
            <a class='breadcrumb--link'> / </a>
            <?php endif; ?>
            <a href="#" class="breadcrumb--link"><?= $codigo ?></a>
        </div>
    </div>

    <div class="container">
        <section class="single single--document">
        
            <div class="single--content">
                <div class="single--image-text">
                    <div class="lightgallery-carrossel">
                        <?php $imagem = (isset($result) && !empty($result[0]['path'])) ? INCLUDE_FILE_PATH . "?file={$result[0]['path']}&size=original" : $sem_imagem; ?>
                        <a href="<?= $imagem; ?>" class="single--image-text--image gallery-item">
                            <img src="<?= $imagem; ?>" alt="<?= $dado['item_acervo_documento'] ?? $result[0]['legenda'] ?? "Arranjo Arquivo Sueli Carneiro"; ?>">
                        </a>

                        <?php if ($result): ?>
                            <?php foreach (array_slice($result, 1) as $r): ?>
                            <?php $imagem = INCLUDE_FILE_PATH . "?file={$r['path']}&size=original"; ?>
                            <div class="single--image-text--carrossel carrossel-single-images slick-initialized slick-slider">
                                <div class="slick-list draggable">
                                    <div class="slick-track" style="opacity: 1; width: 240px; transform: translate3d(0px, 0px, 0px);">
                                        <a href="<?= $imagem; ?>" class="gallery-item slick-slide slick-current slick-active" data-lg-id="8dfa757d-2038-4219-baeb-2ec567838bde" data-slick-index="0" aria-hidden="false" style="width: 176px;" tabindex="0">
                                            <img src="<?= $imagem; ?>" alt="<?= $dado['item_acervo_documento'] ?? $r['legenda'] ?? "Arranjo Arquivo Sueli Carneiro"; ?>">
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div class="single--image-text-paragraph">
                        <p><?= $dado['descricao'] ?? ''; ?></p>
                    </div>
                </div>

                <div class="single--details">
                    <div class="single--details--list-social">
                        <a  class="social pointer" onclick="shareLink('facebook', 'Diálogos', 'item/biblioteca/<?= $codigo ?>')">
                        <i class="cib-facebook-f"></i>
                        </a>
                        <a class="social pointer" onclick="shareLink('twitter', 'Diálogos', 'item/biblioteca/<?= $codigo ?>')">
                        <i class="cib-twitter"></i>
                        </a>
                        <a class="social pointer" onclick="shareLink('whatsapp', 'Diálogos', 'item/biblioteca/<?= $codigo ?>')">
                        <i class="cib-whatsapp"></i>
                        </a>
                        <a class="social pointer" onclick="shareLink('telegram', 'Diálogos', 'item/biblioteca/<?= $codigo ?>')">
                        <i class="cib-telegram"></i>
                        </a>
                        <a class="social pointer" onclick="shareLink('copy', 'Diálogos', 'item/biblioteca/<?= $codigo ?>')">
                        <i class="cil-link"></i>
                        </a>
                        <a class="social pointer" onclick="shareLink('email', 'Diálogos', 'item/biblioteca/<?= $codigo ?>')">
                        <span class="material-symbols-outlined">mail</span>
                        </a>
                    </div>
                    <div id="copy-message" class="text-center mt-2" style="display:none;">
                        <p>Link copiado</p>
                    </div>

                    <?php if (isset($dado['genero_documental']) && !empty($dado['genero_documental'])): ?>
                    <div class="single--details--info">
                        <strong>Espécie/Tipo documental<span class="tooltip-btn">+ <span class="tooltip">Classificação que indica a natureza e a forma de um documento, conforme sua função administrativa, jurídica ou informativa.</span></span></strong>

                        <span>
                            <?= $dado['genero_documental']; ?>
                        </span>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($dado['suportes']) && count($dado['suportes']) > 0): ?>
                    <div class="single--details--info">
                        <strong>Suporte<span class="tooltip-btn">+ <span class="tooltip">Material físico no qual o conteúdo do documento está registrado, como papel, filme, fita magnética ou meio digital.</span></span></strong>

                        <span>
                            <?= implode(', ', $dado['suportes']); ?>
                        </span>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($dado['especies']) && !empty($dado['especies'])): ?>
                    <div class="single--details--info">
                        <strong>Espécie<span class="tooltip-btn">+ <span class="tooltip">Designação que identifica o tipo documental segundo sua configuração e finalidade, como ofício, relatório, ata ou contrato.</span></span></strong>

                        <span>
                            <?= $dado['especies']; ?>
                        </span>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($dado['entidades']) && !empty($dado['entidades'])): ?>
                    <div class="single--details--info">
                        <strong>Agentes<span class="tooltip-btn">+ <span class="tooltip">Pessoas físicas ou jurídicas responsáveis pela criação, produção, acumulação ou custódia do documento.</span></span></strong>

                        <span>
                            <?= $dado['entidades']; ?>
                        </span>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($dado['contextos']) && !empty($dado['contextos'])): ?>
                    <div class="single--details--info">
                        <strong>Contexto<span class="tooltip-btn">+ <span class="tooltip">Conjunto de circunstâncias históricas, institucionais ou funcionais que explicam a origem e a função do documento.</span></span></strong>

                        <span>
                            <?= $dado['contextos']; ?>
                        </span>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($dado['formatos']) && count($dado['formatos']) > 0): ?>
                    <div class="single--details--info">
                        <strong>Formatos<span class="tooltip-btn">+ <span class="tooltip">Configuração física ou digital do documento, indicando dimensões, estrutura e apresentação do conteúdo.</span></span></strong>

                        <span>
                            <?= implode(', ', $dado['formatos']); ?>
                        </span>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($dado['estado_conservacao']) && !empty($dado['estado_conservacao'])): ?>
                    <div class="single--details--info">
                        <strong>Estado de conservação<span class="tooltip-btn">+ <span class="tooltip">Condição física e material do documento, indicando seu grau de integridade, preservação ou deterioração.</span></span></strong>

                        <span>
                            <?= $dado['estado_conservacao']; ?>
                        </span>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($dado['cutter_pha']) && !empty($dado['cutter_pha'])): ?>
                    <div class="single--details--info">
                        <strong>Cutter<span class="tooltip-btn">+ <span class="tooltip">Tabela usada para classificação de autoria pelo sobrenome.</span></span></strong>

                        <span>
                            <?= $dado['cutter_pha']; ?>
                        </span>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($dado['categoria']) && !empty($dado['categoria'])): ?>
                    <div class="single--details--info">
                        <strong>Grupo/subgrupo<span class="tooltip-btn">+ <span class="tooltip">Primeira divisão de um fundo, constituída por documentos acumulados, reunidos por semelhança de função. Subdivisão de um grupo, utilizada em razão da complexidade estrutural ou funcional.</span></span></strong>

                        <span><?= htmlspecialchars($dado['categoria']) ?><?= !empty($dado['subcategoria']) ? ' > ' . htmlspecialchars($dado['subcategoria']) : '' ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($dado['autores']) && !empty($dado['autores'])): ?>
                    <div class="single--details--info">
                        <strong>Autoria<span class="tooltip-btn">+ <span class="tooltip">Pessoa física (individual ou coletiva) ou a pessoa jurídica (Estado, governo, entidades coletivas e similares) que se responsabiliza pelo conteúdo de uma obra.</span></span></strong>

                        <span>
                            <?= $dado['autores']; ?>
                        </span>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($dado['editoras']) && count($dado['editoras']) > 0): ?>
                    <div class="single--details--info">
                        <strong>Editora<span class="tooltip-btn">+ <span class="tooltip">Do ponto de vista comercial, é a pessoa ou empresa que publica uma obra e se responsabiliza tanto pela sua apresentação gráfica como pela sua distribuição e venda.</span></span></strong>

                        <span>
                            <?= implode(', ', $dado['editoras']); ?>
                        </span>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($dado['data_inicial']) && !empty($dado['data_inicial'])): ?>
                    <div class="single--details--info">
                        <strong>Data de publicação<span class="tooltip-btn">+ <span class="tooltip">Indicação do momento, isto é, dia, mês e ano relativos a um documento, a um acontecimento, à publicação de um texto.</span></span></strong>

                        <span><?= date('Y', strtotime($dado['data_inicial'])); ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($dado['numero_paginas']) && !empty($dado['numero_paginas'])): ?>
                    <div class="single--details--info">
                        <strong>Páginas<span class="tooltip-btn">+ <span class="tooltip">Qualquer dos lados de uma folha de papel ou pergaminho, especialmente quando integra obra como livro, folheto, revista, jornal, manuscrito ou carta.</span></span></strong>

                        <span><?= $dado['numero_paginas']; ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($dado['localidades']) && count($dado['localidades']) > 0): ?>
                    <div class="single--details--info">
                        <strong>Local<span class="tooltip-btn">+ <span class="tooltip">Identificação de cidade, estado, país. Local de publicação.</span></span></strong>

                        <span>
                            <?= implode(' | ', $dado['localidades']); ?>
                        </span>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($dado['idiomas']) && count($dado['idiomas']) > 0): ?>
                    <div class="single--details--info">
                        <strong>Idioma<span class="tooltip-btn">+ <span class="tooltip">Língua original.</span></span></strong>

                        <span>
                            <?= implode(', ', $dado['idiomas']); ?>
                        </span>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($dado['palavras_chave']) && count($dado['palavras_chave']) > 0): ?>
                    <div class="single--details--info">
                        <strong>Palavras-chave<span class="tooltip-btn">+ <span class="tooltip">Palavra significativa encontrada no título de um documento, no resumo ou no texto. Essa palavra (ou grupo de palavras) caracteriza o conteúdo temático do item e é usada em catálogos e índices de assuntos.</span></span></strong>

                        <span>
                            <?= implode(', ', $dado['palavras_chave']); ?>
                        </span>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
        </section>
    </div>
</main>