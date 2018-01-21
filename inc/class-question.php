<?php

//
// Класс для обработки текстового описания вопросов (с ответами) в структурированный массив
// 
//


defined( 'ABSPATH' ) || exit;



class mif_qm_question  {


    // Маркеры для выбираемых ответов
    
    private $mark_choice = '-+*~';

    // Маркеры для полей ввода

    private $mark_input = '>%';

   
    
    
    function __construct()
    {
     
        // Шаблоны для выделения вариантов ответов

        $this->pattern_choice = '/^[' . $this->mark_choice . ']/';
        $this->pattern_input = '/^[' . $this->mark_input . ']+/';
        $this->pattern_answers = '/^[' . $this->mark_choice . $this->mark_input . ']/';

        // Шаблон мета-информации ответов

        $this->pattern_meta = '/\(.*\)/U';
       
    }

    
       
    //
    // Преобразует текстовое описание отдельного вопроса (с ответами) в структурированный массив
    //

    function parse( $text ) 
    {
        $question = array();

        $arr = preg_split( '/\\r\\n?|\\n/', $text );

        $description = '';
        $answers_txt = array();
        $answers_flag = false;
        $n = -1;
        
        foreach ( $arr as $item ) {

            // Пропустить строки, если они с комментариями

            if ( preg_match( '/^\/\//', $item ) ) continue;
            if ( preg_match( '/^#/', $item ) ) continue;

            // Переключиться со сбора строк формулировки вопроса на сбор строк ответов

            if ( preg_match( $this->pattern_answers, $item ) ) $answers_flag = true;
            
            if ( $answers_flag ) {

                // Собираем ответы

                if ( preg_match( $this->pattern_answers, $item ) ) $n++;
                if ( ! isset( $answers_txt[$n] ) ) $answers_txt[$n] = '';
                $answers_txt[$n] .= $item . "\n";

            } else {

                // Собираем сам вопрос (формулировку задания)

                $description .= $item . "\n";

            }
            
        }

        $description = trim( preg_replace( '/^=/', '', $description ) );
        $answers_txt = array_map( 'trim', $answers_txt );
        
        
        $answers = $this->parse_answers( $answers_txt );
        
        $question['question'] = $description;
        $question['mode'] = $answers['mode'];
        $question['answers'] = $answers['answers'];

        return $question;
    }

    
    
    
    // 
    // Получить структурированный массив ответов из их текстового описания
    // 
    
    private function parse_answers( $text = '' )
    {
        $answers = array();

        // Получить массив описания ответа "как есть"
        
        $answers_raw = $this->get_answers_raw( $text );
        
        // Определить тип вопроса (одиночный, множественный или др.)

        $mode = $this->get_answers_mode( $answers_raw );
        
        // Составить массив описаний в зависимости от типа

        // Текст с автоматической проверкой

        if ( $mode == 'text' ) {
            
            // Выбрать все текстовые поля с автоматической проверкой

            foreach ( $answers_raw as $item ) {
                
                if ( $item['type'] == '>!' ) {
                    
                    $item['type'] = 'text';
                    $answers[] = $item;

                }
                
            }

        }

        // Текст с ручной проверкой и (или) файлы

        if ( $mode == 'open' ) {

            // Выбрать только первое текстовое поле с ручной проверкой и все поля с загрузкой файлов
            
            $flag = true;
            
            foreach ( $answers_raw as $item ) {
                
                if ( $item['type'] == '%' ) $item['type'] = 'file';
                if ( $item['type'] == '>~' ) $item['type'] = 'text';
                
                if ( $item['type'] == 'file' ) $answers[] = $item;
                if ( $item['type'] == 'text' && $flag ) { 
                    
                    $answers[] = $item;
                    $flag = false;
                    
                }    
                
            }

            // Если ничего нет, то обычное текстовое поле

            if ( empty( $answers ) ) $answers[] = array( 'size' => 3, 'type' => 'text' );
                
        }    

        // Матричная сортировка

        if ( $mode == 'matching' ) {

            $arr = array();

            foreach ( $answers_raw as $item ) {
                
                if ( in_array( $item['type'], array( '~', '-', '+', '*' ) ) ) $arr[] = $item;

            }
            
            $items = count( $arr );
            $matchings =  floor( $items / 2 );

            for ( $i = 0; $i < $matchings; $i++ ) {

                $key = $arr[$i*2]['answer'];
                $value = $arr[$i*2+1]['answer'];

                $answers[] = array( 'type' => 'matching', 'key' => $key, 'value' => $value );

            }

        }

        // Простая сортировка

        if ( $mode == 'sort' ) {

            // Выбрать поля сортировки, указать порядковые номера

            $index = 0;

            foreach ( $answers_raw as $item ) {
                
                if ( $item['type'] == '~' ) {
                    
                    $item['type'] = 'sort';
                    $item['index'] = $index++;

                    $answers[] = $item;

                }
                
            }

        }

        // Множественный выбор

        if ( $mode == 'multiple' ) {

            // Выбрать поля правильных и неправильных ответов, указать статус правильности

            foreach ( $answers_raw as $item ) {
                
                if ( $item['type'] == '-' || $item['type'] == '+' || $item['type'] == '*' ) {
                    
                    $item['well'] = ( $item['type'] == '-' ) ? 'no' : 'yes';
                    $item['type'] = 'multiple';

                    $answers[] = $item;

                }
                
            }
            
        }

        // Одиночный выбор

        if ( $mode == 'single' ) {
            
            // Выбрать поля правильного и неправильных ответов, указать статус правильности

            $flag = false;
            
            foreach ( $answers_raw as $item ) {
                

                if ( $item['type'] == '-' || $item['type'] == '+' ) {
                    
                    if ( $item['type'] == '+' ) $flag = true;

                    $item['well'] = ( $item['type'] == '-' ) ? 'no' : 'yes';
                    $item['type'] = 'single';

                    $answers[] = $item;

                }
                
            }

            // Если не указан правильный ответ, то правильным считается первый

            if ( ! $flag ) $answers[0]['well'] = 'yes';
            
        }

        return array( 'mode' => $mode, 'answers' => $answers );

    }




    //
    // Опеределяет тип вопроса (одиночный, множественный, сортировка и др.)
    //
    //      open - открытый ввод текста (возможно, с прикреплением файлов)
    //      text - ввод текста с автоматической проверкой
    //      single - одиночный выбор
    //      multiple - множественный выбор
    //      sort - сортировка
    //      matching - матричная сортировка
    //
    
    private function get_answers_mode( $answers_raw = array() )
    {
        $types = array();

        // Сколько каких маркеров встречается?
        
        foreach ( $answers_raw as $item ) {

            $types[$item['type']] = ( isset( $types[$item['type']] ) ) ? $types[$item['type']] + 1 : 1;

        }

        // Определить тип вопроса по маркерам ответов

        if( isset( $types['>!'] ) ) {

            // Есть текстовое поле с данными для автоматической проверки

            $mode = 'text';
            
        } elseif ( isset( $types['>~'] ) || isset( $types['%'] ) ) {
            
            // Есть текстовое поле с ручной проверкой или поля для загрузки файлов

            $mode = 'open';

        } elseif( isset( $types['~'] ) && ( isset( $types['-'] ) || isset( $types['+'] ) || isset( $types['*'] ) ) ) {

            // Есть поле сортировки и какие-то еще другие поля

            $mode = 'matching';
                            
        } elseif( isset( $types['~'] ) ) {
            
            // Есть поле сортировки, а других полей нет

            $mode = 'sort';
                            
        } elseif( isset( $types['*'] ) || ( isset( $types['+'] ) && $types['+'] > 1 ) ) {

            // Есть маркер множественного выбора или несколько маркеров одиночного выбора

            $mode = 'multiple';
                            
        } elseif( isset( $types['+'] ) || isset( $types['-'] ) ) {

            // Есть только один маркер одиночного выбора или такого маркера нет совсем
            
            $mode = 'single';
                            
        } else {

            // Вообще ничего нет

            $mode = 'open';

        }

        return $mode;
    }



    //
    // Производит разбор описания ответов в массив "как есть"
    //
    
    private function get_answers_raw( $answers_txt = array() )

    {
        $arr = array();
        
        foreach ( (array) $answers_txt as $item ) {
            
            if ( preg_match( $this->pattern_choice, $item, $res ) ) {
                
                // Обнаружен ответ с вариантом выбора
                
                $item = trim( preg_replace( $this->pattern_choice, '', $item ) );
                $arr[] = array( 'answer' => $item, 'type' => $res[0] );

            }

            if ( preg_match( $this->pattern_input, $item, $res ) ) {
                
                // Обнаружен ответ с вводом текста или загрузкой файла
                
                $item = preg_replace( $this->pattern_input, '', $item );

                $meta_arr = array();

                if ( preg_match_all( $this->pattern_meta, $item, $meta ) ) {
                    
                    // Взять метаданные, если они есть

                    $item = preg_replace( $this->pattern_meta, '', $item );
                    
                    $meta_txt = implode( '|', $meta[0] );
                    $meta_txt = preg_replace( '/[()]/', '', $meta_txt );

                    $meta_arr = explode( '|', $meta_txt );
                    $meta_arr = array_map( 'trim', $meta_arr );

                }

                $item = strim( $item );
                $type = $res[0]{0};

                $data = array();

                $data['size'] = strlen( $res[0] );
                if ( $item != '' ) $data['caption'] = $item;
                if ( ! empty( $meta_arr ) ) $data['meta'] = $meta_arr;

                $data['type'] = $type;
                if ( $type == '>' ) $data['type'] = ( empty( $meta_arr ) ) ? '>~' : '>!';

                $arr[] = $data;

            }

        }

        return $arr;
    }


}

?>