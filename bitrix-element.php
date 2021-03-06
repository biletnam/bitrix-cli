<?php

/*
Получение данных элементов из инфоблоков
Указываются ID через STD_IN (с новой строки), и/или через перечисление в параметрах
*/

namespace Bitrixcli;

require(__DIR__ . '/lib/cli-cms.php');

class ElementCli extends BitrixCli {

  protected $ids = array(), $item, $isHistory;
  public static $cliParams = array(
    'noVal' // без значения
      => array(),
    'val' // со значением
      => array('i' => 'ID', 'v' => 'view', 'f' => 'format'),
    'optionVal' // с необязательным значением
      => array(),
  )
  ;

  public function __construct($BitrixCMS) {
    if(!\CModule::IncludeModule('iblock')) throw new Exception('Can not include iblock');
    parent::__construct($BitrixCMS);
  }

  public function run() {
    try {
      $this->getParms();
      $this->outputElements();
    } catch (Exception $e) {
      $this->error($e);
    }
  }

  protected function getParms() {
    $ids = $this->getCliIdParms();
    $this->ids = array_merge($this->ids, $ids);

    $ids = $this->getStdinIntParms();
    $this->ids = array_merge($this->ids, $ids);

    $this->ids = array_filter(array_unique($this->ids));
    if(count($this->ids) == 0) throw new Exception('No ids', 1);
  }

  protected function outputElements() {
    $view = $this->getViewFormat();
    foreach($this->ids as $id) {
      $this->getElement($id);
      if(isset($this->item)) {
        $DataView = new DataView($this->item);
        $DataView->view($view);
        if($this->isHistory) {
          $lastId = \CIBlockElement::WF_GetLast($id);
          $this->warning(sprintf('Element has history: %d, CIBlockElement::WF_GetLast(): %d' . $id, $lastId));
        }
      }
    }
  }

  protected function getElement($id) {
    unset($this->item);
    $this->isHistory = false;

    $arSort = array('SORT' => 'ASC');
    $arSelect = array('*');
    $arNavStartParams = false;

    $arFilter = array('ID' => $id);
    $rs = \CIBlockElement::GetList($arSort, $arFilter, false, $arNavStartParams, $arSelect);
    if($rs->SelectedRowsCount() == 0) {
      $this->isHistory = true;
      $arFilter['SHOW_NEW'] = 'Y';
      $rs = \CIBlockElement::GetList($arSort, $arFilter, false, $arNavStartParams, $arSelect);
    }
    if($rs->SelectedRowsCount() == 0) {
      $this->warning('Can not find element with id: ' . $id);
    } else {
      $this->item = $rs->Fetch();
    }
  }

}

$ElementCli = new ElementCli($BitrixCMS);
$ElementCli->run();
