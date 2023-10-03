<?php

class tipo_visualizacao extends objeto_base
{

    function __construct()
    {
        $va_campos_visualizacao = array();
        $va_campos_visualizacao["tipo_visualizacao_codigo"] = ["nome" => "tipo_visualizacao_codigo"];
        $va_campos_visualizacao["tipo_visualizacao_nome"] = ["nome" => "tipo_visualizacao_nome"];

        $this->visualizacoes["lista"]["campos"] = $va_campos_visualizacao;
    }

    public function inicializar_chave_primaria()
    {
        //return $va_chave_primaria['genero_textual_codigo'] = ['genero_textual_codigo', 'codigo', 'Codigo', 'i'];
    }

    public function ler($pn_codigo, $ps_visualizacao = 'lista', $pn_idioma_codigo = 1)
    {
        $va_resultado = $this->ler_lista(['tipo_visualizacao_codigo' => $pn_codigo], $ps_visualizacao, 0, 1);

        if (count($va_resultado))
            $va_resultado = $va_resultado[0];

        return $va_resultado;
    }

    public function ler_lista($pa_filtros_busca = null, $ps_visualizacao = "lista", $pn_primeiro_registro = 0, $pn_numero_registros = 20, $pa_order_by = null, $ps_order = null, $pa_log_info = null, $pn_idioma_codigo = 1)
    {
        $va_itens = array();
        $va_resultado = array();

        $va_itens['1'] = ['tipo_visualizacao_codigo' => '1', 'tipo_visualizacao_nome' => 'lista'];
        $va_itens['2'] = ['tipo_visualizacao_codigo' => '2', 'tipo_visualizacao_nome' => 'navegacao'];
        $va_itens['3'] = ['tipo_visualizacao_codigo' => '3', 'tipo_visualizacao_nome' => 'ficha'];

        if (isset($pa_filtros_busca['tipo_visualizacao_codigo']))
            return $va_resultado[] = $va_itens[$pa_filtros_busca['tipo_visualizacao_codigo']];
        else
            return $va_itens;
    }

}

?>