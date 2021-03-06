<?php
/**
 * GitPHP Controller Snapshot
 *
 * Controller for getting a snapshot
 *
 * @author Christopher Han <xiphux@gmail.com>
 * @copyright Copyright (c) 2010 Christopher Han
 * @package GitPHP
 * @subpackage Controller
 */

/**
 * Snapshot controller class
 *
 * @package GitPHP
 * @subpackage Controller
 */
class GitPHP_Controller_Snapshot extends GitPHP_ControllerBase
{

	/**
	 * __construct
	 *
	 * Constructor
	 *
	 * @access public
	 * @return controller
	 */
	public function __construct()
	{
		parent::__construct();
		if (!$this->project) {
			throw new GitPHP_MessageException('Project is required for snapshot', true);
		}
	}

	/**
	 * GetTemplate
	 *
	 * Gets the template for this controller
	 *
	 * @access protected
	 * @return string template filename
	 */
	protected function GetTemplate()
	{
		return 'snapshot.tpl';
	}

	/**
	 * GetCacheKey
	 *
	 * Gets the cache key for this controller
	 *
	 * @access protected
	 * @return string cache key
	 */
	protected function GetCacheKey()
	{
		return isset($this->params['hash']) ? $this->params['hash'] : '';
	}

	/**
	 * GetName
	 *
	 * Gets the name of this controller's action
	 *
	 * @access public
	 * @return string action name
	 */
	public function GetName()
	{
		return 'snapshot';
	}

	/**
	 * ReadQuery
	 *
	 * Read query into parameters
	 *
	 * @access protected
	 */
	protected function ReadQuery()
	{
		if (isset($_GET['h'])) $this->params['hash'] = $_GET['h'];
		if (isset($_GET['f'])) $this->params['path'] = $_GET['f'];
		if (isset($_GET['prefix'])) $this->params['prefix'] = $_GET['prefix'];
	}

	/**
	 * LoadHeaders
	 *
	 * Loads headers for this template
	 *
	 * @access protected
	 */
	protected function LoadHeaders()
	{
		$this->params['compressformat'] = GitPHP_Config::GetInstance()->GetValue('compressformat', GITPHP_COMPRESS_ZIP);
		$rname = $this->project->GetSlug();

		if ($this->params['compressformat'] == GITPHP_COMPRESS_ZIP) {
			$this->headers[] = 'Content-Type: application/x-zip';
			$this->headers[] = 'Content-Disposition: attachment; filename=' . $rname . '.zip';
		} else if (($this->params['compressformat'] == GITPHP_COMPRESS_BZ2) && function_exists('bzcompress')) {
			$this->headers[] = 'Content-Type: application/x-bzip2';
			$this->headers[] = 'Content-Disposition: attachment; filename=' . $rname . '.tar.bz2';
		} else if (($this->params['compressformat'] == GITPHP_COMPRESS_GZ) && function_exists('gzencode')) {
			$this->headers[] = 'Content-Type: application/x-gzip';
			$this->headers[] = 'Content-Disposition: attachment; filename=' . $rname . '.tar.gz';
		} else {
			$this->headers[] = 'Content-Type: application/x-tar';
			$this->headers[] = 'Content-Disposition: attachment; filename=' . $rname . '.tar';
		}

	}

	/**
	 * LoadData
	 *
	 * Loads data for this template
	 *
	 * @access protected
	 */
	protected function LoadData()
	{
		$commit = null;

		if (!isset($hash))
			$commit = $this->project->GetHeadCommit();
		else
			$commit = $this->project->GetCommit($hash);

		$this->tpl->assign("archive", $commit->GetArchive($this->params['compressformat'], $this->params['path'], $this->params['prefix']));
	}

}
