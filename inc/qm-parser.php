<?php

//
// Методы парсинга текста (обработка формулировки вопроса)
// 
//


defined( 'ABSPATH' ) || exit;



class mif_qm_parser { 

    
    function __construct()
    {

    }


    // 
    // Оформление текста перед выводом на экран (формулировка вопроса)
    // 

    public function parse( $text = '' )
    {
        $raw = $text;
        
        $arr = explode( "\n\n", $text );

        foreach ( $arr as $key => $item ) {

            // Перевод строки замемнить на <br> (но не в конце закрывающего тега)

            $item = preg_replace( "/([^>])\n/", "$1<br />", $item );

            // Курсив, полужирный и подчеркивание - специальными символами (//, **. __)

            $item = preg_replace( "/([^:])\/\/(.*?)\/\//", "$1<em>$2</em>", $item );
            $item = preg_replace( "/\*\*(.*?)\*\*/", "<strong>$1</strong>", $item );
            $item = preg_replace( "/\_\_(.*?)\_\_/", "<u>$1</u>", $item );

            $arr[$key] = $item;

        }

        // Склеить в разных абзацах

        $text = '<p>' . implode( '</p><p>', $arr ) .'</p>';

        return apply_filters( 'mif_qm_parser_parser', $text, $raw );
    }


}

?>