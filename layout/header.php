<?php
    // --- Busca os acervos para o filtro lateral ---
    $sql = "SELECT * FROM sobre ORDER BY titulo ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $paginas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<header>
    <div class="container">
        <a href="<?= INCLUDE_PATH; ?>">
            <h1 class="logo">
                <strong>ACERVO</strong>
                <strong>SUELI</strong>
                <strong>CARNEIRO</strong>
            </h1>
        </a>
        <span class="menu-btn material-symbols-outlined">menu</span>
        <nav class="menu">
            <a href="acervo" class="menu__link">Acervo</a>
            <a href="exposicoes-virtuais" class="menu__link">EXPOSIÇÕES VIRTUAIS</a>
            <a href="linha-do-tempo" class="menu__link">LINHA DO TEMPO</a>
            <div href="sobre" class="menu__link --has-child">
                <span>SOBRE <span class="icon material-symbols-outlined">expand_more</span></span>
                <div class="submenu">
                    <?php foreach ($paginas as $pagina): ?>
                    <a href="<?= $pagina['id']; ?>" class="menu__link"><?= $pagina['nome']; ?></a>
                    <?php endforeach; ?>
                    <a href="arranjo" class="menu__link">Arranjo</a>
                    <a href="biografia" class="menu__link">Biografia</a>
                    <a href="bibliografia" class="menu__link">Bibliografia</a>
                    <a href="ficha-tecnica" class="menu__link">Ficha técnica</a>
                </div>
            </div>

            <div class="serach-mobile">
                <form action="acervo" method="get" class="search">
                    <input type="search" name="s" id="search" placeholder="Busca" autocomplete="off" value="<?= $_GET['s'] ?? ""; ?>">
                    <button type="submit">
                    <span class="material-symbols-outlined">search</span>
                    </button>
                </form>
            </div>
        </nav>

        <form id="form_search" action="acervo" method="get" class="search">
            <input type="search" name="s" id="search" placeholder="Busca" autocomplete="off" value="<?= $_GET['s'] ?? ""; ?>">
            <button type="submit">
                <span class="material-symbols-outlined">search</span>
            </button>
        </form>
    </div>
</header>