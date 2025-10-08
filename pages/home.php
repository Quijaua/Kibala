<main>
      <section class="hero">
        <div class="container">
          <div class="logo">
            <strong>ACERVO</strong>
            <strong>SUELI</strong>
            <strong>CARNEIRO</strong>
          </div>

          <div class="hero-content">
            <h2 class="hero-title">Filósofa, escritora e ativista </h2>
            <div class="hero-desc"><p>Os documentos que Sueli Carneiro acumulou ao longo de sua vida, até 2022, encontram-se aqui &nbsp;disponíveis em formato digital, junto com as referências de livros que compõem sua biblioteca pessoal. Estão publicados para o apoio às &nbsp;pesquisas e ampliação do entendimento de sua obra, da história do ativismo por ela empreendido desde os anos 1970, em sua luta por igualdade racial e de gênero. Este é um projeto em aprimoramento constante: seguiremos inserindo novos documentos e também completando descrições do que já está disponível. Para dúvidas e contribuições com informações referentes aos conteúdos deste acervo, escreva para <a href="mailto:acervosuelicarneiro@casasuelicarneiro.org.br">acervosuelicarneiro@casasuelicarneiro.org.br</a></p></div>
          </div>

          <div class="hero-wrapper-holder" style="display:none">
            <div class="hero-wrapper">
                <div class="hero-infos-content">
                  <strong>Arquivo Sueli Carneiro</strong>
                  <span>2573</span>
                </div>

                <div class="hero-infos">
                                      <div class="hero-infos-content">
                        <strong>Ativismo</strong>
                        <span>1381</span>
                    </div>
                                      <div class="hero-infos-content">
                        <strong>Geledés</strong>
                        <span>575</span>
                    </div>
                                      <div class="hero-infos-content">
                        <strong>Vida Civil</strong>
                        <span>600</span>
                    </div>
                                      <div class="hero-infos-content">
                        <strong>Vida Profissional</strong>
                        <span>12</span>
                    </div>
                                  </div>
              </div>

              <div class="hero-wrapper">
                <div class="hero-infos-content">
                  <strong>Biblioteca Sueli Carneiro</strong>
                  <span>1494</span>
                </div>
                <div class="hero-infos">
                  <div class="hero-infos-content">
                    <strong>LIVROS</strong>
                    <span>1184</span>
                  </div>
                  <div class="hero-infos-content">
                    <strong>PERIÓDICOS</strong>
                    <span>310</span>
                  </div>
                </div>
              </div>
          </div>

    </section>

    <?php
        // Busca todos os itens do acervo (substitua a condição WHERE se quiser filtrar)
        $sql = "SELECT
                    a.*,
                    c.codigo AS codigo_livro,
                    c.cutter_pha,
                    c.numero_paginas,
                    d.codigo AS acervo_codigo,
                    d.identificador AS acervo_identificador,
                    d.nome AS acervo_nome,
                    f.nome AS categoria,
                    g.nome AS subcategoria
                FROM item_acervo a
                INNER JOIN documento b ON a.codigo = b.item_acervo_codigo
                LEFT JOIN livro c ON a.codigo = c.item_acervo_codigo
                LEFT JOIN acervo d ON a.acervo_codigo = d.codigo
                LEFT JOIN agrupamento e ON b.agrupamento_codigo = e.codigo
                LEFT JOIN agrupamento_dados_textuais f ON e.codigo = f.agrupamento_codigo
                LEFT JOIN agrupamento_dados_textuais g ON e.agrupamento_superior_codigo = g.agrupamento_codigo
                ORDER BY a.identificador ASC"; // ou outra ordenação que desejar

        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $documentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <section class="section-default documentos">
        <div class="topo">
            <div class="container">
                <div class="info">
                    <a href="acervo?secao=arquivo"><h2 class="title">Arquivo</h2></a>
                    <h3 class="subtitle"><?= count($documentos); ?> documentos</h3>
                </div>
            </div>
        </div>

        <div class="content container">
            <div class="carrossel-cards">
                <?php foreach ($documentos as $dado): ?>

                    <?php
                        $dado['imagem'] = (isset($dado['imagem_base64']) && !empty($dado['imagem_base64'])) ? "data:image/png;base64,{$dado['imagem_base64']}" : INCLUDE_PATH . "assets/img/sem-imagem.png";    
                    ?>

                    <!-- ===== HTML do card ===== -->
                    <a href="<?= INCLUDE_PATH; ?>item/arquivo/<?= $dado['codigo']; ?>" class="card" id="">
                        <div class="card-img">
                            <img src="<?= $dado['imagem']; ?>" alt="">
                        </div>

                        <h4 class="card-title"><?= $dado['identificador']; ?></h4>

                        <div class="card-content">
                            <strong>Data</strong>
                            <span>
                                <?php
                                    if (isset($dado['data_inicial']) && !empty($dado['data_inicial'])) {
                                        echo date('Y', strtotime($dado['data_inicial']));
                                    } else {
                                        echo "s.d.";
                                    }
                                ?>
                            </span>

                            <strong>Grupo/Subgrupo</strong>
                            <span>
                                <?php if (isset($dado['subcategoria']) && !empty($dado['subcategoria'])): ?>
                                <?= $dado['subcategoria']; ?>
                                >
                                <?php endif; ?>
                                <?= $dado['categoria']; ?>
                            </span>
                        </div>
                    </a>

                <?php endforeach; ?>
            </div>
        </div>
    </section>

    
    <?php
        // Busca todos os itens do acervo (substitua a condição WHERE se quiser filtrar)
        $sql = "SELECT
                a.*,
                b.codigo AS codigo_livro,
                b.cutter_pha,
                b.numero_paginas,
                c.codigo AS acervo_codigo,
                c.identificador AS acervo_identificador,
                c.nome AS acervo_nome
            FROM item_acervo a
            INNER JOIN livro b ON a.codigo = b.item_acervo_codigo
            LEFT JOIN acervo c ON a.acervo_codigo = c.codigo
            ORDER BY a.titulo ASC"; // ou outra ordenação que desejar

        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $livros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <section class="section-default biblioteca">
        <div class="topo">
            <div class="container">
                <div class="info">
                    <a href="acervo?secao=biblioteca"><h2 class="title">Biblioteca</h2></a>
                    <h3 class="subtitle"><?= count($livros); ?> exemplares</h3>
                </div>
            </div>
        </div>

        <div class="content container">

            <div class="carrossel-cards">
                <?php foreach ($livros as $dado): ?>

                    <?php
                        $dado['imagem'] = (isset($dado['imagem_base64']) && !empty($dado['imagem_base64'])) ? "data:image/png;base64,{$dado['imagem_base64']}" : INCLUDE_PATH . "assets/img/sem-imagem.png";

                        // ===== Autores =====
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
                        $dado['autores'] = implode(' | ', $autores);
                    ?>

                    <!-- ===== HTML do card ===== -->
                    <a href="<?= INCLUDE_PATH; ?>item/biblioteca/<?= $dado['codigo']; ?>" class="card" id="">
                        <div class="card-img">
                            <img src="<?= $dado['imagem']; ?>" alt="">
                        </div>

                        <h4 class="card-title"><?= $dado['identificador']; ?></h4>

                        <div class="card-content">
                            <strong>Autoria</strong>
                            <span>
                                <?= $dado['autores']; ?>
                            </span>    

                            <strong>Data</strong>
                            <span>
                                <?php
                                    if (isset($dado['data_inicial']) && !empty($dado['data_inicial'])) {
                                        echo date('Y', strtotime($dado['data_inicial']));
                                    } else {
                                        echo "s.d.";
                                    }
                                ?>
                            </span>
                        </div>
                    </a>

                <?php endforeach; ?>
            </div>

        </div>
    </section>
</main>

<script type="text/javascript" src="//code.jquery.com/jquery-1.11.0.min.js"></script>
<script type="text/javascript" src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
<script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>

<script src="<?= INCLUDE_PATH; ?>assets/js/carrossel-cards.js"></script>
<script src="<?= INCLUDE_PATH; ?>assets/js/carrossel-big-image.js"></script>