<?php

//
// Функции шаблонов вопросов
// 
//


defined( 'ABSPATH' ) || exit;



//
// Выводит заголовок вопроса
//

function mif_qm_the_question_title()
{
    global $mif_qm_screen_question;
    echo $mif_qm_screen_question->get_question_data( 'title' );
}


//
// Выводит классы вопроса
//

function mif_qm_the_question_classes()
{
    global $mif_qm_screen_question;
    echo $mif_qm_screen_question->get_question_classes();
}


//
// Выводит классы ответа
//

function mif_qm_the_answer_classes()
{
    global $mif_qm_screen_question;
    echo $mif_qm_screen_question->get_answer_classes();
}


//
// Выводит маркер ответа
//

function mif_qm_the_answer_mark()
{
    global $mif_qm_screen_question;
    echo $mif_qm_screen_question->get_answer_mark();
}


//
// Выводит формулировку ответа
//

function mif_qm_the_answer_answer()
{
    global $mif_qm_screen_question;
    echo $mif_qm_screen_question->get_answer_answer();
}


//
// Выводит поле для ручного ввода (текст или файл)
//

function mif_qm_the_answer_handmake()
{
    global $mif_qm_screen_question;
    echo $mif_qm_screen_question->get_answer_handmake();
}


//
// Выбирает данные очередного ответа
//

function mif_qm_the_answer()
{
    global $mif_qm_screen_question;
    return $mif_qm_screen_question->the_answer();
}




?>