<?php

/**
 * Created by PhpStorm.
 * User: Edvinas
 * Date: 2014.11.04
 * Time: 12:40
 */


require_once('../../config.php');
require_once('lib.php');
require_once('../../lib/moodlelib.php');

if (isset($_GET["task"]) && $_GET["task"] == "generate")
    local_get_statistic();
else if(isset($_GET["task"]) && $_GET["task"] == "download"){
    include '../../lib/phpexcel/PHPExcel.php';
    //include 'phpexcel/PHPExcel.php';

    $objPHPExcel = new PHPExcel();

    $objPHPExcel->getProperties()->setCreator("Edvinas Matulaitis")
        ->setLastModifiedBy("Edvinas Matulaitis")
        ->setTitle("vma.ktu.lt statistika " . date('Y-m-d H:i:s', time()))
        ->setDescription("Dokumentas, kuriame yra vma.ktu.lt kursų statistika");

    $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A1', 'Kurso pavadinimas')
        ->setCellValue('B1', 'Modulio kodas')
        ->setCellValue('C1', 'Fakultetas (kategorija)')
        ->setCellValue('D1', 'Katedra (sub-kategorija)')
        ->setCellValue('E1', 'Kurso autorius')
        ->setCellValue('F1', 'Registruotų dėstytojų skaičius')
        ->setCellValue('G1', 'Aktyvių dėstytojų skaičius')
        ->setCellValue('H1', 'Paskutinė dėstytojo apsilankymo data')
        ->setCellValue('I1', 'Registruotų studentų skaičius')
        ->setCellValue('J1', 'Aktyvių studentų skaičius')
        ->setCellValue('K1', 'Paskutinė studento apsilankymo data')
        ->setCellValue('L1', 'Failai / File')
        ->setCellValue('M1', 'Puslapis / Page')
        ->setCellValue('N1', 'URL')
        ->setCellValue('O1', 'Knyga / Book')
        ->setCellValue('P1', 'Kiti')
        ->setCellValue('Q1', 'Užduočių skaičius')
        ->setCellValue('R1', 'Diskusijų skaičius')
        ->setCellValue('S1', 'Diskusijų forumų žinučių skaičius')
        ->setCellValue('T1', 'Vaizdo paskaitų skaičius')
        ->setCellValue('U1', 'Testų skaičius')
        ->setCellValue('V1', 'Klausimų skaičius testuose')
        ->setCellValue('W1', 'Studentų įkeltų darbų skaičius')
        ->setCellValue('X1', 'Žodynų skaičius')
        ->setCellValue('Y1', 'Žodynuose esančių terminų skaičius')
        ->setCellValue('Z1', 'Kitos veiklos')
        ->setCellValue('AA1', 'Statistikos surinkimo data');


    $objPHPExcel->getActiveSheet()->freezePane('A2');

    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(40);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);

    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);

    $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('W')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('X')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('Y')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('Z')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('AA')->setWidth(20);

    $objPHPExcel->getActiveSheet()->getStyle('A1:AA1')->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getStyle('A1:AA1')->getAlignment()->setWrapText(true);


    $data = $DB->get_records_sql('SELECT * FROM mdl_statistics');
    $c = 2;
    foreach ($data as $d) {
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A' . $c, $d->pavadinimas)
            ->setCellValue('B' . $c, $d->kodas)
            ->setCellValue('C' . $c, $d->fakultetas)
            ->setCellValue('D' . $c, $d->katedra)
            ->setCellValue('E' . $c, $d->autorius)
            ->setCellValue('F' . $c, $d->destytoju_skaicius)
            ->setCellValue('G' . $c, $d->aktyvus_destytojai)
            ->setCellValue('H' . $c, $d->paskutinio_destytojo_data)
            ->setCellValue('I' . $c, $d->studentu_skaicius)
            ->setCellValue('J' . $c, $d->aktyvus_studentai)
            ->setCellValue('K' . $c, $d->paskutinio_studento_data)
            ->setCellValue('L' . $c, $d->resource)
            ->setCellValue('M' . $c, $d->page)
            ->setCellValue('N' . $c, $d->url)
            ->setCellValue('O' . $c, $d->book)
            ->setCellValue('P' . $c, $d->other)
            ->setCellValue('Q' . $c, $d->assign)
            ->setCellValue('R' . $c, $d->forumai)
            ->setCellValue('S' . $c, $d->forumo_pranesimu_kiekis)
            ->setCellValue('T' . $c, $d->vips)
            ->setCellValue('U' . $c, $d->testu_skaicius)
            ->setCellValue('V' . $c, $d->klausimu_skaicius)
            ->setCellValue('W' . $c, $d->failu_skaicius)
            ->setCellValue('X' . $c, $d->zodynu_skaicius)
            ->setCellValue('Y' . $c, $d->terminu_skaicius)
            ->setCellValue('Z' . $c, $d->kitos_veiklos)
            ->setCellValue('AA' . $c, $d->kada_buvo_surinkta);
        $c++;
    }
    $objPHPExcel->getActiveSheet()->getStyle('F2:AA' . $c)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

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

