<?php
/**
 * GitPHP Ref
 *
 * Base class for ref objects
 *
 * @author Christopher Han <xiphux@gmail.com>
 * @copyright Copyright (c) 2010 Christopher Han
 * @package GitPHP
 * @subpackage Git
 */

require_once(GITPHP_GITOBJECTDIR . 'GitObject.class.php');

/**
 * Git Ref class
 *
 * @package GitPHP
 * @subpackage Git
 */
class GitPHP_Ref
{
	
	/**
	 * refName
	 *
	 * Stores the ref name
	 *
	 * @access protected
	 */
	protected $refName;

	/**
	 * refDir
	 *
	 * Stores the ref directory
	 *
	 * @access protected
	 */
	protected $refDir;

	/**
	 * refObject
	 *
	 * Stores the object referenced by this ref internall
	 *
	 * @access protected
	 */
	protected $refObject;

	/**
	 * derefObject
	 *
	 * Stores the object referenced by this ref, following tag links
	 *
	 * @access protected
	 */
	protected $derefObject;

	/**
	 * __construct
	 *
	 * Instantiates ref
	 *
	 * @access public
	 * @param mixed $project the project
	 * @param string $refDir the ref directory
	 * @param string $refName the ref name
	 * @throws Exception if not a valid ref
	 * @return mixed git ref
	 */
	public function __construct($project, $refDir, $refName)
	{
		$this->project = $project;
		$this->refDir = $refDir;
		$this->refName = $refName;
		$this->refObject = $project->GetObject($this->FindHash());
	}

	/**
	 * FindHash
	 *
	 * Looks up the hash for the ref
	 *
	 * @access protected
	 * @throws Exception if hash is not found
	 */
	protected function FindHash()
	{
		$exe = new GitPHP_GitExe($this->project);
		$args = array();
		$args[] = '--hash';
		$args[] = '--verify';
		$args[] = $this->GetRefPath();
		$hash = trim($exe->Execute(GIT_SHOW_REF, $args));

		if (empty($hash))
			throw new Exception('Invalid ref ' . $this->GetRefPath());

		return $hash;
	}

	/**
	 * GetName()
	 *
	 * Gets the ref name
	 *
	 * @access public
	 * @return string ref name
	 */
	public function GetName()
	{
		return $this->refName;
	}

	/**
	 * GetDirectory
	 *
	 * Gets the ref directory
	 *
	 * @access public
	 * @return string ref directory
	 */
	public function GetDirectory()
	{
		return $this->refDir;
	}

	/**
	 * GetObject
	 *
	 * Returns the object referenced by this ref
	 *
	 * @access public
	 * @return GitPHP_GitObject object referenced by this ref
	 */
	public function GetObject()
	{
		return $this->refObject;
	}
	
	/**
	 * SetDereferencedObject
	 *
	 * Set the object referenced by this ref, after resolving all tag indirections
	 *
	 * @access public
	 */
	public function SetDereferencedObject($hash)
	{
		$this->derefObject = $this->project->getObject($hash);
	}

	/**
	 * GetDereferencedObject
	 *
	 * Returns the object referenced by this ref, after resolving all tag indirections
	 *
	 * @access public
	 * @return GitPHP_GitObject object referenced by this ref
	 */
	public function GetDereferencedObject()
	{
		if (isset($this->derefObject))
			return $this->derefObject;
		else
			return $this->refObject;
	}

	/**
	 * GetRefPath
	 *
	 * Gets the path to the ref within the project
	 *
	 * @access public
	 * @return string ref path
	 */
	public function GetRefPath()
	{
		return 'refs/' . $this->refDir . '/' . $this->refName;
	}

	/**
	 * GetFullPath
	 *
	 * Gets the path to the ref including the project path
	 *
	 * @access public
	 * @return string full ref path
	 */
	public function GetFullPath()
	{
		return $this->project->GetPath() . '/' . $this->GetRefPath();
	}

}
