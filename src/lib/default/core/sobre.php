<?php

class sobre extends entidade
{
    function __construct()
    {
        $this->recurso_sistema_codigo = objeto_base::ler_recurso_sistema_codigo(get_class($this));

        $this->tabela_banco = $this->inicializar_tabela_banco();
        $this->chave_primaria = $this->inicializar_chave_primaria();
        $this->atributos = $this->inicializar_atributos();
        $this->relacionamentos = $this->inicializar_relacionamentos();
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

        // Novo atributo: checkbox de ativação do acervo
        $va_atributos["sobre_ativar_acervo"] = [
            "sobre_ativar_acervo",
            "coluna_tabela" => "ativar_acervo",
            "tipo_dado" => "i"
        ];

        $va_atributos['sobre_acervo_codigo'] = [
            'sobre_acervo_codigo',
            'coluna_tabela' => 'acervo_codigo',
            'tipo_dado' => 'i',
            'objeto' => 'acervo'
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

        // ✅ Checkbox: ativar acervo
        $va_campos_edicao["sobre_ativar_acervo"] = [
            "html_checkbox_input",
            "nome" => "sobre_ativar_acervo",
            "label" => "Associar a um acervo?",
            "descricao" => "Marque para selecionar um item de acervo."
        ];

        $va_campos_edicao["sobre_acervo_codigo"] = [
            "html_combo_input",
            "nome" => "sobre_acervo_codigo",
            "label" => "Acervo",
            "objeto" => "conjunto_documental",
            "atributos" => ["acervo_codigo", "acervo_nome"],
            "atributo" => "acervo_codigo",
            "sem_valor" => true,
            "atributo_obrigatorio" => true,
            "dependencia" => [
                [
                    "campo" => "sobre_instituicao_codigo",
                    "atributo" => "acervo_codigo_0_acervo_instituicao_codigo",
                    "obrigatoria" => false
                ]
            ]
        ];

        return $va_campos_edicao;
    }

    public function inicializar_filtros_navegacao($pn_bibliografia_codigo = '')
    {
        $va_filtros_navegacao = array();
        parent::inicializar_filtros_navegacao($pn_bibliografia_codigo);

        $va_filtros_navegacao["sobre_acervo_codigo_0_acervo_instituicao_codigo"] = [
            "html_combo_input",
            "nome" => "sobre_acervo_codigo_0_acervo_instituicao_codigo",
            "label" => "Instituição",
            "objeto" => "instituicao",
            "atributos" => ["instituicao_codigo", "instituicao_nome"],
            "atributo" => "instituicao_codigo",
            "sem_valor" => true,
            "atributo_obrigatorio" => true,
            "operador_filtro" => "=",
            "dependencia" => [
                [
                    "tipo" => "interface",
                    "campo" => "instituicao_codigo",
                    "atributo" => "instituicao_codigo",
                    "obrigatoria" => true
                ]
            ],
            "conectar" => [
                [
                    "campo" => "sobre_acervo_codigo",
                    "atributo" => "acervo_codigo_0_acervo_instituicao_codigo"
                ]
            ],
            "css-class" => "form-select"
        ];

        $va_filtros_navegacao["sobre_acervo_codigo"] = [
            "html_combo_input",
            "nome" => "sobre_acervo_codigo",
            "label" => "Fundo/coleção",
            "objeto" => "conjunto_documental",
            "atributos" => ["acervo_codigo", "acervo_nome"],
            "atributo" => "acervo_codigo",
            "sem_valor" => true,
            "atributo_obrigatorio" => true,
            "operador_filtro" => "=",
            "dependencia" => [
                [
                    "tipo" => "interface",
                    "campo" => "sobre_acervo_codigo_0_acervo_instituicao_codigo",
                    "atributo" => "acervo_codigo_0_acervo_instituicao_codigo",
                    "obrigatoria" => true
                ],
                [
                    "tipo" => "interface",
                    "campo" => "acervo_codigo",
                    "atributo" => "acervo_codigo",
                    "obrigatoria" => true
                ]
            ],
            "css-class" => "form-select"
        ];

        return array_merge($va_filtros_navegacao, $this->filtros_navegacao);
    }

    public function inicializar_visualizacoes()
    {
        $va_campos_visualizacao = array();
        parent::inicializar_visualizacoes();

        $va_campos_visualizacao["sobre_codigo"] = ["nome" => "sobre_codigo", "exibir" => false];
        $va_campos_visualizacao["sobre_nome"] = ["nome" => "sobre_nome"];
        $va_campos_visualizacao["sobre_titulo"] = ["nome" => "sobre_titulo"];
        $va_campos_visualizacao["sobre_id"] = ["nome" => "sobre_id"];
        $va_campos_visualizacao["sobre_conteudo"] = ["nome" => "sobre_conteudo"];
        $va_campos_visualizacao["sobre_ativar_acervo"] = ["nome" => "sobre_ativar_acervo"];
        $va_campos_visualizacao["sobre_acervo"] = ["nome" => "sobre_acervo"];

        $va_campos_visualizacao["sobre_acervo_codigo"] = [
            "nome" => "sobre_acervo_codigo",
            "formato" => ["campo" => "acervo_nome"]
        ];

        $this->visualizacoes["lista"]["campos"] = array_merge($this->visualizacoes["lista"]["campos"], $va_campos_visualizacao);
        $this->visualizacoes["lista"]["order_by"] = ["sobre_nome" => "Nome"];

        $this->visualizacoes["navegacao"]["campos"] = array_merge($va_campos_visualizacao, $this->visualizacoes["navegacao"]["campos"]);
        $this->visualizacoes["navegacao"]["order_by"] = ["sobre_nome" => "Nome"];
        $this->visualizacoes["navegacao"]["ordem_campos"] = [
            "sobre_nome" => ["label" => "Nome", "main_field" => true],
            "sobre_acervo_codigo" => "Fundo/Coleção"
        ];

        $this->visualizacoes["ficha"]["campos"] = array_merge($va_campos_visualizacao, $this->visualizacoes["ficha"]["campos"]);
        $this->visualizacoes["ficha"]["ordem_campos"] = [
            "sobre_nome" => ["label" => "Nome", "main_field" => true],
            "sobre_titulo" => ["label" => "Título"],
            "sobre_id" => ["label" => "ID"]
        ];
    }
}

?>