<?php
/**
 * Created by PhpStorm.
 * User: HiWin10
 * Date: 2018/9/20 0020
 * Time: 下午 16:51
 */
namespace PHPexcelEngine;

if (!defined('PHPEXCELENGINE_ROOT')) {
    define('PHPEXCELENGINE_ROOT', dirname(__FILE__) . '/');
    require(PHPEXCELENGINE_ROOT . 'PHPExcel/PHPExcel.php');
    require(PHPEXCELENGINE_ROOT . 'PHPExcel/PHPExcel/Writer/Excel2007.php');
}


class PhpExcelEngine
{
    protected $objPHPExcel = null;
    protected $objActSheet = null;
    protected $now_col = 0;
    protected $now_row = 1;
    protected $cell = [];
    protected $nowsite = "";
    protected $max_row_span = 1;

    function __construct()
    {

//         $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_to_discISAM;
//         $cacheSettings = array( 'memoryCacheSize' => '32MB');
//         \PHPExcel_Settings::setCacheStorageMethod($cacheMethod,$cacheSettings);
        $this->objPHPExcel = new \PHPExcel();
        $this->objPHPExcel->createSheet(0);
        $this->objPHPExcel->setActiveSheetIndex(0);
        $this->objPHPExcel->setActiveSheetIndex(0)->setTitle('xxx');
        $this->objActSheet = $this->objPHPExcel->getActiveSheet();
        $this->objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $this->objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
    }

    public function renderData($data, $debug = 0){
        foreach ($data as $k1 => $items){
            if ($k1 > 0)$this->now_row +=  $this->max_row_span;
            $this->max_row_span = 1;
            $this->now_col = 0;
            foreach ($items as $cell){
                $this->cell = $cell;
                $this->renderCell($this->cell, $debug);
            }
//             print_r($this->nowsite);exit();
        }
        if ($debug) exit();
        $this->export();
    }

    /**
     * 多sheet渲染
     * @param $data
     * @param int $debug
     */
    public function renderDataMulitSheet($data, $debug = 0)
    {
        //删除构造初始的sheet
        $this->objPHPExcel->removeSheetByIndex(0);

        foreach ($data as $index => $sheetData)
        {
            $title = $sheetData['title'] ?? "sheet-{$index}";

            $this->now_row = 1;
            $this->objPHPExcel->createSheet($index);
            $this->objPHPExcel->setActiveSheetIndex($index)->setTitle($title);
            $this->objActSheet = $this->objPHPExcel->getActiveSheet();

            foreach ($sheetData['sheet'] as $k1 => $items)
            {
                if ($k1 > 0) $this->now_row += $this->max_row_span;

                $this->max_row_span = 1;
                $this->now_col      = 0;
                foreach ($items as $cell)
                {
                    $this->cell = $cell;
                    $this->renderCell($this->cell, $debug);
                }
            }
        }

        //设置默认sheet
        $this->objPHPExcel->setactivesheetindex(0);
        $this->export();
    }


    /**
     * 将数据渲染到excel格子，但是不执行导出
     * @param $data
     * @param int $debug
     */
    public function cacheData($data, $debug = 0){
        foreach ($data as $k1 => $items){
            if ($k1 > 0)$this->now_row +=  $this->max_row_span;
            $this->max_row_span = 1;
            $this->now_col = 0;
            foreach ($items as $cell){
                $this->cell = $cell;
                $this->renderCell($this->cell, $debug);
            }
//             print_r($this->nowsite);exit();
        }
        if ($debug) exit();
    }

    /**
     * 批量合并行
     * @param int $col
     * @param $num
     * @param int $row
     */
    public function mergeRowBatch($col = 0, $num, $row = 1){
        $this->now_col = $col;
        $this->now_row = $row;
        $site = $this->getSite($this->now_col, $this->now_row);
//         print_r($site);
//         print_r("<br />");
        $this->mergeRow($site,$num);
//         print_r($site);
//         print_r($this->getSite($this->now_col, $this->now_row));
    }



    public function renderCell($cell, $debug = 0){

        if ($debug){
            print_r("<br >");
            print_r("<br >");
        }

        $data = $cell['data'];
        $first_cell = $cell;
        if (empty($data)){
            $this->nowsite = $this->getSite($this->now_col, $this->now_row);
            $this->setCellValue($cell,$this->nowsite);
            if ($debug){
                print_r($this->nowsite . $cell['text']);
                print_r("aaaa<br >");
            }

            $this->now_row -= $cell['row_span'] - 1;
            $this->now_col += $cell['col_span'];
        }else{
            $this->nowsite = $this->getSite($this->now_col, $this->now_row);
            if ($debug){
                print_r($this->nowsite . $cell['text']);
                print_r("ffff<br >");
            }

            $this->setCellValue($cell,$this->nowsite);
            $this->now_row += $cell['row_span'];
            $this->now_col -= $cell['col_span'] - 1;
            foreach ($data as $cell){
                //第二层data
                $data = $cell['data'];
                if (!empty($data)) {
                    $this->nowsite = $this->getSite($this->now_col, $this->now_row);
                    $this->setCellValue($cell,$this->nowsite);
                    if ($debug){
                        print_r($this->nowsite. $cell['text']);
                        print_r("bbb<br >");
                    }

                    $this->now_row += $cell['row_span'];
                    $this->now_col -= $cell['col_span'] - 1;

                    foreach ($data as $cell) {
                        //第三层data
                        $this->nowsite = $this->getSite($this->now_col, $this->now_row);
                        if ($debug){
                            print_r($this->nowsite. $cell['text']);
                            print_r("<br >");
                        }


                        $this->setCellValue($cell, $this->nowsite);
                        $this->now_col += $cell['col_span'];
                    }
                    $this->now_row -= $cell['row_span'];
                } else{
                    $this->nowsite = $this->getSite($this->now_col, $this->now_row);
                    if ($debug){
                        print_r($this->nowsite. $cell['text']);
                        print_r("ccc<br >");
                    }

                    $this->setCellValue($cell,$this->nowsite);
                    $this->now_col += $cell['col_span'];
                    $this->now_row -= $cell['row_span'] - 1;
                }
            }
            $this->now_row -= $first_cell['row_span'];
        }

    }

    public function setCellValue($cell, $site){
        $value = $cell['text'];
        $row_span = $cell['row_span'];
        $col_span = $cell['col_span'];
        $width = empty($cell['width']) ? 50 : $cell['width'];

        if (!empty($cell['type']) && $cell['type'] === 'image')
        {
            $this->setImgCellValue($cell, $site);
        }
        else
        {
            $patterns = "/[a-zA-Z]+/";
            preg_match_all($patterns,$site,$arr);
            $words = $arr[0][0];
            $this->objActSheet->getColumnDimension($words)->setWidth($width);
            $this->objActSheet->getStyle($site)->getAlignment()->setWrapText(true);
            $this->objActSheet->setCellValue($site, $value );
        }

        if ($col_span > 1)$this->mergeCol($site, $col_span);
        if ($row_span > 1) $this->mergeRow($site, $row_span);
    }

    /**
     * @param $cell
     * @param $site
     */
    public function setImgCellValue($cell, $site)
    {
        $img_path = $cell['image_path'];
        $img_w    = $cell['image_width'] ?? 100;
        $img_h    = $cell['image_height'] ?? 100;
        $row      = preg_replace('/[a-zA-Z]+/', '', $site);
        $col      = preg_replace('/\d+/', '', $site);

        $this->objActSheet->getColumnDimension($col)->setWidth($img_w);
        $this->objActSheet->getRowDimension($row)->setRowHeight($img_h);

        $objDrawing = new \PHPExcel_Worksheet_Drawing();
        $objDrawing->setPath($img_path);
        $objDrawing->setHeight($img_w);
        $objDrawing->setWidth($img_h);
        $objDrawing->setCoordinates($site); //设置图片要插入的单元格
        //$objDrawing->setOffsetX(12); //图片偏移X距离
        //$objDrawing->setOffsetY(12);//图片偏移Y距离
        $objDrawing->setWorksheet($this->objPHPExcel->getActiveSheet());
    }

    private function mergeRow($site, $row_span){
        $site_info = $this->transformSite($site);
        $row = $site_info['row'];
        $col = $site_info['col'];
        $this->now_row += $row_span - 1;
        $newSite = $this->getSite($col,$this->now_row);
        $this->objActSheet->mergeCells($site .":" . $newSite);
        if ($row_span > $this->max_row_span)$this->max_row_span = $row_span;
    }

    /**
     * 合并列
     * @param $site
     * @param $col_span
     * @return array
     */
    private function mergeCol($site, $col_span){
        $site_info = $this->transformSite($site);
        $row = $site_info['row'];
        $col = $site_info['col'];
        $this->now_col += $col_span-1;
        $newSite = $this->getSite($this->now_col, $row);
        $this->objActSheet->mergeCells($site .":" . $newSite);
    }


    /**
     *
     * @param $col
     * @param $row
     * @return bool|string
     */
    public function getSite($col, $row){
        $word = ["A", "B", "C", "D", "E", "F", "G", "H","I", "J","K", "L","M","N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z"];
        if ($col < 26){
            return $word[$col] . $row;
        }else if ($col < 676){
            $mod = floor($col/26);
            $first = $mod > 1 ? $mod-1 : 0;
            $second = $col%26;
            return $word[$first] . $word[$second]. $row;
        } else if($col < 17576){
            $first = floor($col / 676);
            $mod = $col % 676;
            $second = floor($mod / 26);
            $third = $mod - $second * 26;

            return $word[$first - 1] . $word[$second].$word[$third]. $row;

        }
    }

    /**
     * 将坐标转化为索引值
     * @param $site
     * @return array
     */
    public function transformSite($site){
        $all_words = ["A", "B", "C", "D", "E", "F", "G", "H","I", "J","K", "L","M","N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z"];


        $patterns = "/\d+/";
        preg_match_all($patterns,$site,$arr);
        $row_num = $arr[0][0];

        $patterns = "/[a-zA-Z]+/";
        preg_match_all($patterns,$site,$arr);
        $words = $arr[0][0];


        $words = str_split($words);
        $words = array_reverse($words);
        $col_num = 0;
        foreach ($words as $key => $word) {
            $index = 0;
            if ($key == 0){
                $index = array_search($word,$all_words);
                $col_num +=  $index ;
            }elseif($key == 1){
                $index = array_search($word,$all_words);
                $col_num += ($index + 1) * 26;
            } elseif ($key == 2){
                $col_num += ($index + 1) * 676;
            }

        }


        return ['row' => $row_num, 'col' => $col_num];

    }

    /**
     * 获取单sheet数据
     * @param array $input
     * @param array $fieldMap
     * @param array $param
     * @return array
     */
    public function getSheetData($input = [], $fieldMap = [], $param=[])
    {
        $width = $param['width'] ?? 20;

        //抬头行
        $header = [];
        foreach ($fieldMap as $en => $zh)
        {
            $header[] = [
                'text'     => $zh,
                'row_span' => 1,
                'col_span' => 1,
                'data'     => [],
                'width'    => $width
            ];
        }

        //正文行
        $main = [];
        foreach ($input as $v => $item)
        {
            $row = [];
            foreach ($fieldMap as $en => $zh)
            {
                $tmp = [
                    'text'     => $item[ $en ] ?? '',
                    'row_span' => 1,
                    'col_span' => 1,
                    'data'     => [],
                    'width'    => $width,
                ];

                $row[] = $tmp;
            }

            $main[ $v ] = $row;
        }

        array_unshift($main, $header);

        return $main;
    }

    public function export($filename = ""){
        $filename = microtime(true) . '.xls';
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=\"$filename\"");
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
    }


}