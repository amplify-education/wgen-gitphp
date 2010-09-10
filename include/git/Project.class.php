<?php
/**
 * GitPHP Project
 * 
 * Represents a single git project
 *
 * @author Christopher Han <xiphux@gmail.com>
 * @copyright Copyright (c) 2010 Christopher Han
 * @package GitPHP
 * @subpackage Git
 */

require_once(GITPHP_GITOBJECTDIR . 'GitExe.class.php');
require_once(GITPHP_GITOBJECTDIR . 'Commit.class.php');
require_once(GITPHP_GITOBJECTDIR . 'Head.class.php');
require_once(GITPHP_GITOBJECTDIR . 'Tag.class.php');

/**
 * Project class
 *
 * @package GitPHP
 * @subpackage Git
 */
class GitPHP_Project
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
	 * owner
	 *
	 * Stores the owner internally
	 *
	 * @access protected
	 */
	protected $owner = "";

	/**
	 * readOwner
	 *
	 * Stores whether the file owner has been read
	 *
	 * @access protected
	 */
	protected $readOwner = false;

	/**
	 * description
	 *
	 * Stores the description internally
	 *
	 * @access protected
	 */
	protected $description;

	/**
	 * readDescription
	 *
	 * Stores whether the description has been
	 * read from the file yet
	 *
	 * @access protected
	 */
	protected $readDescription = false;

	/**
	 * category
	 *
	 * Stores the category internally
	 *
	 * @access protected
	 */
	protected $category = '';

	/**
	 * head
	 *
	 * Stores the head hash internally
	 *
	 * @access protected
	 */
	protected $head;

	/**
	 * readHeadRef
	 *
	 * Stores whether the head ref has been read yet
	 *
	 * @access protected
	 */
	protected $readHeadRef = false;

	/**
	 * heads
	 *
	 * Stores the heads for the project
	 *
	 * @access protected
	 */
	protected $heads = array();

	/**
	 * readHeads
	 *
	 * Stores whether heads have been read yet
	 *
	 * @access protected
	 */
	protected $readHeads = false;

	/**
	 * tags
	 *
	 * Stores the tags for the project
	 *
	 * @access protected
	 */
	protected $tags = array();

	/**
	 * readTags
	 *
	 * Stores whether tags have been read yet
	 *
	 * @access protected
	 */
	protected $readTags = false;

	/**
	 * commitCache
	 *
	 * Caches fetched commit objects in case of
	 * repeated requests for the same object
	 *
	 * @access protected
	 */
	protected $commitCache = array();

	/**
	 * blobCache
	 *
	 * Caches blob objects in case of
	 * repeated requests
	 *
	 * @access protected
	 */
	protected $blobCache = array();

	/**
	 * treeCache
	 *
	 * Caches tree objects in case of repeated requests
	 *
	 * @access protected
	 */
	protected $treeCache = array();

	/**
	 * __construct
	 *
	 * Class constructor
	 *
	 * @access public
	 * @throws Exception if project is invalid or outside of projectroot
	 */
	public function __construct($project)
	{
		$this->SetProject($project);
	}

	/**
	 * SetProject
	 *
	 * Attempts to set the project
	 *
	 * @access private
	 * @throws Exception if project is invalid or outside of projectroot
	 */
	private function SetProject($project)
	{
		$projectRoot = GitPHP_Config::GetInstance()->GetValue('projectroot');
		$realProjectRoot = realpath($projectRoot);
		$path = $projectRoot . $project;
		$fullPath = realpath($path);

		if (!is_dir($fullPath)) {
			throw new Exception($project . ' is not a directory.');
		}

		if (!is_file($fullPath . '/HEAD')) {
			throw new Exception($project . ' is not a git repository.');
		}

		if (preg_match('/(^|\/)\.{0,2}(\/|$)/', $project)) {
			throw new Exception($project . ' is attempting directory traversal.');
		}

		$pathPiece = substr($fullPath, 0, strlen($realProjectRoot));

		if ((!is_link($path)) && (strcmp($pathPiece, $realProjectRoot) !== 0)) {
			throw new Exception('Project ' . $project . ' is outside of projectroot.');
		}

		$this->project = $project;

	}

	/**
	 * GetOwner
	 *
	 * Gets the project's owner
	 *
	 * @access public
	 * @return string project owner
	 */
	public function GetOwner()
	{
		if (empty($this->owner) && !$this->readOwner) {
			$uid = fileowner($this->GetPath());
			if ($uid > 0) {
				$data = posix_getpwuid($uid);
				if (isset($data['gecos']) && !empty($data['gecos'])) {
					$this->owner = $data['gecos'];
				} elseif (isset($data['name']) && !empty($data['name'])) {
					$this->owner = $data['name'];
				}
			}
			$this->readOwner = true;
		}
	
		return $this->owner;
	}

	/**
	 * SetOwner
	 *
	 * Sets the project's owner (from an external source)
	 *
	 * @access public
	 * @param string $owner the owner
	 */
	public function SetOwner($owner)
	{
		$this->owner = $owner;
	}

	/**
	 * GetProject
	 *
	 * Gets the project
	 *
	 * @access public
	 * @return string the project
	 */
	public function GetProject()
	{
		return $this->project;
	}

	/**
	 * GetSlug
	 *
	 * Gets the project as a filename/url friendly slug
	 *
	 * @access public
	 * @return string the slug
	 */
	public function GetSlug()
	{
		$from = array(
			'/',
			'.git'
		);
		$to = array(
			'-',
			''
		);
		return str_replace($from, $to, $this->project);
	}

	/**
	 * GetPath
	 *
	 * Gets the full project path
	 *
	 * @access public
	 * @return string project path
	 */
	public function GetPath()
	{
		return GitPHP_Config::GetInstance()->GetValue('projectroot') . $this->project;
	}

	/**
	 * GetDescription
	 *
	 * Gets the project description
	 *
	 * @access public
	 * @param $trim length to trim description to (0 for no trim)
	 * @return string project description
	 */
	public function GetDescription($trim = 0)
	{
		if (!$this->readDescription) {
			$this->description = file_get_contents($this->GetPath() . '/description');
		}
		
		if (($trim > 0) && (strlen($this->description) > $trim)) {
			return substr($this->description, 0, $trim) . '...';
		}

		return $this->description;
	}

	/**
	 * GetDaemonEnabled
	 *
	 * Returns whether gitdaemon is allowed for this project
	 *
	 * @access public
	 * @return boolean git-daemon-export-ok?
	 */
	public function GetDaemonEnabled()
	{
		return file_exists($this->GetPath() . '/git-daemon-export-ok');
	}

	/**
	 * GetCategory
	 *
	 * Gets the project's category
	 *
	 * @access public
	 * @return string category
	 */
	public function GetCategory()
	{
		return $this->category;
	}

	/**
	 * SetCategory
	 * 
	 * Sets the project's category
	 *
	 * @access public
	 * @param string $category category
	 */
	public function SetCategory($category)
	{
		$this->category = $category;
	}

	/**
	 * GetCloneUrl
	 *
	 * Gets the clone URL for this repository, if specified
	 *
	 * @access public
	 * @return string clone url
	 */
	public function GetCloneUrl()
	{
		$cloneurl = GitPHP_Config::GetInstance()->GetValue('cloneurl', '');
		if (!empty($cloneurl))
			$cloneurl .= $this->project;
		return $cloneurl;
	}

	/**
	 * GetPushUrl
	 *
	 * Gets the push URL for this repository, if specified
	 *
	 * @access public
	 * @return string push url
	 */
	public function GetPushUrl()
	{
		$pushurl = GitPHP_Config::GetInstance()->GetValue('pushurl', '');
		if (!empty($pushurl))
			$pushurl .= $this->project;
		return $pushurl;
	}

	/**
	 * GetHeadCommit
	 *
	 * Gets the head commit for this project
	 * Shortcut for getting the tip commit of the HEAD branch
	 *
	 * @access public
	 * @return mixed head commit
	 */
	public function GetHeadCommit()
	{
		if (!$this->readHeadRef)
			$this->ReadHeadCommit();

		return $this->GetCommit($this->head);
	}

	/**
	 * ReadHeadCommit
	 *
	 * Reads the head commit hash
	 *
	 * @access protected
	 */
	public function ReadHeadCommit()
	{
		$this->readHeadRef = true;

		$exe = new GitPHP_GitExe($this);
		$args = array();
		$args[] = '--verify';
		$args[] = 'HEAD';
		$this->head = trim($exe->Execute(GIT_REV_PARSE, $args));
	}

	/**
	 * GetCommit
	 *
	 * Get a commit for this project
	 *
	 * @param name the name of the commit to be retrieved
	 * @access public
	 */
	public function GetCommit($name)
	{
		if (empty($name))
			return null;

		$hash = $this->ParseName($name);

		if (!isset($this->commitCache[$hash]))
			$this->commitCache[$hash] = new GitPHP_Commit($this, $hash);

		return $this->commitCache[$hash];
	}

	/**
	 * ParseName
	 *
	 * Returns the hash for the supplied ref
	 *
	 * @access protected
	 */
	protected function ParseName($name)
	{
		$exe = new GitPHP_GitExe($this);
		$args = array();
		$args[] = '--verify';
		$args[] = $name;
		return trim($exe->Execute(GIT_REV_PARSE, $args));
	}

	/**
	 * GetObject
	 *
	 * Returns a commit object or a tag object named by this name.
	 *
	 * @param name the hash or ref to be loaded
	 * @access public
	 */
	public function GetObject($name)
	{
		$exe = new GitPHP_GitExe($this);
		$args = array();
		$args[] = '-t';
		$args[] = $name;
		$type = trim($exe->Execute(GIT_CAT_FILE, $args));

		switch ($type) {
		case 'commit':
			return $this->GetCommit($name);
		case 'tag':
			return $this->GetTag($name);
		case 'blob':
			return $this->GetBlob($name);
		default:
			throw new Exception("Unknown object type '$type' for object $name");
		}
	}

	/**
	 * CompareProject
	 *
	 * Compares two projects by project name
	 *
	 * @access public
	 * @static
	 * @param mixed $a first project
	 * @param mixed $b second project
	 * @return integer comparison result
	 */
	public static function CompareProject($a, $b)
	{
		$catCmp = strcmp($a->GetCategory(), $b->GetCategory());
		if ($catCmp !== 0)
			return $catCmp;

		return strcmp($a->GetProject(), $b->GetProject());
	}

	/**
	 * CompareDescription
	 *
	 * Compares two projects by description
	 *
	 * @access public
	 * @static
	 * @param mixed $a first project
	 * @param mixed $b second project
	 * @return integer comparison result
	 */
	public static function CompareDescription($a, $b)
	{
		$catCmp = strcmp($a->GetCategory(), $b->GetCategory());
		if ($catCmp !== 0)
			return $catCmp;

		return strcmp($a->GetDescription(), $b->GetDescription());
	}

	/**
	 * CompareOwner
	 *
	 * Compares two projects by owner
	 *
	 * @access public
	 * @static
	 * @param mixed $a first project
	 * @param mixed $b second project
	 * @return integer comparison result
	 */
	public static function CompareOwner($a, $b)
	{
		$catCmp = strcmp($a->GetCategory(), $b->GetCategory());
		if ($catCmp !== 0)
			return $catCmp;

		return strcmp($a->GetOwner(), $b->GetOwner());
	}

	/**
	 * CompareAge
	 *
	 * Compares two projects by age
	 *
	 * @access public
	 * @static
	 * @param mixed $a first project
	 * @param mixed $b second project
	 * @return integer comparison result
	 */
	public static function CompareAge($a, $b)
	{
		$catCmp = strcmp($a->GetCategory(), $b->GetCategory());
		if ($catCmp !== 0)
			return $catCmp;

		if ($a->GetHeadCommit()->GetAge() === $b->GetHeadCommit()->GetAge())
			return 0;
		return ($a->GetHeadCommit()->GetAge() < $b->GetHeadCommit()->GetAge() ? -1 : 1);
	}

	/**
	 * GetTagNames
	 *
	 * Gets list of tag names for this project
	 *
	 * @access public
	 * @return array array of tags
	 */
	public function GetTagRefs()
	{
		if (!$this->readTags)
			$this->ReadTagList();

		return $this->tags;
	}

	/**
	 * GetTag
	 *
	 * Gets the tag object for the specified hash
	 *
	 * @access public
	 * @param string $name a name for the tag object (either a hash or a ref)
	 * @return mixed tag object
	 */
	public function GetTag($name)
	{
		if (empty($name))
			return null;

		$hash = $this->ParseName($name);

		if (!isset($this->tagCache[$hash]))
			$this->tagCache[$hash] = new GitPHP_Tag($this, $hash);

		return $this->tagCache[$hash];
	}

	/**
	 * ReadTagList
	 *
	 * Reads tag list
	 *
	 * @access protected
	 */
	protected function ReadTagList()
	{
		$this->readTags = true;

		$exe = new GitPHP_GitExe($this);
		$args = array();
		$args[] = '--tags';
		$args[] = '--dereference';
		$ret = $exe->Execute(GIT_SHOW_REF, $args);
		unset($exe);

		$lines = explode("\n", $ret);

		foreach ($lines as $line) {
			if (preg_match('/^([0-9a-fA-F]{40}) refs\/tags\/([^^]+)(\^{})?$/', $line, $regs)) {
				try {
					$ref = new GitPHP_Ref($this, 'tags', $regs[2]);
					if (isset($regs[3]) && $regs[3] == "^{}") {
						$this->derefTags[$regs[1]][] = $ref;
						$this->tags[$regs[2]]->SetDereferencedObject($regs[1]);
					} else {
						$this->tags[$regs[2]] = $ref;
					}
				} catch (Exception $e) {
					error_log($e);
				}
			}
		}
	}

	/**
	 * GetTagsForHash
	 *
	 * Return all refs that point to the supplied hash
	 * 
	 * @access public
	 */
	public function GetTagsForHash($hash)
	{
		if (!$this->readTags)
			$this->ReadTagList();

		return $this->derefTags[$hash];
	}

	/**
	 * GetHeads
	 *
	 * Gets list of heads for this project
	 *
	 * @access public
	 * @return array array of heads
	 */
	public function GetHeads()
	{
		if (!$this->readHeads)
			$this->ReadHeadList();

		return $this->heads;
	}

	/**
	 * GetHead
	 *
	 * Gets a single head
	 *
	 * @access public
	 * @param string $head head to find
	 * @return mixed head object
	 */
	public function GetHead($head)
	{
		if (empty($head))
			return null;

		if (!$this->readHeads)
			$this->ReadHeadList();

		foreach ($this->heads as $h) {
			if ($h->GetName() === $head) {
				return $h;
			}
		}
		
		return null;
	}

	/**
	 * ReadHeadList
	 *
	 * Reads head list
	 *
	 * @access protected
	 */
	protected function ReadHeadList()
	{
		$this->readHeads = true;

		$exe = new GitPHP_GitExe($this);
		$args = array();
		$args[] = '--heads';
		$ret = $exe->Execute(GIT_SHOW_REF, $args);
		unset($exe);

		$lines = explode("\n", $ret);

		foreach ($lines as $line) {
			if (preg_match('/^([0-9a-fA-F]{40}) refs\/heads\/(.+)$/', $line, $regs)) {
				try {
					$this->heads[] = new GitPHP_Head($this, $regs[2], $regs[1]);
				} catch (Exception $e) {
					error_log($e);
				}
			}
		}

		usort($this->heads, array('GitPHP_Head', 'CompareAge'));

	}

	/**
	 * GetLogHash
	 *
	 * Gets log entries as an array of hashes
	 *
	 * @access public
	 * @param string $hash hash to start the log at
	 * @param integer $count number of entries to get
	 * @param integer $skip number of entries to skip
	 * @return array array of hashes
	 */
	public function GetLogHash($hash, $count = 50, $skip = 0)
	{
		return $this->RevList($hash, $count, $skip);
	}

	/**
	 * GetLog
	 *
	 * Gets log entries as an array of commit objects
	 *
	 * @access public
	 * @param string $hash hash to start the log at
	 * @param integer $count number of entries to get
	 * @param integer $skip number of entries to skip
	 * @return array array of commit objects
	 */
	public function GetLog($hash, $count = 50, $skip = 0)
	{
		$log = $this->GetLogHash($hash, $count, $skip);
		$len = count($log);
		for ($i = 0; $i < $len; ++$i) {
			$log[$i] = $this->GetCommit($log[$i]);
		}
		return $log;
	}

	/**
	 * GetBlob
	 *
	 * Gets a blob from this project
	 *
	 * @access public
	 * @param string $hash blob name (hash or ref)
	 */
	public function GetBlob($name)
	{
		if (empty($name))
			return null;

		$hash = $this->ParseName($name);

		if (!isset($this->blobCache[$hash]))
			$this->blobCache[$hash] = new GitPHP_Blob($this, $hash);

		return $this->blobCache[$hash];
	}

	/**
	 * GetTree
	 *
	 * Gets a tree from this project
	 *
	 * @access public
	 * @param string $hash tree name (hash or ref)
	 */
	public function GetTree($name)
	{
		if (empty($name))
			return null;

		$hash = $this->ParseName($name);

		if (!isset($this->treeCache[$hash]))
			$this->treeCache[$hash] = new GitPHP_Tree($this, $hash);

		return $this->treeCache[$hash];
	}

	/**
	 * SearchCommit
	 *
	 * Gets a list of commits with commit messages matching the given pattern
	 *
	 * @access public
	 * @param string $pattern search pattern
	 * @param string $hash hash to start searching from
	 * @param integer $count number of results to get
	 * @param integer $skip number of results to skip
	 * @return array array of matching commits
	 */
	public function SearchCommit($pattern, $hash = 'HEAD', $count = 50, $skip = 0)
	{
		if (empty($pattern))
			return;

		$args = array();
		$args[] = '--regexp-ignore-case';
		$args[] = '--grep=\'' . $pattern . '\'';

		$ret = $this->RevList($hash, $count, $skip, $args);
		$len = count($ret);

		for ($i = 0; $i < $len; ++$i) {
			$ret[$i] = $this->GetCommit($ret[$i]);
		}
		return $ret;
	}

	/**
	 * SearchAuthor
	 *
	 * Gets a list of commits with authors matching the given pattern
	 *
	 * @access public
	 * @param string $pattern search pattern
	 * @param string $hash hash to start searching from
	 * @param integer $count number of results to get
	 * @param integer $skip number of results to skip
	 * @return array array of matching commits
	 */
	public function SearchAuthor($pattern, $hash = 'HEAD', $count = 50, $skip = 0)
	{
		if (empty($pattern))
			return;

		$args = array();
		$args[] = '--regexp-ignore-case';
		$args[] = '--author=\'' . $pattern . '\'';

		$ret = $this->RevList($hash, $count, $skip, $args);
		$len = count($ret);

		for ($i = 0; $i < $len; ++$i) {
			$ret[$i] = $this->GetCommit($ret[$i]);
		}
		return $ret;
	}

	/**
	 * SearchCommitter
	 *
	 * Gets a list of commits with committers matching the given pattern
	 *
	 * @access public
	 * @param string $pattern search pattern
	 * @param string $hash hash to start searching from
	 * @param integer $count number of results to get
	 * @param integer $skip number of results to skip
	 * @return array array of matching commits
	 */
	public function SearchCommitter($pattern, $hash = 'HEAD', $count = 50, $skip = 0)
	{
		if (empty($pattern))
			return;

		$args = array();
		$args[] = '--regexp-ignore-case';
		$args[] = '--committer=\'' . $pattern . '\'';

		$ret = $this->RevList($hash, $count, $skip, $args);
		$len = count($ret);

		for ($i = 0; $i < $len; ++$i) {
			$ret[$i] = $this->GetCommit($ret[$i]);
		}
		return $ret;
	}

	/**
	 * RevList
	 *
	 * Common code for using rev-list command
	 *
	 * @access private
	 * @param string $hash hash to list from
	 * @param integer $count number of results to get
	 * @param integer $skip number of results to skip
	 * @param array $args args to give to rev-list
	 * @return array array of hashes
	 */
	private function RevList($hash, $count = 50, $skip = 0, $args = array())
	{
		if ($count < 1)
			return;

		$exe = new GitPHP_GitExe($this);

		$canSkip = true;
		
		if ($skip > 0)
			$canSkip = $exe->CanSkip();

		if ($canSkip) {
			$args[] = '--max-count=' . $count;
			if ($skip > 0) {
				$args[] = '--skip=' . $skip;
			}
		} else {
			$args[] = '--max-count=' . ($count + $skip);
		}

		$args[] = $hash;

		$revlist = explode("\n", $exe->Execute(GIT_REV_LIST, $args));

		if (!$revlist[count($revlist)-1]) {
			/* the last newline creates a null entry */
			array_splice($revlist, -1, 1);
		}

		if (($skip > 0) && (!$exe->CanSkip())) {
			return array_slice($revlist, $skip, $count);
		}

		return $revlist;
	}

}
