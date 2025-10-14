<?php

class linha_tempo extends objeto_base
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
        return "linha_tempo";
    }

    public function inicializar_chave_primaria()
    {
        return $va_chave_primaria['linha_tempo_codigo'] = [
            'linha_tempo_codigo',
            'coluna_tabela' => 'codigo',
            'tipo_dado' => 'i'
        ];
    }

    public function inicializar_atributos()
    {
        $va_atributos = array();

        $va_atributos['linha_tempo_id'] = [
            'linha_tempo_id',
            'coluna_tabela' => 'id',
            'tipo_dado' => 's',
            'processar' => [
                'slugfy',
                ['linha_tempo_titulo']
            ]
        ];

        $va_atributos['linha_tempo_titulo'] = [
            'linha_tempo_titulo',
            'coluna_tabela' => 'titulo',
            'tipo_dado' => 's'
        ];

        $va_atributos['linha_tempo_data'] = [
            'linha_tempo_data',
            'coluna_tabela' => ['data_inicial' => 'data_inicial', 'data_final' => 'data_final', 'presumido' => 'data_presumida', 'sem_data' => 'sem_data'],
            'tipo_dado' => 'dt'
        ];

        $va_atributos['linha_tempo_descricao'] = [
            'linha_tempo_descricao',
            'coluna_tabela' => 'descricao',
            'tipo_dado' => 's'
        ];

        return $va_atributos;
    }

    public function inicializar_relacionamentos($pn_recurso_sistema_codigo = null)
    {
        $va_relacionamentos = array();

        $va_relacionamentos['linha_tempo_item_acervo_codigo'] = [
            [
                'linha_tempo_item_acervo_codigo',
                'linha_tempo_item_acervo_sequencia'
            ],
            'tabela_intermediaria' => 'linha_tempo_item_acervo',
            'chave_exportada' => 'linha_tempo_codigo',
            'campos_relacionamento' => [
                'linha_tempo_item_acervo_codigo' => 'item_acervo_codigo',
                'linha_tempo_item_acervo_sequencia' => ['sequencia', "valor_sequencial" => true],
            ],
            'tipos_campos_relacionamento' => ['i', 'i'],
            'tabela_relacionamento' => 'item_acervo',
            'objeto' => 'item_acervo',
            'alias' => 'itens de acervo'
        ];

        return $va_relacionamentos;
    }

    public function inicializar_campos_edicao($pn_objeto_codigo = '')
    {
        $va_campos_edicao = array();

        $va_campos_edicao["linha_tempo_titulo"] = [
            "html_text_input",
            "nome" => "linha_tempo_titulo",
            "label" => "Título",
            "foco" => true
        ];

        $va_campos_edicao["linha_tempo_data"] = [
            "html_date_input",
            "nome" => "linha_tempo_data",
            "label" => "Período"
        ];

        $va_campos_edicao["linha_tempo_item_acervo_codigo"] = [
            "html_autocomplete",
            "nome" => ["linha_tempo_item_acervo", "linha_tempo_item_acervo_codigo"],
            "label" => "Itens do acervo relacionados",
            "objeto" => "item_acervo",
            "atributos" => [
                "item_acervo_codigo",
                "item_acervo_identificador" => ["item_acervo_identificador", "item_acervo_dados_textuais_0_item_acervo_titulo"]
            ],
            "multiplos_valores" => true,
            "procurar_por" => "item_acervo_identificador",
            "visualizacao" => "lista",
            "draggable" => true
        ];

        $va_campos_edicao["linha_tempo_descricao"] = [
            "html_text_input",
            "nome" => "linha_tempo_descricao",
            "label" => "Descrição",
            "numero_linhas" => 8,
        ];

        return $va_campos_edicao;
    }

    public function inicializar_visualizacoes()
    {
        $va_campos_visualizacao = array();

        $va_campos_visualizacao["linha_tempo_codigo"] = [
            "nome" => "linha_tempo_codigo",
            "exibir" => false
        ];

        $va_campos_visualizacao["linha_tempo_titulo"] = [
            "nome" => "linha_tempo_titulo",
            "label" => "Título"
        ];

        $va_campos_visualizacao["linha_tempo_data"] = [
            "nome" => "linha_tempo_data",
            "formato" => ["data" => "completo"]
        ];

        $va_campos_visualizacao["linha_tempo_descricao"] = [
            "nome" => "linha_tempo_descricao",
            "label" => "Descrição"
        ];

        $this->visualizacoes["lista"]["campos"] = $va_campos_visualizacao;
        $this->visualizacoes["lista"]["order_by"] = ["linha_tempo_data" => "ASC"];

        $this->visualizacoes["navegacao"]["campos"] = $va_campos_visualizacao;
        $this->visualizacoes["navegacao"]["order_by"] = ["linha_tempo_titulo" => "ASC"];
        $this->visualizacoes["navegacao"]["ordem_campos"] = [
            "linha_tempo_titulo" => ["label" => "Título", "main_field" => true],
        ];

        $va_campos_visualizacao["linha_tempo_item_acervo_codigo"] = [
            "nome" => "linha_tempo_item_acervo_codigo",
            "formato" => ["campo" => "item_acervo_identificador"]
        ];

        $this->visualizacoes["ficha"]["campos"] = $va_campos_visualizacao;
        $this->visualizacoes["ficha"]["ordem_campos"] = [
            "linha_tempo_titulo" => ["label" => "Título", "main_field" => true],
            "linha_tempo_item_acervo_codigo" => "Itens do acervo selecionados"
        ];
    }
}

?>