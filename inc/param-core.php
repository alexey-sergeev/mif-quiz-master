<?php

//
// Класс для обработки параметров
// 
//


defined( 'ABSPATH' ) || exit;



class mif_qm_param_core extends mif_qm_core_core {

    private $params_arr = array();
    private $settings_map = array();
    private $params_raw = array();
    private $params = array();
    private $mode = 'quiz';
    private $params_quiz = array();
    
    
    //
    //      $params_raw - текстовое описание параметров
    //      $mode - part, quiz (режим обработки параметров - для раздела или всего теста)
    //      $params_quiz - параметры теста (применимо при определении параметров разделов)
    //
    
    function __construct( $params_raw = array(), $mode = 'quiz', $params_quiz = array() )
    {

        //
        // Описание списка всех возможных параметров
        //
        // Структура:
        //
        //      name - имя (глобально уникально)
        //      alias - буква и другие возможные имена через пробел (все глобально уникальны)
        //      pattern - шаблон для идентификации в режиме без имени (маркер параметров и пробелы предварительно удаляются)
        //      apply - область применения ('quiz', 'part' или 'any' (по умолчанию))
        //      cumulative - множественный или нет (true или false (по умолчанию))
        //      inheritance - наследуется ли значение от теста к разделу? (false или true (по умолчанию))
        //      default - значение по умолчанию
        //      description - текстовое пояснение параметра (не обязательно)
        //

        $this->params_arr = apply_filters( 'mif-qm-params', array(

                // attempt (repeat? retry?)
                // Тест - количество попыток прохождения теста (если 0, то не ограничено)
                // Раздел - не применимо
                
                array(
                    'name' => 'attempt',
                    'alias' => 'a att',
                    'pattern' => '/^[\d]+[\s]?att$/', // 10att, 10 att
                    'apply' => 'quiz',
                    'default' => 0,
                    'description' => __( 'Количество попыток прохождения теста', 'mif-qm' )
                ),
                
                // number
                // Тест - количество вопросов из разделов по умолчанию
                // Раздел - количество выбираемых вопросов из данного раздела (если 0, то выбираются все)
                
                array(
                    'name' => 'number',
                    'alias' => 'n num',
                    'pattern' => '/^[\d+][\s]?(num)?$/', // одна или несколько цифр целиком в строке
                    'default' => 0,
                    'description' => array( 'quiz' => __( '', 'mif-qm' ),
                                            'part' => __( 'Количество вопросов для раздела', 'mif-qm' ) )
                ),
                
                // competences
                // Тест - компетенции по умолчанию для разделов
                // Раздел - компетенции для раздела
        
                array(
                    'name' => 'competences',
                    'alias' => 'c cmp competence',
                    'pattern' => '/ОК-|УК-|ПК-|СК-/', // УК, ОК, ОПК, ПК, ДПК, СК, ПСК
                    'cumulative' => true,
                    'description' => __( 'Компетенции', 'mif-qm' ),
                ),
        
                // rating
                // Тест - общее количество баллов за весь тест (по умолчанию 0 - сумма баллов за все вопросы)
                // Раздел - количество баллов за каждый вопрос раздела (по умолчанию - 1; если баллы противоречат настройкам теста, то используются как вес)
        
                array(
                    'name' => 'rating',
                    'alias' => 'r rat',
                    'pattern' => '/^[\d]+[\s]?att$/', // 10rat, 10 rat
                    'default' => array( 'quiz' => '0', 'part' => '1' ),
                    'inheritance' => false,
                    'description' => array( 'quiz' => __( 'Количество баллов за тест', 'mif-qm' ), 
                                            'part' => __( 'Количество баллов за каждый вопрос', 'mif-qm' ) ),
                ),

                // success
                // Тест - порог положительной оценки (0 - без порога; по умолчанию - 60%)
                // Раздел - порог положительной оценки (если не преодолевается по разделу, то и тест не положителен) (по умолчанию - 0)
        
                array(
                    'name' => 'success',
                    'alias' => 's suc',
                    'pattern' => '/^[\d]+[\s]?[%]$/', // 50%
                    'default' => array( 'quiz' => '60%', 'part' => '0' ),
                    'inheritance' => false,
                    'description' => __( 'Порог положительной оценки', 'mif-qm' ),
                ),

                // time
                // Тест - ограничение времени
                // Раздел - не применимо
                
                array(
                    'name' => 'time',
                    'alias' => 't times',
                    'pattern' => '/^\d+s$|^\d+m$/', // 30s или 15m (30 секунд или 15 минут)
                    'apply' => 'quiz',
                    'description' => __( 'Ограничение времени', 'mif-qm' )
                ),
                
                // tags
                // Тест - метки 
                // Раздел - метки
                
                array(
                    'name' => 'tags',
                    'alias' => 'tag',
                    'pattern' => '/^{\w+}/', // {любая цифра, буква или знак подчеркивания}
                    'cumulative' => true,
                    'inheritance' => false,
                    'description' => __( 'Метки', 'mif-qm' )
                ),
                
                // settings
                // Тест - разные настройки
                // Раздел - разные настройки
        
                array(
                    'name' => 'settings',
                    'alias' => 'set setting',
                    'pattern' => '/auto|correction|email|interactive|manual|navigation|numeration|ordered|part|question|quiz|random|resume/',
                    'cumulative' => true,
                    'inheritance' => false,
                    'default' => array( 'quiz' => 'ordered question manual better balanced', 'part' => 'random' ),
                    'description' => __( 'Настройки', 'mif-qm' ),
                ),
                
                ) );

        //
        // Описание списка параметров настройки
        //
        // Структура:
        //
        //      group - группа (логическая связь параметров)
        //      apply - область применения ('quiz', 'part' или 'any' (по умолчанию))
        //      description - текстовое пояснение параметра
        //
                

        $this->settings_map = apply_filters( 'mif-qm-settings', array( 
                    
                'random' => array( 
                                'group' => 'group1', 
                                'apply' => 'any', 
                                'description' => array( 'quiz' => __( 'Случайный порядок всех вопросов теста', 'mif-qm' ),
                                                        'part' => __( 'Случайный порядок вопросов', 'mif-qm' ) )
                            ), 
                'ordered' => array( 
                                'group' => 'group1', 
                                'apply' => 'any', 
                                'description' => array( 'quiz' => __( 'Последовательный порядок разделов', 'mif-qm' ),
                                                        'part' => __( 'Последовательный порядок вопросов', 'mif-qm' ) )
                            ),
                'question' => array( 
                                'group' => 'group2', 
                                'apply' => 'quiz', 
                                'description' => __( 'Показывать отдельно каждый вопрос', 'mif-qm' ) 
                            ), 
                'part' => array( 
                                'group' => 'group2', 
                                'apply' => 'quiz', 
                                'description' => __( 'Показывать вместе все вопросы разделов', 'mif-qm' ) 
                            ),
                'quiz' => array( 
                                'group' => 'group2', 
                                'apply' => 'quiz', 
                                'description' => __( 'Показывать вместе все вопросы теста', 'mif-qm' ) 
                            ),
                'auto' => array( 
                                'group' => 'group3', 
                                'apply' => 'quiz', 
                                'description' => __( 'Автоматическое начало теста', 'mif-qm' ) 
                            ),
                'manual' => array( 
                                'group' => 'group3', 
                                'apply' => 'quiz', 
                                'description' => __( 'Начало теста после нажания кнопки', 'mif-qm' ) 
                            ),
                'email' => array( 
                                'group' => 'group4', 
                                'apply' => 'quiz', 
                                'description' => __( 'Уведомлять администратора о новых результатах', 'mif-qm' ) 
                            ),
                'navigation' => array( 
                                'group' => 'group5', 
                                'apply' => 'quiz', 
                                'description' => __( 'Навигация по тесту', 'mif-qm' ) 
                            ),
                // 'correction' => array( 
                //                 'group' => 'group5', 
                //                 'apply' => 'quiz', 
                //                 'description' => __( 'Навигация с возможностью исправления ответов', 'mif-qm' ) 
                //             ),
                'numeration' => array( 
                                'group' => 'group6', 
                                'apply' => 'quiz', 
                                'description' => __( 'Нумерация вопросов', 'mif-qm' ) 
                            ),
                'resume' => array( 
                                'group' => 'group7', 
                                'apply' => 'quiz', 
                                'description' => __( 'Показывать правильные ответы по завершению теста', 'mif-qm' ) 
                            ),
                'interactive' => array( 
                                'group' => 'group8', 
                                'apply' => 'quiz', 
                                'description' => __( 'Показывать результат при ответе на каждый вопрос', 'mif-qm' ) 
                            ),
                'better' => array( 
                                'group' => 'group9', 
                                'apply' => 'quiz', 
                                'description' => __( 'Учитывать лучший результат нескольких попыток', 'mif-qm' ) 
                            ),
                'latest' => array( 
                                'group' => 'group9', 
                                'apply' => 'quiz', 
                                'description' => __( 'Учитывать последний результат нескольких попыток', 'mif-qm' ) 
                            ),
                'average' => array( 
                                'group' => 'group9', 
                                'apply' => 'quiz', 
                                'description' => __( 'Учитывать средний результат нескольких попыток', 'mif-qm' ) 
                            ),
                'strict' => array( 
                                'group' => 'group10',
                                'apply' => 'quiz', 
                                'description' => __( 'Строгая оценка', 'mif-qm' ) 
                            ),
                'balanced' => array( 
                                'group' => 'group10',
                                'apply' => 'quiz', 
                                'description' => __( 'Сбалансированная оценка', 'mif-qm' ) 
                            ),
                'detailed' => array( 
                                'group' => 'group10',
                                'apply' => 'quiz', 
                                'description' => __( 'Детальная оценка', 'mif-qm' ) 
                            ),
        ) );

        $this->mode = $mode;
        $this->params_raw = $params_raw;
        $this->params_quiz = $params_quiz;
        $this->params = $this->get_params();

    }

    
    //
    // Возвращает структурированное описание параметров
    //
    //

    public function parse()
    {
        return $this->params;
    }



    //
    // Преобразование всех параметров к применяемому виду
    //

    public function explication()
    {
        $out = $this->param_init( $this->mode );
        // $keys = $this->get_param_keys( $this->mode );
        $default = $this->get_params_map( $this->mode );
        $params_quiz = $this->params_quiz;
        $params = $this->parse();

        if ( $this->mode == 'quiz' || $this->mode == 'part' ) {
            
            foreach ( $out as $key => $value ) {
                
                // Взять значение по умолчанию
                
                if ( isset( $default[$key]['default'] ) ) $out[$key] = $default[$key]['default'];
                
                // Взять значения из теста (если это раздел)
                
                if ( $this->mode == 'part' ) {
                    
                    if ( isset( $params_quiz[$key] ) && isset( $default[$key]['inheritance'] ) && $default[$key]['inheritance'] ) $out[$key] = $params_quiz[$key];

                }

                // Уточнить локальными параметрами

                if ( isset( $params[$key] ) ) $out[$key] = $params[$key];
                
            }

            // Дополнительно обработать настройки
            
            $out['settings'] = $this->explication_settings();

            // Удалить пустые элементы из параметров

            foreach ( $out as $key => $value ) if ( $value == '' || $value == array() ) unset( $out[$key] );

           
        } else {

            $out = $params;

        }

        return $out;
    }



    //
    // Преобразование переметров настройки к применяемому виду
    //

    public function explication_settings()
    {
        $params = $this->parse();
        $settings = ( isset( $params['settings'] ) ) ? $params['settings'] : array();
        $map = $this->get_settings_map();
        
        // Получить индексированный массив настроек по умолчанию
        
        $default = $this->get_params_map( $this->mode );
        $default_settings = $default['settings']['default'];
        $default_settings_index = array();
        foreach ( (array) $default_settings as $item ) if ( isset( $map[$item]['group'] ) ) $default_settings_index[$map[$item]['group']] = $item;
        
        // p($default_settings_index);
        
        // Получить индексированный массив фактических настроек
        
        $settings_index = array();
        foreach ( (array) $settings as $item ) if ( isset( $map[$item]['group'] ) ) $settings_index[$map[$item]['group']] = $item;
        
        // p($settings_index);
        
        // Добавить в настройки по умолчанию данные фактических настроек
        
        foreach ( $settings_index as $key => $value ) $default_settings_index[$key] = $value;
        
        // p($default_settings_index);
        
        // Сформировать итоговый массив настроек
        
        $out = array();
        foreach ( $default_settings_index as $item ) $out[] = $item;

        // Удалить настройки, которые в данном случае не применимы

        foreach ( (array) $out as $key => $value ) {

            $apply = $map[$value]['apply'];
            if ( $apply == 'any' || $apply == $this->mode ) continue;

            unset( $out[$key] );

        }

        // p($out);

        return $out;
    }    

    

    //
    // Преобразует массив параметров в структурированное описание
    //
    //

    public function get_params()
    {

        $params_raw = $this->params_raw;
        $mode = $this->mode;
        
        $map = $this->get_params_map( $mode );
        $params = $this->param_init( $mode );
        $arr = array();
        
        // Нормализация строковых значений параметров
        
        foreach ( (array) $params_raw as $key => $value ) {
            
            // $value = strim( preg_replace( '/[\s=:,;.]/', ' ', $value ) );
            $value = strim( preg_replace( '/[\s=:,;]/', ' ', $value ) );
            $value = preg_replace( '/' . $this->mark_param . ' /', $this->mark_param, $value );
            
            $params_raw[$key] = $value;
            
        }
        
        // Выбор параметров
        
        foreach ( (array) $params_raw as $key => $value ) {
            
            $param = $this->get_name( $value );

            if ( ! $param ) continue;
            
            // В режиме теста пропустить то, что не относится к тесту явно

            if ( $mode == 'quiz' && ! $this->is_only_quiz_param( $value ) ) continue;
            
            // В режиме раздела пропустить то, что относится только к тесту
            
            if ( $mode == 'part' && $this->is_only_quiz_param( $value ) ) continue;

            // Сохранить данные параметра

            if ( array_key_exists( $param['name'], $map ) ) {

                if ( $map[$param['name']]['cumulative'] ) {
                    
                    $arr[$param['method']][$map[$param['name']]['name']][] = $this->param_normalize( $value );

                } else {

                    $arr[$param['method']][$map[$param['name']]['name']] = $this->param_normalize( $value );

                }

                // unset( $params_raw[$key] );

            }

        }

        // Собрать данные параметров в общий список

        foreach ( $params as $key => $value ) {

            if ( is_array( $value ) && isset( $arr['auto'][$key] ) && isset( $arr['manual'][$key] ) ) {

                $params[$key] = array_merge( (array) $arr['auto'][$key], (array) $arr['manual'][$key] );

                // p($arr['manual'][$key]);
                // p($arr['auto'][$key]);

            } else {

                if ( isset( $arr['auto'][$key] ) ) $params[$key] = $arr['auto'][$key];
                if ( isset( $arr['manual'][$key] ) ) $params[$key] = $arr['manual'][$key];

            }

        }

        // Навести порядок в значениях settings

        $settings = array();
        foreach ( (array) $params['settings'] as $item ) $settings = array_merge( $settings, explode( ' ', $item ) );
        $params['settings'] = $settings;

        // Навести порядок в значениях tags

        $tags = array();
        foreach ( (array) $params['tags'] as $item ) $tags = array_merge( $tags, explode( ' ', $item ) );
        $params['tags'] = $tags;

        // Удалить пустые элементы из параметров

        foreach ( $params as $key => $value ) if ( $value == '' || $value == array() ) unset( $params[$key] );

        // p($params_raw);
        // p($params);

        return $params;
    }


        
    //
    // Инициализирует массив параметров
    //

    public function param_init( $mode = 'part' )
    {
        $arr = array();

        foreach ( $this->params_arr as $item ) {

            if ( $this->not_apply( $item, $mode ) ) continue;

            $name = trim( $item['name'] );
            $arr[$name] = ( isset( $item['cumulative'] ) && $item['cumulative'] ) ? array() : '';

        }

        return $arr;
    }

    
    //
    // Возвращает массив всех возможных параметров с информацией о них
    //

    public function get_params_map( $mode = 'quiz' )
    {
        $map = array();

        foreach ( $this->params_arr as $item ) {
            
            if ( $this->not_apply( $item, $mode ) ) continue;

            $name = trim( $item['name'] );
            $cumulative = ( isset( $item['cumulative'] ) && $item['cumulative'] == true ) ? true : false;
            $inheritance = ( isset( $item['inheritance'] ) && $item['inheritance'] == false ) ? false : true;

            // // Значение по умолчанию

            // if ( isset( $item['default'] ) && is_array( $item['default'] ) ) {
                
            //     // Значение по умолчанию есть и оно в массиве (разное для part и quiz)
                
            //     $default = ( isset( $item['default'][$mode] ) ) ? strim( $item['default'][$mode] ) : '';

            // } elseif ( isset( $item['default'] ) ) {
                
            //     // Значение по умолчанию есть и оно обычная строка (одинаковое для part и quiz)

            //     $default = strim( $item['default'] );
                
            // } else {
                
            //     $default = '';
               
            // }

            
            // Описание
            
            $arr = array();
            
            foreach ( array( 'default', 'description' ) as $key ) {
                
                if ( isset( $item[$key] ) && is_array( $item[$key] ) ) {
                    
                    // Значение есть и оно в массиве (разное для part и quiz)
                    
                    $arr[$key] = ( isset( $item[$key][$mode] ) ) ? strim( $item[$key][$mode] ) : '';
                    
                } elseif ( isset( $item[$key] ) ) {
                    
                    // Значение по умолчанию есть и оно обычная строка (одинаковое для part и quiz)
                    
                    $arr[$key] = strim( $item[$key] );
                    
                } else {
                    
                    $arr[$key] = '';
                    
                }
                
            }
            
            // Развернуь arr в переменные
            
            extract( $arr );
            
            // Накопительные значения по умолчанию превратить в массив
            
            if ( $default && $cumulative ) $default = explode( ' ', $default );

            // Записать данные о параметре

            $map[$name] = array( 'name' => $name, 'cumulative' => $cumulative, 'default' => $default, 'inheritance' => $inheritance, 'description' => $description );

        }

        return $map;
    }



    //
    // Возвращает карту параметров
    //

    public function get_params_arr()
    {
        return $this->params_arr;
    }    



    //
    // Возвращает карту настроек
    //

    public function get_settings_map()
    {
        return $this->settings_map;
    }    



    //
    // Проверяет, что параметр предназначен или применим только для теста
    //

    private function is_only_quiz_param( $value )
    {
        $param_quiz_only = array();

        foreach ( $this->params_arr as $item ) {

            if ( ! $this->not_apply( $item, 'part' ) ) continue;
            $param_quiz_only[] = trim( $item['name'] );
            
        }
        
        $param = $this->get_name( $value );

        $ret = ( preg_match( '/' . $this->mark_param . $this->mark_param . '/', $value ) || in_array( $param['name'], $param_quiz_only ) ) ? true : false;

        return $ret;
    }



    //
    // Проверяет, применим ли параметр для указанного режима
    //

    private function not_apply( $item, $mode  = 'part' )
    {
        $ret = false;
        $apply = ( isset( $item['apply'] ) ) ? trim( $item['apply'] ) : 'any';
        if ( ! ( $apply == $mode || $apply == 'any' ) ) $ret = true;

        return $ret;
    }


    //
    // Получить массив сопоставления базовых имен и алиасов
    //

    private function get_alias_map()
    {
        $arr = array();        

        foreach ( $this->params_arr as $param ) {
            
            $name = trim( $param['name'] );
            $arr[$name] = $name;
            
            $aliases = strim( $param['alias'] );
            $aliases_arr = explode( ' ', $aliases );
            
            foreach ( (array) $aliases_arr as $alias ) $arr[$alias] = $name;
            
        }

        return $arr;
    }


    //
    // Определяет имя параметра из строки параметров
    //

    private function get_name( $item )
    {
        $out = array();        
        $arr = $this->get_alias_map();
        
        // Выделить первое слово в строке параметров
        
        preg_match( '/' . $this->mark_param . '([\w]+) /', $item, $ret ); 
        $name_raw = ( isset( $ret[1] ) ) ? $ret[1] : NULL;

        // Если первое слово является именем параметра
        
        if ( isset( $arr[$name_raw] ) ) {
            
            $out['name'] = $arr[$name_raw];
            $out['method'] = 'manual';
            
            return $out;
        }
        
        // Имя параметра не указано - определяем по значениям
        
        $item = $this->param_normalize( $item );

        foreach ( $this->params_arr as $param ) {
            
            if ( preg_match( $param['pattern'], $item ) ) {
                
                $out['name'] = $param['name'];
                $out['method'] = 'auto';
                
                return $out;
            }

        }
        
        return false;
    }

    // 
    // Нормализация значений параметров
    // 

    private function param_normalize( $item )
    {
        $arr = $this->get_alias_map();
        
        preg_match( '/' . $this->mark_param . '([\w]+) /', $item, $ret ); 
        $name_raw = ( isset( $ret[1] ) ) ? $ret[1] : NULL;
        
        if ( array_key_exists( $name_raw, $arr ) ) $item = preg_replace( '/' . $this->mark_param . $name_raw . '/', '', $item );
        $item = trim( preg_replace( '/' . $this->mark_param . '/', '', $item ) );

        return $item;
    }


}

?>