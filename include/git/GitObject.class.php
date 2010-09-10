<?php
/**
 * GitPHP GitObject
 *
 * Base class for all hash objects in a git repository
 *
 * @author Christopher Han <xiphux@gmail.com>
 * @copyright Copyright (c) 2010 Christopher Han
 * @package GitPHP
 * @subpackage Git
 */

/**
 * Git Object class
 *
 * @abstract
 * @package GitPHP
 * @subpackage Git
 */
abstract class GitPHP_GitObject
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
	 * hash
	 *
	 * Stores the hash of the object internally
	 *
	 * @access protected
	 */
	protected $hash;

	/**
	 * __construct
	 *
	 * Instantiates object
	 *
	 * @access public
	 * @param mixed $project the project
	 * @param string $hash object hash
	 * @return mixed git object
	 * @throws Exception exception on invalid hash
	 */
	public function __construct($project, $hash, $type = null)
	{
		$this->project = $project;
		$this->type = $type;
		$this->SetHash($hash);
	}

	/**
	 * GetProject
	 *
	 * Gets the project
	 *
	 * @access public
	 * @return mixed project
	 */
	public function GetProject()
	{
		return $this->project;
	}

	/**
	 * GetHash
	 *
	 * Gets the hash
	 *
	 * @access public
	 * @return string object hash
	 */
	public function GetHash()
	{
		return $this->hash;
	}

	/**
	 * SetHash
	 *
	 * Attempts to set the hash of this object
	 *
	 * @param string $hash the hash to set
	 * @throws Exception on invalid hash
	 * @access protected
	 */
	protected function SetHash($hash)
	{
		if (!(preg_match('/[0-9a-f]{40}/i', $hash))) {
			throw new Exception('Invalid hash ' . $hash);
		}
		$this->hash = $hash;
	}

	/**
	 *
	 * Returns the type of this git object, as reported by git cat-file -t
	 *
	 * @access public
	 */
	public function GetType()
	{
		if (!isset($this->type))
		{
			$exe = new GitPHP_GitExe($this->project);
			$args = array();
			$args[] = '-t';
			$args[] = $this->hash;
			$this->type = trim($exe->Execute(GIT_CAT_FILE, $args));
		}
		return $this->type;
	}
}
