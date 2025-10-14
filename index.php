<?php
    session_start();
    ob_start();
    include_once('config.php');

    // Captura a URL amigável
    $url = isset($_GET['url']) ? $_GET['url'] : 'home';
    $url = rtrim($url, '/'); // remove barra final se houver
    $segments = explode('/', $url);

    // Exemplo de uso: /item/arquivo/asc_001376
    $page = $segments[0] ?? 'home';
    $action = $segments[1] ?? null;
    $param = $segments[2] ?? null;
?>
<!DOCTYPE html>
<html lang="pt-br">
  <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acervo Sueli Carneiro</title>
    <link rel="android-chrome" sizes="192x192" href="<?= INCLUDE_PATH; ?>assets/img/favicon_io/android-chrome-192x192.png">
    <link rel="android-chrome" sizes="521x512" href="<?= INCLUDE_PATH; ?>assets/img/favicon_io/android-chrome-512x512.png">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= INCLUDE_PATH; ?>assets/img/favicon_io/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= INCLUDE_PATH; ?>assets/img/favicon_io/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= INCLUDE_PATH; ?>assets/img/favicon_io/favicon-32x32.png">
    <link rel="icon" type="image/png" href="<?= INCLUDE_PATH; ?>assets/img/favicon_io/favicon.ico">

    <!-- Externo -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
    <link rel="stylesheet" href="https://unpkg.com/@coreui/icons/css/all.min.css">

    <?php if ($page == 'item'): ?>
    <!-- Lightbox -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.0.0-beta.4/css/lightgallery.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/justifiedGallery@3.8.1/dist/css/justifiedGallery.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.0.0-beta.4/css/lg-zoom.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.0.0-beta.3/css/lg-fullscreen.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.0.0-beta.3/css/lg-thumbnail.css">
    <?php endif; ?>

    <?php if ($page == 'acervo'): ?>
    <script type="text/javascript" src="//code.jquery.com/jquery-1.11.0.min.js"></script>
    <?php endif; ?>

    <!-- Local -->
    <link rel="stylesheet" href="<?= INCLUDE_PATH; ?>assets/css/main.css">
</head>

<body>

    <!-- Cabeçalho -->
    <?php include('layout/header.php'); ?>

    <!-- Conteúdo -->
    <?php
        // Se existir um arquivo com o nome da primeira parte da URL
        if (file_exists("pages/{$page}.php") && $page !== 'sobre') {
            include("pages/{$page}.php");
        } else {
            $acervo_s = $_GET['s'] ?? ''; // parâmetro ?s=, se existir

            if (!empty($acervo_s)) {
                // --- Busca o acervo pelo nome (primeira palavra) ---
                $acervo_s = strtolower(trim($acervo_s));

                $sql = "
                    SELECT s.*
                    FROM sobre s
                    INNER JOIN acervo a ON a.codigo = s.acervo_codigo
                    WHERE s.id = ?
                    AND LOWER(SUBSTRING_INDEX(a.nome, ' ', 1)) = ?
                    LIMIT 1
                ";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$page, $acervo_s]);
                $data = $stmt->fetch(PDO::FETCH_ASSOC);

            } else {
                // --- Caso não tenha ?s=, busca a primeira página da sobre ---
                $sql = "SELECT * FROM sobre WHERE id = ? ORDER BY codigo ASC LIMIT 1";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$page]);
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
            }

            if ($data) {
                include("pages/sobre.php");
            } else {
                // A página não existe
                header('Location: ' . INCLUDE_PATH . '404');
                exit;
            }
        }
    ?>
    <!-- Fim Conteúdo -->

    <!-- Rodapé -->
    <?php include('layout/footer.php'); ?>

    <!-- Externo -->
    <script type="text/javascript" src="//code.jquery.com/jquery-1.11.0.min.js"></script>
    <script type="text/javascript" src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
    <script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/lightgallery@2.0.0-beta.3/lightgallery.umd.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/lightgallery@2.0.0-beta.3/plugins/zoom/lg-zoom.umd.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/lightgallery@2.0.0-beta.3/plugins/thumbnail/lg-thumbnail.umd.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/lightgallery@2.0.0-beta.3/plugins/fullscreen/lg-fullscreen.umd.js"></script>

    <!-- Local -->
    <script src="<?= INCLUDE_PATH; ?>assets/js/main.js"></script>
    <script src="<?= INCLUDE_PATH; ?>assets/js/carrossel-single-images.js"></script>
    <script src="<?= INCLUDE_PATH; ?>assets/js/lightGallery.js"></script>
    <script src="<?= INCLUDE_PATH; ?>assets/js/tolltip.js"></script>

	<script src="https://plugin.handtalk.me/web/latest/handtalk.min.js"></script>
	<script>
		var ht = new HT({
			token:"b981fbcae1c596ce73bcde4dbc7d7555",
			avatar: "MAYA",
			pageSpeech: true
		});
	</script>

</body>
</html>