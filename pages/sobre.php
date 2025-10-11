<?php
    // $codigo = $_GET['cod'] ?? null;

    // if (!$codigo) {
    //     header('Location: ' . INCLUDE_PATH);
    // }

    // // --- Busca os acervos para o filtro lateral ---
    // $sql = "SELECT * FROM sobre WHERE codigo = ?";

    // $stmt = $conn->prepare($sql);
    // $stmt->execute([$codigo]);
    // $data = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<main class="page-linha-do-tempo">
    <section class="section-default">
        <div class="topo">
            <div class="container">
            <div class="info">
                <h1 class="title"><?= $data['titulo']; ?></h1>
                <div class="breadcrumb --default">
                <a href="<?= INCLUDE_PATH; ?>" title="Home" class="breadcrumb--link">Home / </a>
                <a href="sobre" title="Sobre" class="breadcrumb--link "><?= $data['titulo']; ?></a>
                </div>
            </div>
            </div>
        </div>

        <div class="interno container">
            <h2 class="interno--title --active"><?= $data['titulo']; ?></h2>
            <div class="interno--content">
                <?= $data['conteudo']; ?>
            </div>
        </div>
    </section>
</main>