<?php
// @codingStandardsIgnoreFile
// @codeCoverageIgnoreStart
// this is an autogenerated file - do not edit
function autoload5103ac6c164ceb70e9e84d3f8943f985($class) {
    static $classes = null;
    if ($classes === null) {
        $classes = array(
            'hudson_svnplugin' => '/hudson_svnPlugin.class.php',
            'tuleap\\hudsonsvn\\continuousintegrationcollector' => '/ContinuousIntegrationCollector.php',
            'tuleap\\hudsonsvn\\formpresenter' => '/FormPresenter.php',
            'tuleap\\hudsonsvn\\job\\dao' => '/Job/Dao.php',
            'tuleap\\hudsonsvn\\job\\factory' => '/Job/Factory.php',
            'tuleap\\hudsonsvn\\job\\job' => '/Job/Job.php',
            'tuleap\\hudsonsvn\\job\\manager' => '/Job/Manager.php',
            'tuleap\\hudsonsvn\\plugin\\hudsonsvnplugindescriptor' => '/HudsonSvnPluginDescriptor.php',
            'tuleap\\hudsonsvn\\plugin\\hudsonsvnplugininfo' => '/HudsonSvnPluginInfo.php'
        );
    }
    $cn = strtolower($class);
    if (isset($classes[$cn])) {
        require dirname(__FILE__) . $classes[$cn];
    }
}
spl_autoload_register('autoload5103ac6c164ceb70e9e84d3f8943f985');
// @codeCoverageIgnoreEnd