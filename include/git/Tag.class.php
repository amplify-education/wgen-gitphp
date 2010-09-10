<?php
/**
 * GitPHP Tag
 *
 * Represents a single tag object
 *
 * @author Christopher Han <xiphux@gmail.com>
 * @copyright Copyright (c) 2010 Christopher Han
 * @package GitPHP
 * @subpackage Git
 */

require_once(GITPHP_GITOBJECTDIR . 'GitExe.class.php');
require_once(GITPHP_GITOBJECTDIR . 'GitObject.class.php');

/**
 * Class for annotated git tags (lightweight tags are represented by GitPHP_Ref objects)
 *
 * @package GitPHP
 * @subpackage Git
 */
class GitPHP_Tag extends GitPHP_GitObject
{

	/**
	 * dataRead
	 *
	 * Indicates whether data for this tag has been read
	 *
	 * @access protected
	 */
	protected $dataRead = false;

	/**
	 * object
	 *
	 * Stores the tagged object internally
	 *
	 * @access protected
	 */
	protected $object;

	/**
	 * objectHash
	 *
	 * Stores the hash of the object before the object hash been loaded
	 *
	 * @access protected
	 */
	protected $objectHash;

	/**
	 * tagName
	 *
	 * Stores the name of this tag internally
	 *
	 * @access protected
	 */
	protected $tagName;

	/**
	 * tagger
	 *
	 * Stores the tagger internally
	 *
	 * @access protected
	 */
	protected $tagger;

	/**
	 * taggerEpoch
	 *
	 * Stores the tagger epoch internally
	 *
	 * @access protected
	 */
	protected $taggerEpoch;

	/**
	 * taggerTimezone
	 *
	 * Stores the tagger timezone internally
	 *
	 * @access protected
	 */
	protected $taggerTimezone;

	/**
	 * comment
	 *
	 * Stores the tag comment internally
	 *
	 * @access protected
	 */
	protected $comment = array();

	/**
	 * __construct
	 *
	 * Instantiates tag
	 *
	 * @access public
	 * @param mixed $project the project
	 * @param string $tagHash tag hash
	 * @return mixed tag object
	 * @throws Exception exception on invalid tag or hash
	 */
	public function __construct($project, $tagHash)
	{
		parent::__construct($project, $tagHash, 'tag');
	}

	/**
	 * GetObject
	 *
	 * Gets the object this tag points to
	 *
	 * @access public
	 * @return mixed object for this tag
	 */
	public function GetObject()
	{
		if (!$this->dataRead)
			$this->ReadData();

		if (!$this->object)
			$this->object = $this->project->GetObject($this->objectHash);

		return $this->object;
	}

	/**
	 * GetTagger
	 *
	 * Gets the tagger
	 *
	 * @access public
	 * @return string tagger
	 */
	public function GetTagger()
	{
		if (!$this->dataRead)
			$this->ReadData();

		return $this->tagger;
	}

	/**
	 * GetTaggerEpoch
	 *
	 * Gets the tagger epoch
	 *
	 * @access public
	 * @return string tagger epoch
	 */
	public function GetTaggerEpoch()
	{
		if (!$this->dataRead)
			$this->ReadData();

		return $this->taggerEpoch;
	}

	/**
	 * GetTaggerLocalEpoch
	 *
	 * Gets the tagger local epoch
	 *
	 * @access public
	 * @return string tagger local epoch
	 */
	public function GetTaggerLocalEpoch()
	{
		$epoch = $this->GetTaggerEpoch();
		$tz = $this->GetTaggerTimezone();
		if (preg_match('/^([+\-][0-9][0-9])([0-9][0-9])$/', $tz, $regs)) {
			$local = $epoch + ((((int)$regs[1]) + ($regs[2]/60)) * 3600);
			return $local;
		}
		return $epoch;
	}

	/**
	 * GetTaggerTimezone
	 *
	 * Gets the tagger timezone
	 *
	 * @access public
	 * @return string tagger timezone
	 */
	public function GetTaggerTimezone()
	{
		if (!$this->dataRead)
			$this->ReadData();

		return $this->taggerTimezone;
	}

	/**
	 * GetComment
	 *
	 * Gets the tag comment
	 *
	 * @access public
	 * @return array comment lines
	 */
	public function GetComment()
	{
		if (!$this->dataRead)
			$this->ReadData();

		return $this->comment;
	}

	/**
	 * ReadData
	 *
	 * Reads the tag data
	 *
	 * @access protected
	 */
	protected function ReadData()
	{
		$this->dataRead = true;

		$exe = new GitPHP_GitExe($this->project);

		/* get data from tag object */
		$args = array();
		$args[] = 'tag';
		$args[] = $this->GetHash();
		$ret = $exe->Execute(GIT_CAT_FILE, $args);
		unset($exe);

		$lines = explode("\n", $ret);

		if (!isset($lines[0]))
			return;

		$readInitialData = false;
		foreach ($lines as $i => $line) {
			if (!$readInitialData) {
				if (preg_match('/^object ([0-9a-fA-F]{40})$/', $line, $regs)) {
					$this->objectHash = $regs[1];
					continue;
				} else if (preg_match('/^type (.+)$/', $line, $regs)) {
					// Ignore this, because it's determined by GetType of the object
					continue;
				} else if (preg_match('/^tag (.+)$/', $line, $regs)) {
					$this->tagName = $regs[1];
					continue;
				} else if (preg_match('/^tagger (.*) ([0-9]+) (.*)$/', $line, $regs)) {
					$this->tagger = $regs[1];
					$this->taggerEpoch = $regs[2];
					$this->taggerTimezone = $regs[3];
					continue;
				}
			}

			$trimmed = trim($line);

			if ((strlen($trimmed) > 0) || ($readInitialData === true)) {
				$this->comment[] = $line;
			}
			$readInitialData = true;

		}

	}

	/**
	 * CompareAge
	 *
	 * Compares two tags by age
	 *
	 * @access public
	 * @static
	 * @param mixed $a first tag
	 * @param mixed $b second tag
	 * @return integer comparison result
	 */
	public static function CompareAge($a, $b)
	{
		$aObj = $a->GetObject();
		$bObj = $b->GetObject();
		if (($aObj instanceof GitPHP_Commit) && ($bObj instanceof GitPHP_Commit)) {
			if ($aObj->GetAge() === $bObj->GetAge())
				return 0;
			return ($aObj->GetAge() < $bObj->GetAge() ? -1 : 1);
		}

		if ($aObj instanceof GitPHP_Commit)
			return 1;

		if ($bObj instanceof GitPHP_Commit)
			return -1;

		return strcmp($a->GetName(), $b->GetName());
	}

	/**
	 * GetName
	 *
	 * Return the name of the tag
	 *
	 * @access public 
	 */
	public function GetName()
	{
		return $this->tagName;
	}
}
