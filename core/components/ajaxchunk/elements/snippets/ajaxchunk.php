<?php
/*
    Добавляет чанк с возможностью обновления, управления по ajax
    
        &chunk - название чанка, который необходимо вывести
        &name - уникальное имя чанка, используется как id для обёртки чанка, 
                также используется для управления при помощи js
        &mode - режим работы
            - static - при загрузке страницы обработать чанк как обычно, далее обновлять по ajax
            - onload - при загрузке страницы не обрабатывать чанк, но загрузить его по ajax
            - oncall - не загружать чанк при загрузке страницы, только загрузка по команде
        &resource - id ресурса используемого для тегов в чанке, по умолчанию текущий, также можно
                изменить его при помощи api
        &* - Остальные параметры, будут переданы в чанк как плейсхолдеры,
                могут быть изменены при помощи js api
                
        --------- Не реализовано
        &websokets - включить обновление по веб-сокету
        комментарии с именем чанка в формате <!--&ame|ключь|значение--> будут включены в ответ при обновлении чанка
*/
$scriptProperties['mode'] = $modx->getOption('mode',$scriptProperties,'static');
$scriptProperties['resource'] = $modx->getOption('resource',$scriptProperties,$modx->resource->id);

$ajaxChunk = $modx->getService('ajaxChunk','ajaxChunk',MODX_CORE_PATH.'components/ajaxchunk/model/ajaxchunk/');

$ajaxChunk->initialize($scriptProperties);

if($ajaxChunk->hasErrors)return $ajaxChunk->getErrors();
$ajaxChunk->loadWebFiles();

$content='';
if($scriptProperties['mode'] == 'static')$content = $ajaxChunk->getContent();

$script = '
    ajaxchunk.storage["'.$ajaxChunk->config['options']['name'].'"] = {};
    ajaxchunk.storage["'.$ajaxChunk->config['options']['name'].'"]["config"] = {"mode":"'.$scriptProperties['mode'].'","resource":"'.$scriptProperties['resource'].'"};
    ajaxchunk.storage["'.$ajaxChunk->config['options']['name'].'"]["data"] = {"resource":"'.$scriptProperties['resource'].'"};
';
if($scriptProperties['mode']=='onload')$script.='ajaxchunk.load("'.$ajaxChunk->config['options']['name'].'")';

$modx->regClientScript('<script type="text/javascript">'.$script.'</script>');
	
return '<div class="ajaxchunk" id="ajaxchunk_'.$ajaxChunk->config['options']['name'].'">'.$content.'</div>';
