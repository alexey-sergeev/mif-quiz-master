<?php

//
// Создание xlsx-файлов
// 
//


defined( 'ABSPATH' ) || exit;


// 
// Требуется установка библиотеки
// 
// Команда для установки:
// composer require phpoffice/phpspreadsheet
// 
// Документация: https://phpspreadsheet.readthedocs.io/en/latest/
// 

require dirname( __FILE__ ) . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;




class mif_qm_xlsx_core  {

    private $blank = '';

    function __construct( $blank = '' )
    {
        $this->blank = dirname( __FILE__ ) . '/../templates/xlsx/default.xlsx';
        if ( $blank ) $this->blank = $blank;        

    }


    //
    // Получить готовый xlsx-файл
    // 
    //  $arr - данные, которые надо вставить в таблицу
    //  $cell - левая верхняя ячейка, откуда начинать заполнять
    // 

    function get( $arr = array(), $cell = 'A1' )
    {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load( $this->blank );
        $sheet = $spreadsheet->getActiveSheet();

        // $sheet->setCellValue( 'A1', 'Hello World !' );
        $sheet->fromArray( $arr, '', $cell, true );
       
        $upload_dir = (object) wp_upload_dir();
        $file = trailingslashit( $upload_dir->path ) . md5( serialize( $arr ) . $cell ) . '.xlsx';
        
        $writer = new Xlsx( $spreadsheet );
        $writer->save( $file );

        return $file;
    }




}

?>