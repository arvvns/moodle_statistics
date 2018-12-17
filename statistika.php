<?php

require_once('../../config.php');
require_once('lib.php');
require_once('../../lib/moodlelib.php');


if (isset($_GET["task"]) && $_GET["task"] == "generate")
    local_statistic_get();
else if(isset($_GET["task"]) && $_GET["task"] == "download"){
    require_once('../../lib/phpexcel/PHPExcel.php');

    $objPHPExcel = new PHPExcel();
    $export_fields_conf = get_config('local_statistics', 'export_fields');
    if (empty($export_fields_conf) or !$export_fields_conf) {
        $export_fields = EXPORT_FIELDS;
    } else {
        $export_fields = explode(',', $export_fields_conf);

    }
    $objPHPExcel->getProperties()->setCreator("emtc")
        ->setLastModifiedBy("emtc")
        ->setTitle("Statistika " . date('Y-m-d H:i:s', time()))
        ->setDescription("Dokumentas, kuriame yra moodle kursų statistika");

    $column = 'A';
    foreach ($export_fields as $field_name) {
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($column . '1', get_string($field_name, 'local_statistics'));
        $column++;
    }

    $objPHPExcel->getActiveSheet()->freezePane('A2');
    $column = 'A';
    for ($i = 0; $i < count($export_fields); $i++) {
        $objPHPExcel->getActiveSheet()->getColumnDimension($column)->setWidth(20);
        $column++;
    }

    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(40);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);

    $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(30);

    $objPHPExcel->getActiveSheet()->getStyle('A1:AZ1')->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getStyle('A1:AZ1')->getAlignment()->setWrapText(true);


    $data = $DB->get_records_sql('SELECT * FROM mdl_statistics');
    $c = 2;
    foreach ($data as $d) {
        $column = 'A';
        foreach ($export_fields as $field_name) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($column . $c, $d->$field_name);
            $column++;
        }

        $c++;
    }
    $objPHPExcel->getActiveSheet()->getStyle('F2:AZ' . $c)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    $objPHPExcel->getActiveSheet()->setTitle('Statistika');

    $objPHPExcel->setActiveSheetIndex(0);


// Redirect output to a client’s web browser (Excel2007)
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="vma.ktu.lt statistika ' . date('Y-m-d H-i-s', time()) . '.xlsx"');
    header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
    header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
    header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
    header('Pragma: public'); // HTTP/1.0

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
    exit;
}

