<?php
class Netzkollektiv_EasyCredit_Model_Autoloader extends Varien_Event_Observer {

    public function controllerFrontInitBefore( $event ) {
        spl_autoload_register( array($this, 'load'), true, true );
    }

    public static function load( $class )
    {
        if ( preg_match( '#^(Netzkollektiv\\\\EasyCreditApi)\b#', $class ) ) {
            $phpFile = Mage::getBaseDir('lib') . '/' . str_replace( '\\', '/', $class ) . '.php';
            require_once( $phpFile );
            return;
        }
        if ( preg_match( '#^(Netzkollektiv\\\\EasyCredit\\\\Api\\\\)\b#', $class ) ) {
            $class = str_replace('Netzkollektiv\\EasyCredit\\','',$class);
            $phpFile = dirname(__FILE__) . '/../' . str_replace( '\\', '/', $class ) . '.php';
file_put_contents('/tmp/bla',$class.' => '.$phpFile.PHP_EOL,FILE_APPEND);
            require_once( $phpFile );
            return;
        }
    }

}
