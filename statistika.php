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
//$a = 'A';
//for ($i = 0; $i < 30; $i++) {
//    echo $a . '<br>';
//    $a++;
//}
//
//die;
//$columnstitles = [
//    'Kurso pavadinimas',
//    'Modulio kodas',
//    'Fakultetas (kategorija)',
//    'Katedra (sub-kategorija)',
//    'Kurso autorius',
//    'Registruotų dėstytojų skaičius',
//    'Aktyvių studentų skaičius',
//    'Paskutinė dėstytojo apsilankymo data',
//    'Registruotų studentų skaičius',
//    'Aktyvių studentų skaičius',
//    'Paskutinė studento apsilankymo data',
//    'Failai / File',
//    'Puslapis / Page',
//    'URL',
//    'Knyga / Book',
//    'Kiti',
//    'Užduočių skaičius',
//    'Diskusijų skaičius',
//    'Diskusijų forumų žinučių skaičius',
//    'Vaizdo paskaitų skaičius',
//    'Testų skaičius',
//    'Klausimų skaičius testuose',
//    'Studentų įkeltų darbų skaičius',
//    'Žodynų skaičius',
//    'Žodynuose esančių terminų skaičius',
//    'Kitos veiklos',
//    'Statistikos surinkimo data'
//    ];
$columnstitles = [
    get_string('coursename', 'local_statistics'),
    get_string('idnumber', 'local_statistics'),
    get_string('subcategory', 'local_statistics'),
    get_string('category', 'local_statistics'),
    get_string('teachers', 'local_statistics'),
    get_string('teachers_count', 'local_statistics'),
    get_string('active_teachers', 'local_statistics'),
    get_string('teacher_last_access', 'local_statistics'),
    get_string('students_count', 'local_statistics'),
    get_string('active_students', 'local_statistics'),
    get_string('student_last_access', 'local_statistics'),
    get_string('resource', 'local_statistics'),
    get_string('page', 'local_statistics'),
    get_string('url', 'local_statistics'),
    get_string('book', 'local_statistics'),
    get_string('other', 'local_statistics'),
    get_string('assign', 'local_statistics'),
    get_string('forum', 'local_statistics'),
    get_string('forum_posts', 'local_statistics'),
    get_string('quiz', 'local_statistics'),
    get_string('quiz_questions', 'local_statistics'),
    get_string('files', 'local_statistics'),
    get_string('glossary', 'local_statistics'),
    get_string('glossary_entries', 'local_statistics'),
    get_string('wiki', 'local_statistics'),
    get_string('data', 'local_statistics'),
    get_string('choice', 'local_statistics'),
    get_string('lesson', 'local_statistics'),
    get_string('feedback', 'local_statistics'),
    get_string('attendance', 'local_statistics'),
    get_string('folder', 'local_statistics'),
    get_string('imscp', 'local_statistics'),
    get_string('label', 'local_statistics'),
    get_string('date', 'local_statistics'),
];

if (isset($_GET["task"]) && $_GET["task"] == "generate")
    local_statistic_get();
else if(isset($_GET["task"]) && $_GET["task"] == "download"){
    include '../../lib/phpexcel/PHPExcel.php';
    //include 'phpexcel/PHPExcel.php';

    $objPHPExcel = new PHPExcel();

    $objPHPExcel->getProperties()->setCreator("emtc")
        ->setLastModifiedBy("emtc")
        ->setTitle("Statistika " . date('Y-m-d H:i:s', time()))
        ->setDescription("Dokumentas, kuriame yra moodle kursų statistika");

//    $objPHPExcel->setActiveSheetIndex(0)
//        ->setCellValue('A1', 'Kurso pavadinimas')
//        ->setCellValue('B1', 'Modulio kodas')
//        ->setCellValue('C1', 'Fakultetas (kategorija)')
//        ->setCellValue('D1', 'Katedra (sub-kategorija)')
//        ->setCellValue('E1', 'Kurso autorius')
//        ->setCellValue('F1', 'Registruotų dėstytojų skaičius')
//        ->setCellValue('G1', 'Aktyvių dėstytojų skaičius')
//        ->setCellValue('H1', 'Paskutinė dėstytojo apsilankymo data')
//        ->setCellValue('I1', 'Registruotų studentų skaičius')
//        ->setCellValue('J1', 'Aktyvių studentų skaičius')
//        ->setCellValue('K1', 'Paskutinė studento apsilankymo data')
//        ->setCellValue('L1', 'Failai / File')
//        ->setCellValue('M1', 'Puslapis / Page')
//        ->setCellValue('N1', 'URL')
//        ->setCellValue('O1', 'Knyga / Book')
//        ->setCellValue('P1', 'Kiti')
//        ->setCellValue('Q1', 'Užduočių skaičius')
//        ->setCellValue('R1', 'Diskusijų skaičius')
//        ->setCellValue('S1', 'Diskusijų forumų žinučių skaičius')
//        ->setCellValue('T1', 'Vaizdo paskaitų skaičius')
//        ->setCellValue('U1', 'Testų skaičius')
//        ->setCellValue('V1', 'Klausimų skaičius testuose')
//        ->setCellValue('W1', 'Studentų įkeltų darbų skaičius')
//        ->setCellValue('X1', 'Žodynų skaičius')
//        ->setCellValue('Y1', 'Žodynuose esančių terminų skaičius')
//        ->setCellValue('Z1', 'Kitos veiklos')
//        ->setCellValue('AA1', 'Statistikos surinkimo data');

    $column = 'A';
    foreach ($columnstitles as $cs) {
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($column . '1', $cs);
        $column++;
    }


    $objPHPExcel->getActiveSheet()->freezePane('A2');
    $column = 'A';
    for ($i = 0; $i < count($columnstitles); $i++) {
        $objPHPExcel->getActiveSheet()->getColumnDimension($column)->setWidth(20);
        $column++;
    }

    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(40);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);

    $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(30);
//    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
//
//    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
//    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
//    $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
//    $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
//    $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
//    $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
//
//    $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(20);
//    $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(20);
//    $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(20);
//    $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(20);
//    $objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(20);
//    $objPHPExcel->getActiveSheet()->getColumnDimension('W')->setWidth(20);
//    $objPHPExcel->getActiveSheet()->getColumnDimension('X')->setWidth(20);
//    $objPHPExcel->getActiveSheet()->getColumnDimension('Y')->setWidth(20);
//    $objPHPExcel->getActiveSheet()->getColumnDimension('Z')->setWidth(20);
//    $objPHPExcel->getActiveSheet()->getColumnDimension('AA')->setWidth(20);

    $objPHPExcel->getActiveSheet()->getStyle('A1:AZ1')->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getStyle('A1:AZ1')->getAlignment()->setWrapText(true);


    $data = $DB->get_records_sql('SELECT * FROM mdl_statistics');
    $c = 2;
    foreach ($data as $d) {
//        $objPHPExcel->setActiveSheetIndex(0)
//            ->setCellValue('A' . $c, $d->coursename)
//            ->setCellValue('B' . $c, $d->idnumber)
//            ->setCellValue('C' . $c, $d->subcategory)
//            ->setCellValue('D' . $c, $d->category)
//            ->setCellValue('E' . $c, $d->teachers)
//            ->setCellValue('F' . $c, $d->teachers_count)
//            ->setCellValue('G' . $c, $d->active_teachers)
//            ->setCellValue('H' . $c, $d->teacher_last_access)
//            ->setCellValue('I' . $c, $d->students_count)
//            ->setCellValue('J' . $c, $d->active_students)
//            ->setCellValue('K' . $c, $d->student_last_access)
//            ->setCellValue('L' . $c, $d->resource)
//            ->setCellValue('M' . $c, $d->page)
//            ->setCellValue('N' . $c, $d->url)
//            ->setCellValue('O' . $c, $d->book)
//            ->setCellValue('P' . $c, $d->other)
//            ->setCellValue('Q' . $c, $d->assign)
//            ->setCellValue('R' . $c, $d->forum)
//            ->setCellValue('S' . $c, $d->forum_posts)
//            ->setCellValue('T' . $c, $d->quiz)
//            ->setCellValue('U' . $c, $d->quiz_questions)
//            ->setCellValue('V' . $c, $d->files)
//            ->setCellValue('W' . $c, $d->glossary)
//            ->setCellValue('X' . $c, $d->glossary_entries)
//            ->setCellValue('Z' . $c, $d->wiki)
//            ->setCellValue('AA' . $c, $d->data)
//            ->setCellValue('AB' . $c, $d->choice)
//            ->setCellValue('AC' . $c, $d->lesson)
//            ->setCellValue('AD' . $c, $d->feedback)
//            ->setCellValue('AE' . $c, $d->attendance)
//            ->setCellValue('AF' . $c, $d->folder)
//            ->setCellValue('AG' . $c, $d->imscp)
//            ->setCellValue('AH' . $c, $d->label)
//            ->setCellValue('AI' . $c, $d->date);
        $column = 'A';
        $dataOrder = [
            $d->coursename,
            $d->idnumber,
            $d->subcategory,
            $d->category,
            $d->teachers,
            $d->teachers_count,
            $d->active_teachers,
            $d->teacher_last_access,
            $d->students_count,
            $d->active_students,
            $d->student_last_access,
            $d->resource,
            $d->page,
            $d->url,
            $d->book,
            $d->other,
            $d->assign,
            $d->forum,
            $d->forum_posts,
            $d->quiz,
            $d->quiz_questions,
            $d->files,
            $d->glossary,
            $d->glossary_entries,
            $d->wiki,
            $d->data,
            $d->choice,
            $d->lesson,
            $d->feedback,
            $d->attendance,
            $d->folder,
            $d->imscp,
            $d->label,
            $d->date
            ];
        foreach ($dataOrder as $do) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($column . $c, $do);
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

