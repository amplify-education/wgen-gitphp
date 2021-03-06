<?php
/**
 * GitPHP Controller Tree
 *
 * Controller for displaying a tree
 *
 * @author Christopher Han <xiphux@gmail.com>
 * @copyright Copyright (c) 2010 Christopher Han
 * @package GitPHP
 * @subpackage Controller
 */

/**
 * Tree controller class
 *
 * @package GitPHP
 * @subpackage Controller
 */
class GitPHP_Controller_Tree extends GitPHP_ControllerBase
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
			throw new GitPHP_MessageException('Project is required for tree', true);
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
		return 'tree.tpl';
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
		return (isset($this->params['hashbase']) ? $this->params['hashbase'] : '') . '|' . (isset($this->params['hash']) ? $this->params['hash'] : '') . '|' . (isset($this->params['file']) ? sha1($this->params['file']) : '');
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
		return 'tree';
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
		if (isset($_GET['f']))
			$this->params['file'] = $_GET['f'];
		if (isset($_GET['h']))
			$this->params['hash'] = $_GET['h'];
		if (isset($_GET['hb']))
			$this->params['hashbase'] = $_GET['hb'];

		if (!(isset($this->params['hashbase']) || isset($this->params['hash']))) {
			$this->params['hashbase'] = 'HEAD';
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
		if (!isset($this->params['hashbase'])) {
			// TODO: write a lookup for hash (tree) -> hashbase (commithash) and remove this
			throw new Exception('Hashbase is required');
		}

		$commit = $this->project->GetCommit($this->params['hashbase']);

		$this->tpl->assign('commit', $commit);

		if (!isset($this->params['hash'])) {
			if (isset($this->params['file'])) {
				$this->params['hash'] = $commit->PathToHash($this->params['file']);
			} else {
				$this->params['hash'] = $commit->GetTree()->GetHash();
			}
		}

		$tree = $this->project->GetTree($this->params['hash']);
		if (!$tree->GetCommit()) {
			$tree->SetCommit($commit);
		}
		$this->tpl->assign('tree', $tree);

		if (!isset($this->params['file']))
			$this->params['file'] = $tree->GetPath();
	}

}
