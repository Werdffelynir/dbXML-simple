<?php

/* Методы выборки файлов   */
/*
// Открытие файлов
$xml->open('*')                             // открыть все файлы * 
    [->open('file')]                        // или один по имени
    [->open('file, file, file')]            // или несколько имен в строке через запятую
    [->open(array('file, file, file'))]     // или несколько имен в массиве
    ->where('id=10')                        // параметры выборки, включает также whereOr() whereAnd()
    ->sortBy("id")                          // сортировка по полю
    ->select('*')                           // выбрать все
    ->select('id, title, content')          // или конкретные поля с строки, файлы через запятую
    ->select(array('file', 'file', 'file'));  // или конкретные поля с массива

// Создарние файлов
$xml->create('file')                        // имя создаваемого файла
    ->insert('item1','<p>value 1<p>')       // название поля и значение
    ->insert('item2','<p>value 2<p>')
    ->save();                               // сохранение

// Обновление файлов
$xml->update('file')                        // имя файла
    [->update('file, file, file')]          // или имена в строке через запятую
    [->update(array('file', 'file', 'file'))]   // или несколько имен в массиве
    ->where('id=10')                        // параметры выборки, включает также whereOr() whereAnd()
    ->rewrite('item1','<p>new value 1<p>')  // поле для обновления и новое значение
    ->rewrite('item2','<p>new value 2<p>')
    ->save();                               // сохранение изминений

// Удадение файлов
$xml->delete('file')                        // имя файла
    [->delete('file, file, file')]          // или имена в строке через запятую
    [->delete(array('file', 'file', 'file'))]   // или несколько имен в массиве
    ->where('id=10')                        // параметры выборки, включает также whereOr() whereAnd()
    ->save(); || remove();                  // подтвердить удаление 
    
    
# Общие методы для: open, update, deleted
    ->where('id>40')
    ->whereOr('id=39')
    ->whereAnd('author=vasia');

	
	
$xml = new dbXML();
/*
// Открытие файлов
$rOpen = $xml->open()
    ->sortBy("id", "DESC")
    ->select(array('id', 'title', 'content'));
    //->select('id,title,content');
var_dump($rOpen);

foreach ($rOpen as $a) {
    echo "<h1>".$a['title']."</h1><p>".$a['content']."</p><p>".$a['id']."</p>";
}



// Создарние файлов


for($i=0;$i<100;$i++){

    $xml->create('file_'.$i)
        ->insert('item1','value 1')
        ->save();
}
*/

/*
// Обновление файлов
$xml->open('file4')
    ->update('item1', 'ZZZZnew value 1')
    ->update('item2', 'new value 2')
    ->save();

$xml->open('file2')
    ->update('item1', 'ZZZnew value 1')
    ->update('item2', 'new value 2')
    ->save();


$xml->open()
    ->where('id>7');
$select = $xml->select();
var_dump($select);
*/
/*
    ->delete()
    ->save();
*/














