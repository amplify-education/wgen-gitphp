<?php
/**
 * GitPHP
 *
 * Index
 *
 * @author Christopher Han <xiphux@gmail.com>
 * @copyright Copyright (c) 2010 Christopher Han
 * @package GitPHP
 */

define('GITPHP_START_TIME', microtime(true));
define('GITPHP_START_MEM', memory_get_usage());

/**
 * Define some paths
 */
define('GITPHP_BASEDIR', dirname(__FILE__) . '/');
define('GITPHP_CONFIGDIR', GITPHP_BASEDIR . 'config/');
define('GITPHP_INCLUDEDIR', GITPHP_BASEDIR . 'include/');
define('GITPHP_GITOBJECTDIR', GITPHP_INCLUDEDIR . 'git/');
define('GITPHP_CONTROLLERDIR', GITPHP_INCLUDEDIR . 'controller/');

include_once(GITPHP_INCLUDEDIR . 'version.php');

require_once(GITPHP_INCLUDEDIR . 'Config.class.php');

require_once(GITPHP_INCLUDEDIR . 'Log.class.php');

require_once(GITPHP_GITOBJECTDIR . 'ProjectList.class.php');

require_once(GITPHP_INCLUDEDIR . 'MessageException.class.php');

require_once(GITPHP_CONTROLLERDIR . 'Controller.class.php');

date_default_timezone_set('UTC');

//try {

	// Define these here because these get used in the config file
	define('GITPHP_COMPRESS_BZ2', 1);
	define('GITPHP_COMPRESS_GZ', 2);
	define('GITPHP_COMPRESS_ZIP', 3);

	/*
	 * Configuration
	 */
	GitPHP_Config::GetInstance()->LoadConfig(GITPHP_CONFIGDIR . 'gitphp.conf.php');

	/*
	 * Debug
	 */
	if (GitPHP_Log::GetInstance()->GetEnabled()) {
		GitPHP_Log::GetInstance()->SetStartTime(GITPHP_START_TIME);
		GitPHP_Log::GetInstance()->SetStartMemory(GITPHP_START_MEM);
	}

	if (!GitPHP_Config::GetInstance()->GetValue('projectroot', null)) {
		throw new GitPHP_MessageException('A projectroot must be set in the config', true);
	}

	/*
	 * Project list
	 */
	GitPHP_ProjectList::Instantiate(GITPHP_CONFIGDIR . 'gitphp.conf.php');

	session_start();

	$controller = GitPHP_Controller::GetController((isset($_GET['a']) ? $_GET['a'] : null));
	if ($controller) {
		$controller->RenderHeaders();
		$controller->Render();
	}

//} catch (Exception $e) {
//
//	require_once(GITPHP_CONTROLLERDIR . 'Controller_Message.class.php');
//	$controller = new GitPHP_Controller_Message();
//	$controller->SetParam('message', $e->getMessage());
//	if ($e instanceof GitPHP_MessageException) {
//		$controller->SetParam('error', $e->Error);
//	} else {
//		$controller->SetParam('error', true);
//	}
//	$controller->Render();
//
//}

if (GitPHP_Log::GetInstance()->GetEnabled()) {
	$entries = GitPHP_Log::GetInstance()->GetEntries();
	foreach ($entries as $logline) {
		echo "\n" . $logline;
	}
}

?>
