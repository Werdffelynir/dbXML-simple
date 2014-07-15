<?php
/**
 * 
 **/
class DbXML
{
    public static $debug = true;

    /** @var string Путь к каталогу БД */
    protected $pathDb ='./databaseXml/';

    /** @var string Начало автоинкремента */
    public $autoIncrement = 1;

    /** @var string Файл автоинкремента */
    protected $autoIncrementFile ='dbXmlAutoIncrement.ini';

    /** @var array записи по умолчанию */
    protected $dataDefaultValues;

    /** @var string */
    public static $saveType;
    public static $dataFile;
    public static $createFile;
    public static $updateFile;

    /** @var array массив содержит имена всех выбраных методом open() файлов */
    public static $namesAllFiles;

    /** @var array  */
    public $dataInsert = array();
    public $dataSelect = array();
    public $dataSelectWhere = array();

    public $xml;


    /**
     * В конструктор класса
     *
     * @param null|string   $pathDF             Путь к директории с файлами DB
     * @param array         $defaultElements    Масиив елементов для записи в файл по умолчанию
     */
    public function __construct($pathDF=null, $defaultElements=array())
    {
        if(empty($pathDF)) {
            $pathDF = $this->pathDb;
        } else {
            $pathDF = $pathDF.DIRECTORY_SEPARATOR;
            $this->pathDb = $pathDF;
        }

        // создание каталога файлов если не существует
        if(!is_dir($pathDF))
            if(mkdir($pathDF, 0777) == false)
                die('Оштбка. Нехватает прав на создание каталога!');

        // Елементы для записи в файлы по умолчанию
        $this->dataDefaultValues = $this->defaultValues($defaultElements);
    }

    /**
     * Внутрений метод заполняет дополнительные поля если не были указаны при создании,
     * параметры могут изменяться по необходимеости, с помощу передачи массива конфигурации
     * в конструктор созданого обекта данного класса.
     */
    protected function defaultValues(array $values=array())
    {
        $valuesData = array(
            'date'          => date('d.m.Y H:i'),
            'order'         =>'10',
            'status'        =>'public',
            'parent'        =>'none',
            'url'           => $_SERVER['HTTP_HOST'],
            'title'         =>'New Document',
            'content'       =>'New Content new document',
            'metakey'       =>'meta, key, none',
            'metacontent'   =>'meta content none',
        );

        $valuesDataMerge = array_merge($valuesData, $values);
        return $valuesDataMerge;
    }



    /**
     * Открыть файл для читания
     *
     * @param   null    $file   имя файла
     * @return  $this
     */
    public function open($file=null)
    {
        $dataSelect = null;

        if($file==null){
            $filesRead = (array) $this->readFiles();
            $i=0;
            foreach($filesRead as $fileRead){
                $dataSelect[$i] = (array) simplexml_load_file($this->pathDb.$fileRead);
                $dataSelect[$i]['file'] = str_replace('.xml','',$fileRead);
                $i++;
            }
            $this->dataSelect = $dataSelect;
            return $this;

        }elseif(is_array($file) OR strpos($file, ',') !==false){

            if(is_string($file))
                $filesRead = array_values(array_diff(explode(',',$file),array()));
            else
                $filesRead = $file;

            $filesRead = array_map(function($a){ return trim($a); },$filesRead);
            $i=0;
            foreach($filesRead as $fileRead){

                if(!is_file($this->pathDb.$fileRead.'.xml'))
                    self::Error("<b>".$this->pathDb.$fileRead.'.xml'."</b> file not found");

                $dataSelect[$i] = (array) simplexml_load_file($this->pathDb.$fileRead.'.xml');
                $dataSelect[$i]['file'] = $fileRead;
                $i++;
            }

            $this->dataSelect = $dataSelect;
            return $this;

        }elseif(is_string($file)){
            $file = strtolower($this->clean($file));

            if(!is_file($this->pathDb.$file.'.xml'))
                self::Error("<b>".$this->pathDb.$file.'.xml'."</b> file not found");

            self::$updateFile = $file;

            $this->xml = simplexml_load_file($this->pathDb.$file.'.xml');

            $this->dataSelect = (array) $this->xml;
            $this->dataSelect['file'] = $file;
            return $this;
        }

        return $this;

    }


    /**
     * Выбрать количество полей
     *
     * @param   string  $name   Имя поля или несколько через запятую или знак * - все
     * @return  array|bool|string
     */
    public function select($name='*')
    {

        if($this->dataSelectWhere==false)
            $select = $this->dataSelect;
        else
            $select = $this->dataSelectWhere;

        if($name == '*'){

            if(count($select) == 1)
                return $select[0];
            else
                return $select;

        }elseif(is_array($name) OR (stripos($name, ',') !== false)){

            //$sD = $select;

            if(is_string($name))
                $dataString = array_values(array_diff(explode(',',$name),array()));
            else
                $dataString = $name;

            $dataString = array_map(function($a){ return trim($a); },$dataString);

            $newSelectData = array();
            $iC=0;
            foreach($select as $valFile){
                foreach($dataString as $get){
                    if(!isset($valFile[$get]))
                        self::Error('ERROR. Name ['.$get.'] not exists!');
                    else
                        $newSelectData[$iC][$get] = $valFile[$get];
                }
                $iC++;
            }

            $this->dataSelect = $newSelectData;
            return $newSelectData;
            /*if(count($newSelectData) == 1) {
                $this->dataSelect = $newSelectData[0];
                return $newSelectData[0];
            } else {
                $this->dataSelect = $newSelectData;
                return $newSelectData;
            }*/

        }elseif(is_string($name)){
            $name = $this->clean($name);

            if(isset($this->xml->$name)){
                return $this->strDecode($this->xml->$name);
            }else
                self::Error('ERROR. Name ['.$name.'] not exists!');
        }else
            return false;
    }




    /**
     * сортировка выбраного по значению
     *
     * @param string $attr
     * @param string $asc
     * @return $this
     */
    public function sortBy($attr='id', $asc='ACS')
    {
        $selectData = $this->dataSelect;

        if(strtoupper($asc)=='ACS') {
            usort($selectData, function ($a, $b) use ($attr) {
                //return strcmp($a[$attr], $b[$attr]);
                return ($a[$attr] - $b[$attr]);
            });
        } elseif(strtoupper($asc)=='DESC') {
            usort($selectData, function ($a, $b) use ($attr) {
                //return strcmp($b[$attr], $a[$attr]);
                return ($b[$attr] - $a[$attr]);
            });
        }

        $this->dataSelect = $selectData;
        return $this;
    }


    /**
     * Правила для выборки, похож на оператор SQL WHERE но приниает только по одому правилу
     * с каждого обявления
     *
     * <pre>
     *
     *
     * </pre>
     *
     * @param $rule
     * @return $this
     */
    public function where($rule)
    {
        $this->dataSelectWhere = $this->rules($this->dataSelect, $rule);
        return $this;
    }

    /**
     * Дополнительные правила для ->where(),
     * схож по принцыпу на AND
     *
     * <pre>
     *
     *
     * </pre>
     *
     * @param $rule
     * @return $this
     */
    public function whereAnd($rule)
    {
        if($this->dataSelectWhere==null)
            self::Error('Error! Method whereAnd(...) must set after where(...)');

        $this->dataSelectWhere = $this->rules($this->dataSelectWhere, $rule);
        return $this;
    }

    /**
     * Дополнительные правила для ->where(),
     * схож по принцыпу на OR
     *
     * <pre>
     *
     *
     * </pre>
     *
     * @param $rule
     * @return $this
     */
    public function whereOr($rule)
    {
        $arrayWhere = $this->dataSelectWhere;
        $arrayOr = $this->rules($this->dataSelect, $rule);
        $this->dataSelectWhere = array_merge( $arrayWhere,  $arrayOr);
        return $this;
    }



    /**
     * Внутрений метод, сортирует массивы по выборке
     *
     * @param $aData
     * @param $rule
     * @return array
     */
    protected function rules($aData, $rule) {

        $aDataNew = array();

        if($el = stripos($rule, '<=')){
            $col = mb_strtolower(trim(substr($rule, 0, $el)));
            $val = mb_strtolower(trim(substr($rule, $el+2)));
            foreach($aData as $ad){
                if($ad[$col] <= $val)
                    $aDataNew[] = $aData;
            }
        }

        if($el = stripos($rule, '>=')){
            $col = mb_strtolower(trim(substr($rule, 0, $el)));
            $val = mb_strtolower(trim(substr($rule, $el+2)));
            foreach($aData as $ad){
                if($ad[$col] >= $val)
                    $aDataNew[] = $ad;
            }
        }

        if($el = stripos($rule, '!=')){
            $col = mb_strtolower(trim(substr($rule, 0, $el)));
            $val = mb_strtolower(trim(substr($rule, $el+2)));
            foreach($aData as $ad){
                if($ad[$col] != $val)
                    $aDataNew[] = $ad;
            }
        }

        if($el = stripos($rule, '<')){
            $col = mb_strtolower(trim(substr($rule, 0, $el)));
            $val = mb_strtolower(trim(substr($rule, $el+1)));
            foreach($aData as $ad){
                if($ad[$col] < $val)
                    $aDataNew[] = $ad;
            }
        }

        if($el = stripos($rule, '>')){
            $col = mb_strtolower(trim(substr($rule, 0, $el)));
            $val = mb_strtolower(trim(substr($rule, $el+1)));
            foreach($aData as $ad){
                if($ad[$col] > $val)
                    $aDataNew[] = $ad;
            }
        }

        if($el = stripos($rule, '=')){
            $col = mb_strtolower(trim(substr($rule, 0, $el)));
            $val = mb_strtolower(trim(substr($rule, $el+1)));
            foreach($aData as $ad){
                if(mb_strtolower($ad[$col]) == $val)
                    $aDataNew[] = $ad;
            }
        }

        return $aDataNew;
    }


    /**
     * Создать новый файл
     *
     * @param  string   $file    Имя файлаы
     * @return $this
     */
    public function create($file)
    {
        self::$saveType = 'create';

        if(is_file($this->pathDb.$file.'.xml'))
            self::Error("Can`t create new file, \"<b>$file.xml</b>\" is already");

        $fileName = strtolower($this->clean($file));
        self::$createFile = $fileName;

        $lastID = $this->autoIncrement();

        $this->xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><item></item>');
        $this->xml->addChild('id', $lastID);

        return $this;
    }

    /**
     * Выбрать файлов для обновления данных
     *
     * @param string $name  Имя поля
     * @param string $text  Новое значение
     * @return $this
     */
    public function update($name, $text)
    {
        $name = $this->clean($name);
        $text = $this->strEncode($text);

        if(isset($this->xml->$name)){
            self::$saveType = 'update';

            $this->xml->$name = $text;
            return $this;
        }else
            self::Error('->update(<b>'.$name.'</b>... <br>name <b>"'.$name.'"</b> not exists!');

        return $this;
    }


    /**
     * Вставляет знаение в поле файла
     *
     * @param   string  $name   имя поля
     * @param   string  $text   значение поля
     * @return  $this
     */
    public function insert($name,$text)
    {
        $name = $this->clean($name);
        $text = $this->strEncode($text);
        if(!isset($this->xml->$name)){
            $this->xml->addChild($name,$text);
            $this->dataInsert[$name] = $text;
            return $this;
        }else
            self::Error('->insert(<b>'.$name.'</b>... <br>name <b>"'.$name.'"</b> is already!');

        return $this;
    }

    public function delete()
    {
        self::$saveType='delete';
        return $this;
    }

    /**
     * сохраняет созданий файл и записует в него данные
     */
    public function save()
    {
        $file=false;

        if(self::$saveType=='create'){

            $addValues = array_merge($this->dataDefaultValues, $this->dataInsert);

            foreach($addValues as $key=>$value){
                    $this->xml->$key = $value;
            }

            $file = $this->pathDb.self::$createFile.'.xml';

        }elseif(self::$saveType=='update'){
            $file = $this->pathDb.self::$updateFile.'.xml';

        }elseif(self::$saveType=='delete'){
            $file = $this->pathDb.self::$updateFile.'.xml';
            $result = unlink($file);

            if($result){
                self::$saveType = null;
                return true;
            } else {
                self::Error("can`t delete file: ".$file);
                return false;
            }

        }else{
            self::Error("inside error, undefined type: ".self::$saveType);
            return false;
        }

        $saveData = $this->xml->asXML();

        if(!$file)
            self::Error("inside error, file noy found, its bug! sorry.");

        $fileSave = file_put_contents($file, $saveData);
        if($fileSave > 0){
            self::$createFile = null;
            $this->dataInsert = null;
            self::$saveType = null;
        }

    }


    public function addDefaultChild() {

        $addValues = array_merge($this->dataDefaultValues,$this->dataInsert);

        foreach($addValues as $key=>$value){
            if(!isset($addValues[$key]))
                $this->xml->addChild($key, $value);
        }
    }

    /**
     * Чтение всех файлов для дальнейшего разбора
     * @return array
     */
    protected function readFiles(){

        $result = opendir($this->pathDb);

        $filesName = array();

        while (false !== ($entry = readdir($result))) {
            if ($entry != "." && $entry != ".." && $entry != $this->autoIncrementFile) {
                $filesName[] = $entry;
            }
        }
        closedir($result);

        self::$namesAllFiles = $filesName;
        return $filesName;
    }




    // WORKING METHODS
    // * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *


    /**
     * Кодирует записи перед записом в файл
     *
     * @param string    $text
     * @return mixed|string
     */
    public function strEncode($text) {
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        $text = str_replace(chr(13), '', $text); //
        $text = str_replace(chr(12), '', $text); // FF
        $text = str_replace(chr(3), ' ', $text); // ETX
        return $text;
    }


    /**
     * Розкодирует строку после чтения из файла
     *
     * @param string    $text
     * @return string
     */
    public function strDecode($text) {
        return html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    }


    /**
     * чистильшик строк
     *
     * @param string    $text
     * @return string
     */
    public function clean($text){
        $data = trim(stripslashes(strip_tags(html_entity_decode($text, ENT_QUOTES, 'UTF-8'))));
        return $data;
    }


    /**
     * Автоинкремент для создаваемых файлов
     * @return mixed
     */
    protected function autoIncrement()
    {

        // инициализация авто-инкремента
        if(!file_exists($this->pathDb.$this->autoIncrementFile)){
            file_put_contents($this->pathDb.$this->autoIncrementFile, "1");
        }else{
            $this->autoIncrement = file_get_contents($this->pathDb.$this->autoIncrementFile);
        }

        $newId = (int) $this->autoIncrement + 1;
        file_put_contents($this->pathDb.$this->autoIncrementFile, $newId);
        return $newId;

    }


    /**
     * Для отдалки
     *
     * @param $msg
     * @return bool
     */
    public static function Error($msg){
        if(self::$debug){
            die( "<div style='padding:10px; color: #fff; background-color:#565656; font-family: monospace,sans-serif;'>
                <h2 style='color: #df1000'>ERROR</h2><p style='padding:3px; color: #fff; background-color:#323232'>".$msg."</p></div>" );
        }else{
            return false;
        }
    }

}
