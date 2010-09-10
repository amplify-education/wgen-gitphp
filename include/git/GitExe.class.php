<?php
/**
 * GitPHP GitExe
 *
 * Class to wrap git executable
 *
 * @author Christopher Han <xiphux@gmail.com>
 * @copyright Copyright (c) 2010 Christopher Han
 * @package GitPHP
 * @subpackage Git
 */

define('GIT_CAT_FILE','cat-file');
define('GIT_DIFF_TREE','diff-tree');
define('GIT_LS_TREE','ls-tree');
define('GIT_REV_LIST','rev-list');
define('GIT_REV_PARSE','rev-parse');
define('GIT_SHOW_REF','show-ref');
define('GIT_ARCHIVE','archive');
define('GIT_GREP','grep');
define('GIT_BLAME','blame');
define('GIT_TAG','tag');

/**
 * Git Executable class
 *
 * @package GitPHP
 * @subpackage Git
 */
class GitPHP_GitExe
{
	/**
	 * project
	 *
	 * Stores the project internally
	 *
	 * @access protected
	 */
	protected $project;

	/**
	 * bin
	 *
	 * Stores the binary path internally
	 *
	 * @access protected
	 */
	protected $binary;

	/**
	 * __construct
	 *
	 * Constructor
	 *
	 * @param string $binary path to git binary
	 * @param mixed $project project to operate on
	 * @return mixed git executable class
	 */
	public function __construct($project = null)
	{
		$binary = GitPHP_Config::GetInstance()->GetValue('gitbin');
		if (empty($binary)) {
			// try to pick a reasonable default
			if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
				$this->binary = 'C:\\Progra~1\\Git\\bin\\git.exe';
			} else {
				$this->binary = 'git';
			}
		} else {
			$this->binary = $binary;
		}

		$this->SetProject($project);
	}

	/**
	 * SetProject
	 *
	 * Sets the project for this executable
	 *
	 * @param mixed $project project to set
	 */
	public function SetProject($project = null)
	{
		$this->project = $project;
	}

	/**
	 * Execute
	 *
	 * Executes a command
	 *
	 * @param string $command the command to execute
	 * @param array $args arguments
	 * @return string result of command
	 */
	public function Execute($command, $args)
	{
		$gitDir = '';
		if ($this->project) {
			$gitDir = '--git-dir=' . $this->project->GetPath();
		}
		
		$fullCommand = $this->binary . ' ' . $gitDir . ' ' . $command . ' ' . implode(' ', $args);

		GitPHP_Log::GetInstance()->Log('Begin executing "' . $fullCommand . '"');

		$ret = shell_exec($fullCommand);

		GitPHP_Log::GetInstance()->Log('Finish executing "' . $fullCommand . '"');

		return $ret;
	}

	/**
	 * GetBinary
	 *
	 * Gets the binary for this executable
	 *
	 * @return string binary
	 * @access public
	 */
	public function GetBinary()
	{
		return $this->binary;
	}

	/**
	 * GetVersion
	 *
	 * Gets the version of the git binary
	 *
	 * @return string version
	 * @access public
	 */
	public function GetVersion()
	{
		$versionCommand = $this->binary . ' --version';
		$ret = trim(shell_exec($versionCommand));
		if (preg_match('/^git version ([0-9\.]+)$/i', $ret, $regs)) {
			return $regs[1];
		}
		return '';
	}

	/**
	 * CanSkip
	 *
	 * Tests if this version of git can skip through the revision list
	 *
	 * @access public
	 * @return boolean true if we can skip
	 */
	public function CanSkip()
	{
		$version = $this->GetVersion();
		if (!empty($version)) {
			$splitver = explode('.', $version);

			/* Skip only appears in git >= 1.5.0 */
			if (($splitver[0] < 1) || (($splitver[0] == 1) && ($splitver[1] < 5))) {
				return false;
			}
		}

		return true;
	}

	/**
	 * CanShowSizeInTree
	 *
	 * Tests if this version of git can show the size of a blob when listing a tree
	 *
	 * @access public
	 * @return true if we can show sizes
	 */
	public function CanShowSizeInTree()
	{
		$version = $this->GetVersion();
		if (!empty($version)) {
			$splitver = explode('.', $version);

			/*
			 * ls-tree -l only appears in git 1.5.3
			 * (technically 1.5.3-rc0 but i'm not getting that fancy)
			 */
			if (($splitver[0] < 1) || (($splitver[0] == 1) && ($splitver[1] < 5)) || (($splitver[0] == 1) && ($splitver[1] == 5) && ($splitver[2] < 3))) {
				return false;
			}
		}

		return true;

	}

}
