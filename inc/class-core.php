<?php

//
// Ядро классов для работы с тестами
// 
//


defined( 'ABSPATH' ) || exit;



class mif_qm_core  {

    // Маркер для раздела теста
    private $mark_quiz_part = '==';
    
    // Маркер для описания параметров раздела теста
    private $mark_param = '@';

    // Маркер для вопроса
    private $mark_question = '=';
    
    // Маркеры для выбираемых ответов
    private $mark_choice = '-+*~';
    
    // Маркеры для полей ввода
    private $mark_input = '>%';
    
    
    function __construct()
    {

        // Шаблон для выделения разделов теста
        $this->pattern_quiz_part = '/^' . $this->mark_quiz_part . '/';
        
        // Шаблон для выделения инормации о параметрах
        $this->pattern_param = '/^[' . $this->mark_param . ']/';

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