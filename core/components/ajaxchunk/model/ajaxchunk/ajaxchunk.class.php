<?php

class ajaxChunk
{
	public $modx;
	public $authenticated = false;
	public $errors = array();

	function __construct(modX &$modx, array $config = array())
	{
		$this->modx =& $modx;

		$corePath = $this->modx->getOption('ajaxchunk.core_path', $config, $this->modx->getOption('core_path') . 'components/ajaxchunk/');
		$assetsPath = $this->modx->getOption('ajaxchunk.assets_path', $config, $this->modx->getOption('assets_path') . 'components/ajaxchunk/');
		$assetsUrl = $this->modx->getOption('ajaxchunk.assets_url', $config, $this->modx->getOption('assets_url') . 'components/ajaxchunk/');
		$connectorUrl = $assetsUrl . 'connector.php';
		$context_path = $this->modx->context->get('key')=='mgr'?'mgr':'web';

		$this->config = array_merge(array(
			'assetsUrl' => $assetsUrl,
			'cssUrl' => $assetsUrl . $context_path . '/css/',
			'jsUrl' => $assetsUrl . $context_path . '/js/',
			'jsPath' => $assetsPath . $context_path . '/js/',
			'imagesUrl' => $assetsUrl . $context_path . '/img/',
			'connectorUrl' => $connectorUrl,
			'corePath' => $corePath,
			'modelPath' => $corePath . 'model/',
			'chunksPath' => $corePath . 'elements/chunks/',
			'templatesPath' => $corePath . 'elements/templates/',
			'chunkSuffix' => '.chunk.tpl',
			'snippetsPath' => $corePath . 'elements/snippets/',
			'processorsPath' => $corePath . 'processors/'
		), $config);

		$this->modx->lexicon->load('ajaxchunk:default');
		$this->authenticated = $this->modx->user->isAuthenticated($this->modx->context->get('key'));
	}

	public function initialize($scriptProperties = array(),$ctx = 'web')
	{
		$this->config['options'] = $scriptProperties;
		$this->config['ctx'] = $ctx;
        if(!$this->modx->getOption('name',$scriptProperties,false)){
            $this->hasErrors = true;
            $this->errors[] = $this->modx->lexicon('ac.init_name_error');
            return false;
        }
        $_SESSION['ajaxchunk_'.$this->config['options']['name']] = $this->config['options'];
		return true;
	}
	
	public function getContent()
	{
	    $chunk = $this->modx->getObject('modChunk',array('name'=>$this->config['options']['chunk']));
        if(!$chunk){
            $this->hasErrors = true;
            $this->errors[] = $this->modx->lexicon('ac.content_chunk_error', $this->config['options']);
            return false;
        }
        $chunk->setCacheable(false);
        $chunkContent = $chunk->getContent();
        
        $placeholders = $this->config['options'];
        
        $output = $chunk->process($placeholders,$chunkContent);
        $maxIterations = (integer) $this->modx->getOption('parser_max_iterations', null, 10);
        $this->modx->getParser()->processElementTags('', $output, false, false, '[[', ']]', array(), $maxIterations);
        $this->modx->getParser()->processElementTags('', $output, true, true, '[[', ']]', array(), $maxIterations);
        return $output;
	}

	public function error($message = '', $data = array(), $placeholders = array())
	{
		$response = array(
			'success' => false,
			'message' => $this->modx->lexicon($message, $placeholders),
			'data' => $data,
		);

		return $this->config['json_response']
			? $this->modx->toJSON($response)
			: $response;
	}
	
	public function getErrors()
	{
	    return implode(', ',$this->errors);
	}

	public function success($message = '', $data = array(), $placeholders = array())
	{
		$response = array(
			'success' => true,
			'message' => $this->modx->lexicon($message, $placeholders),
			'data' => $data,
		);

		return $this->config['json_response']
			? $this->modx->toJSON($response)
			: $response;
	}
    
    public function loadWebFiles(array $properties = array())
    {
        $clientConfig = array(
			'assets_js' => $this->config['assetsUrl'],
			'connector_url' => $this->config['connectorUrl'],
			'storage'=>array(),
			'lang'=>array('notavail'=>$this->modx->lexicon('ac.notavail'))
		);
        $this->modx->regClientStartupHTMLBlock
        (
        '<script type="text/javascript">
            var ajaxchunk = '.$this->modx->toJSON($clientConfig).';
        </script>'
        );
        $this->modx->regClientScript($this->config['jsUrl'].'ajaxchunk.js');
    }
}
