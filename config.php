<?php
    // Inclui o arquivo de configuração e armazena o retorno em uma variável
    $config = require 'config/custom/envs.php';

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