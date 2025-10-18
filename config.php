<?php
    // Caminho para o arquivo de configuração
    $configPath = __DIR__ . '/config/custom/envs.php';

    // Se o arquivo não existir, redireciona para a instalação
    if (!file_exists($configPath)) {
        header('Location: install');
        exit;
    }

    // Se existir, carrega o arquivo normalmente
    $config = require $configPath;

    // Agora você pode acessar cada item assim:
    $db_host = $config['db_host'];
    $db_name = $config['db_name'];
    $db_user = $config['db_user'];
    $db_pass = $config['db_password'];

    try{
        //Conexão com a porta
        $conn = new PDO("mysql:host=$db_host;dbname=" . $db_name, $db_user, $db_pass);

        //Conexão sem a porta
        //$conn = new PDO("mysql:host=$host;dbname=" . $dbname, $user, $pass);
        // echo "Conexão com banco de dados realizado com sucesso!";
    }catch(PDOException $err){
        // echo "Erro: Conexão com banco de dados não realizado com sucesso. Erro gerado " . $err->getMessage();
    }

    define('INCLUDE_PATH', $config['institution_url']);
    define('INCLUDE_FILE_PATH', INCLUDE_PATH . 'app/functions/serve_file.php');