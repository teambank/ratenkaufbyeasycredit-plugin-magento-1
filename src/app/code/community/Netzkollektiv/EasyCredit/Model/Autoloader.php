<?php
class Netzkollektiv_EasyCredit_Model_Autoloader extends Varien_Event_Observer {

    public function controllerFrontInitBefore( $event ) {
        spl_autoload_register( array($this, 'load'), true, true );
    }

    public static function load( $class )
    {
        require_once dirname(__FILE__). '/../vendor/autoload.php';

        if ( preg_match( '#^(Netzkollektiv\\\\EasyCredit\\\\Api\\\\)\b#', $class ) ) {
            $class = str_replace('Netzkollektiv\\EasyCredit\\','',$class);
            $phpFile = dirname(__FILE__) . '/../' . str_replace( '\\', '/', $class ) . '.php';
            require_once( $phpFile );
            return;
        }
    }

}
