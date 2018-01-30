<?php

//
// Класс для обработки текстового описания вопросов (с ответами) в структурированный массив
// 
//


defined( 'ABSPATH' ) || exit;

include_once dirname( __FILE__ ) . '/class-core-core.php';

class mif_qm_core_question extends mif_qm_core_core {

    
    function __construct()
    {

        parent::__construct();

    }

    
       
    //
    // Преобразует текстовое описание отдельного вопроса (с ответами) в структурированный массив
    //

    function parse( $text ) 
    {
        $question = array();

        $arr = preg_split( '/\\r\\n?|\\n/', $text );

        $title = '';
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

                $title .= $item . "\n";

            }
            
        }

        $title = trim( preg_replace( $this->pattern_question, '', $title ) );
        $answers_txt = array_map( 'trim', $answers_txt );
        
        
        $answers = $this->parse_answers( $answers_txt );
        
        $question['title'] = $title;
        $question['type'] = $answers['type'];
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

        $type = $this->get_answers_type( $answers_raw );
        
        // Составить массив описаний в зависимости от типа

        // Текст с автоматической проверкой

        if ( $type == 'text' ) {
            
            // Выбрать все текстовые поля с автоматической проверкой

            foreach ( $answers_raw as $item ) {
                
                if ( $item['type'] == '>!' ) {
                    
                    $item['type'] = 'text';
                    $answers[] = $item;

                }
                
            }

        }

        // Текст с ручной проверкой и (или) файлы

        if ( $type == 'open' ) {

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

        if ( $type == 'matching' ) {

            $arr = array();

            foreach ( $answers_raw as $item ) {
                
                if ( in_array( $item['type'], array( '~', '-', '+', '*' ) ) ) $arr[] = $item;

            }
            
            $items = count( $arr );
            $matchings =  floor( $items / 2 );

            for ( $i = 0; $i < $matchings; $i++ ) {

                $answer = $arr[$i*2]['answer'];
                $status = $arr[$i*2+1]['answer'];

                // $answers[] = array( 'type' => 'matching', 'key' => $key, 'value' => $value );
                // $answers[] = array( 'key' => $key, 'value' => $value );
                $answers[] = array( 'label' => $answer, 'answer' => $status );

            }

        }

        // Простая сортировка

        if ( $type == 'sort' ) {

            // Выбрать поля сортировки, указать порядковые номера

            $index = 0;

            foreach ( $answers_raw as $item ) {
                
                if ( $item['type'] == '~' ) {
                    
                    // $item['type'] = 'sort';
                    unset( $item['type'] );
                    $item['status'] = $index++;

                    $answers[] = $item;

                }
                
            }

        }

        // Множественный выбор

        if ( $type == 'multiple' ) {

            // Выбрать поля правильных и неправильных ответов, указать статус правильности

            foreach ( $answers_raw as $item ) {
                
                if ( $item['type'] == '-' || $item['type'] == '+' || $item['type'] == '*' ) {
                    
                    $item['status'] = ( $item['type'] == '-' ) ? 'no' : 'yes';
                    // $item['type'] = 'multiple';
                    unset( $item['type'] );

                    $answers[] = $item;

                }
                
            }
            
        }

        // Одиночный выбор

        if ( $type == 'single' ) {
            
            // Выбрать поля правильного и неправильных ответов, указать статус правильности

            $flag = false;
            
            foreach ( $answers_raw as $item ) {
                

                if ( $item['type'] == '-' || $item['type'] == '+' ) {
                    
                    if ( $item['type'] == '+' ) $flag = true;

                    $item['status'] = ( $item['type'] == '-' ) ? 'no' : 'yes';
                    // $item['type'] = 'single';
                    unset( $item['type'] );

                    $answers[] = $item;

                }
                
            }

            // Если не указан правильный ответ, то правильным считается первый

            if ( ! $flag ) $answers[0]['status'] = 'yes';
            
        }

        return array( 'type' => $type, 'answers' => $answers );

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
    
    private function get_answers_type( $answers_raw = array() )
    {
        $types = array();

        // Сколько каких маркеров встречается?
        
        foreach ( $answers_raw as $item ) {

            $types[$item['type']] = ( isset( $types[$item['type']] ) ) ? $types[$item['type']] + 1 : 1;

        }

        // Определить тип вопроса по маркерам ответов

        if( isset( $types['>!'] ) ) {

            // Есть текстовое поле с данными для автоматической проверки

            $type = 'text';
            
        } elseif ( isset( $types['>~'] ) || isset( $types['%'] ) ) {
            
            // Есть текстовое поле с ручной проверкой или поля для загрузки файлов

            $type = 'open';

        } elseif( isset( $types['~'] ) && ( isset( $types['-'] ) || isset( $types['+'] ) || isset( $types['*'] ) ) ) {

            // Есть поле сортировки и какие-то еще другие поля

            $type = 'matching';
                            
        } elseif( isset( $types['~'] ) ) {
            
            // Есть поле сортировки, а других полей нет

            $type = 'sort';
                            
        } elseif( isset( $types['*'] ) || ( isset( $types['+'] ) && $types['+'] > 1 ) ) {

            // Есть маркер множественного выбора или несколько маркеров одиночного выбора

            $type = 'multiple';
                            
        } elseif( isset( $types['+'] ) || isset( $types['-'] ) ) {

            // Есть только один маркер одиночного выбора или такого маркера нет совсем
            
            $type = 'single';
                            
        } else {

            // Вообще ничего нет

            $type = 'open';

        }

        return $type;
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
                    $meta_txt = preg_replace( '/[,;:!]/', '|', $meta_txt );

                    $meta_arr = explode( '|', $meta_txt );
                    $meta_arr = array_map( 'trim', $meta_arr );

                }

                $item = strim( $item );
                $type = $res[0]{0};

                $data = array();

                $data['size'] = strlen( $res[0] );
                if ( $item != '' ) $data['answer'] = $item;
                // if ( ! empty( $meta_arr ) ) $data['meta'] = $meta_arr;
                if ( ! empty( $meta_arr ) ) $data['meta'] = implode( '|', $meta_arr );

                $data['type'] = $type;
                if ( $type == '>' ) $data['type'] = ( empty( $meta_arr ) ) ? '>~' : '>!';

                $arr[] = $data;

            }

        }

        return $arr;
    }


}

?>