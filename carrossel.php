<?php
// carrossel.php
header("Content-Type: text/html; charset=utf-8");

// Conexão com o banco
include_once('config.php');

// Recebe o código da linha do tempo
$contexto = isset($_GET['contexto']) ? intval($_GET['contexto']) : 0;
if ($contexto <= 0) {
    exit;
}

// Consulta os itens relacionados da linha do tempo
$sql = "
    SELECT 
        i.*,
        COALESCE(g.nome, '') AS grupo,
        COALESCE(f.nome, '') AS subgrupo
    FROM linha_tempo_item_acervo lta
    INNER JOIN item_acervo i ON lta.item_acervo_codigo = i.codigo
    LEFT JOIN documento b ON i.codigo = b.item_acervo_codigo
    LEFT JOIN agrupamento e ON b.agrupamento_codigo = e.codigo
    LEFT JOIN agrupamento_dados_textuais f ON e.codigo = f.agrupamento_codigo
    LEFT JOIN agrupamento_dados_textuais g ON e.agrupamento_superior_codigo = g.agrupamento_codigo
    WHERE lta.linha_tempo_codigo = :codigo
    ORDER BY lta.sequencia ASC
";

$stmt = $conn->prepare($sql);
$stmt->bindValue(':codigo', $contexto, PDO::PARAM_INT);
$stmt->execute();
$itens = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$itens) {
    exit;
}
?>

<main class="page-linha-do-tempo">
    <div class="carrossel-cards" id="carrossel_cards_<?= htmlspecialchars($contexto) ?>">
        <?php foreach ($itens as $index => $item): ?>
            <?php
                $link = "item/arquivo/" . htmlspecialchars($item['codigo']);
                $titulo = htmlspecialchars($item['identificador']);
                $data = $item['data_inicial'] ? date('d M. Y', strtotime($item['data_inicial'])) : 's.d.';
                $grupo = trim($item['grupo']);
                $subgrupo = trim($item['subgrupo']);
                $grupo_subgrupo = $grupo ? "$grupo > $subgrupo" : null;
                $img_src = isset($item['imagem_capa_base64']) && !empty($item['imagem_capa_base64']) 
                    ? "data:image/png;base64," . $item['imagem_capa_base64'] 
                    : INCLUDE_PATH . "assets/img/sem-imagem.png";
            ?>

            <a href="<?= $link ?>" class="card" id="<?= $index + 1 ?>">
                <div class="card-img">
                    <img src="<?= $img_src ?>" alt="">
                </div>
                <h4 class="card-title"><?= $titulo ?></h4>
                <div class="card-content">
                    <strong>Data</strong>
                    <span><?= $data ?></span>
                    <?php if ($grupo_subgrupo): ?>
                    <strong>Grupo/Subgrupo</strong>
                    <span><?= $grupo_subgrupo ?></span>
                    <?php endif; ?>
                </div>
                <span class="card-count"><?= $index + 1 ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</main>