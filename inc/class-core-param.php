<?php

//
// Класс для обработки параметров
// 
//


defined( 'ABSPATH' ) || exit;



class mif_qm_core_param extends mif_qm_core_core {

    private $params = array();
    private $settings = array();


    function __construct()
    {

        //
        // Описание списка всех возможных параметров
        //
        //
        // Структура:
        //
        //      name - имя (глобально уникально)
        //      alias - буква и другие возможные имена через пробел (все глобально уникальны)
        //      pattern - шаблон для идентификации в режиме без имени (маркер параметров и пробелы предварительно удаляются)
        //      apply - область применения ('quiz', 'part' или 'any' (по умолчанию))
        //      cumulative - множественный или нет (true или false (по умолчанию))
        //      default - значение по умолчанию
        //      description - текстовое пояснение параметра (не обязательно)
        //      description_val - массив возможных значений и их описаний (не обязательно)
        //
        // От последовательности описания зависит последовательность применения шаблонов (выбирается первый применимый)
        //

        $this->params = apply_filters( 'mif-qm-params', array(

                // attempt (repeat? retry?)
                // Тест - количество попыток прохождения теста (если 0, то не ограничено)
                // Раздел - не применимо
                
                array(
                    'name' => 'attempt',
                    'alias' => 'a att',
                    'pattern' => '/^\d+$/', // одна или несколько цифр целиком в строке
                    'apply' => 'quiz',
                    'default' => 0,
                    'description' => __( 'Количество попыток прохождения теста', 'mif-qm' )
                ),

                // number
                // Тест - количество вопросов в разделах по умолчанию
                // Раздел - количество выбираемых вопросов из данного раздела
                
                array(
                    'name' => 'number',
                    'alias' => 'n num',
                    'pattern' => '/^\d+$/', // одна или несколько цифр целиком в строке
                    'description' => __( 'Количество вопросов для раздела', 'mif-qm' )
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
                // Тест - общее количество баллов за весь тест / порог положительной оценки (по умолчанию - сумма баллов за все вопросы / 0)
                // Раздел - количество баллов за каждый вопрос раздела / порог положительной оценки (если баллы противоречат настройкам теста, 
                // то используются как вес; при указании порога тест не отмечается как пройденный, если не пройден порог хотя бы по одному разделу)
        
                array(
                    'name' => 'rating',
                    'alias' => 'r rat',
                    // 'pattern' => '/^\d+\.\.\d+$|^\d+-\d+$/', // 0..10 или 0-10
                    'pattern' => '/^\d+\/\d+$/', // 10/8
                    'default' => array( 'quiz' => '0/0', 'part' => '1/0' ),
                    'description' => array( 'quiz' => __( 'Количество баллов за тест и порог положительной оценки', 'mif-qm' ), 
                                            'part' => __( 'Количество баллов (вес) раздела и порог положительной оценки', 'mif-qm' ) ),
                ),

                // duration
                // Тест - ограничение времени
                // Раздел - не применимо
                
                array(
                    'name' => 'duration',
                    'alias' => 'd duration',
                    'pattern' => '/^\d+s$|^\d+m$/', // 30s или 15m (30 секунд или 15 минут)
                    'apply' => 'quiz',
                    'description' => __( 'Ограничение времени', 'mif-qm' )
                ),
                
                // tags
                // Тест - метки 
                // Раздел - метки
                
                array(
                    'name' => 'tags',
                    'alias' => 't tag',
                    'pattern' => '/^{\w+}/', // {любая цифра, буква или знак подчеркивания}
                    'cumulative' => true,
                    'description' => __( 'Метки', 'mif-qm' )
                ),
                
                // settings
                // Тест - разные настройки
                // Раздел - разные настройки
        
                array(
                    'name' => 'settings',
                    'alias' => 's set setting',
                    'pattern' => '/random|ordered|question|part|quiz|auto|manual|email|navigation|correction|resume|interactive|dialog/',
                    'cumulative' => true,
                    'default' => array( 'quiz' => 'ordered question manual', 'part' => 'random' ),
                    'description' => __( 'Настройки', 'mif-qm' ),
                ),
                
                ) );
                
        $this->settings = apply_filters( 'mif-qm-settings', array( 
                    
                'random' => array( 'apply' => 'any', 'description' => __( 'Случайное отображение', 'mif-qm' ) ), 
                'ordered' => array( 'apply' => 'any', 'description' => __( 'Последовательное отображение', 'mif-qm' ) ),
                'question' => array( 'apply' => 'quiz', 'description' => __( 'Каждый вопрос отдельно', 'mif-qm' ) ), 
                'part' => array( 'apply' => 'quiz', 'description' => __( 'Показывать вместе все вопросы разделов', 'mif-qm' ) ),
                'quiz' => array( 'apply' => 'quiz', 'description' => __( 'Показывать вместе все вопросы теста', 'mif-qm' ) ),
                'auto' => array( 'apply' => 'quiz', 'description' => __( 'Автоматическое начало теста', 'mif-qm' ) ),
                'manual' => array( 'apply' => 'quiz', 'description' => __( 'Ручное начало теста', 'mif-qm' ) ),
                'email' => array( 'apply' => 'quiz', 'description' => __( 'Уведомлять администратора о новых результатах', 'mif-qm' ) ),
                'navigation' => array( 'apply' => 'quiz', 'description' => __( 'Навигация по тесту', 'mif-qm' ) ),
                'correction' => array( 'apply' => 'quiz', 'description' => __( 'Навигация с возможностью исправления ответов', 'mif-qm' ) ),
                'resume' => array( 'apply' => 'quiz', 'description' => __( 'Показывать анализ результатов ответов по завершении теста', 'mif-qm' ) ),
                'interactive' => array( 'apply' => 'quiz', 'description' => __( 'Показывать результат при ответе на каждый вопрос', 'mif-qm' ) ),

        ) );

    }

    
    //
    // Преобразует массив параметров в структурированное описание
    //
    // $mode - part, quiz (режим обработки параметров - для раздела или всего теста)
    //

    function parse( $params_raw = array(), $mode = "part" )
    {
        $keys = $this->get_param_keys( $mode );
        $params = $this->param_init( $mode );
        $arr = array();
        
        // Нормализация строковых значений параметров
        
        foreach ( $params_raw as $key => $value ) {
            
            $value = strim( preg_replace( '/[\s=:,;.]/', ' ', $value ) );
            $value = preg_replace( '/' . $this->mark_param . ' /', $this->mark_param, $value );
            
            $params_raw[$key] = $value;
            
        }
        
        // Выбор строк, где параметр указан явно
        
        foreach ( $params_raw as $key => $value ) {
            
            $param = $this->get_name( $value );

            if ( ! $param ) continue;
            
            // В режиме теста пропустить то, что не относится к тесту явно

            if ( $mode == 'quiz' && ! $this->is_only_quiz_param( $value ) ) continue;
            
            // В режиме раздела пропустить то, что относится только к тесту
            
            if ( $mode == 'part' && $this->is_only_quiz_param( $value ) ) continue;

            // Сохранить данные параметра

            if ( array_key_exists( $param['name'], $keys ) ) {

                if ( $keys[$param['name']]['cumulative'] ) {
                    
                    $arr[$param['method']][$keys[$param['name']]['name']][] = $this->param_normalize( $value );

                } else {

                    $arr[$param['method']][$keys[$param['name']]['name']] = $this->param_normalize( $value );

                }

                // unset( $params_raw[$key] );

            }

        }

        // Собрать данные параметров в общий список

        foreach ( $params as $key => $value ) {

            if ( is_array( $value ) ) {

                $params[$key] = array_merge( (array) $arr['auto'][$key], (array) $arr['manual'][$key] );

                // p($arr['manual'][$key]);
                // p($arr['auto'][$key]);

            } else {

                if ( isset( $arr['auto'][$key] ) ) $params[$key] = $arr['auto'][$key];
                if ( isset( $arr['manual'][$key] ) ) $params[$key] = $arr['manual'][$key];

            }

        }

        // // Навести порядок в значениях settings

        // $setings_auto = implode( ' ', $arr['auto']['settings'] );
        // $setings_manual = implode( ' ', $arr['manual']['settings'] );
        // $settings = trim( $setings_auto . ' ' . $setings_manual );
        
        // $params['settings'] = $settings;

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

        foreach ( $this->params as $item ) {

            if ( $this->not_apply( $item, $mode ) ) continue;

            $name = trim( $item['name'] );
            $arr[$name] = ( isset( $item['cumulative'] ) && $item['cumulative'] ) ? array() : '';

        }

        return $arr;
    }

    
    //
    // Возвращает массив всех возможных ключей параметров с указанием на базовый параметр и режим кумулятивности
    //

    public function get_param_keys( $mode = 'part' )
    {
        $arr = array();

        foreach ( $this->params as $item ) {

            $name = trim( $item['name'] );
            $cumulative = ( isset( $item['cumulative'] ) && $item['cumulative'] == true ) ? true : false;

            if ( $this->not_apply( $item, $mode ) ) continue;
            
            $arr[$name] = array( 'name' => $name, 'cumulative' => $cumulative );

        }

        return $arr;
    }


    //
    // Проверяет, что параметр предназначен или применим только для теста
    //

    private function is_only_quiz_param( $value )
    {
        $param_quiz_only = array();

        foreach ( $this->params as $item ) {

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

        foreach ( $this->params as $param ) {
            
            $name = trim( $param['name'] );
            $arr[$name] = $name;
            
            $aliases = strim( $param['alias'] );
            $aliases_arr = explode( ' ', $aliases );
            
            foreach ( (array) $aliases_arr as $alias ) $arr[$alias] = $name;
            
        }

        return $arr;
    }


    //
    // Определяет имя параметра строки параметров
    //

    private function get_name( $item )
    {
        $out = array();        
        $arr = $this->get_alias_map();
        
        // Выделить первое слово в строке параметров
        
        preg_match( '/' . $this->mark_param . '([\w]+) /', $item, $ret ); 
        $name_raw = $ret[1];

        // Если первое слово является именем параметра
        
        if ( isset( $arr[$name_raw] ) ) {
            
            $out['name'] = $arr[$name_raw];
            $out['method'] = 'manual';
            
            return $out;
        }
        
        // Имя параметра не указано - определяем по значениям
        
        foreach ( $this->params as $param ) {
            
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
        $name_raw = $ret[1];
        
        if ( array_key_exists( $name_raw, $arr ) ) $item = preg_replace( '/@' . $name_raw . '/', '', $item );
        $item = trim( preg_replace( '/@/', '', $item ) );

        return $item;
    }


}

?>