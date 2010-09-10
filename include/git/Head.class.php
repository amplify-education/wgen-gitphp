<?php
/**
 * GitPHP Head
 *
 * Represents a single head
 *
 * @author Christopher Han <xiphux@gmail.com>
 * @copyright Copyright (c) 2010 Christopher Han
 * @package GitPHP
 * @subpackage Git
 */

require_once(GITPHP_GITOBJECTDIR . 'Ref.class.php');

/**
 * Head class
 *
 * @package GitPHP
 * @subpackage Git
 */
class GitPHP_Head extends GitPHP_Ref
{

	/**
	 * __construct
	 *
	 * Instantiates head
	 *
	 * @access public
	 * @param mixed $project the project
	 * @param string $head head name
	 * @param string $headHash head hash
	 * @return mixed head object
	 * @throws Exception exception on invalid head or hash
	 */
	public function __construct($project, $head, $headHash = '')
	{
		parent::__construct($project, 'heads', $head, $headHash);
	}

	/**
	 * GetCommit
	 *
	 * Gets the commit for this head
	 *
	 * @access public
	 * @return mixed commit object for this tag
	 */
	public function GetCommit()
	{
		return $this->refObject;
	}
	
	/**
	 * CompareAge
	 *
	 * Compares two heads by age
	 *
	 * @access public
	 * @static
	 * @param mixed $a first head
	 * @param mixed $b second head
	 * @return integer comparison result
	 */
	public static function CompareAge($a, $b)
	{
		$aObj = $a->GetCommit();
		$bObj = $b->GetCommit();
		if ($aObj->GetAge() === $bObj->GetAge())
			return 0;
		return ($aObj->GetAge() < $bObj->GetAge() ? -1 : 1);
	}

}
