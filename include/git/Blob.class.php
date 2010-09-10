<?php
/**
 * GitPHP Blob
 *
 * Represents a single blob
 *
 * @author Christopher Han <xiphux@gmail.com>
 * @copyright Copyright (c) 2010 Christopher Han
 * @package GitPHP
 * @subpackage Git
 */

require_once(GITPHP_GITOBJECTDIR . 'FilesystemObject.class.php');
require_once(GITPHP_GITOBJECTDIR . 'GitExe.class.php');

/**
 * Commit class
 *
 * @package GitPHP
 * @subpackage Git
 */
class GitPHP_Blob extends GitPHP_FilesystemObject
{

	/**
	 * data
	 *
	 * Stores the file data
	 *
	 * @access protected
	 */
	protected $data;

	/**
	 * dataRead
	 *
	 * Stores whether data has been read
	 *
	 * @access protected
	 */
	protected $dataRead = false;

	/**
	 * size
	 *
	 * Stores the size
	 *
	 * @access protected
	 */
	protected $size;

	/**
	 * history
	 *
	 * Stores the history
	 *
	 * @access protected
	 */
	protected $history = array();

	/**
	 * historyRead
	 *
	 * Stores whether the history has been read
	 *
	 * @access protected
	 */
	protected $historyRead = false;

	/**
	 * blame
	 *
	 * Stores blame info
	 *
	 * @access protected
	 */
	protected $blame = array();

	/**
	 * blameRead
	 *
	 * Stores whether blame was read
	 *
	 * @access protected
	 */
	protected $blameRead = false;

	/**
	 * __construct
	 *
	 * Instantiates object
	 *
	 * @access public
	 * @param mixed $project the project
	 * @param string $hash object hash
	 * @return mixed blob object
	 * @throws Exception exception on invalid hash
	 */
	public function __construct($project, $hash)
	{
		parent::__construct($project, $hash);
	}

	/**
	 * GetData
	 *
	 * Gets the blob data
	 *
	 * @access public
	 * @param boolean $explode true to explode data into an array of lines
	 * @return string blob data
	 */
	public function GetData($explode = false)
	{
		if (!$this->dataRead)
			$this->ReadData();

		if ($explode)
			return explode("\n", $this->data);
		else
			return $this->data;
	}

	/**
	 * PipeData
	 *
	 * Pipes the blob data to a file
	 *
	 * @access public
	 * @param string $file file to pipe to
	 */
	public function PipeData($file)
	{
		if (empty($file))
			return;

		if (!$this->dataRead)
			$this->ReadData();

		file_put_contents($file, $this->data);
	}

	/**
	 * ReadData
	 *
	 * Reads the blob data
	 *
	 * @access private
	 */
	private function ReadData()
	{
		$this->dataRead = true;

		$exe = new GitPHP_GitExe($this->project);

		$args = array();
		$args[] = 'blob';
		$args[] = $this->hash;

		$this->data = $exe->Execute(GIT_CAT_FILE, $args);
	}

	/**
	 * FileType
	 *
	 * Gets a file type from its octal mode
	 *
	 * @access public
	 * @static
	 * @param string $octMode octal mode
	 * @return string file type
	 */
	public static function FileType($octMode)
	{
		$mode = octdec($octMode);
		if (($mode & 0x4000) == 0x4000)
			return 'directory';
		else if (($mode & 0xA000) == 0xA000)
			return 'symlink';
		else if (($mode & 0x8000) == 0x8000)
			return 'file';
		return 'unknown';
	}

	/**
	 * GetSize
	 *
	 * Gets the blob size
	 *
	 * @access public
	 * @return integer size
	 */
	public function GetSize()
	{
		return $this->size;
	}

	/**
	 * SetSize
	 *
	 * Sets the blob size
	 *
	 * @access public
	 * @param integer $size size
	 */
	public function SetSize($size)
	{
		$this->size = $size;
	}

	/**
	 * FileMime
	 *
	 * Get the file mimetype
	 *
	 * @access public
	 * @param boolean $short true to only the type group
	 * @return string mime
	 */
	public function FileMime($short = false)
	{
		$mime = $this->FileMime_Fileinfo();

		if (empty($mime))
			$mime = $this->FileMime_File();

		if (empty($mime))
			$mime = $this->FileMime_Extension();

		if ((!empty($mime)) && $short) {
			$mime = strtok($mime, '/');
		}

		return $mime;
	}

	/** 
	 * FileMime_Fileinfo
	 *
	 * Get the file mimetype using fileinfo
	 *
	 * @access private
	 * @return string mimetype
	 */
	private function FileMime_Fileinfo()
	{
		if (!function_exists('finfo_buffer'))
			return '';

		if (!$this->dataRead)
			$this->ReadData();

		if (!$this->data)
			return '';

		$mime = '';

		$magicdb = GitPHP_Config::GetInstance()->GetValue('magicdb', null);
		if (empty($magicdb)) {
			if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
				$magicdb = 'C:\\wamp\\php\\extras\\magic';
			} else {
				$magicdb = '/usr/share/misc/magic';
			}
		}

		$finfo = finfo_open(FILEINFO_MIME, $magicdb);
		if ($finfo) {
			$mime = finfo_buffer($finfo, $this->data, FILEINFO_MIME);
			if ($mime && strpos($mime, '/')) {
				if (strpos($mime, ';')) {
					$mime = strtok($mime, ';');
				}
			}
			finfo_close($finfo);
		}

		return $mime;
	}

	/**
	 * FileMime_File
	 *
	 * Get the file mimetype using file command
	 *
	 * @access private
	 * @return string mimetype
	 */
	private function FileMime_File()
	{
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			return '';
		}

		if (!$this->dataRead)
			$this->ReadData();

		if (!$this->data)
			return '';

		$descspec = array(
			0 => array('pipe', 'r'),
			1 => array('pipe', 'w')
		);

		$proc = proc_open('file -b --mime -', $descspec, $pipes);
		if (is_resource($proc)) {
			fwrite($pipes[0], $this->data);
			fclose($pipes[0]);
			$mime = stream_get_contents($pipes[1]);
			fclose($pipes[1]);
			proc_close($proc);

			if ($mime && strpos($mime, '/')) {
				if (strpos($mime, ';')) {
					$mime = strtok($mime, ';');
				}
				return $mime;
			}
		}

		return '';
	}

	/**
	 * FileMime_Extension
	 *
	 * Get the file mimetype using the file extension
	 *
	 * @access private
	 * @return string mimetype
	 */
	private function FileMime_Extension()
	{
		$file = $this->GetName();

		if (empty($file))
			$file = $this->GetPath();

		if (empty($file))
			return '';

		$dotpos = strrpos($file, '.');
		if ($dotpos !== FALSE)
			$file = substr($file, $dotpos+1);
		switch ($file) {
			case 'jpg':
			case 'jpeg':
			case 'jpe':
				return 'image/jpeg';
				break;
			case 'gif':
				return 'image/gif';
				break;
			case 'png';
				return 'image/png';
				break;
		}

		return '';
	}

	/**
	 * GetHistory
	 *
	 * Gets the history of this file
	 *
	 * @access public
	 * @return array array of filediff changes
	 */
	public function GetHistory()
	{
		if (!$this->historyRead)
			$this->ReadHistory();

		return $this->history;
	}

	/**
	 * ReadHistory
	 *
	 * Reads the file history
	 *
	 * @access private
	 */
	private function ReadHistory()
	{
		$this->historyRead = true;

		$exe = new GitPHP_GitExe($this->project);
		
		$args = array();
		if (isset($this->commit))
			$args[] = $this->commit->GetHash();
		else
			$args[] = 'HEAD';
		$args[] = '|';
		$args[] = $exe->GetBinary();
		$args[] = '--git-dir=' . $this->project->GetPath();
		$args[] = GIT_DIFF_TREE;
		$args[] = '-r';
		$args[] = '--stdin';
		$args[] = '--';
		$args[] = $this->GetPath();
		
		$historylines = explode("\n", $exe->Execute(GIT_REV_LIST, $args));

		$commit = null;
		foreach ($historylines as $line) {
			if (preg_match('/^([0-9a-fA-F]{40})/', $line, $regs)) {
				$commit = $this->project->GetCommit($regs[1]);
			} else if ($commit) {
				try {
					$history = new GitPHP_FileDiff($this->project, $line);
					$history->SetCommit($commit);
					$this->history[] = $history;
				} catch (Exception $e) {
					error_log($e);
				}
				unset ($commit);
			}
		}
	}

	/**
	 * GetBlame
	 *
	 * Gets blame info
	 *
	 * @access public
	 * @return array blame array (line to commit mapping)
	 */
	public function GetBlame()
	{
		if (!$this->blameRead)
			$this->ReadBlame();

		return $this->blame;
	}

	/**
	 * ReadBlame
	 *
	 * Read blame info
	 *
	 * @access private
	 */
	private function ReadBlame()
	{
		$this->blameRead = true;

		$exe = new GitPHP_GitExe($this->project);

		$args = array();
		$args[] = '-s';
		$args[] = '-l';
		if ($this->commit)
			$args[] = $this->commit->GetHash();
		else
			$args[] = 'HEAD';
		$args[] = '--';
		$args[] = $this->GetPath();

		$blamelines = explode("\n", $exe->Execute(GIT_BLAME, $args));

		$lastcommit = '';
		foreach ($blamelines as $line) {
			if (preg_match('/^([0-9a-fA-F]{40})\s+([0-9]+)\)/', $line, $regs)) {
				if ($regs[1] != $lastcommit) {
					$this->blame[(int)($regs[2])] = $this->project->GetCommit($regs[1]);
					$lastcommit = $regs[1];
				}
			}
		}
	}
}
