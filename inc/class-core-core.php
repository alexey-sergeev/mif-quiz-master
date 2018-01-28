<?php

//
// Ядро классов для работы с тестами
// 
//


defined( 'ABSPATH' ) || exit;

include_once dirname( __FILE__ ) . '/class-core-xml.php';



class mif_qm_core_core  {

    // Маркер для теста
    public $mark_quiz = '===';
    
    // Маркер для раздела теста
    public $mark_part = '==';
    
    // Маркер для описания параметров
    public $mark_param = '@';

    // Маркер для вопроса
    public $mark_question = '=';
    
    // Маркеры для выбираемых ответов
    public $mark_choice = '-+*~';
    
    // Маркеры для полей ввода
    public $mark_input = '>%';
    
    
    function __construct()
    {

        // Шаблон для выделения теста
        $this->pattern_quiz = '/^' . $this->mark_quiz . '/';
        
        // Шаблон для выделения разделов теста
        $this->pattern_part = '/^' . $this->mark_part . '/';
        
        // Шаблонs для выделения инормации о параметрах
        $this->pattern_param = '/^' . $this->mark_param . '/';
        
        // Шаблон для выделения вопросов (с ответами)
        $this->pattern_question = '/^[' . $this->mark_question . ']/';
        
        // Шаблоны для выделения вариантов ответов
        $this->pattern_choice = '/^[' . $this->mark_choice . ']/';
        $this->pattern_input = '/^[' . $this->mark_input . ']+/';
        $this->pattern_answers = '/^[' . $this->mark_choice . $this->mark_input . ']/';

        // Шаблон мета-информации ответов
        $this->pattern_meta = '/\(.*\)/U';

    }

}

?>