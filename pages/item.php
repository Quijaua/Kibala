<?php
    // Limpa o parâmetro recebido (ex: asc_000123 → 000123)
    $codigo = $param;
    $param = preg_replace('/\D+/', '', $param);

    $sql = "SELECT
                a.*,
                b.codigo AS codigo_livro,
                b.cutter_pha,
                b.numero_paginas,
                d.codigo AS acervo_codigo,
                d.identificador AS acervo_identificador,
                d.nome AS acervo_nome,
                f.nome AS subcategoria,
                g.nome AS categoria
            FROM item_acervo a
            LEFT JOIN livro b ON a.codigo = b.item_acervo_codigo
            LEFT JOIN documento c ON a.codigo = c.item_acervo_codigo
            LEFT JOIN acervo d ON a.acervo_codigo = d.codigo
            LEFT JOIN agrupamento e ON c.agrupamento_codigo = e.codigo
            LEFT JOIN agrupamento_dados_textuais f ON e.codigo = f.agrupamento_codigo
            LEFT JOIN agrupamento_dados_textuais g ON e.agrupamento_superior_codigo = g.agrupamento_codigo
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
                        <a href="<?= INCLUDE_PATH; ?>assets/img/sem-imagem.png" class="single--image-text--image gallery-item">
                            <img src="<?= INCLUDE_PATH; ?>assets/img/sem-imagem.png" alt="">
                        </a>
                    </div>
                </div>

                <div class="single--image-text-paragraph">
                    <p></p>
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

                    <!-- <div class="single--details--info">
                        <strong>Série<span class="tooltip-btn">+ <span class="tooltip">Conjunto de obras organizado por uma editora e impresso sob um título coletivo e formato padronizado.</span></span></strong>
                        
                        <span>Clássicos de ouro: gregos e romanos</span>
                    </div> -->

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