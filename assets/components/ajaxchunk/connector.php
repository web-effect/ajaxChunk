<?php
//error_reporting(E_ERROR | E_WARNING | E_PARSE);
//ini_set('error_reporting', E_ERROR | E_WARNING | E_PARSE);
require dirname(dirname(dirname(dirname(__FILE__))))."/config.core.php";

if(!defined('MODX_CORE_PATH')) require_once '../../../config.core.php';
require_once MODX_CORE_PATH.'config/'.MODX_CONFIG_KEY.'.inc.php';
require_once MODX_CORE_PATH.'model/modx/modx.class.php';
/*define('MODX_API_MODE', true);
require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/index.php');*/
$modx = new modX();

$collections_cp = $modx->getOption('collections.core_path', null, $modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/collections/');
if(file_exists($collections_cp))
{
	require_once $collections_cp.'model/collections/mysql/collectioncontainer.class.php';
	require_once $collections_cp.'model/collections/collectioncontainer.class.php';
	require_once $collections_cp.'model/collections/mysql/selectioncontainer.class.php';
	require_once $collections_cp.'model/collections/selectioncontainer.class.php';
}

if($_POST['resource']){$modx->resource = $modx->getObject('modResource',(int)$_POST['resource'],false);}
$modx->initialize( 'web' );
$modx->invokeEvent("OnLoadWebDocument");
$modx->lexicon->load($modx->getOption('cultureKey').':core:default');
if($modx->resource){
	$q_var = $modx->getOption('request_param_alias', null, 'q');
	$_REQUEST[$q_var]=$modx->makeUrl($modx->resource->id);
	if($tvs=$modx->resource->getMany('TemplateVars', 'all')){
		foreach ($tvs as $tv){
			$modx->resource->set($tv->get('name'), array(
				$tv->get('name'),
				$tv->getValue($modx->resource->get('id')),
				$tv->get('display'),
				$tv->get('display_params'),
				$tv->get('type'),
			));
		}
	}
}

$result = array();
$result['timestamp'] = (int)$_POST['timestamp'];
if(!isset($_POST['ajaxchunk_name'])){
    $result['success'] = false;
    $result['error'] = 'Identifer missed.';
}elseif(!isset($_SESSION['ajaxchunk_'.$_POST['ajaxchunk_name']])){
    $result['success'] = false;
    $result['error'] = 'Indetifer not inited';
}else{
    $result['success'] = true;
    $scriptProperties = $_SESSION['ajaxchunk_'.$_POST['ajaxchunk_name']];
    
    $ajaxChunk = $modx->getService('ajaxChunk','ajaxChunk',MODX_CORE_PATH.'components/ajaxchunk/model/ajaxchunk/');
    $ajaxChunk->initialize($scriptProperties);

    if($ajaxChunk->hasErrors){
        $result['success'] = false;
        $result['error'] = $ajaxChunk->getErrors();
    }else{
        $result['content'] = $ajaxChunk->getContent();
    }
}

echo json_encode($result);
