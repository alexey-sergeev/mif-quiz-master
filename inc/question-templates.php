<?php

//
// Функции шаблонов вопросов
// 
//


defined( 'ABSPATH' ) || exit;



//
// Выводит заголовок вопроса
//

function mif_qm_the_question_header()
{
    global $mif_qm_question_screen;
    echo $mif_qm_question_screen->get_question_header();
}


//
// Выводит формулировку вопроса
//

function mif_qm_the_question_question()
{
    global $mif_qm_question_screen;
    echo $mif_qm_question_screen->get_question_data( 'title' );
}    


//
// Выводит классы вопроса
//

function mif_qm_the_question_classes()
{
    global $mif_qm_question_screen;
    echo $mif_qm_question_screen->get_question_classes();
}


// //
// // Выводит классы значка пеермещения
// //

// function mif_qm_the_mover_classes()
// {
//     global $mif_qm_question_screen;
//     echo $mif_qm_question_screen->get_mover_classes();
// }


//
// Выводит классы блока ответов
//

function mif_qm_the_answers_classes()
{
    global $mif_qm_question_screen;
    echo $mif_qm_question_screen->get_answers_classes();
}


//
// Выводит классы конкретного ответа
//

function mif_qm_the_answer_classes()
{
    global $mif_qm_question_screen;
    echo $mif_qm_question_screen->get_answer_classes();
}


//
// Выводит маркер ответа
//

function mif_qm_the_answer_marker()
{
    global $mif_qm_question_screen;
    echo $mif_qm_question_screen->get_answer_marker();
}


//
// Выводит значок перемещения для ответа
//

function mif_qm_the_answer_mover()
{
    global $mif_qm_question_screen;
    echo $mif_qm_question_screen->get_answer_mover();
}


//
// Выводит формулировку ответа
//

function mif_qm_the_answer_caption()
{
    global $mif_qm_question_screen;
    echo $mif_qm_question_screen->get_answer_caption();
}


//
// Выводит поле для ручного ввода (текст или файл)
//

function mif_qm_the_answer_handmake()
{
    global $mif_qm_question_screen;
    echo $mif_qm_question_screen->get_answer_handmake();
}


//
// Выбирает данные очередного ответа
//

function mif_qm_the_answer()
{
    global $mif_qm_question_screen;
    return $mif_qm_question_screen->the_answer();
}




?>