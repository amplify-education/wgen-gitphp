<?php
/**
 * GitPHP Controller Project
 *
 * Controller for displaying a project summary
 *
 * @author Christopher Han <xiphux@gmail.com>
 * @copyright Copyright (c) 2010 Christopher Han
 * @package GitPHP
 * @subpackage Controller
 */

/**
 * Project controller class
 *
 * @package GitPHP
 * @subpackage Controller
 */
class GitPHP_Controller_Project extends GitPHP_ControllerBase
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
			throw new GitPHP_MessageException('Project is required for project summary', true);
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
		return 'project.tpl';
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
		return '';
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
		return 'summary';
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
		$this->tpl->assign('head', $this->project->GetHeadCommit());

		$revlist = $this->project->GetLog('HEAD', 17);
		if ($revlist) {
			$this->tpl->assign('hasmorerevs', count($revlist) > 16);
			$this->tpl->assign("revlist", array_slice($revlist, 0, 16));
		}

		$taglist = array_values($this->project->GetTagRefs());
		if (isset($taglist) && (count($taglist) > 0)) {
			$this->tpl->assign('hasmoretags', count($taglist) > 16);
			$this->tpl->assign("taglist", array_slice($taglist, 0, 16));
		}

		$headlist = $this->project->GetHeads();
		if (isset($headlist) && (count($headlist) > 0)) {
			$this->tpl->assign('hasmoreheads', count($headlist) > 16);
			$this->tpl->assign("headlist", array_slice($headlist, 0, 16));
		}
	}

}
