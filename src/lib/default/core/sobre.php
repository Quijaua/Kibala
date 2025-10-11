<?php

class sobre extends entidade
{
    function __construct()
    {
        $this->recurso_sistema_codigo = objeto_base::ler_recurso_sistema_codigo(get_class($this));

        $this->tabela_banco = $this->inicializar_tabela_banco();
        $this->chave_primaria = $this->inicializar_chave_primaria();
        $this->atributos = $this->inicializar_atributos();
        $this->inicializar_visualizacoes();
    }

    public function inicializar_tabela_banco()
    {
        return "sobre";
    }

    public function inicializar_chave_primaria()
    {
        return [
            "sobre_codigo",
            "coluna_tabela" => "codigo",
            "tipo_dado" => "i"
        ];
    }

    public function inicializar_atributos()
    {
        $va_atributos = [];

        $va_atributos["sobre_nome"] = [
            "sobre_nome",
            "coluna_tabela" => "nome",
            "tipo_dado" => "s"
        ];

        $va_atributos["sobre_titulo"] = [
            "sobre_titulo",
            "coluna_tabela" => "titulo",
            "tipo_dado" => "s"
        ];

        $va_atributos["sobre_id"] = [
            "sobre_id",
            "coluna_tabela" => "id",
            "tipo_dado" => "s"
        ];

        $va_atributos["sobre_conteudo"] = [
            "sobre_conteudo",
            "coluna_tabela" => "conteudo",
            "tipo_dado" => "s"
        ];

        return $va_atributos;
    }

    public function inicializar_campos_edicao()
    {
        $va_campos_edicao = [];

        $va_campos_edicao["sobre_nome"] = [
            "html_text_input",
            "nome" => "sobre_nome",
            "label" => "Nome",
            "foco" => true
        ];

        $va_campos_edicao["sobre_titulo"] = [
            "html_text_input",
            "nome" => "sobre_titulo",
            "label" => "Título"
        ];

        $va_campos_edicao["sobre_id"] = [
            "html_text_input",
            "nome" => "sobre_id",
            "label" => "ID",
            "descricao" => "Esse ID será responsável por criar o link da página."
        ];

        $va_campos_edicao["sobre_conteudo"] = [
            "html_text_input",
            "nome" => "sobre_conteudo",
            "label" => "Conteúdo da página",
            "numero_linhas" => 15
        ];

        return $va_campos_edicao;
    }

    public function inicializar_visualizacoes()
    {
        $va_campos_visualizacao = array();

        $va_campos_visualizacao["sobre_codigo"] = ["nome" => "sobre_codigo", "exibir" => false];
        $va_campos_visualizacao["sobre_nome"] = ["nome" => "sobre_nome"];
        $va_campos_visualizacao["sobre_titulo"] = ["nome" => "sobre_titulo"];
        $va_campos_visualizacao["sobre_id"] = ["nome" => "sobre_id"];
        $va_campos_visualizacao["sobre_conteudo"] = ["nome" => "sobre_conteudo"];

        $this->visualizacoes["lista"]["campos"] = $va_campos_visualizacao;
        $this->visualizacoes["lista"]["order_by"] = ["sobre_nome" => "Nome"];

        $this->visualizacoes["navegacao"]["campos"] = $va_campos_visualizacao;
        $this->visualizacoes["navegacao"]["order_by"] = ["sobre_nome" => "Nome"];
        $this->visualizacoes["navegacao"]["ordem_campos"] = [
            "sobre_nome" => ["label" => "Nome", "main_field" => true],
        ];

        $this->visualizacoes["ficha"]["campos"] = $va_campos_visualizacao;
        $this->visualizacoes["ficha"]["ordem_campos"] = [
            "sobre_nome" => ["label" => "Nome", "main_field" => true],
            "sobre_titulo" => ["label" => "Título"],
            "sobre_id" => ["label" => "ID"]
        ];
    }
}

?>