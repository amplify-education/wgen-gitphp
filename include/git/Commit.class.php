<?php
/**
 * GitPHP Commit
 *
 * Represents a single commit
 *
 * @author Christopher Han <xiphux@gmail.com>
 * @copyright Copyright (c) 2010 Christopher Han
 * @package GitPHP
 * @subpackage Git
 */

require_once(GITPHP_GITOBJECTDIR . 'GitExe.class.php');
require_once(GITPHP_GITOBJECTDIR . 'GitObject.class.php');
require_once(GITPHP_GITOBJECTDIR . 'Tree.class.php');
require_once(GITPHP_GITOBJECTDIR . 'TreeDiff.class.php');

/**
 * Commit class
 *
 * @package GitPHP
 * @subpackage Git
 */
class GitPHP_Commit extends GitPHP_GitObject
{

	/**
	 * dataRead
	 *
	 * Indicates whether data for this commit has been read
	 *
	 * @access protected
	 */
	protected $dataRead = false;

	/**
	 * parents
	 *
	 * Array of parent commits
	 *
	 * @access protected
	 */
	protected $parents = array();

	/**
	 * tree
	 *
	 * Tree object for this commit
	 *
	 * @access protected
	 */
	protected $tree;

	/**
	 * author
	 *
	 * Author for this commit
	 *
	 * @access protected
	 */
	protected $author;

	/**
	 * authorEpoch
	 *
	 * Author's epoch
	 *
	 * @access protected
	 */
	protected $authorEpoch;

	/**
	 * authorTimezone
	 *
	 * Author's timezone
	 *
	 * @access protected
	 */
	protected $authorTimezone;

	/**
	 * committer
	 *
	 * Committer for this commit
	 *
	 * @access protected
	 */
	protected $committer;

	/**
	 * committerEpoch
	 *
	 * Committer's epoch
	 *
	 * @access protected
	 */
	protected $committerEpoch;

	/**
	 * committerTimezone
	 *
	 * Committer's timezone
	 *
	 * @access protected
	 */
	protected $committerTimezone;

	/**
	 * title
	 *
	 * Stores the commit title
	 *
	 * @access protected
	 */
	protected $title;

	/**
	 * comment
	 *
	 * Stores the commit comment
	 *
	 * @access protected
	 */
	protected $comment = array();

	/**
	 * treeHashes
	 *
	 * Stores tree name to hash mappings
	 *
	 * @access protected
	 */
	protected $treeHashes = array();

	/**
	 * blobHashes
	 *
	 * Stores blob name to hash mappings
	 *
	 * @access protected
	 */
	protected $blobHashes = array();

	/**
	 * readTree
	 *
	 * Stores whether tree filenames have been read
	 *
	 * @access protected
	 */
	protected $readTree = false;

	/**
	 * blobPaths
	 *
	 * Stores blob hash to path mappings
	 *
	 * @access protected
	 */
	protected $blobPaths = array();

	/**
	 * treePaths
	 *
	 * Stores tree hash to path mappings
	 *
	 * @access protected
	 */
	protected $treePaths = array();

	/**
	 * hashPathsRead
	 *
	 * Stores whether hash paths have been read
	 *
	 * @access protected
	 */
	protected $hashPathsRead = false;

	/**
	 * containingTag
	 *
	 * Stores the tag containing the changes in this commit
	 *
	 * @access protected
	 */
	protected $containingTag = null;

	/**
	 * containingTagRead
	 *
	 * Stores whether the containing tag has been looked up
	 *
	 * @access public
	 */
	protected $containingTagRead = false;

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
	public function __construct($project, $hash)
	{
		parent::__construct($project, $hash);
	}

	/**
	 * GetParent
	 *
	 * Gets the main parent of this commit
	 *
	 * @access public
	 * @return mixed commit object for parent
	 */
	public function GetParent()
	{
		if (!$this->dataRead)
			$this->ReadData();

		if (isset($this->parents[0]))
			return $this->parents[0];
		return null;
	}

	/**
	 * GetParents
	 *
	 * Gets an array of parent objects for this commit
	 *
	 * @access public
	 * @return mixed array of commit objects
	 */
	public function GetParents()
	{
		if (!$this->dataRead)
			$this->ReadData();

		return $this->parents;
	}

	/**
	 * GetTree
	 *
	 * Gets the tree for this commit
	 *
	 * @access public
	 * @return mixed tree object
	 */
	public function GetTree()
	{
		if (!$this->dataRead)
			$this->ReadData();

		return $this->tree;
	}

	/**
	 * GetAuthor
	 *
	 * Gets the author for this commit
	 *
	 * @access public
	 * @return string author
	 */
	public function GetAuthor()
	{
		if (!$this->dataRead)
			$this->ReadData();

		return $this->author;
	}

	/**
	 * GetAuthorName
	 *
	 * Gets the author's name only
	 *
	 * @access public
	 * @return string author name
	 */
	public function GetAuthorName()
	{
		if (!$this->dataRead)
			$this->ReadData();

		return preg_replace('/ <.*/', '', $this->author);
	}

	/**
	 * GetAuthorEpoch
	 *
	 * Gets the author's epoch
	 *
	 * @access public
	 * @return string author epoch
	 */
	public function GetAuthorEpoch()
	{
		if (!$this->dataRead)
			$this->ReadData();

		return $this->authorEpoch;
	}

	/**
	 * GetAuthorLocalEpoch
	 *
	 * Gets the author's local epoch
	 *
	 * @access public
	 * @return string author local epoch
	 */
	public function GetAuthorLocalEpoch()
	{
		$epoch = $this->GetAuthorEpoch();
		$tz = $this->GetAuthorTimezone();
		if (preg_match('/^([+\-][0-9][0-9])([0-9][0-9])$/', $tz, $regs)) {
			$local = $epoch + ((((int)$regs[1]) + ($regs[2]/60)) * 3600);
			return $local;
		}
		return $epoch;
	}

	/**
	 * GetAuthorTimezone
	 *
	 * Gets the author's timezone
	 *
	 * @access public
	 * @return string author timezone
	 */
	public function GetAuthorTimezone()
	{
		if (!$this->dataRead)
			$this->ReadData();

		return $this->authorTimezone;
	}

	/**
	 * GetCommitter
	 *
	 * Gets the author for this commit
	 *
	 * @access public
	 * @return string author
	 */
	public function GetCommitter()
	{
		if (!$this->dataRead)
			$this->ReadData();

		return $this->committer;
	}

	/**
	 * GetCommitterName
	 *
	 * Gets the author's name only
	 *
	 * @access public
	 * @return string author name
	 */
	public function GetCommitterName()
	{
		if (!$this->dataRead)
			$this->ReadData();

		return preg_replace('/ <.*/', '', $this->committer);
	}

	/**
	 * GetCommitterEpoch
	 *
	 * Gets the committer's epoch
	 *
	 * @access public
	 * @return string committer epoch
	 */
	public function GetCommitterEpoch()
	{
		if (!$this->dataRead)
			$this->ReadData();

		return $this->committerEpoch;
	}

	/**
	 * GetCommitterLocalEpoch
	 *
	 * Gets the committer's local epoch
	 *
	 * @access public
	 * @return string committer local epoch
	 */
	public function GetCommitterLocalEpoch()
	{
		$epoch = $this->GetCommitterEpoch();
		$tz = $this->GetCommitterTimezone();
		if (preg_match('/^([+\-][0-9][0-9])([0-9][0-9])$/', $tz, $regs)) {
			$local = $epoch + ((((int)$regs[1]) + ($regs[2]/60)) * 3600);
			return $local;
		}
		return $epoch;
	}

	/**
	 * GetCommitterTimezone
	 *
	 * Gets the author's timezone
	 *
	 * @access public
	 * @return string author timezone
	 */
	public function GetCommitterTimezone()
	{
		if (!$this->dataRead)
			$this->ReadData();

		return $this->committerTimezone;
	}

	/**
	 * GetTitle
	 *
	 * Gets the commit title
	 *
	 * @access public
	 * @param integer $trim length to trim to (0 for no trim)
	 * @return string title
	 */
	public function GetTitle($trim = 0)
	{
		if (!$this->dataRead)
			$this->ReadData();
		
		if (($trim > 0) && (strlen($this->title) > $trim)) {
			return substr($this->title, 0, $trim) . '...';
		}

		return $this->title;
	}

	/**
	 * GetComment
	 *
	 * Gets the lines of comment
	 *
	 * @access public
	 * @return array lines of comment
	 */
	public function GetComment()
	{
		if (!$this->dataRead)
			$this->ReadData();

		return $this->comment;
	}

	/**
	 * SearchComment
	 *
	 * Gets the lines of the comment matching the given pattern
	 *
	 * @access public
	 * @param string $pattern pattern to find
	 * @return array matching lines of comment
	 */
	public function SearchComment($pattern)
	{
		if (empty($pattern))
			return $this->GetComment();

		if (!$this->dataRead)
			$this->ReadData();

		return preg_grep('/' . $pattern . '/i', $this->comment);
	}

	/**
	 * GetAge
	 *
	 * Gets the age of the commit
	 *
	 * @access public
	 * @return string age
	 */
	public function GetAge()
	{
		if (!$this->dataRead)
			$this->ReadData();

		if (!empty($this->committerEpoch))
			return time() - $this->committerEpoch;
		
		return '';
	}

	/**
	 * ReadData
	 *
	 * Read the data for the commit
	 *
	 * @access protected
	 */
	protected function ReadData()
	{
		$this->dataRead = true;

		/* get data from git_rev_list */
		$exe = new GitPHP_GitExe($this->project);
		$args = array();
		$args[] = '--header';
		$args[] = '--parents';
		$args[] = '--max-count=1';
		$args[] = $this->hash;
		$ret = $exe->Execute(GIT_REV_LIST, $args);
		unset($exe);

		$lines = explode("\n", $ret);

		if (!isset($lines[0]))
			return;

		/* In case we returned something unexpected */
		$tok = strtok($lines[0], ' ');
		if ($tok != $this->hash)
			return;

		/* Read all parents */
		$tok = strtok(' ');
		while ($tok !== false) {
			try {
				$this->parents[] = new GitPHP_Commit($this->project, $tok);
			} catch (Exception $e) {
			}
			$tok = strtok(' ');
		}

		foreach ($lines as $i => $line) {
			if (preg_match('/^tree ([0-9a-fA-F]{40})$/', $line, $regs)) {
				/* Tree */
				try {
					$tree = $this->project->GetTree($regs[1]);
					if ($tree) {
						$tree->SetCommit($this);
						$this->tree = $tree;
					}
				} catch (Exception $e) {
				}
			} else if (preg_match('/^author (.*) ([0-9]+) (.*)$/', $line, $regs)) {
				/* author data */
				$this->author = $regs[1];
				$this->authorEpoch = $regs[2];
				$this->authorTimezone = $regs[3];
			} else if (preg_match('/^committer (.*) ([0-9]+) (.*)$/', $line, $regs)) {
				/* committer data */
				$this->committer = $regs[1];
				$this->committerEpoch = $regs[2];
				$this->committerTimezone = $regs[3];
			} else {
				/* commit comment */
				if (!(preg_match('/^[0-9a-fA-F]{40}/', $line) || preg_match('/^parent [0-9a-fA-F]{40}/', $line))) {
					$trimmed = trim($line);
					if (empty($this->title) && (strlen($trimmed) > 0))
						$this->title = $trimmed;
					if (!empty($this->title)) {
						if ((strlen($trimmed) > 0) || ($i < (count($lines)-1)))
							$this->comment[] = $trimmed;
					}
				}
			}
		}

	}

	/**
	 * GetHeads
	 *
	 * Gets heads that point to this commit
	 * 
	 * @access public
	 * @return array array of heads
	 */
	public function GetHeads()
	{
		$heads = array();

		$projectHeads = $this->project->GetHeads();

		foreach ($projectHeads as $head) {
			if ($head->GetCommit()->GetHash() === $this->hash) {
				$heads[] = $head;
			}
		}

		return $heads;
	}

	/**
	 * GetTags
	 *
	 * Gets tags that point to this commit
	 *
	 * @access public
	 * @return array array of tags
	 */
	public function GetTags()
	{
		$tags = array();

		$projectTags = $this->project->GetTags();

		foreach ($projectTags as $tag) {
			if (is_object($tag->GetObject()) && $tag->GetObject()->GetHash() === $this->hash) {
				$tags[] = $tag;
			}
		}

		return $tags;
	}

	/**
	 * GetContainingTag
	 *
	 * Gets the tag that contains the changes in this commit
	 *
	 * @access public
	 * @return tag object
	 */
	public function GetContainingTag()
	{
		if (!$this->containingTagRead)
			$this->ReadContainingTag();

		return $this->containingTag;
	}

	/**
	 * ReadContainingTag
	 *
	 * Looks up the tag that contains the changes in this commit
	 *
	 * @access private
	 */
	public function ReadContainingTag()
	{
		$this->containingTagRead = true;

		$tags = array();

		$projectTags = $this->project->GetTags();

		foreach ($projectTags as $pTag) {
			$tags[$pTag->GetObject()->GetHash()] = $pTag;
		}

		$exe = new GitPHP_GitExe($this->project);
		$args = array();
		$args[] = 'HEAD';
		$revs = explode("\n", $exe->Execute(GIT_REV_LIST, $args));

		$this->containingTag = null;
		foreach ($revs as $rev) {
			if (isset($tags[$rev]))
				$this->containingTag = $tags[$rev];
			if ($rev == $this->hash)
				break;
		}
	}

	/**
	 * GetArchive
	 *
	 * Gets an archive of this commit
	 *
	 * @access public
	 * @return string the archive data
	 * @param format the archive format
	 * @param path The Path to archive
	 */
	public function GetArchive($format, $path = null, $prefix = null)
	{

		if (is_null($prefix))
			$prefix = $this->project->GetSlug() . "/";
		
		$exe = new GitPHP_GitExe($this->project);
		$args = array();
		if ($format == GITPHP_COMPRESS_ZIP)
			$args[] = '--format=zip';
		else
			$args[] = '--format=tar';
		$args[] = '--prefix=' . $prefix;
		$args[] = $this->hash;
		if (! is_null($path)) $args[] = $path;

		$data = $exe->Execute(GIT_ARCHIVE, $args);
		unset($exe);

		if (($format == GITPHP_COMPRESS_BZ2) && function_exists('bzcompress')) {
			return bzcompress($data, GitPHP_Config::GetInstance()->GetValue('compresslevel', 4));
		} else if (($format == GITPHP_COMPRESS_GZ) && function_exists('gzencode')) {
			return gzencode($data, GitPHP_Config::GetInstance()->GetValue('compresslevel', -1));
		}

		return $data;
	}

	/**
	 * DiffToParent
	 *
	 * Diffs this commit with its immediate parent
	 *
	 * @access public
	 * @return mixed Tree diff
	 */
	public function DiffToParent()
	{
		return new GitPHP_TreeDiff($this->project, $this->hash);
	}

	/**
	 * PathToHash
	 *
	 * Given a filepath, get its hash
	 *
	 * @access public
	 * @param string $path path
	 * @return string hash
	 */
	public function PathToHash($path)
	{
		if (empty($path))
			return '';

		if (!$this->hashPathsRead)
			$this->ReadHashPaths();

		foreach ($this->blobPaths as $h => $p) {
			if ($path == $p) {
				return $h;
			}
		}

		foreach ($this->treePaths as $h => $p) {
			if ($path == $p) {
				return $h;
			}
		}

		return '';
	}

	/**
	 * HashToPath
	 *
	 * Given a blob/tree hash, get its path
	 *
	 * @access public
	 * @param string $hash hash
	 * @return string path
	 */
	public function HashToPath($hash)
	{
		if (empty($hash))
			return '';

		if (!$this->hashPathsRead)
			$this->ReadHashPaths();

		if (isset($this->blobPaths[$hash]))
			return $this->blobPaths[$hash];

		if (isset($this->treePaths[$hash]))
			return $this->treePaths[$hash];

		return '';
	}

	/**
	 * ReadHashPaths
	 *
	 * Read hash to path mappings
	 *
	 * @access private
	 */
	private function ReadHashPaths()
	{
		$this->hashPathsRead = true;

		$exe = new GitPHP_GitExe($this->project);

		$args = array();
		$args[] = '--full-name';
		$args[] = '-r';
		$args[] = '-t';
		$args[] = $this->hash;

		$lines = explode("\n", $exe->Execute(GIT_LS_TREE, $args));

		foreach ($lines as $line) {
			if (preg_match("/^([0-9]+) (.+) ([0-9a-fA-F]{40})\t(.+)$/", $line, $regs)) {
				switch ($regs[2]) {
					case 'tree':
						$this->treePaths[$regs[3]] = trim($regs[4]);
						break;
					case 'blob';
						$this->blobPaths[$regs[3]] = trim($regs[4]);
						break;
				}
			}
		}
	}
	
	/**
	 * SearchFilenames
	 *
	 * Returns array of objects matching pattern
	 *
	 * @access public
	 * @param string $pattern pattern to find
	 * @return array array of objects
	 */
	public function SearchFilenames($pattern)
	{
		if (empty($pattern))
			return;

		if (!$this->hashPathsRead)
			$this->ReadHashPaths();

		$results = array();

		foreach ($this->treePaths as $hash => $path) {
			if (preg_match('/' . $pattern . '/i', $path)) {
				$obj = $this->project->GetTree($hash);
				$obj->SetCommit($this);
				$results[$path] = $obj;
			}
		}

		foreach ($this->blobPaths as $hash => $path) {
			if (preg_match('/' . $pattern . '/i', $path)) {
				$obj = $this->project->GetBlob($hash);
				$obj->SetCommit($this);
				$results[$path] = $obj;
			}
		}

		ksort($results);

		return $results;
	}

	/**
	 * SearchFileContents
	 *
	 * Searches for a pattern in file contents
	 *
	 * @access public
	 * @param string $pattern pattern to search for
	 * @return array multidimensional array of results
	 */
	public function SearchFileContents($pattern)
	{
		if (empty($pattern))
			return;

		$exe = new GitPHP_GitExe($this->project);

		$args = array();
		$args[] = '-I';
		$args[] = '--full-name';
		$args[] = '--ignore-case';
		$args[] = '-n';
		$args[] = '-e';
		$args[] = $pattern;
		$args[] = $this->hash;
		
		$lines = explode("\n", $exe->Execute(GIT_GREP, $args));

		$results = array();

		foreach ($lines as $line) {
			if (preg_match('/^[^:]+:([^:]+):([0-9]+):(.+)$/', $line, $regs)) {
				if (!isset($results[$regs[1]]['object'])) {
					$hash = $this->PathToHash($regs[1]);
					if (!empty($hash)) {
						$obj = $this->project->GetBlob($hash);
						$obj->SetCommit($this);
						$results[$regs[1]]['object'] = $obj;
					}
				}
				$results[$regs[1]]['lines'][(int)($regs[2])] = $regs[3];
			}
		}

		return $results;
	}

	/**
	 * SearchFiles
	 *
	 * Searches filenames and file contents for a pattern
	 *
	 * @access public
	 * @param string $pattern pattern to search
	 * @param integer $count number of results to get
	 * @param integer $skip number of results to skip
	 * @return array array of results
	 */
	public function SearchFiles($pattern, $count = 100, $skip = 0)
	{
		if (empty($pattern))
			return;

		$grepresults = $this->SearchFileContents($pattern);

		$nameresults = $this->SearchFilenames($pattern);

		/* Merge the results together */
		foreach ($nameresults as $path => $obj) {
			if (!isset($grepresults[$path]['object'])) {
				$grepresults[$path]['object'] = $obj;
			}
		}

		ksort($grepresults);

		return array_slice($grepresults, $skip, $count, true);
	}

	/**
	 * GetMarkUrl
	 *
	 * Returns the current url, but with the 'm' mark parameter set
	 *
	 * @access public
	 * @param reset Return a mark url that will reset the mark
	 */
	public function GetMarkUrl($reset = false)
	{
		$get_params = $_GET;
		if ($reset) {
			$get_params['m'] = 'reset';
		} else {
			$get_params['m'] = $this->GetHash();
		}
		$query_string = "?";
		foreach ($get_params as $key => $value) {
			$query_string .= "&" . urlencode($key) . "=" . urlencode($value);
		}

		return $_SERVER["SCRIPT_NAME"].$query_string;
	}
}
