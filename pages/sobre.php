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

<main class="page-<?= $data['id']; ?>">
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

        <?php
            // Seleciona todos os "sobre" que têm o mesmo id mais de uma vez
            $sql = "
                SELECT DISTINCT
                    s.id, s.titulo, s.acervo_codigo, a.nome AS acervo_nome
                FROM sobre s
                LEFT JOIN acervo a ON a.codigo = s.acervo_codigo
                WHERE s.id IN (
                    SELECT id FROM sobre GROUP BY id HAVING COUNT(*) > 1
                )
                AND
                s.id = ?
                ORDER BY s.id, a.nome
            ";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$page]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Agrupa por id para exibir juntos
            $paginas_duplicadas = [];
            foreach ($result as $row) {
                $paginas_duplicadas[$row['id']][] = $row;
            }

            // Exibição em HTML
            foreach ($paginas_duplicadas as $id => $acervos) {
                echo '<div class="container">';
                echo '  <div class="categorys-holder">';

                foreach ($acervos as $index => $item) {
                    $acervo_nome = $item['acervo_nome'] ?: 'Acervo Desconhecido';
                    $primeira_palavra = strtolower(explode(' ', trim($acervo_nome))[0]); // Pega a primeira palavra

                    // Lógica corrigida para definir a classe ativa
                    $get_s = isset($_GET['s']) ? strtolower($_GET['s']) : '';
                    $classe_ativa = ($get_s !== '' && $get_s === $primeira_palavra) ? ' -active' : ($get_s === '' && $get_s !== $primeira_palavra && $index === 0 ? ' -active' : '');

                    echo '    <div class="category-content">';
                    echo '      <strong class="category-title' . $classe_ativa . '" id="' . htmlspecialchars($primeira_palavra) . '" onclick="window.location.href=\'?s=' . urlencode($primeira_palavra) . '\'">';
                    echo            htmlspecialchars($acervo_nome);
                    echo '      </strong>';
                    echo '    </div>';
                }

                echo '  </div>';
                echo '</div>';
            }
        ?>

        <?php
            // Seleciona todos os "sobre" que têm o mesmo id mais de uma vez
            $sql = "
                SELECT 
                    a.*
                FROM representante_digital a
                WHERE 
                    a.recurso_sistema_codigo = 83
                AND
                    a.registro_codigo = ?
            ";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$data['codigo']]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>

        <div class="interno container">
            <div class="imagem_container">
                <?php if ($result): ?>
                    <?php foreach ($result as $r): ?>
                        <img src="<?= INCLUDE_FILE_PATH; ?>?file=<?= $r['path']; ?>&size=original" alt="<?= $r['legenda'] ?? "Arranjo Arquivo Sueli Carneiro"; ?>" width="100%" height="auto" class="--horizontal">
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="sobre--content">
                <div class="<?= $data['id']; ?>-content">
                    <h2 class="interno--title --active"><?= $data['titulo']; ?></h2>
                    <div class="interno--content">
                        <?= $data['conteudo']; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>