<?php

class toLessCssPluginConfiguration extends sfPluginConfiguration
{
  public 
    $exe = '',
    $options = array(),
    $currentRunFail = array();

  public function configure(){
    $this->dispatcher->connect('dm.layout.filter_stylesheets', array($this, 'listenToFilterStylesheetsEvent'));
    // client side injection -- not supported yet :(
    //$this->dispatcher->connect('dm.layout.filter_javascripts', array($this, 'listenToFilterJavaScriptsEvent'));
  }

  
  public function listenToFilterStylesheetsEvent(sfEvent $event, array $assets){
    $this->exe = trim(sfConfig::get('app_lessCss_executable', ''));
    $this->options = sfConfig::get('app_lessCss_options', array());
    $vals = array_values($assets);
    $assets = array_combine(array_map(array($this, 'handleStylesheet'), array_keys($assets)), $vals);   
    unset($assets['']);
    return $assets;
  }
  
  public function listenToFilterJavaScriptsEvent(sfEvent $event, array $assets){
    $this->exe = trim(sfConfig::get('app_lessCss_executable', ''));
    $this->options = sfConfig::get('app_lessCss_options', array());
    $lessJs = trim(sfConfig::get('app_lessCss_lessjs', ''));
    if(stripos($lessJs, 'http') !== 0 && substr($lessJs, 0, 1) != '/')
      $lessJs = sprintf('/%s/js/%s', $this->getName(), $lessJs);
    $includeLessJs = !empty($this->currentRunFail) || empty($this->exe);
        
    if($includeLessJs && !empty($lessJs)){
      //die(get_class($this->dispatcher->getService('response')));
      $toLessConfig = sprintf('/%s/js/%s', $this->getName(), 'toLessConfig.js');
      $toLessConfigPath = sfConfig::get('sf_web_dir') . $toLessConfig;
      if(!file_exists($toLessConfigPath)){
        file_put_contents($toLessConfigPath, sprintf('less = %s;', json_encode($this->options)) . 'jQuery("link[href$=\'less\']").attr("ref", "stylesheet/less");');
      }
      sfConfig::set('app_js_head_inclusion', array_merge(sfConfig::get('app_js_head_inclusion', array()), array($toLessConfig, $lessJs)));
      //$assets[$toLessConfig] = array();
      //$assets[$lessJs] = array();
    }
    return $assets;
  }
  
  public function handleStylesheet($strAssetPath){
    if(false === stripos($strAssetPath, 'less.css'))
      return $strAssetPath;

    $assetPath = sfConfig::get('sf_web_dir') . $strAssetPath;
    $lessFile = substr($strAssetPath, 0, -4);
    $lessPath = sfConfig::get('sf_web_dir') . $lessFile;
    
    
    if(!file_exists($lessPath))
      return '';
    
    $mTimeLessFile = filemtime($lessPath);
    
    // caching
    if(file_exists($assetPath) && $mTimeLessFile == filemtime($assetPath)){
      return $strAssetPath;
    }
    
    $lessPath = realpath($lessPath);

    // less exists
    // SERVER-SIDE
    $done = false;
    if(!empty($this->exe)){
      $output = array();
      $retVal = -1;
      exec(sprintf('%s %s', escapeshellcmd($this->exe), escapeshellarg($lessPath)), $output, $retVal);
      $done = $retVal === 0;
      if($done){
          file_put_contents($assetPath, implode(PHP_EOL, $output));
          touch($assetPath, $mTimeLessFile);
          return $strAssetPath;
      }
    }
    $this->currentRunFail[$strAssetPath] = true;
    
    // client side not supported yet
    return '';
  }
}