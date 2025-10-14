<?php
    function traduzirMeses($data) {
        $meses = [
            'Jan' => 'Jan', 'Feb' => 'Fev', 'Mar' => 'Mar', 'Apr' => 'Abr', 'May' => 'Mai', 'Jun' => 'Jun',
            'Jul' => 'Jul', 'Aug' => 'Ago', 'Sep' => 'Set', 'Oct' => 'Out', 'Nov' => 'Nov', 'Dec' => 'Dez'
        ];

        return strtr($data, $meses);
    }

    // --- Consulta principal da linha do tempo ---
    $sql = "
        SELECT 
            codigo,
            id,
            titulo,
            data_inicial,
            data_final,
            descricao
        FROM linha_tempo
        ORDER BY data_inicial ASC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $linhas_tempo = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="page-linha-do-tempo">

    <section class="section-default">
        <div class="topo">
            <div class="container">
                <div class="info">
                    <h2 class="title">Linha do Tempo</h2>

                    <div class="breadcrumb --default">
                        <a href="<?= INCLUDE_PATH; ?>" title="Home" class="breadcrumb--link">Home / </a>
                        <a href="linha-do-tempo" title="Linha do Tempo" class="breadcrumb--link">Linha do Tempo</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="container">
        <div class="linha-do-tempo">

            <?php foreach ($linhas_tempo as $linha): ?>
                <?php
                    // --- Formata a data ---
                    $data_inicio = $linha['data_inicial'] ? traduzirMeses(date('d M. Y', strtotime($linha['data_inicial']))) : '';
                    $data_final  = $linha['data_final']  ? traduzirMeses(date('d M. Y', strtotime($linha['data_final'])))  : '';

                    // --- Se tiver período ---
                    $periodo = $data_inicio;
                    if ($data_final && $data_final != $data_inicio) {
                        $periodo = "$data_inicio - $data_final";
                    }

                    // --- Prepara o ID único e o título ---
                    $id_html = htmlspecialchars($linha['id']);
                    $descricao = nl2br($linha['descricao']);
                ?>

                <div class="linha-do-tempo--linha" id="<?= $id_html ?>">
                    <h2 class="linha-do-tempo--title" id="<?= $linha['codigo'] ?>">
                        <strong><?= $periodo ?></strong>
                        <span><?= htmlspecialchars($linha['titulo']); ?></span>
                    </h2>

                    <div class="linha-do-tempo--content">
                        <div class="carrossel-cards" id="carr_<?= $linha['codigo'] ?>"></div>

                        <div class="linha-do-tempo--desc">
                            <?= $descricao ?>
                        </div>

                        <button class="btn btn-desc">
                            <span class="material-symbols-outlined">expand_more</span>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>

        </div>
    </div>

</main>