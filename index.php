<?php
define('START_TIME',microtime(true));

include_once('./DbXML.php');
$xml = new DbXML();

if($_GET['page'] == 'records')
{
    // All records
    $records = $xml->open()->sortBy("id")->where('status=public')->select('*');
    $page = 'records';
}
elseif($_GET['page'] == 'form')
{
    if(isset($_POST['title'])){

        // new file or update
        if(empty($_POST['file'])){
            $xml->create($_POST['url']);
            $xml->insert('title',$_POST['title']);
            $xml->insert('url',$_POST['url']);
            $xml->insert('order',$_POST['order']);
            $xml->insert('status',$_POST['status']);
            $xml->insert('content',$_POST['content']);
            $xml->save();
        }else{
            $xml->open($_POST['file'])->select();
            $xml->update('title',$_POST['title']);
            $xml->update('url',$_POST['url']);
            $xml->update('order',$_POST['order']);
            $xml->update('status',$_POST['status']);
            $xml->update('content',$_POST['content']);
            $xml->save();
        }
    }

    if(isset($_GET['edit'])){
        $recordsEdit = $xml->open()->sortBy("id")->where('id='.$xml->clean($_GET['edit']))->select('*');
    }else{
        $recordsEdit = array('title'=>'','url'=>'','content'=>'','author'=>'','id'=>'', 'order'=>'', 'status'=>'');
    }

    $page = 'form';
}
else
{
    if(isset($_GET['read'])){
        $recordsRead = $xml ->open()->where('id='.$_GET['read'])->select();
    }else{
        $recordsRead = $xml ->open()->where('id=7525')->select();
    }
    $page = 'home';
}




function limitWords($input_text, $limit = 50, $end_str = '...')
{
    $input_text = strip_tags($input_text);
    $words = explode(' ', $input_text);
    if ($limit < 1 || sizeof($words) <= $limit) {
        return $input_text;
    }
    $words = array_slice($words, 0, $limit);
    $out = implode(' ', $words);
    return $out . $end_str;
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>XMLdb</title>
    <style type="text/css">
        body, html{ margin:0; padding:0; font-family: Arial, Verana; font-size: 12px; background-color: #333; }
        .wrapper{ width: 860px; margin: 0 auto;}
        .header{ height: 25px; text-align: center; padding: 10px; background-color:  #FFF; }
        .menu ul{ margin:0; padding:0; }
        .menu ul li{ display: inline; }
        .menu ul li a{ background: #686868; color: #FFF; display: inline-block; padding: 3px 20px; text-decoration: none; }
        .menu ul li a:hover{ color: #B2B2B2;  background: #404040; }
        .content{ padding: 10px; background-color: #FFF; margin: 10px 0; min-height: 400px; }
        .footer{ padding: 10px; background-color: #FFF; min-height: 40px; }
        .pageRecords .h{}
        .pageRecords .s{}
        .pageRecords .i{ color: #747474; background-color: #DDD; padding: 3px 5px;}
		hr{ height: 2px; border:none; background-color: #333; margin-bottom: 15px;}
		h1 a{ font-size: 16px; }
    </style>

    <script src="./public/nicEdit/nicEdit.js" type="text/javascript"></script>
    <script type="text/javascript">
        bkLib.onDomLoaded(function() {
            var editor = new nicEditor({
                iconsPath: 'public/nicEdit/nicEditorIcons.gif',
                buttonList : ['bold','italic','underline','left','center','right','justify','ol','ul','removeformat','indent',
                    'outdent','hr','image','upload','forecolor','bgcolor','link','unlink','fontSize','fontFamily','fontFormat','xhtml']
            }).panelInstance('editor');
        });
    </script>
</head>
<body>

    <div class="wrapper">
        <div class="header">
            <div class="menu">
                <ul>
                    <li><a href="?page=home">Home</a></li>
                    <li><a href="?page=records">Records</a></li>
                    <li><a href="?page=form">Form</a></li>
                </ul>
            </div>
        </div>
        <div class="content">


            <?php if($page=='home'):?>
            <div class="pageHome">
                <h1 class="h"> <?php echo $recordsRead['title'] ?>   </h1>
                <div class="c"> <?php echo $xml->strDecode($recordsRead['content']) ?> </div>
                <div class="i"> <?php echo $recordsRead['author'] ?> | <?php echo $recordsRead['date'] ?> | <a href="?page=form&edit=<?php echo $recordsRead['id'] ?>">Редактировать</a> </div>
				<hr>
            </div>
            <?php endif; ?>


            <?php if($page=='form'): ?>
            <div class="pageFORM">
                <form action="" method="post">

                    <div class="row">
                        <label>title</label><br>
                        <input type="text" name="title" value="<?php echo $recordsEdit['title']; ?>">
                    </div>

                    <div class="row">
                        <label>url</label><br>
                        <input type="text" name="url" value="<?php echo $recordsEdit['url']; ?>">
                    </div>

                    <div class="row" style="width: 70px; display:inline-block;">
                        <label>order</label><br>
                        <select name="order">
                            <?php for($i=1;$i<11;$i++):?>
                                <option <?php echo ($recordsEdit['order']==$i)?'selected':'';?> value="<?=$i?>"><?=$i?></option>
                            <?php endfor;?>
                        </select>
                    </div>

                    <div class="row" style="width: 60px; display:inline-block;">
                        <label>status</label><br>
                        <select name="status">
                            <option <?php echo ($recordsEdit['status']=='private')?'selected':'';?> value="private">private</option>
                            <option <?php echo ($recordsEdit['status']=='public') ?'selected':'';?> value="public" select="select">public</option>
                            <option <?php echo ($recordsEdit['status']=='static') ?'selected':'';?> value="static">static</option>
                        </select>
                    </div>


                    <div class="row">
                        <!--<div id="myNicPanel" style="width: 835px;"></div>-->
                        <label></label><br>
                        <textarea id="editor" name="content" style="width: 835px; min-width:835px; max-width:835px; min-height: 300px"><?php echo $recordsEdit['content']; ?></textarea>
                        <!--<div id="myInstance1"><?php //echo $recordsEdit['content']; ?></div>-->
                    </div>

                    <div class="row">
                        <br>
                        <input type="hidden" name="id" value="<?php echo $recordsEdit['id']; ?>">
                        <input type="hidden" name="file" value="<?php echo $recordsEdit['file']; ?>">
                        <input type="submit" value="Seve Query">
                    </div>

                </form>
            </div>
            <?php endif; ?>


            <?php if($page=='records'): ?>
            <div class="pageRecords">

                <?php foreach($records as $record): ?>
                <h1 class="h">  <a href="?page=home&read=<?php echo $record['id'] ?>"><?php echo $record['title'] ?></a>   </h1>
                <div class="c"> <?php echo limitWords(strip_tags( $xml->strDecode($record['content']) ), 30, '<a href="?page=home&read='.$record['id'].'"> Дальше...</a>') ?> </div>
                <div class="i"> <?php echo $record['author'] ?> | <?php echo $record['date'] ?> | <a href="?page=form&edit=<?php echo $record['id'] ?>">Редактировать</a> </div>
				<hr>
                <?php endforeach; ?>


            </div>
            <?php endif; ?>


        </div>
        <div class="footer"> Время генерациии составило <?php echo round(microtime(true)-START_TIME,4); ?> сек.</div>
    </div>



</body>
</html>



