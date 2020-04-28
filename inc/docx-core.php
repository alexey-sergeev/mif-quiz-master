<?php

//
// Создание docx-файлов
// 
//


defined( 'ABSPATH' ) || exit;


// 
// Требуется установка библиотеки
// 
// Команда для установки:
// composer require phpoffice/phpword
// 
// Документация: https://github.com/PHPOffice/PHPWord
// 

require dirname( __FILE__ ) . '/../vendor/autoload.php';



class mif_qm_docx_core  {

    private $blank = '';

    function __construct( $blank = '' )
    {
        $this->blank = dirname( __FILE__ ) . '/../xlsx/default.docx';
        if ( $blank ) $this->blank = $blank; 

    }


    //
    // Получить готовый вщсx-файл
    // 
    //  $arr - данные, которые надо вставить в шаблон
    // 

    function get( $arr = array() )
    {

        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor( $this->blank );

        // $arr = array(
        //     'testname' => 'Название теста',
        //     'time' => '12.02.2020 14:36',
        //     'fullname' => array(
        //                 array( 'fullname' => 'Мари', 'invite_code' => '1234-567', 'site' => 'http://qm.vspu.ru' ),
        //                 array( 'fullname' => 'Максим', 'invite_code' => '9876-543', 'site' => 'http://qm.vspu.ru' ),
        //                 array( 'fullname' => 'Мирон', 'invite_code' => '1029-384', 'site' => 'http://qm.vspu.ru' ),
        //             )
        //     );
            
        foreach ( $arr as $key => $item ) {

            if ( is_array( $item ) ) {

                $templateProcessor->cloneRowAndSetValues( $key, $item );

            } else {
                
                $templateProcessor->setValue( $key, $item );

            }

        }

        $upload_dir = (object) wp_upload_dir();
        $file = trailingslashit( $upload_dir->path ) . md5( serialize( $arr ) ) . '.docx';        

        $templateProcessor->saveAs( $file );


        // $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter( $phpWord, 'Word2007' );
        // $objWriter->save( $file );

        return $file;
    }




}

?>