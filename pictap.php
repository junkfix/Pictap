<?php
/* Pictap Gallery https://github.com/junkfix/Pictap */

const PIC_VER = ['2.0.0',2]; //[main, config]]

if(get('sf')){sfile(get('sf'));}

@ini_set('memory_limit', '512M');
@ini_set('max_execution_time', '0');
@ini_set('max_input_time', '-1');


function myConfig($save=0){
	$f = dirnm(__FILE__) .'/pictap_config.php';
	if($save){
		$save['err'] = 0;
		$s = ['<?php die; ?>', $save];
		if(!file_put_contents($f, json_encode($s,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES))){
			return false;
		}
	}
	$c = @file_get_contents($f);
	if($c){
		$c = json_decode($c, true);
		if(empty($c[1])){return null;}
		$c = $c[1];
	}
	return $c;
}

$setup=myConfig();

if(!is_array($setup)){
	$setup=['version'=>[0,0], 'db_setup'=>0, 'salt'=>bin2hex(random_bytes(8))];
}

$setup = (object) $setup;
define( 'PICTAP', $setup );


define( 'ROLES', (object)roleList());

class MyDB {
	private $conn = null;
	public $emsg = '';
	private $d = '';
	private $rows = 0;
	private $lastid = 0;
	public function __construct($db) {
		$this->d = $db->db_type;
		try {
			switch ($this->d) {
				case 'pgsql':
					$u='host='.$db->db_host;
					if($db->db_port){$u.=' port='.$db->db_port;}
					$u.=' dbname='.$db->db_name.' user='.$db->db_user.' password='.$db->db_pass;
					$this->conn = @pg_connect($u);
					if (!$this->conn) {
						$e = error_get_last();
						throw new Exception($e['message']);
					}
					if($db->db_schema){
						$this->exec('SET search_path TO '.$db->db_schema);
					}
					break;

				case 'mysql':
					$host = $db->db_host; $socket = null;
					if((stripos($host, '/') !== false)){$socket = $host; $host = null;}
					$this->conn = new mysqli($host, $db->db_user, $db->db_pass, $db->db_name, $db->db_port ?: 3306, $socket);
					if ($this->conn->connect_errno) {
						throw new Exception($this->conn->connect_error);
					}
					$this->conn->set_charset('utf8mb4');
					break;

				default:
					$this->conn = new SQLite3($db->db_file);
					$this->conn->busyTimeout(60000);
					$this->exec("PRAGMA foreign_keys = 1");
			}
		} catch (Exception $e) {
			throw new Exception("Db Error: ".$e->getMessage());
		}
	}

	public function close() {
		match($this->d) {'pgsql' => pg_close($this->conn), 'mysql' => $this->conn->close(), default => $this->conn->close()};
		$this->conn = null;
	}

	public function exec($q) {
		try {
			return match($this->d) {
				'pgsql' => ($r = @pg_query($this->conn, $q)) === false ? throw new Exception(error_get_last()['message']) : pg_affected_rows($r),
				'mysql' => $this->myexec($q,$this->conn),
				default => $this->conn->exec($q) !== false
			};
		} catch (Exception $e) {
			throw new Exception("Query failed: " . $e->getMessage());
		}
	}

	private function myexec($q,$c) {
		if (!$c->multi_query($q)){ throw new Exception($c->error);}
		do {
			if ($res = $c->store_result()){$res->free();}
			if ($c->errno){throw new Exception($c->error);}
		} while ($c->more_results() && $c->next_result());
		return true;
	}

	public function rowCount() {
		return $this->rows;
	}

	public function lastInsertId() {
		if($this->d == 'pgsql'){
			$li = @pg_query($this->conn, "SELECT lastval()");
			$this->lastid = $li ? @pg_fetch_result($li, 0, 0) : 0;
		}
		return $this->lastid;
	}

	public function run($q, $args = null, $fet = 0, $line=0){
		$this->emsg = '';
		$res = null;
		try {
			$this->lastid = (stripos($q, 'INSERT ') !== false);
			switch ($this->d) {
				case 'pgsql':
					if($args){
						$i = 1;
						$q = preg_replace_callback('/\?/', function() use (&$i) {return '$'.$i++;}, $q);
						$result = @pg_query_params($this->conn, $q, $args);
					}else{
						$result = @pg_query($this->conn, $q);
					}
					if ($result === false) {
						$e = error_get_last();
						throw new Exception("query failed: ".$e['message']);
					}
					$this->rows = $result ? pg_affected_rows($result) : 0;
					if($fet){
						if($fet > 1){
							$s = pg_fetch_all($result, PGSQL_ASSOC) ?: [];
						}else{
							$s = pg_fetch_assoc($result);
						}
						if($s === false){$s = null;}
						$result = $s;
					}
					if (is_resource($result)) {
						pg_free_result($result);
					}
					return $result;

				case 'mysql':
					$stmt = $this->conn->prepare($q);
					if (!empty($args)) {
						$t = implode('', array_map(fn($p) => match(true) {is_int($p) => 'i', is_float($p) => 'd', default => 's'}, $args));
						$stmt->bind_param($t, ...$args);
					}
					$result = $stmt->execute();
					$res = $stmt->get_result();
					$this->rows = $stmt->affected_rows;
					$this->lastid = $this->lastid ? $stmt->insert_id : 0;
					if($fet){
						if ($res instanceof mysqli_result) {
							$s = ($fet > 1)? $res->fetch_all(MYSQLI_ASSOC) : $res->fetch_assoc();
						} else {
							$s = ($fet > 1)? [] : null;
						}
						if($s === false){$s = null;}
						$result = $s;
					}else{
						if ($res === false && $result) {
							$result = $this->rows;
						}
					}
					if ($res instanceof mysqli_result) {$res->free();}
					$stmt->close();
					return $result;

				default:
					$stmt = $this->conn->prepare($q);
					if (!$stmt) return false;
					if (!empty($args)) {
						foreach ($args as $i => $p) {
							$stmt->bindValue($i + 1, $p);
						}
					}
					$res = $stmt->execute();
					$this->rows = $this->conn->changes();
					$this->lastid = $this->lastid ? $this->conn->lastInsertRowID() : 0;
					if($fet){
						if($fet > 1){
							$s = [];
							while ($i = $res->fetchArray(SQLITE3_ASSOC)) {
								$s[] = $i;
							}
						}else{
							$s = $res->fetchArray(SQLITE3_ASSOC);
						}
						if($s === false){$s = null;}
						$res = $s;
					}
					$stmt->close();
					return $res;
			}
		} catch (Exception $e) {
			$m = ' Error: '.$e->getMessage()."\nQuery: ".$q;
			$this->emsg = $m;
			error_log($m);
			$this->rows = 0;
			$this->lastid = 0;
			if($line){
				logger('Line:'.$line.$m."\n".print_r($args, true));
			}
		}
		return false;
	}
	public function __destruct() {
		$this->close();
	}
}

function openDb($db=0){
	static $dbo = null;
	if($db === -1){
		if($dbo){$dbo->close();}
		$dbo = null;
	}else{
		if($dbo === null){
			$d = $db===0 ? PICTAP : $db;
			try {
				$dbo = new MyDB($d);
			} catch (Exception $e) {
				logger($e->getMessage());
				error_log($e->getMessage());
				$d->err = $e->getMessage();
				return null;
			}
		}
	}
	return $dbo;
}

if($setup->version[1] != PIC_VER[1]){
	if(property_exists($setup,'users')){
		userAuth(true);
	}
	pageConfig($setup);
}

function logger($msg='', $level=3){
	$cli = (php_sapi_name() == 'cli');
	if ($cli && $msg){echo $msg."\n";}
	if($msg && defined('PICTAP') && PICTAP->debug && $level >= PICTAP->debug && !empty(PICTAP->debug_file)){
		$s = $cli ? 'CLI' : $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
		file_put_contents(PICTAP->debug_file, '['.date("Y-m-d H:i:s")."] $s $msg\n",FILE_APPEND);
	}
	return $cli;
}

//command line
if (logger()) {
	set_time_limit(0);
	userAuth();

	//$p=getopt("s:c:f:d:"); //print_r($p); exit;

	$force = 0;

	if (!globber(PICTAP->path_pictures, $force) ||
	!($dbo = openDb()) ||
	($stmt = $dbo->run("SELECT f.*, d.dir FROM ".PICTAP->db_prefix."files f INNER JOIN ".PICTAP->db_prefix."dirs d USING(dirid)", null, 2, __LINE__)) === false){exit(1);}

	$rows = $stmt ?: [];

	openDb(-1);
	logger("Scan begins", 1);
	$forcethumb = 0;
	$forcescan = 0;
	foreach ($rows as $r){
		$f = $r['dir'].'/'.$r['file'];
		if((int)$r['th']===2 || $forcescan){
			logger('Scan /'.$f, 2);
			if(!getExif($r)){logger('Error getExif() '.$f, 2);}
		}
		if((int)$r['th']===1 || $forcethumb){
			logger('Thum /'.$f, 2);
			if(makeThumb($r,$forcethumb) === false){
				logger('Error makeThumb() '.$f, 2);
			}
		}
	}

	if(PICTAP->db_type == 'sqlite'){
		if(!($dbo = openDb())){echo 'Database Error';exit(1);}
		logger("Database Optimize\n", 1);
		$dbo->exec('PRAGMA optimize;');
		$dbo->exec('VACUUM;');
	}
	logger("Checking for orphan thumbs", 1);
	if(file_exists(PICTAP->path_pictures) && count(glob(glob_nobr(PICTAP->path_pictures)."/*"))>5 && count(glob(glob_nobr(PICTAP->path_thumbs)."/*"))>1 ){
		del_orphan_thumbs(PICTAP->path_thumbs);
	}
	//TODO: keywords.py
	//shell_exec("/usr/bin/nohup ".$cmd." >/dev/null 2>&1 &");

	exit;

}

function glob_nobr($p){
	return preg_match('/\[.+]/', $p)? str_replace(['[',']','\[','\]'],['\[','\]','[[]','[]]'], $p) : $p;
}

function globber($dir, $force){
	logger('Checking: '.$dir, 1);
	if(!scanFolder($dir, $force)){return false;};
	$deep = glob(glob_nobr($dir) . '/*', GLOB_ONLYDIR | GLOB_NOSORT);
	foreach ($deep as $d) {
		if(!globber($d, $force)){return false;}
	}
	return true;
}

function del_orphan_thumbs($file){
	if(is_dir($file)){
		$scan = glob(glob_nobr(rtrim($file,'/')).'/*');
		foreach($scan as $i=>$path){
			del_orphan_thumbs($path);
		}
		$relth = relativethumb($file);
		$orgdir = PICTAP->path_pictures . $relth;
		if(!file_exists($orgdir)){
			rmdir($file);
			logger('Removed Thumb Directory: '.$relth, 2);
		}
		return;
	}
	$f = splitExt($file);
	if( $f[2] != 'webp' ){return;}
	$count = glob(glob_nobr(PICTAP->path_pictures . relativethumb($f[0])).'.*');
	if(!count($count)){
		if (!@unlink(file)) {
			logger('Failed to remove thumb: ' . relativethumb($file), 2);
		} else {
			logger('Removed Thumb: ' . relativethumb($file), 2);
		}
	}
}

function getDevID($dev, $insert=true){
	if(!$dev){return null;}
	if(!($dbo = openDb()) ||
	($stmt = $dbo->run("SELECT devid FROM ".PICTAP->db_prefix."devs WHERE LOWER(dev) LIKE ?;", [strtolower($dev)], 1, __LINE__)) === false){return false;}
	if($stmt){
		return $stmt['devid'];
	}else if($insert){
		if($dbo->run("INSERT INTO ".PICTAP->db_prefix."devs (dev) VALUES (?)", [$dev], 0, __LINE__) === false){return false;}
		//$dirID=$dbo->lastInsertId();
		return getDevID($dev, false);
	}
	return null;
}

function getDirID($n, $insert=true){
	$name = ltrim($n,'/');
	$nt = PICTAP->db_prefix."dirs";
	if(!($dbo = openDb()) ||
	($stmt = $dbo->run("SELECT * FROM $nt WHERE dir = ?;", [$name], 1, __LINE__)) === false){return false;}
	if ($stmt) {
		return $stmt;
	} else if($insert){
		if(($stmt = $dbo->run("SELECT CASE WHEN (SELECT COUNT(*) FROM $nt) - (SELECT MAX(dirid) FROM $nt) >= 10 THEN COALESCE( (SELECT MIN(t1.dirid + 1) FROM $nt t1 WHERE NOT EXISTS (SELECT 1 FROM $nt t2 WHERE t2.dirid = t1.dirid + 1)), 1) ELSE (SELECT COALESCE(MAX(dirid), 0) + 1 FROM $nt) END AS did;", null, 1, __LINE__)) === false ||
		$dbo->run("INSERT INTO $nt (dirid, dir) VALUES (?, ?)", [$stmt['did'], $name], 0, __LINE__) === false
		){return false;}
		//$dirID=$dbo->lastInsertId();
		return getDirID($n, false);
	}
	return false;
}

function familyQ(){
	$r=''; $f = PICTAP->family_dir; $s=[];
	if(!userCan('alldir')){
		$d = ["d.dir = ?","d.dir LIKE ?"];
		$s[] = USER->user; $s[] = USER->user."/%";
		if(userCan('family') && $f){
			$d[] = "d.dir = ?";
			$d[] = "d.dir LIKE ?";
			$s[] = $f; $s[] = $f."/%";
		}
		$r=" AND ( ".implode(' OR ',$d)." ) ";
	}
	return [$r,$s];
}

function getDirRow($id){
	if(!is_numeric($id)){return null;}
	$id = (int)$id;
	[$fs, $fp] = familyQ();
	$q = "SELECT * FROM ".PICTAP->db_prefix."dirs d WHERE d.dirid = ?" . $fs;
	if(!($dbo = openDb()) ||
	($stmt = $dbo->run($q, array_merge([$id], $fp), 1, __LINE__)) === false) {$stmt = null;}
	return $stmt;
}

function updateDir($dir, $c){
	$col = []; $a = [];
	foreach($c as $k=>$v){
		$col[] = $k.' = ?';
		$a[] = $v;
	}
	$a[] = $dir['dirid'];

	if(!($dbo = openDb()) ||
	($dbo->run("UPDATE ".PICTAP->db_prefix."dirs SET ".implode(', ',$col)." WHERE dirid = ?", $a, 0, __LINE__) === false)){return false;}
	$r = $dbo->rowCount();
	if(isset($c['dir'])){//dir name updated
		if(($f = getFileIds($dir['dirid'])) === false){return false;}
		$fid=[];
		foreach($f as $g) {
			$fid[]=(int)$g['fileid'];
		}
		if(albumLinks(1,$fid) === false){return false;}
	}
	return $r;
}

function getDirWild($path){
	$epath = addcslashes($path, '%_');
	if(!($dbo = openDb()) ||
	($stmt = $dbo->run("SELECT * FROM ".PICTAP->db_prefix."dirs WHERE dir = ? OR dir LIKE ? ORDER BY LENGTH(dir) - LENGTH(REPLACE(dir, '/', '')) DESC", [$path, "$epath/%"], 2, __LINE__)) === false){return false;}
	$stmt = $stmt ?: [];
	return $stmt;
}

function delDirRow($path){
	if(($res = getDirWild($path))===false){return false;}
	foreach($res as $r) {
		if(($f = getFileIds((int)$r['dirid'])) === false){return false;}
		foreach($f as $g) {
			if(delFileRow($g)===false){return false;}
		}
	}
	if(!($dbo = openDb())){return false;}
	foreach($res as $r) {
		$q = "DELETE FROM ".PICTAP->db_prefix."dirs WHERE dirid = ?;";
		if($dbo->run($q, [(int)$r['dirid']], 0, __LINE__) === false) {
			return false;
		}
	}
	return true;
}

function getFileIds($dirid){
	if(!($dbo = openDb()) ||
	($stmt = $dbo->run("SELECT f.fileid, f.file, d.dir FROM ".PICTAP->db_prefix."files f INNER JOIN ".PICTAP->db_prefix."dirs d USING(dirid) WHERE dirid = ?;", [$dirid], 2, __LINE__)) === false ){return false;}
	return $stmt ?: [];
}

function delFileRow($r){
	if(!$r){return null;}
	$rp = joinp($r['dir'],$r['file']);
	$fullp = PICTAP->path_pictures . $rp;
	if(!file_exists($fullp) && $r['ft']){
		del_thumb($rp);
	}

	$q = "DELETE FROM ".PICTAP->db_prefix."files WHERE fileid = ?;";
	if(!($dbo = openDb()) ||
	albumLinks(0,(int)$r['fileid']) === false ||
	$dbo->run($q, [(int)$r['fileid']], 0, __LINE__) === false){
		return false;
	}
	return $dbo->rowCount();
}

function insertFile($c, $file){
	logger("insertFile: ".$c['dirid'].' '.$file, 2);
	$c['file'] = $file;
	$nt = PICTAP->db_prefix."files";

	if(!($dbo = openDb()) ||
	($stmt = $dbo->run("SELECT CASE WHEN (SELECT COUNT(*) FROM $nt) - (SELECT MAX(fileid) FROM $nt) >= 10 THEN COALESCE( (SELECT MIN(t1.fileid + 1) FROM $nt t1 WHERE NOT EXISTS (SELECT 1 FROM $nt t2 WHERE t2.fileid = t1.fileid + 1)), 1) ELSE (SELECT COALESCE(MAX(fileid), 0) + 1 FROM $nt) END AS fid;", null, 1, __LINE__)) === false){
		return false;
	}
	$c['fileid'] = (int)$stmt['fid'];

	$v = implode(',', array_fill(0, count($c), '?'));

	[$pf, $sf] = ignoreVars(PICTAP);
	$q = "INSERT $pf INTO $nt (".implode(',',array_keys($c)).") VALUES ($v) $sf;";

	if($dbo->run($q, array_values($c), 0, __LINE__)===false){return false;}
	//$fileid=$dbo->lastInsertId();
	return $c['fileid'];
}

function updateFile($fileid, $c){
	$col = [];$s=[];
	foreach($c as $k=>$v){
		$col[] = $k.' = ?';
		$s[] = $v;
	}
	$s[] = $fileid;
	$q = "UPDATE ".PICTAP->db_prefix."files SET ".implode(',',$col)." WHERE fileid = ?";

	if( !($dbo = openDb()) || ($stmt = $dbo->run($q, $s, 0, __LINE__)) === false ){return false;}
	return $dbo->rowCount();
}

function getFileRow($id, $name=''){
	$f = "f.fileid = ?";
	$a = [$id];
	if($name !== ''){
		$f = "d.dirid = ? AND f.file = ?";
		$a[] = $name;
	}
	[$fs, $fp] = familyQ();
	$q = "SELECT f.*, d.dir, d.parentid FROM ".PICTAP->db_prefix."files f INNER JOIN ".PICTAP->db_prefix."dirs d USING(dirid) WHERE ". $f . $fs;
	if(!($dbo = openDb()) ||
	($stmt = $dbo->run($q, array_merge($a, $fp), 1, __LINE__)) === false){return false;}
	return $stmt;
}

function gpsCity($lat=0, $lon=0){
	$q = "SELECT locationid	FROM ".PICTAP->db_prefix."locations ORDER BY ABS(lat - ?) + ABS(lon - ?) LIMIT 1";
	if(!($dbo = openDb()) ||
	($stmt = $dbo->run($q, [$lat, $lon], 1, __LINE__)) === false){
		return false;
	}
	if ($stmt) {
		return $stmt['locationid'];
	}
	return null;
}

function dirJSON($dir, $posttime=0){
	if(!($dirID = getDirRow($dir))){sendjs(0,"Invalid dir $dir");}
	$dir = rtrim(PICTAP->path_pictures.'/'.$dirID['dir'], '/');
	$dirtime = @filemtime($dir);
	if($dirID['mt'] != $dirtime){
		if(!$dirtime){//directory deleted
			if($dirID['parentid']){
				return dirJSON($dirID['parentid']);
			}
		}
		if(locker(5)){
			ignore_user_abort(true);
			scanFolder($dir);
			locker();//unlocks
		}else{
			$dirtime = 1;
		}
	}

	$f=[
		'mode'	=> 'd',
		'key'	=> $dirID['dirid'],
		'mt'	=> $dirtime,
		'ids'	=> [],
		'fds'	=> []
	];
	if($dirtime != $posttime){
		$f['Dir'] = menuList();
	}

	$q = fileSql()." WHERE dirid = ? GROUP BY f.fileid, l.location, s.state, c.country, v.dev ORDER BY file ASC;";

	if(!($dbo = openDb()) ||
	($stmt = $dbo->run($q, [$dirID['dirid']], 2, __LINE__)) === false){
		return false;
	}
	$stmt = $stmt ?: [];
	loopFiles($stmt, $f);
	return $f;
}

function validGps($lat, $lon) {
	return ($lat >= -90 && $lat <= 90 && $lon >= -180 && $lon <= 180);
}

function getExif(&$r){
	$rp = joinp($r['dir'],$r['file']);
	foreach(['ft','tk','mt','th','fileid'] as $i){
		if(is_numeric($r[$i])){$r[$i] = (int)$r[$i];}
	}
	$fullp = PICTAP->path_pictures . $rp;
	if(!file_exists($fullp)){
		delFileRow($r);
		return 0;
	}
	$lf = $r['ft']? '-api largefilesupport=1' : '';
	openDb(-1);
	if(logger() || locker(30)){
		ignore_user_abort(true);
		$exif = json_decode(shell_exec(escapeshellarg(PICTAP->bin_exiftool).' -n -json '.$lf.' '.escapeshellarg($fullp)), true);
		locker();//unlocks
	}
	if(empty($exif)) {
		logger('exiftool error '.$rp);
		return 0;
	}
	$exif=$exif[0];
	$taken = 0;
	$r['tk'] = $r['mt'];
	$t = fileDate($r['file']);
	if($t){$exif["NameDate"] = date("Y-m-d H:i:s", $t);}
	foreach (["GPSDateTime", "DateTimeOriginal", "CreateDate", "CreationDate", "DateCreated", "TrackCreateDate", "MediaCreateDate", "NameDate", "ModifyDate", "MediaModifyDate", "TrackModifyDate", "FileCreateDate", "FileModifyDate"] as $tag) {
		if ( !array_key_exists($tag,$exif) ) {
			continue;
		}
		$taken = strtotime(explode(".", $exif[$tag])[0]);
		if ($taken !== false && $taken > 0 ) {
			break;
		}
		$taken = 0;
	}
	if($taken && $taken < $r['tk']){
		$r['tk'] = $taken;
	}
	if(!empty($exif["GPSLatitude"]) && !empty($exif["GPSLongitude"])){
		$lat = $exif["GPSLatitude"]; $lon = $exif["GPSLongitude"];
		if (validGps($lat,$lon)){
			$latt = round($lat*10000);
			$lonn = round($lon*10000);
			if($latt != $r['lat'] || $lonn != $r['lon']){
				if(($gc = gpsCity($lat, $lon)) === false){
					return 0;
				}
				$r['locationid']=$gc;
				$r['lat']=$latt;
				$r['lon']=$lonn;
			}
		}
	}
	$r['w'] = 0;
	$r['h'] = 0;
	if($r['ft'] && !empty($exif["ImageWidth"]) && !empty($exif["ImageHeight"])){
		$w = $exif["ImageWidth"]; $h = $exif["ImageHeight"];
		$ori = 0;
		if($r['ft'] == 1 && !empty($exif['Orientation'])){
			$ori = intval($exif['Orientation']);
		}
		if($r['ft'] == 2 && !empty($exif['Rotation'])){
			$ori = intval($exif['Rotation']);
		}
		if(in_array($ori,[5,6,7,8,90,270])){
			[$w, $h] = [$h, $w];//swap
		}
		if($ori === 1){$ori = 0;}
		$r['ori'] = $ori;
		$r['w'] = $w;
		$r['h'] = $h;
	}
	$model='';
	if(!empty($exif['Model'])){
		$model=$exif['Model'];
	}
	if(!empty($exif['Make'])
		&& !empty($model)
		&& strpos($model,$exif['Make'])===false
	){
		$model=$exif['Make'].' '.$model;
	}
	if($model){
		$model=ucwords(trim(strtolower($model),' -'));
	}
	if(($d = getDevID($model)) === false){return 0;}
	$r['devid'] = $d;
	if(!empty($exif['Duration'])){$r['dur'] = floor($exif['Duration']);}

	$ky = array_flip(explode(' ','tk lat lon locationid w h ori devid dur th'));
	if($r['th'] === 2){$r['th']=1;}
	if(!$r['ft']){$r['th']=0;}
	foreach($ky as $k=>$v){
		$ky[$k] = $r[$k];
	}

	return (updateFile($r['fileid'], $ky) !== false);

}

function scanFolder($dir, $force=0){
	$dir = rtrim($dir,'/');
	if(!$dir || $dir === '.' || $dir === '..') {return false;}
	$rpath = ltrim(relative($dir), '\/');
	if(($dirs=getDirID($rpath)) === false){return false;}
	$parentid=null;
	
	if(strlen($rpath)){
		$p = getDirID(dirnm('/'.$rpath), false);
		if(!$p){return false;}
		$parentid = $p['dirid'];
	}

	if(!($dbo = openDb()) ||
	($stmt = $dbo->run("SELECT * FROM ".PICTAP->db_prefix."dirs WHERE parentid = ?;", [$dirs['dirid']], 2, __LINE__)) === false) {
		return false;
	}
	$stmt = $stmt ?: [];

	$old=[];
	foreach ($stmt as $r) {
		$d = $r['dir'];
		if (str_starts_with($d, $dirs['dir'])) {
			$d = substr($d, strlen($dirs['dir']));
		}
		$old[ ltrim($d,'/') ] = $r;
	}


	if (($stmt = $dbo->run("SELECT * FROM ".PICTAP->db_prefix."files WHERE dirid = ? ORDER BY file ASC;", [$dirs['dirid']], 2, __LINE__)) === false) {return false;}
	$stmt = $stmt ?: [];
	foreach ($stmt as $r) {
		$old[ $r['file'] ] = $r;
	}
	openDb(-1);
	$dirsize = 0;
	$totalfiles = 0;
	$dirlist = (file_exists($dir) && filetype($dir) === 'dir') ? scandir($dir, SCANDIR_SORT_NONE) : [];
	if($dirlist === false || !loopDir($dir, $dirs, $dirlist, $old, $dirsize, $totalfiles, $force)){logger('loopDir Error');return false;}

	if(!empty($old)){
		foreach($old as $k => $v){
			if(array_key_exists('parentid',$v)){//isdir
				if($v['dir']){
					if(!delDirRow($v['dir'])){return false;}
					logger("Removed: ".$v['dir'], 2);
				}else{
					logger("Baddirval: $k : ".$v['dir']);
				}
			}else{
				$p = joinp($dirs['dir'], $k);
				if(delFileRow(getFileRow($v['fileid']))===false){return false;}
				logger("Removed: ".$p, 2);
			}
		}
	}
	$dtime = filemtime($dir);
	if($dtime){
		$u = ['mt'=>$dtime, 'qt'=>$totalfiles, 'sz'=>$dirsize, 'parentid'=>$parentid, 'thm'=>$dirs['thm']];
		foreach($u as $k=>$v){
			if($dirs[$k]!=$v){
				if(updateDir($dirs, $u)===false){return false;}
				break;
			}
		}
	}else{
		if(!delDirRow($dirs['dir'])){return false;}
		$dirs = null;
	}
	return true;
}

function loopDir(&$dir, &$dirs, &$dirlist, &$old, &$dirsize, &$totalfiles, $force) {
	$dupchecker=[];
	foreach($dirlist as $filename) {
		if($filename === '.' || $filename === '..') continue;

		$path = $dir . '/' . $filename;
		if(!is_readable($path)){
			logger("unreadable $path");
			continue;
		}
		$is_dir = filetype($path) === 'dir' ? true : false;
		if(is_exclude($path, $is_dir)){
			logger("excluded dir $path", 1);
			continue;
		}
		$filemtime = filemtime($path);
		$filesize = $is_dir ? 0 : filesize($path);
		$ext = '';
		$ft = 0;
		if(!$is_dir){
			$ext = strtolower(splitExt($path)[2]);
			$ft = in_array($ext, PICTAP->ext_videos)? 2 : (in_array($ext, PICTAP->ext_images)?1:0);
			if(!$filesize){$ft=0;}
			$totalfiles++;
			$dirsize += $filesize;
			if($ft){
				$cleanname = sanitise_name($filename);
				if($filename !== $cleanname){
					$npath = $dir . '/' . $cleanname;
					$r = safe_rename($path, $npath);
					if(!$r){
						die("Error rename ".$npath );
					}else{
						$filename = basename($r[1]);
						$path = $dir . '/' . $filename;
					}
				}
				if($filesize){
					$nameonly = splitExt($path);
					if(isset($dupchecker[$nameonly[0]])){
						$q=1;
						while(file_exists($nameonly[0].'_'.$q.'.'.$nameonly[2])){
							$q++;
						}
						$npath=$nameonly[0].'_'.$q.'.'.$nameonly[2];
						$filename = basename($npath);

						logger('** rename '.$path." to ".$npath, 2);
						safe_rename($path,$npath);
						$path = $npath;
					}
					$dupchecker[$nameonly[0]]=1;

				}
			}
		}

		if( !empty($old[$filename]) && !$force){
			if($filemtime == $old[$filename]['mt'] && $filesize == $old[$filename]['sz'] ){
				if(!empty($old[$filename]['fileid'])){
					$cfid = $old[$filename]['fileid'];
					$tn = thumb_name(joinp($dirs['dir'],$filename));
					if($old[$filename]['th']==0 && $ft>0 && !file_exists(PICTAP->path_thumbs . $tn)){
						logger('Missing thumb '.$tn);
						if(updateFile($cfid, ['th'=>2])===false){continue;};
					}
					if($filename === PICTAP->folder_thumb && !$dirs['thm']){
						$dirs['thm'] = $cfid;
					}
				}
				unset($old[$filename]);
				continue;
			}
		}

		if( $is_dir ){
			if(($currsubdir = getDirID(joinp($dirs['dir'], $filename, 0))) === false){return false;}
			if( !empty($old[$filename])){//update
				unset($old[$filename]);
			}
			if($dirs['dirid'] !== $currsubdir['parentid']){
				if(updateDir($currsubdir, ['parentid'=>$dirs['dirid']])===false){continue;};
			}
		}else{
			$c = [
				'dirid' => $dirs['dirid'],
				'ft' => $ft,
				'sz' => $filesize,
				'mt' => $filemtime,
			];
			if( !empty($old[$filename])){//update
				$c['th']=2;
				$cfid = $old[$filename]['fileid'];
				if(updateFile($cfid, $c)===false){continue;}
				unset($old[$filename]);
			}else{
				if(!$ft){$c['tk']=$filemtime;}
				if(($cfid = insertFile($c,$filename)) === false){
					return false;
				}
			}
			if($filename === PICTAP->folder_thumb && !$dirs['thm']){
				$dirs['thm'] = $cfid;
			}
		}

	}
	return true;
}

function renameSubDir($old,$newdir){
	if(($res = getDirWild($old)) === false){return false;}
	foreach($res as $r) {
		$upd=trim($newdir . substr($r['dir'], strlen($old)),'/');
		if(updateDir($r,['dir'=>$upd])===false){return false;}
	}
	return true;
}

function loopFiles(&$stmt, &$f){
	foreach($stmt as $r) {
		$j=[
			'n' => $r['file'],
			'd' => (int)$r['dirid'],
			's' => (int)$r['sz'],
			'm' => (int)$r['mt'],
			't' => (int)$r['tk']
		];
		if(isset($r['rank'])){
			$j['z'] = (int)$r['rank'];
		}
		$r['city'] = implode(', ', array_filter(array_unique([$r['location'], $r['state'], $r['country']])));
		foreach(['w','h','ori','dev','lat','lon','city','th','dur','k'] as $k => $v){
			if(!empty($r[$v])){
				$j[$v] = is_numeric($r[$v])? (int)$r[$v] : $r[$v];
			}
		}
		$f['ids'][] = (int)$r['fileid'];
		$f['fds'][] = $j;
	}
}

function sanitise_name($name,$isdir=0){
	$name = preg_replace('/[<>:"\/\\\|?*]|\.\.|\.$/', '', $name);
	$name = trim(preg_replace('/[[:cntrl:]]/', '', $name));
	if($isdir){return $name;}
	$n = splitExt($name);
	if(!empty(PICTAP->auto_rename)){
		$n[0] = preg_replace('/^(?:IMG_|VID_)([0-9]{8}_[0-9]{6}.*)$/', '$1',$n[0]);
	}
	$n[2] = strtolower($n[2]);
	if($n[2] === 'jpeg'){$n[2] = 'jpg';}
	return $n[0] . '.' .$n[2];
}

function safe_rename($oldpath, $newpath, $ret = 0) {
	if (file_exists($newpath)) {
		$s = splitExt($newpath);
		$e = $s[2];
		$b = $s[0];
		$c = 1;
		do {
			$n = $b . '_' . $c . '.' . $e;
			$c++;
		} while (file_exists($n));
		$newpath = $n;
	}
	if($ret){return $newpath;}
	$r = @rename($oldpath, $newpath);
	return [$r, $newpath];
}

function dirnm($file){
	return str_replace('\\','/',dirname($file));
}

function sendThumb(&$r){
	if(!$r){
		cacheHdr();
		http_response_code(204); exit;
	}
	if(is_array($r)){
		if(((int)$r['th']===2 && !getExif($r)) ||
		((int)$r['th']===1 && makeThumb($r) === false) ||
		(int)$r['th']
		){
			http_response_code(500);
		}else{
			$f = implode('/', array_map('rawurlencode', explode('/', thumb_name(joinp($r['dir'],$r['file'])))));
			cacheHdr();
			header("Location: ".PICTAP->url_thumbs.$f, true, 301);
		}
	}else{
		http_response_code(500);
	}
	exit;
}

function getPostFile(&$fr, &$path){
	$path = post('name');
	$fr = getFileRow(post('fid'));
	if($fr){
		$path = PICTAP->path_pictures . joinp($fr['dir'],$fr['file']);
		if(!file_exists($path)){$fr=0;}
	}
	if(!$fr){
		sendjs(0,"Invalid path ".$path);
	}
}

function getPostDir(&$id, &$dirpath){
	if(!($id = getDirRow(post('id')))){
		sendjs(0,"Invalid id ".post('id'));
	}
	$dirpath = rtrim(PICTAP->path_pictures.'/'.$id['dir'],'/');
}

function menuList(){
	$m = 0;
	$menu=[];
	$root = 0;
	$home=0;
	[$fs, $fp] = familyQ();
	$user = (USER->id === 1)? '' : USER->user;
	if(!($dbo = openDb()) ||
	($stmt = $dbo->run("SELECT dirid, dir, mt, sz, qt, parentid FROM ".PICTAP->db_prefix."dirs d WHERE 1 = 1 $fs ORDER BY dir ASC;", $fp, 2, __LINE__)) === false) {return null;}

	$stmt = $stmt ?: [];
	foreach ($stmt as $r) {
		$m = max($m, (int)$r['mt']);
		$p = intval($r['parentid']);
		$l = [ $r['dir'], $p, (int)$r['mt'], (int)$r['sz'], (int)$r['qt'] ];
		$menu[$r['dirid']] = $l;
		if($r['dir'] === $user){
			$home = (int)$r['dirid'];
			$root = $p;
		}
		if($r['dir'] === ''){$root = (int)$r['dirid'];}
	}

	if($root && !isset($menu[ $root ])){
		$menu[ $root ] = ['',0,0,0,0];
	}
	$a = [];
	$wh = userCan('admin')? '' : "WHERE userid = ".USER->id." OR family > 0";
	if (($stmt = $dbo->run("SELECT albumid, name, qt, mtime, userid, share, family FROM ".PICTAP->db_prefix."albums ".$wh." ORDER BY CASE WHEN userid = ".USER->id." THEN 0 ELSE 1 END, mtime DESC;", null, 2, __LINE__)) === false) {return null;}
	$stmt = $stmt ?: [];
	foreach ($stmt as $r) {
		if(!$r['share']){$r['share'] = 0;}
		$own = $r['userid'] == USER->id ? 1 : 0;
		$a[] = [(int)$r['albumid'], $r['name'], (int)$r['qt'], (int)$r['mtime'], $own, (int)$r['family'], $r['share']];
	}
	return ['d' => (object) $menu, 'm' => $m, 'a' => $a, 'home' => $home, 'root' => $root ];
}

function reltiveroot($p){
	$r = str_replace('\\','/',$_SERVER['DOCUMENT_ROOT']);
	if (str_starts_with($p, $r)) {
		$p = substr($p, strlen($r));
	}
	return $p;
}

function relative($p){
	if (str_starts_with($p, PICTAP->path_pictures)) {
		$p = substr($p, strlen(PICTAP->path_pictures));
	}
	return $p;
}
function relativethumb($p){
	if (str_starts_with($p, PICTAP->path_thumbs)) {
		$p = substr($p, strlen(PICTAP->path_thumbs));
	}
	return $p;
}

function joinp($dir,$file,$lslash=1){
	$p = $dir;
	if($p!=''){$p='/'.$p;}
	$p .= '/'.$file;
	if(!$lslash){$p = ltrim($p,'/');}
	return $p;
}

function fileSql($pre=''){
	$g = (PICTAP->db_type=='pgsql')? 'STRING_AGG' : 'GROUP_CONCAT';
	$c = (PICTAP->db_type=='mysql')? ' SEPARATOR' : ',';
	$p = PICTAP->db_prefix;
	return "SELECT f.*, l.location, s.state, c.country, v.dev, $g(t.tag$c ', ') AS k $pre
	FROM ".$p."files f
	INNER JOIN ".$p."dirs d USING(dirid)
	LEFT JOIN ".$p."locations l USING(locationid)
	LEFT JOIN ".$p."states s USING(stateid)
	LEFT JOIN ".$p."countries c USING(countryid)
	LEFT JOIN ".$p."devs v USING(devid)
	LEFT JOIN ".$p."tagfiles tf USING(fileid)
	LEFT JOIN ".$p."tags t USING(tagid) ";
}




function thumb_name($path, $size=0){
	if(!$size){$size = PICTAP->thumb_size;}
	if($size == PICTAP->thumb_size){
		$size = '';
	}else{
		$size = $size . '/';
	}
	return ('/'. $size . splitExt(ltrim($path, '\/'))[0].'.webp');
}




function del_thumb($p){
	$t = PICTAP->path_thumbs . thumb_name($p);
	if(file_exists($t)) {
		logger("Removed Thumb: $p", 2);
		@unlink($t);
	}
}

function splitExt($path){
	$basename = basename($path);
	$n = strrpos($basename,".");
	if($n === false){
		return [ $path, $basename, '', dirnm($path)];
	}
	return [
		substr($path, 0, strrpos($path, ".")),	//[0] full path no ext
		substr($basename,0,$n),					//[1] filename only
		substr($basename,$n+1),					//[2] ext only
		dirnm($path)							//[3] dirname
	];
}

function is_exclude($path, $isdir){

	if(!$path || $path === PICTAP->path_pictures) return;

	if(PICTAP->exclude_dirs) {
		$dirname = $isdir ? $path : dirnm($path);
		if($dirname !== PICTAP->path_pictures
			&& preg_match(PICTAP->exclude_dirs, relative($dirname))
		){
			return true;
		}
	}
	if(!$isdir){
		$basename = basename($path);
		if(PICTAP->exclude_files && preg_match(PICTAP->exclude_files, $basename)) return true;
	}else{
		foreach(['thumbs','recycle','data','shared'] as $s){
			if($path === PICTAP->{'path_'.$s}){
				return true;
			}
		}
	}
}







function locker($timeout = 0) {
	static $lock = null;

	if ($timeout) { // Lock
		$start = time();
		$end = $start + $timeout;

		while (!$lock && time() < $end) {
			$lock = fopen(PICTAP->path_data .'/php.lock', 'w');
			if ($lock !== false &&!flock($lock, LOCK_EX | LOCK_NB)) {
				fclose($lock);
				$lock = null;
				usleep(50000); //0.05 seconds
			}
		}
		if ($lock) {
			return 1;
		}
	} else { // Unlock
		if ($lock) {
			flock($lock, LOCK_UN);
			fclose($lock);
			$lock = null;
		}
	}
	return 0;
}

function userCan($cando, $role=null) {
	$r = ($role === null)? USER->role : $role;
	$o = ((intval($r) & ROLES->$cando) === ROLES->$cando)? 1 : 0;
	return $o;
}

function roleList($role=null){
	$x=[];
	foreach(explode(',','admin,alldir,login,family,upload,search,edit,rename,move,delete,album,newdir') as $k=>$v){
		$c = 1 << $k;
		if($role !== null){
			$c = ((($role & $c) === $c)? 1 : 0);
		}
		$x[$v] = $c;
	}
	return $x;
}

function flushIP($days=-1){
	if($days<0){
		$days = PICTAP->login_block_days;
	}
	if(!$days){@unlink(PICTAP->path_data.'/failed_logins.log');}
	$days=strtotime('-'.$days.' days');
	$ip=PICTAP->path_data . '/badip';
	makeDir($ip);
	$mydir = dir($ip);
	while(($file = $mydir->read()) !== false){
		if (!($file=='.' || $file=='..' )){
			if(@filemtime($ip."/".$file) < $days){
				@unlink($ip."/".$file);
			}
		}
	}
	$mydir->close();
}


function procUpload(){
	$dirpath = PICTAP->path_pictures;
	$upmode = get('mode');
	if($upmode === ''){$upmode = 1;}//auto rename
	$upmode = intval($upmode);
	$id = get('updir');
	if($id){
		if(!($id = getDirRow($id))){
			return [[0,"Invalid id ".get('updir')]];
		}
		$dirpath = rtrim($dirpath.'/'.$id['dir'],'/');
	}else{
		if(file_exists($dirpath.'/'.USER->user)){
			$dirpath .= '/'.USER->user;
		}
	}
	if(($id = getDirID(relative($dirpath),0)) === false){return [[0,"Database Error"]];}
	$rt=[];
	$file = !empty($_FILES['media']) && is_array($_FILES['media']) ? $_FILES['media'] : false;

	if(empty($file) || !isset($file['error']) || !is_array($file['error']) || count($file['error'])<1){
		return [[0,"Upload invalid files"]];
	}
	$count = count($file['name']);

	for ($i = 0; $i < $count; $i++) {

		if($file['error'][$i] !== UPLOAD_ERR_OK) {
			$upload_errors = [
				UPLOAD_ERR_INI_SIZE		=> 'UPLOAD_ERR_INI_SIZE',
				UPLOAD_ERR_FORM_SIZE	=> 'UPLOAD_ERR_FORM_SIZE',
				UPLOAD_ERR_PARTIAL		=> 'UPLOAD_ERR_PARTIAL',
				UPLOAD_ERR_NO_FILE		=> 'UPLOAD_ERR_NO_FILE',
				UPLOAD_ERR_NO_TMP_DIR	=> 'UPLOAD_ERR_NO_TMP_DIR',
				UPLOAD_ERR_CANT_WRITE	=> 'UPLOAD_ERR_CANT_WRITE',
				UPLOAD_ERR_EXTENSION	=> 'UPLOAD_ERR_EXTENSION'
			];
			$rt[]=[0, isset($upload_errors[$file['error'][$i]]) ? $upload_errors[$file['error'][$i]] : 'UPLOAD_ERR_UNKNOWN'];
			continue;
		}

		if(!isset($file['size'][$i]) || empty($file['size'][$i])){
			$rt[]=[0, 'invalid file size'];
			continue;
		}
		$filename = sanitise_name($file['name'][$i]);
		$p = splitExt($filename);
		$ext = $p[2];
		if( (in_array($ext, PICTAP->ext_nouploads) && !userCan('admin')) ||
			!in_array($ext, PICTAP->ext_images) &&
			!in_array($ext, PICTAP->ext_videos) &&
			!in_array($ext, PICTAP->ext_uploads) &&
			!in_array('*', PICTAP->ext_uploads)
		){
			$rt[]=[0, $ext.' file not allowed'];
			continue;
		}
		$overwritten = '';
		if(file_exists($dirpath.'/'.$filename)){
			if($upmode){
				if($upmode === 2){
					$overwritten = ' Overwritten';
				}else{
					$overwritten = ' Auto Renamed';
					$c = 1;
					do {
						$filename = $p[0] . '_' . $c . '.' . $p[2];
						$c++;
					} while (file_exists($dirpath.'/'.$filename));
				}
			}else{//skip
				$rt[]=[1, 'Skipped: '.$filename,0,0];
			continue;
			}
		}
		ignore_user_abort(true);
		$res = move_uploaded_file($file['tmp_name'][$i], $dirpath.'/'.$filename);
		if($res){
			$mtime=get('mtime');
			if(!$mtime){
				$t=fileDate($filename);
				if($t){
					$mtime = $t*1000;
				}
			}
			if($mtime){
				touch($dirpath.'/'.$filename, round(intval($mtime)/1000));
			}
		}
		if($res){
			$rt[]=[1,"Uploaded:".$overwritten." ".relative($dirpath.'/'.$filename), $id['dirid'], $filename];
			continue;
		}else{
			$rt[]=[0,"Failed to upload ".$filename];
			continue;
		}
	}
	scanFolder($dirpath);
	return $rt;
}

function post($key, $strict = false) {
	return isset($_POST[$key]) && is_string($_POST[$key]) ? $_POST[$key] : ($strict ? null : '');
}

function get($key) {
	return isset($_GET[$key]) && is_string($_GET[$key]) ? $_GET[$key] : '';
}


function sendJSON($json){
	if(empty($json)){return sendjs(0,'NoData');}
	$json = json_encode($json, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
	if(empty($json)) sendjs(0, json_last_error() ? json_last_error_msg() : 'Error: json_encode()');
	if(logger()){logger($json);die;}
	header('content-type: application/json');
	die($json);
}

function sendjs($status,$msg,$d=[]){
	$d['ok'] = $status;
	$d['msg'] = $msg;
	sendJSON($d);
}

function error($msg, $code = 0){
	locker();//unlocks
	if(logger($msg)) {//cli?
		exit;
	}
	if($code){http_response_code($code);}
	header('Content-Type: text/html; charset=UTF-8');
	header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
	header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0, s-maxage=0');
	header('Cache-Control: post-check=0, pre-check=0', false);
	header('Pragma: no-cache');
	exit('<h2>Error</h2>' . $msg);
}

function thumbOk(&$r,$thm=0,$mtime=0){
	if(updateFile($r['fileid'], ['th'=>0]) === false){return false;}
	$r['th'] = 0;
	if($mtime && $thm){
		touch($thm,$mtime);
	}
	return $thm;
}

function makeThumb(&$r, $forcethumb = 0){
	// Early return if not an image or video
	if(!in_array((int)$r['ft'], [1, 2])){
		return thumbOk($r);
	}
	$rp = joinp($r['dir'],$r['file']);
	$org = PICTAP->path_pictures . $rp;
	$mtime = @filemtime($org);
	if(!$mtime){//no File
		delFileRow($r);
		return 0;
	}
	$ts = PICTAP->thumb_size;
	$size = $ts; //multiple sizes here

	$nobad = PICTAP->skip_corrupt;
	$thm = PICTAP->path_thumbs . thumb_name($rp, $size);
	makeDir(dirnm($thm));
	if(file_exists($thm)){
		if(filemtime($thm) === $mtime && ($forcethumb == 0 /*|| $r['th']==0 */ )){
			return thumbOk($r,$thm);
		}
		unlink($thm);
	}

	$retErr = function($m) use ($r, $thm, $mtime, $org) {
		logger("Error: $org - $m");
		return PICTAP->skip_corrupt ? thumbOk($r, $thm, $mtime) : 0;
	};

	if(!(int)$r['w']){
		return $retErr("Not a valid image/video");
	}

	openDb(-1); //close db while thumb gen

	makeDir(dirnm($thm));
	try {
		if ((int)$r['ft'] === 2) {
			return genThumbVid($org, $thm, $ts, $r, $mtime);
		} else {
			return genThumbImg($org, $thm, $ts, $r, $mtime, $retErr);
		}
	} finally {
		locker();
	}
}


function genThumbVid($org, $thm, $ts, &$r, $mtime) {
	$cmd = ((PHP_SHLIB_SUFFIX==='dll')?'':'nice -n 19 ').escapeshellarg(PICTAP->bin_ffmpeg).' -y -hide_banner -ss 0 -t 7 -threads 1 -i ' . escapeshellarg($org) . ' -threads 1 -an -vf "fps=2,scale=iw*sar:ih,scale=w='.$ts.':h='.$ts.':force_original_aspect_ratio=decrease,setsar=1:1" -loop 0 -quality 40 ' . escapeshellarg($thm) . ' 2>&1';

	$output = [];
	$failed = 1;

	if (logger() || locker(30)) {
		ignore_user_abort(true);
		exec($cmd, $output, $failed);
		locker(); // Unlock
	} else {
		return 0;
	}
	if ($failed) {
		logger("makeThumb Error $failed $cmd\n" . print_r($output, true));
		if (!PICTAP->skip_corrupt) return 0;
	}
	return thumbOk($r, $thm, $mtime);
}

function genThumbImg($org, $thm, $ts, &$r, $mtime, $retErr) {
	$imginfo = getimagesize($org);
	if(empty($imginfo) || !is_array($imginfo)){
		return $retErr("getimagesize() failed");
	}

	if(PICTAP->max_mp && $imginfo[0] * $imginfo[1] > PICTAP->max_mp){
		$h = round(($ts/4)*3);
		exec(((PHP_SHLIB_SUFFIX==='dll')?'':'nice -n 19 ').escapeshellarg(PICTAP->bin_ffmpeg).' -y -hide_banner -threads 1 -i ' . escapeshellarg($org) . ' -threads 1 -vf scale='.$ts.':'.$h.' ' . escapeshellarg($thm) . ' 2>&1');
		return $retErr("Exceeds max_mp");
	}

	$ratio = max($imginfo[0], $imginfo[1]) / $ts;
	$width = round($imginfo[0] / $ratio);
	$height = round($imginfo[1] / $ratio);

	$image = match($imginfo[2]) {
		IMAGETYPE_JPEG => imagecreatefromjpeg($org),
		IMAGETYPE_PNG => imagecreatefrompng($org),
		IMAGETYPE_GIF => imagecreatefromgif($org),
		IMAGETYPE_WEBP => imagecreatefromwebp($org),
		IMAGETYPE_BMP => imagecreatefrombmp($org),
		IMAGETYPE_AVIF => imagecreatefromavif($org),
		default => null
	};
	if(!$image){
		return $retErr("Unsupported image type");
	}
	$new_image = imagecreatetruecolor($width, $height);

	if(!imagecopyresampled($new_image, $image, 0, 0, 0, 0, $width, $height, $imginfo[0], $imginfo[1])){
		imagedestroy($image);
		return $retErr("imagecopyresampled() failed");
	}
	imagedestroy($image);

	if(!empty($r['ori'])){
		$rotate = [0,0,0,180,180,270,270,90,90][$r['ori']] ?? 0;
		$mirror = [0,0,1,0,1,1,0,1,0][$r['ori']] ?? 0;
		if ($rotate) {
			$new_image = imagerotate($new_image, $rotate, 0);
		}
		if ($mirror) {
			imageflip($new_image, IMG_FLIP_HORIZONTAL);
		}
	}

	$matrix = [ [-1, -1, -1], [-1, 20, -1], [-1, -1, -1] ];
	$divisor = array_sum(array_map('array_sum', $matrix));
	imageconvolution($new_image, $matrix, $divisor, 0);

	if(!imagewebp($new_image, $thm, PICTAP->thumb_quality)){
		imagedestroy($new_image);
		return $retErr("imagewebp() failed");
	}
	imagedestroy($new_image);
	return thumbOk($r, $thm, $mtime);
}

function fileDate($t){
	$t = substr(preg_replace("/[^0-9]/", '', $t.'000000'),0,14);
	if(strlen($t)===14 && str_starts_with($t,'2')){
		$t=strtotime($t);
		if($t && $t>946684800 && $t < time()){//2000+
			return $t;
		}
	}
	return 0;
}

function makeDir($path){
	return (is_dir($path) || mkdir($path, 0777, true) || error("Failed to create dir ".$path, 500));
}

function ignoreVars($setup){
	$pf = ''; $sf = '';
	if($setup->db_type=='pgsql'){
		$sf = 'ON CONFLICT DO NOTHING';
	}else{
		$pf = 'IGNORE ';
		if($setup->db_type=='sqlite'){$pf = 'OR '.$pf;}
	}
	return [$pf,$sf];
}

function qdate($key, $format) {
	return match(PICTAP->db_type) {
		'pgsql' => "TO_CHAR(TO_TIMESTAMP($key), '".str_replace(['%Y','%m','%d'],['YYYY','MM','DD'],$format)."')",
		'mysql' => "DATE_FORMAT(FROM_UNIXTIME($key), '$format')",
		default => "strftime('$format', datetime($key, 'unixepoch'))"
	};
}

function cacheHdr(){
	header('Pragma: cache');
	$sec = 86400*7;
	header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $sec) . ' GMT');
	header("Cache-Control: public, max-age=$sec, s-maxage=$sec, immutable");
}

function getUser($u){
	$u = strtolower($u);
	if($u){
		foreach(PICTAP->users as $id=>$user) {
			if(strtolower($user[0]) === $u && $id>0) {
				return (object)['id'=>(int)$id,'user'=>$user[0],'pass'=>$user[1],'hash'=>$user[0].'-'.md5($user[1] . $user[2] ),'role'=>$user[3]];
			}
		}
	}
	return null;
}

function saveLogin($f){
	return (makeDir(dirnm($f)) && file_put_contents($f,''));
}

function userAuth($html=0){
	if(!empty(PICTAP->timezone)){date_default_timezone_set(PICTAP->timezone);}
	if(logger()) {
		$user = getUser(PICTAP->users[1][0]);
		define('USER', $user);
		return;
	}

	if($html && !file_exists(PICTAP->path_pictures)) {
		error('path_pictures missing.');
	}

	$c=isset($_COOKIE[PICTAP->cookie]) && is_string($_COOKIE[PICTAP->cookie]) ? $_COOKIE[PICTAP->cookie] : '';
	[$user] = explode('-', $c . '');

	$user = getUser($user);
	$hash = ($user)? $user->hash : null;

	$authfile = PICTAP->path_data .'/auth/'.$hash.'.txt';

	if($c !== $hash){
		if($html){
			$u = post('username');
			$u = is_string($u)? trim($u) : '';

			$p = post('password');
			$p = is_string($p)? $p : '';

			$attempt = $u && $p;
			if($attempt){
				$user = getUser($u);
				if($user && strlen($user->pass) < 32 &&	$p == $user->pass){
					//admin password set in config.php manually, change it in config.php
					$f = clone PICTAP;
					$z = $f->users[$user->id];
					$k=usergen($z[0],$p,$z[2],$z[3],$f);
					$f->users[$user->id]=$k;
					$f = myConfig((array)$f);
					if(!userUpdate(PICTAP, $user->id, $k)){error('Password update error.', 500);}
					error('Password updated.', 401);
				}
				$q = md5($p . PICTAP->salt );

				$ip = PICTAP->path_data . '/badip/'.str_replace(':','.',$_SERVER['REMOTE_ADDR']);
				$bip = $ip.'.txt';
				$iplist=file_exists($bip);
				$valid = $user && $q === $user->pass;
				if(!$iplist && $valid){
					$authfile = PICTAP->path_data .'/auth/'.$user->hash.'.txt';
					setcookie(PICTAP->cookie, $user->hash, time()+60*60*24*PICTAP->login_remember, "/");
					saveLogin($authfile);
				} else {
					if($valid){$u=$p='*';}
					$t='['.date("Y-m-d H:i:s").'] ['.$_SERVER['REMOTE_ADDR'].'] '.$u.' - '.$p.' '.htmlspecialchars($_SERVER['HTTP_USER_AGENT'])."\n";
					@file_put_contents(PICTAP->path_data.'/failed_logins.log', $t, FILE_APPEND);
					if(!$iplist){
						@file_put_contents($ip, ' ', FILE_APPEND);
						if(intval(@filesize($ip)) > PICTAP->login_attempts){
							@unlink($ip);
							@file_put_contents($bip, '', FILE_APPEND);
						}
					}
					login_page($attempt);
				}
			} else {
				login_page($attempt);
			}
		} else if(post('task')){
			sendjs(0,'login');

		} else {
			error('You are not logged in.', 401);
		}
	}else{
		if($html && $user && userCan('login',$user->role) && !file_exists($authfile)){
			saveLogin($authfile);
		}
	}

	if($user && !userCan('login',$user->role)){
		if(file_exists($authfile)){@unlink($authfile);}
		error('Account disabled', 401);
	}

	if(post('logout') && $user){
		setcookie(PICTAP->cookie, '', time() - 3600, '/');
		@unlink($authfile);
		if(post('logout')!=='1'){
			$setup = clone PICTAP;
			$setup->users[$user->id][2] = bin2hex(random_bytes(4));
			myConfig((array)$setup);
		}
		sendjs(0,'login');
	}

	define('USER', $user);
}

function userform($id,$user){
	$req = ' ';
	$ul = 'User';
	if($user[0]){
		$req = ' required';
		$ul = $user[0];
		$nu = $user[0];
	}else{
		$nu = 'Add New User';
	}
	$out = '<details class="accounts"><summary>'.$id.': <b>'.$nu.'</b></summary><div>'.itext('<i>Username:</i>','user'.$id, $user[0], 'Username, Clear to Delete a user', ' maxlength="16" pattern="^[A-Za-z0-9]*$"');
	$out .= itext('<i>Password:</i>', 'pass'.$id, $user[1], 'Password', $req.' maxlength="32"');

	if($id != '1'){
		$perm = (userCan('alldir',$user[3]) << 1) | userCan('family',$user[3]);
		$u = '/'.$ul;
		$out .= '<label><i>Permission:</i><br/><select name="perm'.$id.'">';
		foreach([$u.' Folder',$u.' + /Family Folders','All Folders'] as $i=>$t){
			$chk = ($perm == $i)? ' selected' : '';
			if($i === 1 && PICTAP->family_dir == ''){$chk = ' disabled';}
			$out.='<option value="'.$i.'"'.$chk.'>'.$t.'</option>';
		}
		$out .= '</select></label>';
	}

	foreach(ROLES as $r=>$n){
		if(in_array($r,['family','alldir'])){continue;}
		$out .= itick($r.$id, userCan($r,$user[3]), ($id == '1' && in_array($r,['admin','login'])), ucfirst($r));
	}

	$out .= '</div></details>';
	return $out;
}

function itick($name, $chk=0, $dis=0, $title=null, $type='checkbox'){
	$title = $title === null ? ucfirst($name) : $title;
	$chk = $chk ? ' checked' : '';
	if($dis){$chk.=' disabled';}
	return '<label class="togg"><input type="'.$type.'" name="'.$name.'" value="1"'.$chk.'> '.$title.'</label> ';
}

function iselect($label, $name, $val, $opts){
	$o= '<label><i>'.$label.':</i> '.$name.'<br/><select id="'.$label.'" name="'.$label.'">';
	foreach($opts as $k=>$t){
		$chk = ($val == $k)? ' selected' : '';
		$o.='<option value="'.$k.'"'.$chk.'>'.$t.'</option>';
	}
	$o .= '</select></label>';
	return $o;
}

function itext($label, $name, $val, $ph='', $attr='', $type='text'){
	$o = '<p><label>'.$label;
	if($label !== ''){$o .= '<br/>';}
	$o .= '<input type="'.$type.'" id="'.$name.'" name="'.$name.'" value="'.$val.'" placeholder="'.$ph.'"'.$attr.'></label></p>';
	return $o;
}

function usergen($u,$p,$t,$r,$s){
	$u = preg_replace("/[^A-Za-z0-9]/", '', $u);
	if(strlen($p) < 32){
		$p = md5($p.$s->salt);
	}
	return [$u,$p,$t,(int)$r];
}

function userUpdate($setup,$id,$u,$insert=0){
	[$pf, $sf] = ignoreVars($setup);
	$t = $setup->db_prefix . 'users';
	$i = "INSERT $pf INTO $t (userid,username,password,token,role) VALUES ($id, ?, ?, ?, ?) $sf;";
	$p = "UPDATE $t SET username = ?, password = ?, token = ?, role = ? WHERE userid = $id;";
	if(!($dbo=openDb($setup)) ||
	($insert && $dbo->run($i, $u, 0, __LINE__) === false)){return false;}
	if($dbo->run($p, $u, 0, __LINE__) === false){return false;}
	return true;
}

function pageAccounts(){
	$setup = clone PICTAP;
	$url = strtok($_SERVER['REQUEST_URI'], '?');

	$ispost = post('submit');
	$out='';
	$maxid = 1;
	$iserr = 0;
	if($ispost){
		$allu=[];
		if($setup->family_dir){
			$allu[$setup->family_dir] = 0;
		}
		foreach($setup->users as $id=>$u){
			$allu[$u[0]]=(int)$id;
		}
		$idlist = explode(',', post('idlist'));
		$msg = '<div style="text-align:center">';
		if(!($dbo = openDb())){$msg .= '<h3>openDb error</h3>';$idlist=[];$iserr = 1;}
		foreach($idlist as $id){
			if(!is_numeric($id)){break;}
			$id = (int)$id;
			$role = 0;
			$af = intval(post('perm'.$id));
			$_POST['alldir'.$id]=''.(($af >> 1) & 0x1);
			$_POST['family'.$id]=''.(($af & 0x1));
			foreach(ROLES as $r=>$n){
				if(post($r.$id)
					|| ($id === 1 && in_array($r,['admin','alldir','login']) )
				){
					$role |= $n;
				}
			}

			$tok = empty($setup->users[$id][2])? bin2hex(random_bytes(4)) : $setup->users[$id][2];
			$user = usergen(post('user'.$id), post('pass'.$id), $tok, $role, $setup);
			$ret = 0;

			if($user[0] && isset($allu[$user[0]]) && $allu[$user[0]] != $id ){
				error('Duplicate user: '.$user[0]);
			}

			if(isset($setup->users[$id])){//existing user
				if($user[0] ==''){//delete
					if($id>1){//not admin
						//transfer albums to admin
						if($dbo->run("UPDATE ".$setup->db_prefix."albums SET userid = 1 WHERE userid = ?;",[$id], 0, __LINE__) === false){error($dbo->emsg);}

						if($dbo->run("DELETE FROM ".$setup->db_prefix."users WHERE userid = ?;",[$id], 0, __LINE__) === false){error($dbo->emsg);}

						$d = 'User Deleted '.$id.': '.$setup->users[$id][0].' by: '.USER->user;
						logger($d);
						$msg .= '<h3>'.$d.'</h3>';
						unset($setup->users[$id]);
					}
				}else{//update
					$setup->users[$id] = $user;
					if(!userUpdate($setup, $id, $setup->users[$id])){$msg.='<h3>Error userUpdate</h3>';}
				}
			}else if($user[0] !==''){//create
				$setup->users[$id] = $user;
				$newp = PICTAP->path_pictures .'/'.$user[0];
				makeDir($newp);
				scanFolder($newp);
				if(!userUpdate($setup, $id, $setup->users[$id], 1)){$msg.='<h3>Error userCreate</h3>';}
				$d = 'User Created '.$id.': '.$setup->users[$id][0].' by: '.USER->user;
				logger($d);
				$msg .= '<h3>'.$d.'</h3>';
			}
		}
		if($iserr || !myConfig((array)$setup)){
			$msg .= '<h3>Error Saving</h3>';
		}else{
			$msg .= '<h3>Saved sucessfully</h2><h3><a href="'.$url.'">View Gallery</a></h3>';
		}
		if(post('ipclear')){
			flushIP(0);
			$msg .= '<h3>IP Cleared</h3>';
		}
		$out = $msg.'</div>'.$out;
	}

	$idlist=[];
	foreach($setup->users as $id=>$user){
		if($id < 1){continue;}
		$maxid = max((int)$id,$maxid);
		$out .= userform($id,$user);
		$idlist[]=$id;
	}

	$id = $maxid + 1;
	$out .= userform($id, ['','','',(0xfff ^ ROLES->admin ^ ROLES->alldir ^ ROLES->family)]);
	$idlist[]=$id;

	$out .= '<input type="hidden" name="idlist" value="'.implode(',',$idlist).'">';

	$blocked = @file_get_contents(PICTAP->path_data.'/failed_logins.log');
	$fl = 'is Empty';
	if($blocked){
		$fl = itick('ipclear', 0, 0, 'Clear');
	}else{
		$blocked='';
	}
	$out .= '<details class="accounts"><summary><b>Failed Login Log '.$fl.'</b></summary><pre style="text-wrap:wrap">'.$blocked.'</pre></details>';
	$out .='<p style="padding:1em"><a class="btn" style="max-width:170px;font-size:14px" href="'.$url.'">Close</a>';
	$out .='<input style="max-width:170px;font-size:14px" class="btn default" type="submit" name="submit" value="Save"><p>';
	$out = '<form method="post" action="'.$url.'?page=accounts" name="login" autocomplete="off">'.$out.'</form>';

	htmldoc('<h1 style="text-align:center">Accounts</h1>'.$out);
}

function pageConfig($oldsetup){
	$url = strtok($_SERVER['REQUEST_URI'], '?');
	$out='<form style="padding:1em" method="post" action="'.$url.'?page=settings" name="login" autocomplete="off" onsubmit="_id(\'ssav\').classList.add(\'loader\');">';

	$input=['db_setup'=>0,'users'=>['1'=>['Admin','','',0xfff]]]; //id => [user, pass, token, role]

	$setup = clone $oldsetup;
	if($setup->version[1]){
		if($setup->version[1] == '1,1'){//db migration
			$setup->db_setup = 1;
			$setup->family_dir = $setup->users[0][0];
			unset($setup->users[0]);
		}
		if($setup->db_setup){
			$input['db_setup'] = 1;
			$input['users'] = $setup->users;
		}
	}

	$rp = dirnm(__FILE__).'/';
	$pp = $rp.'pictures';
	$tp = $rp.'thumbs';
	$db = $rp.'data';
	$sp = $rp.'shared';
	$tz = DateTimeZone::listIdentifiers();
	$tz = array_combine($tz, $tz);

	$defs = [ //[type, default, required, title, extra]
		'admin_user'	=> ['text', $input['users'][1][0], 1, 'Main user','pattern="^[A-Za-z0-9]*$"'],
		'admin_pass'	=> ['text', $input['users'][1][1], 1, 'Less then 32 chars'],
		'db_type'		=> ['select', 'sqlite', 1, 'Database Engine',['sqlite'=>'Sqlite 3','mysql'=>'MySQL','pgsql'=>'PostgreSQL']],
		'db_host' 		=> ['text', 'localhost', 0, 'database hostname or unix-socket path'],
		'db_port' 		=> ['number', 0, 0, 'Port number 3306 for mysql, 5432 for pgsql'],
		'db_user' 		=> ['text', 'root', 0, 'Database username'],
		'db_pass' 		=> ['text', '', 0, 'Database password'],
		'db_name' 		=> ['text', 'database1', 0, 'Database name'],
		'db_schema' 	=> ['text', '', 0, 'Database schema for pgsql, empty to keep default'],
		'db_prefix' 	=> ['text', '', 0, 'Tables prefix, eg. pic_ or leave empty','pattern="^[a-z0-9_]*$"'],
		'db_file' 		=> ['text', $db .'/pictap.db', 1, 'Sqlite database file eg. '.$db .'/pictap.db'],
		'path_pictures' => ['text', $pp, 1, 'Main folder path eg. '.$pp],
		'url_pictures' 	=> ['text', reltiveroot($rp.'pictures'), 1, 'Full or relative url of main pictures folder'],
		'path_thumbs' 	=> ['text', $tp, 1, 'Thumb folder path eg. '.$tp],
		'url_thumbs' 	=> ['text', reltiveroot($rp.'thumbs'), 1, 'Full or relative url of thumbs folder'],
		'path_shared' 	=> ['text', $sp, 1, 'Shared Albums folder path eg. '.$sp],
		'url_shared' 	=> ['text', reltiveroot($rp.'shared'), 1, 'Full or relative url of shared folder'],
		'path_recycle' 	=> ['text', '', 0, 'Trash path (leave blank to disable) eg. '.$rp.'trash'],
		'path_data' 	=> ['text', $db, 1, 'folder path to store login tokens, can be anywhere outside public folder eg. '.$db],
		'family_dir' 	=> ['text', 'Family', 0, 'Name of shared folder eg. Family, leave blank to disable','pattern="^[A-Za-z0-9]*$"'],
		'thumb_quality' => ['number', 80, 1, '100 is max quality, 80 is good'],
		'thumb_size' 	=> ['number', 200, 1, 'Thumbnail size'],
		'bin_ffmpeg' 	=> ['text', 'ffmpeg', 1, 'eg. ffmpeg or /usr/bin/ffmpeg'],
		'bin_exiftool' 	=> ['text', 'exiftool', 1, 'eg. exiftool or /usr/bin/exiftool'],
		'bin_jpegtran' 	=> ['text', 'jpegtran', 1, 'eg. jpegtran or /usr/bin/jpegtran'],
		'login_remember' => ['number', 90, 1, 'Days to Remember Login'],
		'login_attempts' => ['number', 5, 1, 'Block IP after Failed Attempts'],
		'login_block_days' => ['number', 7, 1, 'Days to ban ip for'],
		'ext_images' 	=> ['text', 'jpg,jpeg,png,gif,webp,bmp,avif,tiff,tif,wbmp,xbm', 1, 'eg. jpg,png'],
		'ext_videos' 	=> ['text', 'mp4,m4v,m4p,webm,ogv,mkv,avi,mov,wmv,mpg,mpeg,vob', 0, 'eg. mp4,mov'],
		'ext_uploads' 	=> ['text', 'pdf,doc,docx,txt', 0, 'Allowed extra upload types eg. pdf,txt or * for all'],
		'ext_nouploads' => ['text', 'php,pl,cgi,sh', 0, 'Disable upload types for non admin eg. php,pl,cgi,sh'],
		'exclude_dirs' 	=> ['text', '', 0,'Regex for preg_match eg. <code>/\/bank|\/house(\/|$)/i</code> to exclude bank* or /house/* dirs'],
		'exclude_files' => ['text', '', 0,'Regex for preg_match eg. <code>/\.(gif|png)$/i</code> to exclude .gif/.png files'],
		'folder_thumb' 	=> ['text', '', 0,'Default Folder Thumbnail (optional) eg. folder.jpg'],
		'timezone'		=> ['select', date_default_timezone_get(), 1, 'Local Timezone',$tz],
		'cookie'		=> ['text', 'pictap', 1, 'Login Cookie (in case of multi install)','pattern="^[A-Za-z0-9]*$"'],
		'search_max_results' 	=> ['number', 1000, 1, ''],
		'auto_hide_slideshow_ui'=> ['number', 0, 1,'0 = disable, 4 = after 4 sec ...'],
		'max_mp' 		=> ['number', 6000 * 5000, 1,'Width x Height, Larger images may not get thumbnails'],
		'auto_rename' 	=> ['tick', 0, 1, 'Auto Rename IMG_/VID_date_time.* to date_time.*'],
		'skip_corrupt' 	=> ['tick', 1, 1, 'Do not attempt to regenerate thumbnails that have already failed once.'],
		'debug'			=> ['select', '2', 1, 'Debug Level',['0'=>'0: Off','1'=>'1: Verbose (All)','2'=>'2: Info (Some)','3'=>'3: Warnings only']],
		'debug_file' 	=> ['text', $db.'/debug.log', 0,'Debug file (optional) eg. '. $db.'/debug.log'],
	];
	$dbhash = '';
	foreach ($defs as $k=>$v){
		if(!isset($setup->{$k})){
			$setup->{$k} = $v[1];
		}
		if(str_starts_with($k,'db')){
			$dbhash .= '#'.$setup->{$k};
		}
	}
	if(post('submit')){
		$dbhashn = '';
		$setuporg = clone $setup;
		foreach ($defs as $k=>$v){
			if($setup->db_setup && in_array($k,['admin_user','admin_pass'])){continue;}
			$p = post($k,1);
			if($p === null){$p = $v[1];}
			$i = trim($p);
			if($v[0] === 'number'){$i = (int)$i;}
			if($v[0] === 'tick'){$i = $i? 1 : 0;}
			$input[$k]=$i;
			if(str_starts_with($k,'db')){$dbhashn .= '#'.$i;}
		}
		$dpf = preg_replace("/[^a-z0-9_]/", '', $input['db_prefix']);
		$input['debug'] = (int)$input['debug'];
		$input['db_prefix'] = $dpf;
		$input['salt'] = $setup->salt;
		foreach(['pictures','thumbs','recycle','data','shared'] as $s){
			$i = rtrim(str_replace('\\','/',$input['path_'.$s]),'/');
			$input['path_'.$s] = $i;
			if($i){
				if(!(is_dir(dirnm($i)) && makeDir($i))){
					error('Failed to create '.$i);
				}
			}
		}

		foreach(['images','videos','uploads','nouploads'] as $s){
			$input['ext_'.$s] = explode(',',preg_replace("/[^a-z0-9_\*,]/", '', $input['ext_'.$s]));
		}

		$input['family_dir'] = preg_replace("/[^A-Za-z0-9]/", '', $input['family_dir']);
		if($input['family_dir']){
			$np = $input['path_pictures'] .'/'.$input['family_dir'];
			makeDir($np);
		}

		if(!$setup->db_setup){
			$input['users'][1] = usergen($input['admin_user'], $input['admin_pass'], bin2hex(random_bytes(4)), 0xfff, $setup);
			if(!$input['users'][1][0] || !$input['admin_pass']){die('invalid user/pass');}
			unset($input['admin_user']);
			unset($input['admin_pass']);
		}

		$input['version'] = PIC_VER;
		//for future db upgrades
		$oldver = $setup->version[1]; //config

		$msg = '';
		$setup = myConfig($input);
		if($setup){
			$setup = (object) $setup;

			if($oldver=='1,1'){//db migration
				if(!($dbo = openDb($setup))){error('openDB Error');}
				$dbo->exec('ALTER TABLE config RENAME COLUMN key TO id');
				$input['db_setup'] = 1;
				$setup = myConfig($input);
				$setup = (object) $setup;
				$dbo=openDb(-1);
			}
			if(!defined('PICTAP')){define( 'PICTAP', $setup );}
			if(!$setup->db_setup || $dbhashn != $dbhash){
				$tf = $setup->path_data .'/tables-'.$setup->db_type.'.sql';
				$t = @file_get_contents($tf);
				$gf = $setup->path_data .'/gps.sql';
				$g = @file_get_contents($gf);
				if(!$t){error('missing '.$tf);}
				if(!$g){error('missing '.$gf);}
				[$pf, $sf] = ignoreVars($setup);
				$g = str_replace(['/*#*/', '/*P*/', '/*S*/'], [$dpf, $pf, $sf], $t.$g);
				if(!($dbo = openDb($setup))){error('openDB Error');}
				try {
					$dbo->exec($g);
				}catch(Exception $e) {
					error('Table Error: '. $e->getMessage());
				}
				if(!userUpdate($setup, 1, $setup->users[1],1)){
					$msg.='<h3>Error userCreate</h3>';
				}else{
					$input['db_setup']=1;
				}
				$setup = (object) myConfig($input);
				$msg .= '<div>Tables Created</div>';
			}
			if(($dbo = openDb($setup))){
				foreach ($setup as $k=>$v){
					if($k=='users'){continue;}
					$q = " INTO ".$dpf."config (id, value) VALUES (?, ?)";
					if($setup->db_type=='pgsql'){
						$q = 'INSERT'.$q.' ON CONFLICT (lower(id)) DO UPDATE SET value=excluded.value';
					}else{
						$q = 'REPLACE'.$q;
					}
					if(is_array($v)){$v = json_encode($v,JSON_UNESCAPED_SLASHES);}
					if($dbo->run($q, [$k, $v], 0, __LINE__) === false){$msg.=$dbo->emsg;}
				}
				$msg .= '<h3>Saved sucessfully</h2><h3><a href="'.$url.'">View Gallery</a></h3>';
			}else{
				$msg.='<h3>Error openDB</h3>';
			}
		}else{
			error($msg.'Error Saving');
		}
		$out = $msg.$out;
	}

	foreach ($defs as $k=>$v){
		if(in_array($k,['admin_user','admin_pass']) && $setup->db_setup){continue;}

		$sv = property_exists($setup,$k) ? $setup->$k : $v[1];
		if(is_array($sv)){
			$sv = implode(',',$sv);
		}
		if($v[0] === 'select'){
			$out .= '<p>'. iselect($k, $v[3], $sv, $v[4]) .'</p>';
		}else if($v[0] === 'tick'){
			$out .= '<p>'. itick($k, $sv, 0, $v[3]) .'</p>';
		}else{
			$req = $v[2] ? ' required':'';
			if(isset($v[4])){$req .= ' '.$v[4];}
			$out .= itext('<i>'.$k.':</i> '. $v[3], $k, $sv, $v[1], $req, $v[0]);
		}
	}

	if($setup->db_setup){
		$out .='<a class="btn" style="max-width:170px;font-size:14px" href="'.$url.'">Close</a>';
	}

	$out .='<button type="submit" disabled class="hide" aria-hidden="true"></button><button style="max-width:170px;font-size:14px" class="btn default" type="submit" name="submit" value="Save" id="ssav">Save</button>';
	$out .= '</form>';
	htmldoc('<h1 style="text-align:center">Settings</h1>'.$out,'','dbSetup(0);');
}




function albumLinks($act, $fid, $alb=0){
	if (empty($fid)) return false;
	$fid=is_array($fid) ? $fid : [$fid];
	$cond = "albumid = ?";
	if(!$alb){
		$p = implode(',', array_fill(0, count($fid), '?'));
		$cond = "fileid IN ($p)";
	}
	$pp = PICTAP->db_prefix;
	$q="SELECT fileid, ft, th, share, dir, file FROM {$pp}albums a
	INNER JOIN {$pp}albumfiles af USING (albumid)
	INNER JOIN {$pp}files f USING (fileid)
	INNER JOIN {$pp}dirs d USING(dirid)
	WHERE $cond AND share IS NOT NULL";
	if(!($dbo = openDb()) ||
	($stmt = $dbo->run($q, $fid, 2, __LINE__)) === false){return false;}
	$stmt = $stmt ?: [];

	foreach ($stmt as $r) {
		$sp = PICTAP->path_shared .'/'.$r['share'];
		$t = PICTAP->path_thumbs . thumb_name(joinp($r['dir'],$r['file']));
		$tl = $sp.thumb_name($r['fileid'].'t-'.$r['file']);

		$p = PICTAP->path_pictures . joinp($r['dir'],$r['file']);
		$pl = $sp.'/'.$r['fileid'].'f-'.$r['file'];

		if($act!==2){//del if not insert
			if((int)$r['ft']){@unlink($tl);}
			@unlink($pl);
		}
		if($act===1){//update
			makeDir($sp);
			$mysym = (PHP_SHLIB_SUFFIX==='dll') ? 'copy' : 'symlink';
			if((int)$r['ft'] && !(int)$r['th']){
				$mysym($t,$tl);
			}
			$mysym($p,$pl);
		}
	}
}

function remSymLinks($d,$s=0){
	if(file_exists($d) && filetype($d) === 'dir'){
		$fn = scandir($d, SCANDIR_SORT_NONE);
		foreach($fn as $f){
			if($f === '.' || $f === '..') continue;
			$fp = $d.'/'.$f;
			if(is_link($fp) || (PHP_SHLIB_SUFFIX==='dll')){
				unlink($fp);
			}
		}
		if($s){
			rmdir($d);
		}
	}
}




if(post('task')){

	userAuth();

	$task = post('task');
	$name = trim(preg_replace('/\s+/', ' ', str_replace(['\\','/'],' ',post('name'))));
	$pp = PICTAP->db_prefix;

	if($task === 'album' && userCan('album')){
		$act=post('act');
		if(!($dbo = openDb())){sendjs(0,'Db Error');}
		$aid = intval(post('aid'));
		$sp = PICTAP->path_shared.'/';
		$uf = userCan('admin')? '' : "AND (userid = ".USER->id." OR family > 0)";
		$ua=0;
		if($aid){
			if(($stmt = $dbo->run("SELECT * FROM {$pp}albums WHERE albumid = ? $uf", [$aid], 1, __LINE__)) === false){
				sendjs(0,$dbo->emsg);
			}

			if($stmt) {
				$ua = $stmt;
			}
		}
		if($act == 'edit'){
			$fam = intval(post('fam'));
			$shr = post('shr');
			if(!$name){sendjs(0,'Invalid name');}
			if($shr=='0'){
				$shr = NULL;
			}else{
				$shr = str_replace(' ','',sanitise_name($shr, 1));
			}

			$r=0;
			if(!$aid){//add
				$query = "INSERT INTO {$pp}albums (userid, name, mtime, share, family) VALUES (".USER->id.", ?, ?, ?, ?);";
			}else{//edit
				if($ua) {
					if(USER->id != $ua['userid']){ //edit by admin
						$fam = $ua['family'];
					}
					$query = "UPDATE {$pp}albums SET name = ?, mtime = ?, share = ?, family = ? WHERE albumid = $aid;";
				}else{
					sendjs(0,'Invalid id');
				}
			}
			$qa = [$name, time(), $shr, $fam];

			if(($stmt = $dbo->run($query, $qa, 0, __LINE__)) === false){
				sendjs(0, $dbo->emsg);
			}
			$lid = $aid? '' : ' - '.$dbo->lastInsertId();

			if($ua){
				if($shr && $ua['share'] && $shr !== $ua['share']){//rename dir
					@rename($sp.$ua['share'], $sp.$shr);
				}else if($ua['share'] && !$shr){//del dir
					albumLinks(0,$aid,1);
					remSymLinks($sp.$ua['share'],1);
				}else{//update
					albumLinks(1,$aid,1);
				}
			}
			$msg = 'Album Updated: '.$name.$lid;
			logger($msg,2);
			sendjs(1, $msg, ['Dir' => menuList()]);

		}else if($act == 'rema' && $aid){
			if(!$ua){sendjs(0,'Invalid request');}
			albumLinks(0,$aid,1);

			if(($fr = $dbo->run("DELETE FROM {$pp}albumfiles af WHERE af.albumid = ? AND EXISTS ( SELECT 1 FROM {$pp}albums aa WHERE aa.albumid = af.albumid $uf );", [$aid], 0, __LINE__)) === false){
				sendjs(0, $dbo->emsg);
			}
			$fr = $dbo->rowCount();
			if($ua['share']) {
				@unlink($sp.$ua['share']);
			}
			if (($r = $dbo->run("DELETE FROM {$pp}albums WHERE albumid = ? $uf", [$aid], 0, __LINE__)) === false) {
				sendjs(0, $dbo->emsg);
			}
			$msg='Album Removed: '.$name.' ('.$fr.')';
			logger($msg,2);
			sendjs(1, $msg, ['Dir' => menuList()]);

		}else if($act == 'add' || $act == 'rem'){
			$fids = array_filter(explode(',',preg_replace("/[^0-9,]/", '', post('fids'))));
			if(!$ua || !$aid || !count($fids)){sendjs(0,'Invalid request');}
			$lid = array_map('intval', $fids);

			$qq = [];
			$ph = [];

			if ($act == 'add') {
				foreach ($lid as $fid) {
					$ph[] = '(?, ?)';
					$qq[] = $aid;
					$qq[] = $fid;
				}
				[$pf, $sf] = ignoreVars(PICTAP);
				$query = "INSERT $pf INTO {$pp}albumfiles (albumid, fileid) VALUES " . implode(',', $ph) . " $sf;";
			} else {
				foreach ($lid as $fid) {
					$ph[] = '(albumid = ? AND fileid = ?)';
					$qq[] = $aid;
					$qq[] = $fid;
				}

				$query = "DELETE FROM {$pp}albumfiles WHERE " . implode(' OR ', $ph);
				albumLinks(0, $lid);
			}

			if (($r = $dbo->run($query, $qq, 0, __LINE__)) === false) {
				sendjs(0, $dbo->emsg);
			}
			$tot = $dbo->rowCount();
			if($act == 'add'){
				albumLinks(1,$lid);
			}

			if (($r = $dbo->run("UPDATE {$pp}albums aa SET mtime = ?, qt = (SELECT COUNT(*) FROM {$pp}albumfiles af WHERE af.albumid = aa.albumid) WHERE aa.albumid = ?", [time(), $aid], 0, __LINE__)) === false) {
				sendjs(0, $dbo->emsg);
			}
			$msg=$tot.' File(s) '.($act == 'add' ? 'Added': 'Removed');
			logger($ua['name'].' - '.$msg,2);
			sendjs(1, $msg, ['Dir' => menuList()]);
		}

	}else if($task === 'city' && userCan('edit')){
		$name = addcslashes(strtolower($name), '%_');
		if(!($dbo = openDb())){sendjs(0,'Db Error');}
		$query = "SELECT l.location, s.state, c.country, l.lat, l.lon
		FROM {$pp}locations l
		INNER JOIN {$pp}states s ON l.stateid = s.stateid
		INNER JOIN {$pp}countries c ON l.countryid = c.countryid
		WHERE LOWER(l.location) LIKE ?
		OR LOWER(s.state) LIKE ?
		OR LOWER(c.country) LIKE ?
		LIMIT 10";
		if (($stmt = $dbo->run($query, ["%$name%","%$name%","%$name%"], 2, __LINE__)) === false) {
			sendjs(0, $dbo->emsg);
		}
		$rv = [];
		$stmt = $stmt ?: [];
		foreach ($stmt as $r) {
			$rv[ $r['lat'] . ',' . $r['lon'] ] = implode(', ',array_filter(array_unique([$r['location'], $r['state'], $r['country']])));
		}
		sendjs(1,$rv);


	}else if($task === 'srch'){
		$mode = post('m');
		if(!in_array($mode,['s','a','t','k','d'])){sendjs(0,"Hmm");}
		$name = trim(str_replace('%','',$name));
		$f=[
			'mode'	=> $mode,
			'key'	=> $name,
			'mt'	=> 0,
			'ids'	=> [],
			'fds'	=> []
		];
		$query = 0;
		if($name !== ''){
			if(!($dbo = openDb())){sendjs(0,'Db Error');}
			[$fs, $fp] = familyQ();
			$kwds = [];
			$joins = '';
			$rank = $limit = '';
			$rank_params = [];
			$cond_params = [];
			if($mode === 'd'){
				sendJSON(dirJSON(intval($name), intval(post('mt'))));
			}else if($mode === 't'){
				if($name==='Timeline'){
					$f['i']=[];
					$query = "SELECT COUNT(*) as n, ".qdate('f.tk', '%Y-%m')." as m FROM {$pp}files f
					INNER JOIN {$pp}dirs d USING(dirid)
					WHERE 1 = 1 $fs GROUP BY m ORDER BY m DESC;";
					if (($stmt = $dbo->run($query, $fp, 2, __LINE__)) === false) {
						sendjs(0, $dbo->emsg);
					}
					$stmt = $stmt ?: [];
					foreach ($stmt as $r) {
						$f['i'][$r['m']] = (int)$r['n'];
					}
				}else{
					$name = preg_replace("/[^0-9-]/", '', $name);
					$s = '%Y';
					$d = explode('-',$name);
					if(count($d)>1){$s.='-%m';}
					if(count($d)>2){$s.='-%d';}
					$cond = qdate('f.tk', $s)." = '".$name."'";
				}
			}else if($mode === 'a'){
				if(!userCan('album')){die;}
				if($name==='Albums'){
					$query = "SELECT t.cat, t.tag, COUNT(tf.tagid) AS n
					FROM {$pp}tags t
					INNER JOIN {$pp}tagfiles tf USING(tagid)
					INNER JOIN {$pp}files f USING(fileid)
					INNER JOIN {$pp}dirs d USING(dirid)
					WHERE 1 = 1 $fs GROUP BY t.tag;";
					if (($stmt = $dbo->run($query, $fp, 2, __LINE__)) === false) {
						sendjs(0, $dbo->emsg);
					}
					$f['i']=[];
					$stmt = $stmt ?: [];
					foreach ($stmt as $r) {
						if(!isset($f['i'][ $r['cat'] ])){$f['i'][ $r['cat'] ] = [];}
						$f['i'][ $r['cat'] ][ $r['tag'] ] = (int)$r['n'];
					}
				}else{
					$name = preg_replace("/[^0-9]/", '', $name);
					if(!$name){sendJSON($f);}
					$joins = "LEFT JOIN {$pp}albumfiles af USING(fileid) LEFT JOIN {$pp}albums a USING(albumid)";
					$cond = "albumid = $name".(userCan('admin')? '' : " AND (userid = ".USER->id. " OR family > 0)");


				}
			}else if($mode === 'k'){
				if($name==='Tags'){
					$query = "SELECT t.cat, t.tag, COUNT(tf.tagid) AS n
					FROM {$pp}tags t
					INNER JOIN {$pp}tagfiles tf USING(tagid)
					INNER JOIN {$pp}files f USING(fileid)
					INNER JOIN {$pp}dirs d USING(dirid)
					WHERE 1 = 1 $fs GROUP BY t.cat, t.tag;";
					if (($stmt = $dbo->run($query, $fp, 2, __LINE__)) === false) {
						sendjs(0, $dbo->emsg);
					}
					$f['i']=[];
					$stmt = $stmt ?: [];
					foreach ($stmt as $r) {
						if(!isset($f['i'][ $r['cat'] ])){$f['i'][ $r['cat'] ] = [];}
						$f['i'][ $r['cat'] ][ $r['tag'] ] = (int)$r['n'];
					}
				}else{
					$cond = "1 = 1";
					$limit = 'HAVING COUNT(CASE WHEN t.tag = ? THEN 1 END) > 0';
					$cond_params[] = $name;
				}
			}else{// s
				if(!userCan('search')){sendjs(0,"Search Disabled");}
				$kwds = array_unique(explode(' ', strtolower($name)));

				$limit = 'ORDER BY rank DESC LIMIT '.PICTAP->search_max_results.';';
				$cond = [];
				$rank = [];
				$scol = ['f.file', 'l.location', 's.state', 'c.country','v.dev','t.tag'];
				foreach ($kwds as $i => $kwd) {
					foreach($scol as $c){
						$a = 'LOWER('.$c.') LIKE ?';
						$cond[] = $a;
						$rank[] = 'CAST('.$a.' AS INT)';
						$rank_params[] = "%$kwd%";
						$cond_params[] = "%$kwd%";
					}
					$ft = 0;
					if(stripos($kwd,'photo') !== false){
						$ft = 1;
					}
					if(stripos($kwd,'video') !== false){
						$ft = 2;
					}
					if($ft){
						$a = 'ft = ?';
						$cond[] = $a;
						$rank[] = 'CASE WHEN '.$a.' THEN 1 ELSE 0 END';
						$rank_params[] = $ft;
						$cond_params[] = $ft;
					}
				}

				$rank = ', (' . implode(' + ', $rank) .') AS rank';
				$cond = '(' . implode(' OR ', $cond) .')';

			}

			if(!$query){
				$cond .= $fs;
				$query = fileSql($rank)." $joins
				WHERE $cond GROUP BY f.fileid, l.location, s.state, c.country, v.dev, t.tag ".$limit;
				$ara = array_merge($rank_params, $cond_params, $fp);
				if (($stmt = $dbo->run($query, $ara, 2, __LINE__)) === false) {
					sendjs(0, $dbo->emsg);
				}
				$stmt = $stmt ?: [];
				loopFiles($stmt, $f);
			}
			if(empty($f['mt'])){
				if (($gtt = $dbo->run("SELECT MAX(mt) as mm FROM {$pp}dirs d WHERE 1 = 1 $fs LIMIT 1;", $fp, 1, __LINE__)) === false) {
					sendjs(0, $dbo->emsg);
				}
				$f['mt'] = (int)$gtt['mm'];
			}
		}

		sendJSON($f);

	}else if($task === 'gps-tag' && userCan('edit')){
		getPostFile($fr, $path);
		if(empty($fr['ft'])){sendjs(0,"Unsupported: ".basename($path));}
		$cor = explode(',',post('new'));
		$lat = $lon = 999;
		if(count($cor) === 2){
			$lat=is_numeric($cor[0])? $cor[0] : $lat;
			$lon=is_numeric($cor[1])? $cor[1] : $lon;
		}

		if(!validGps((float)$lat,(float)$lon)){
			sendjs(0,"Invalid Lat/Lon");
		}
		$ef = escapeshellarg($path);
		$cmd = escapeshellarg(PICTAP->bin_exiftool).' -m -P -api largefilesupport=1 -GPSLatitude*='.$lat.' -GPSLongitude*='.$lon.' -overwrite_original '.$ef;
		if(locker(1)){
			ignore_user_abort(true);
			exec($cmd, $output,	$result);
			locker();//unlocks
		}else{
			sendjs(0,"System busy, please try again");
		}
		if ( 0 !== $result) {
			sendjs(0,"exiftool failed ".implode('',$output)."<br>".$cmd);
		}

		if(($gc = gpsCity($lat, $lon)) === false ||
		updateFile($fr['fileid'],[
			'lat'=>round($lat*10000),
			'lon'=>round($lon*10000),
			'locationid'=>$gc
		]) === false){
			sendjs(0,"Update Error");
		}

		sendjs(1,"Edited: ".basename($path));

	} else if($task === 'edit' && userCan('edit')){
		getPostFile($fr, $path);
		if(!$fr['ft']){sendjs(0,"Unsupported: ".basename($path));}
		$p = [];
		foreach(['w','h','x','y','r','o'] as $i){
			$p[$i]=intval(post($i));
		}
		if(!$p['r'] && (!$p['w'] || !$p['h'])){
			sendjs(0,"Nothing to do");
		}
		if(!$p['o']){
			$npath = safe_rename(0,$path,1);
			if (!copy($path,$npath)){
				sendjs(0,"Copy failed ".relative($npath));
			}
			$path = $npath;
		}else{
			del_thumb(relative($path));
		}

		$ef = escapeshellarg($path);
		if($p['r']){
			$s = (PHP_SHLIB_SUFFIX==='dll')?'"':"'";
			$i = '-Orientation='.$p['r'];
			if(post('vrot')){$i = '-rotation<${rotation;$_ += '.$p['r'].'}';}

			$cmd = escapeshellarg(PICTAP->bin_exiftool).' -n '.$s.$i.$s.' -m -api largefilesupport=1 -overwrite_original '.$ef;

			if(locker(15)){
				ignore_user_abort(true);
				exec($cmd, $output,	$result);
				locker();//unlocks
			}else{
				sendjs(0,"System busy, please try again");
			}

			if ( 0 !== $result) {
				sendjs(0,"exiftool failed ".implode('<br>',$output)."<br>".$cmd);
			}
		}
		if($p['w'] && $p['h']){
			exec(escapeshellarg(PICTAP->bin_jpegtran).' -optimize -copy all -crop '.$p['w'].'x'.$p['h'].'+'.$p['x'].'+'.$p['y'].' -outfile '.$ef.' '.$ef, $output, $res);
			if ( 0 !== $res) {
				sendjs(0,"jpegtran failed ".implode('',$output));
			}
		}
		scanFolder(dirnm($path));
		sendjs(1,"Edited: ".basename($path));



	} else if($task === 'newdir' && userCan('newdir')){
		getPostDir($id,$dirpath);
		$name = sanitise_name(str_replace('%',' ',$name), true);
		$path = $dirpath . '/' . $name;
		if(file_exists($path)){
			sendjs(0,"Already exists: ".relative($path));
		}
		if(makeDir($path)){
			scanFolder($dirpath);
			sendjs(1,"Created: ".relative($path));
		}
		sendjs(0,"Failed to create dir: ".relative($path));

	} else if($task === 'delete' && userCan('delete')){
		if(post('type')==='dir'){//directory
			getPostDir($id,$dirpath);
			if(!rmdir($dirpath)){
				sendjs(0,"Failed to delete /".$id['dir'].'<br>(only empty folder can be deleted)');
			}else{
				scanFolder(dirnm($dirpath));
				sendjs(1,"Deleted /".$id['dir']);
			}
		} else {// file
			getPostFile($fr, $path);
			$rp = relative($path);
			if($fr['ft']){del_thumb($rp);}
			if(PICTAP->path_recycle){
				$binpath=PICTAP->path_recycle.$rp;

				makeDir(dirnm($binpath));
				$res=safe_rename($path,$binpath)[0];
			}else{
				$res=@unlink($path);
			}
			if($res){
				scanFolder(dirnm($path));
				sendjs(1,"Deleted ".$rp);
			}else{
				sendjs(0,"Failed to delete ".$rp);
			}
		}

	} else if($task === 'move' && userCan('move')){
		getPostDir($id,$dirpath);

		if(!($newid = getDirRow(post('new')))){
			sendjs(0,"Invalid newid ".post('new'));
		}
		//$moveto = $newid['dir'];
		//if($moveto !== ''){$moveto = '/'.$moveto;}
		$targetdir = rtrim(PICTAP->path_pictures.'/'.$newid['dir'],'/');

		if(post('type')==='dir'){
			$new_path = $targetdir.'/'.basename($dirpath);

			$relpath = relative($new_path);

			if(file_exists($new_path) || $dirpath === $new_path){
				sendjs(0,"Already exists: ".$relpath);
			}
			if(locker(10)){
				$res = rename($dirpath, $new_path);

				if($res){

					$newdir = relative($new_path);

					$thmfrom = PICTAP->path_thumbs . relative($dirpath);
					$thmto = PICTAP->path_thumbs . $newdir;
					makeDir(dirnm($thmto));
					@rename($thmfrom, $thmto);
					locker();//unlocks
					$newparent = $newid['dirid'];
					if(updateDir($id,['dir'=>ltrim($newdir, '\/'),'parentid'=>$newparent]) === false){
						sendjs(0,"Error updating: ".$relpath);
					}

					renameSubDir($id['dir'], ltrim($newdir, '\/'));
					scanFolder(dirnm($dirpath));
					scanFolder(dirnm($new_path));

					sendjs(1,"Moved to: ".$relpath);
				}
				locker();//unlocks
			}
			sendjs(0,"Failed to move to: ".$relpath);


		}else{//file
			getPostFile($fr, $path);

			$new_path = $targetdir. '/' . $fr['file'];

			$relpath = relative($path);
			$relnew = relative($new_path);

			if(file_exists($new_path)){
				sendjs(0,"Already exists ".$relnew);
			}
			if(locker(10)){
				$res = rename($path, $new_path);

				if($res){
					$thmfrom = thumb_name($relpath);
					$thmto = thumb_name($relnew);
					makeDir(dirnm(PICTAP->path_thumbs.$thmto));
					@rename(PICTAP->path_thumbs.$thmfrom, PICTAP->path_thumbs.$thmto);
					locker();//unlocks
					if(updateFile((int)$fr['fileid'], ['dirid'=>$newid['dirid'] ]) === false){sendjs(0,"Failed");}
					albumLinks(1, (int)$fr['fileid']);
					scanFolder(dirnm($new_path));
					sendjs(1,"Moved ".$relpath);
				}
				locker();//unlocks
			}
			sendjs(0,"Failed to move ".$relpath);

		}

	} else if($task === 'thumb' && userCan('edit')){
		getPostFile($fr, $path);
		if(!$fr['ft']){sendjs(0,"Unsupported: ".basename($path));}

		if(!($ni = getDirRow(post('new')))){
			sendjs(0,"Invalid id ".post('new'));
		}
		$m = time();
		touch(rtrim(PICTAP->path_pictures.'/'.$ni['dir'],'/'),$m);
		if(updateDir($ni,['thm'=>$fr['fileid'],'mt'=>$m]) === false){
			sendjs(0,"Error updating ".$ni['dir']);
		}
		sendjs(1, "Updated Thumbnail: ".$ni['dir'], ['Dir' => menuList()]);

	} else if($task === 'rename' && userCan('rename')){
		getPostDir($id,$dirpath);

		if(post('type')==='dir'){

			$newname = sanitise_name(post('new'),1);
			$new_path = dirnm($dirpath) . '/' . $newname;
			if($newname === '' || file_exists($new_path)){
				sendjs(0,"Invalid name ".post('new'));
			}
			$relpath = relative($dirpath);
			if(locker(10)){
				$res = rename($dirpath, $new_path);
				if($res){
					$newdir = relative($new_path);
					$thmfrom = PICTAP->path_thumbs . relative($dirpath);
					$thmto = PICTAP->path_thumbs . $newdir;

					makeDir(dirnm($thmto));
					@rename($thmfrom, $thmto);
					locker();//unlocks
					if(updateDir($id,['dir'=>ltrim($newdir, '\/')]) === false){
						sendjs(0,"Error updating: ".$relpath);
					}

					renameSubDir($id['dir'], ltrim($newdir, '\/'));

					sendjs(1,"Renamed ".$relpath);
				}
				locker();//unlocks
			}
			sendjs(0,"Failed to rename ".$relpath);

		}else{
			getPostFile($fr, $path);

			$relpath = relative($dirpath);

			$newname = sanitise_name(post('new'));
			$new_path = dirnm($path) . '/' . $newname;
			$oldext = splitExt($fr['file'])[2];
			$ext = splitExt($newname)[2];

			if($newname === '' || (in_array($ext, PICTAP->ext_nouploads) && !userCan('admin')) || (
				!in_array($ext, PICTAP->ext_images) &&
				!in_array($ext, PICTAP->ext_videos) &&
				!in_array($ext, PICTAP->ext_uploads)
			)){
				sendjs(0,"Not Allowed ".post('new'));
			}
			if(file_exists($new_path)){
				sendjs(0,"Already exists ".post('new'));
			}
			if(locker(10)){
				$res = rename($path, $new_path);
				if($res){
					$uf = ['file'=>$newname];
					$thmfrom = thumb_name(relative($path));
					$thmto = thumb_name(relative($new_path));
					if($oldext != $ext){
						if(delFileRow($fr)===false){
							sendjs(0,"Failed");
						};
						locker();//unlocks
					}else{
						@rename(PICTAP->path_thumbs.$thmfrom, PICTAP->path_thumbs.$thmto);
						locker();//unlocks
						if(updateFile((int)$fr['fileid'], $uf)===false){sendjs(0,"Failed");}
						albumLinks(1, (int)$fr['fileid']);
					}

					scanFolder(dirnm($new_path));
					sendjs(1,"Renamed ".$relpath);
				}
				locker();//unlocks
			}
			sendjs(0,"Failed to rename ".$relpath);
		}


	} else {
		sendjs(0, ucfirst($task).' Not Allowed');
	}


} else{

	$pic = PICTAP->path_pictures;
	$thm = PICTAP->path_thumbs;
	$pp = PICTAP->db_prefix;
	if(get('ithumb')){//make thumb
		userAuth();
		$fid = intval(get('ithumb'));
		$em = "thumb error ".get('n');
		if($fid){
			$fid = getFileRow($fid);
		}
		if($fid ){
			if(
			((int)$fid['th']==2 && !getExif($fid)) ||
			((int)$fid['th']==1 && makeThumb($fid) === false) ||
			(int)$fid['th']
			){
				sendjs(0, $em);
			}
			sendjs(1, $fid);
		}
		sendjs(0, $em);

	}else if(get('fthmb')){//folder thumb
		userAuth();
		$id = intval(get('fthmb'));
		if(!$id){http_response_code(400); exit;}
		if(($d = getDirRow($id))){
			$c = "d.dirid = ?";
			$arb = [$id];
			if($d['thm']){$c = "f.fileid = ?"; $arb = [$d['thm']];}
			[$fs, $fp] = familyQ();

			if(!($dbo = openDb()) ||
			($stmt = $dbo->run("SELECT f.*, d.dir from {$pp}files f INNER JOIN {$pp}dirs d USING(dirid) WHERE f.ft <> 0 AND $c $fs ORDER BY tk DESC Limit 1", array_merge($arb, $fp), 1, __LINE__)) === false) {http_response_code(500); exit;}

			if(!$stmt){//fallback thumbnail
				$epath = addcslashes($d['dir'], '%_');
				if (($stmt = $dbo->run("SELECT f.*, d.dir FROM {$pp}files f INNER JOIN {$pp}dirs d USING(dirid) WHERE f.ft <> 0 AND dir LIKE ? ORDER BY tk DESC Limit 1", ["$epath/%"], 1, __LINE__)) === false) {http_response_code(500); exit;}
			}
			sendThumb($stmt);
		}else{
			http_response_code(500);
		}
		exit;

	}else if(get('tthmb')){//timeline thumb
		userAuth();

		$dt = preg_replace("/[^0-9-]/", '', trim(get('tthmb')));
		if(!$dt){http_response_code(400); exit;}

		$s = '%Y';
		$d = explode('-',$dt);
		if(count($d)>1){$s.='-%m';}
		if(count($d)>2){$s.='-%d';}
		[$fs, $fp] = familyQ();
		$cond = qdate('f.tk', $s)." = '".$dt."'".$fs;

		if(!($dbo = openDb()) ||
		($stmt = $dbo->run("SELECT f.*, d.dir FROM {$pp}files f INNER JOIN {$pp}dirs d USING(dirid) WHERE f.ft <> 0 AND $cond ORDER BY f.tk DESC LIMIT 1", $fp, 1, __LINE__)) === false){
			http_response_code(500); exit;
		}

		sendThumb($stmt);

	}else if(get('kthmb')){//tags thumb
		userAuth();

		$kw = trim(get('kthmb'));
		if(!$kw){http_response_code(400); exit;}
		[$fs, $fp] = familyQ();
		$q = "SELECT f.*, d.dir FROM {$pp}tags t
		INNER JOIN {$pp}tagfiles tf USING(tagid)
		INNER JOIN {$pp}files f USING(fileid)
		INNER JOIN {$pp}dirs d USING(dirid)
		WHERE f.ft <> 0 AND t.tag = ? $fs ORDER BY f.tk DESC LIMIT 1";

		if(!($dbo = openDb()) ||
		($stmt = $dbo->run($q, array_merge([$kw], $fp), 1, __LINE__)) === false){
			http_response_code(500); exit;
		}
		sendThumb($stmt);

	}else if(get('athmb')){//albums thumb
		userAuth();

		$aid = intval(get('athmb'));
		if(!$aid){http_response_code(400); exit;}
		[$fs, $fp] = familyQ();
		$q = "SELECT f.*, d.dir FROM {$pp}files f
		INNER JOIN {$pp}dirs d USING(dirid)
		INNER JOIN {$pp}albumfiles af USING(fileid)
		WHERE f.ft <> 0 AND af.albumid = ? $fs
		ORDER BY f.tk DESC LIMIT 1;";
		if(!($dbo = openDb()) ||
		($stmt = $dbo->run($q, array_merge([$aid], $fp), 1, __LINE__)) === false){
			http_response_code(500); exit;
		}
		sendThumb($stmt);

	}else if(get('page') === 'settings'){
		userAuth(true);
		if( userCan('admin')){
			pageConfig(PICTAP);
		}
		error("not allowed");

	} else if(get('page') === 'accounts'){

		userAuth(true);
		if( userCan('admin')){
			pageAccounts();
		}
		error("not allowed");



	} else if(get('upload') ){
		$up=get('upload');
		userAuth($up!='js');
		$rt=[[0,'Cannot upload']];
		if(userCan('upload')){
			$rt = procUpload();
		}
		if($up=='js'){
			sendjs($rt[0][0], $rt[0][1]);
		}else{
			$js=[];$m=[];$i=0;$out='';
			foreach($rt as $u){
				if($u[0]){//result
					if($u[2]){//dirid
						$r = getFileRow($u[2],$u[3]);
						if($r){
							$js[$u[2].'/'.$r['file'].'/'.$r['fileid'].'/file']=$r['sz'];
							$i++;
						}else{
							$m[]='Missing '.$u[3];$i++;
						}
					}
				}else{
					$m[]=$u[1];//msg
					$i++;
				}
			}

			if(count($m)){
				$out='toast("'.implode(',',$m).'",{theme:"red",timeout:0,close:1});';
			}else{$m='';}
			if(count($js)){
				$out .= 'thumbabrt.abort();navi.ticked = '.json_encode($js).';tickShow();act_move();';
			}
			htmldoc('',$out);
		}

	} else { // front page

		userAuth(true);
		htmldoc();

	}



} //Get






function sfile($f){
	$p=''; $u = strtok($_SERVER['REQUEST_URI'], '?');
	cacheHdr();
	if($f=='png'){
		header('Content-Type: image/png');
		$p=base64_decode('iVBORw0KGgoAAAANSUhEUgAAALQAAAC0BAMAAADP4xsBAAAABGdBTUEAALGPC/xhBQAAAA9QTFRF////8cQUFLYE0wQDFFzz/taDWAAAAvJJREFUaN7tmttt4zAQRUlZBYhRCnBoFxCHDVgA+69p7VXk1eMOHzMksgF4P/1xfHBFiByBSrW0tLS0tLS0tLRUS2cfOVcAazvnXI1s7VCjjDkftZwfKYtek7dt9/6ZexnyRtsvkRe9Rff+X+7iotfoNZnH3pOXsrdkDruj0N5L2ZZA+2PE0jO692K2JdCInFdJR6A9jlD6ie69mN0RaIqcUQki24B0unZHoGlysrbF6IB0qjaW/ghJp2q/QfQ5KJ2ojfs4h8lJ2rgPG5FO0sZ92Bg5RRuTrwXQHRftueg4Oa7Nlq6J9tX6iGp3fOmaaM+p2ldDJ0pHGpFIM9DX/wDts9G+DLpLtZ5MCTTUNuYts+wusezJGPNeZ/WZZ0qgr0gasO+MrRFKHyu5y99P39IHbSV/QS3kvbZ8m3lJ79nyfcaskrM76qj2tEa/5xwXbEzbbNIXPORMW/TQp5/6dETb7LKMC/xT8KI97dHD36lPMhYs2sYctWVj46I9HdE5n7xC2saItDWtPSH0IG/EYuksbaqR64TRJbSNqaV9KYAmtJ0z8kag9oVEi7Wdq6V9KYRWWJpkiz5tXYJokbZzIfYg0L6E0YmN6AfgttdeyO4mQJ9mxNBBaUkjekF8QmlKOwU9vhhfUJrSTq5jx16TCe0c6UegNKEdb2QLQdKE9pDTx6uSnTRm56IdkmaiR3fUPkhjdl7Vs7arhHZIGrLz0V+uGprIr0RXLISxQsZ66BO7j1JoUw19Y72wfxrN2wpGrvSgSpTN3MA0UzrltMBFK1WgEVMNzT89KZ50Ejqy/D7lJ1UqWnK+DktryVQQ1FaygSMkrWQTWEBbKdmUpAPSWjiAjbS0Fk6kmpTG6Jyp8URJY3TW5RNKWolHXaytCHTmdRxAHgh07rU5DeuAXWdffTqhOhCacWFrROQjmnWLENRxRDPvJwLy4Tly7/QB8k5bsTOulh3SVoJ8f9HZ/FSGjP9O9gTDqQZuaWlpaWlpaWn5XfkDdD7J4HoVV4MAAAAASUVORK5CYII=');
	}else if($f=='svg'){
		header('Content-Type: image/svg+xml');
		$p = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 96 96"><path fill="#fff" d="M0 96V0h96v96z"/><path fill="#125bf3" d="M48 48 36 36s12-19 24-18 26 18 20 24c-7 7-20-6-20-6Z"/><path fill="#12b600" d="M48 48 36 60S16 45 15 35c0-9 19-25 26-18s-5 19-5 19z"/><path fill="#f1c40f" d="m48 48 12-12s21 13 19 27-20 22-26 16 7-19 7-19Z"/><path fill="#d30000" d="m48 48 12 12S46 81 35 80c-11-2-24-18-17-25s18 5 18 5z"/></svg>';
	}else if($f=='sw'){
		header('Content-Type: application/javascript');
		$p = '
addEventListener("install",()=>{skipWaiting();});
addEventListener("activate",()=>{clients.claim();});
addEventListener("fetch",(e)=>{if(e.request.url.includes("'.$u.'?upload=app")){
	e.respondWith(Response.redirect("'.$u.'"), 303);
	e.waitUntil((async ()=>{
		const c = await self.clients.get(e.resultingClientId);
		const d = await e.request.formData();
		c.postMessage({files: d.getAll("media[]")});
	})());
}});';
	}else if($f=='mf'){
		header('Content-Type: application/json');
		$p = '{
"name":"Pictap","short_name":"Pictap","description":"Photo Gallery",
"start_url":"'.$u.'",
"display":"standalone","background_color":"#000000",
"launch_handler":{"client_mode":["focus-existing","auto"]},
"display":"standalone","share_target":{"action":"'.$u.'?upload=app","enctype":"multipart/form-data","method":"POST",
"params":{"title":"title","text":"text","url":"url",
"files":[{"name":"media[]","accept":["image/*","video/*"]}]}},
"icons":[{"src":"'.$u.'?sf=svg","type":"image/svg+xml","sizes":"any","purpose":"maskable"},{"src":"'.$u.'?sf=png","sizes":"180x180","type":"image/png"}]}';
	}else{
		http_response_code(400);
		exit;
	}
	header('Content-Length: '.strlen($p));
	die($p);
}

function login_page($attempt){
	flushIP();
	$per=$attempt ? '<p style="color:red">Error! Please try again</p>' : '';

	$url=strtok($_SERVER['REQUEST_URI'], '?');
	http_response_code(401);
	header('Content-Type: text/html; charset=UTF-8');
	header('Cache-Control: no-cache, must-revalidate');
	echo '<!DOCTYPE html><html><head><title>Login</title><link rel="icon" type="image/svg+xml" href="'.$url.'?file=svg" /><meta name="viewport" content="width=device-width, initial-scale=1" /><style>body{font-family:Roboto,sans-serif;background:#222}form,input{padding:16px}main{width:320px;margin:16px auto;font-size:16px}nav{width:0;margin:0 auto;border:12px solid transparent;border-bottom-color:#796e65}h3{margin:0;background:#796e65;padding:20px;text-align:center;color:#fff;border-radius:10px 10px 0 0}form{background:#ebebeb;border-radius:0 0 10px 10px}input{margin:16px 0;box-sizing:border-box;display:block;width:100%;border:1px solid #bbb;outline:0;font-family:inherit;font-size:.95em;background:#fff;color:#555;border-radius:10px}input:focus{border-color:#888}#s{background:#ab6c34;border-color:transparent;color:#fff;cursor:pointer}#s:hover{background:#b97232}#s:focus{border-color:#05a}</style></head><body><main><nav></nav><h3>Welcome</h3><form method="post" action="'.$url.'">'.$per.'<input name="username" type="text" placeholder="Username" required><input type="password" name="password" placeholder="Password" required><input id="s" type="submit" value="Log in"></form></main></body></html>';
	exit;
}


function htmldoc($config='',$lightbox='',$js=''){
	header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s:s") . " GMT");
	header('Content-Type: text/html; charset=UTF-8');
	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("X-Frame-Options: SAMEORIGIN");
	header("X-Content-Type-Options: nosniff");
	header("X-XSS-Protection: 1; mode=block");
	header("Referrer-Policy: strict-origin-when-cross-origin");
	header("Pragma: no-cache");
	$page = '';
	if(!$config){
		if(!userCan('admin')){$page='noset';}
	}else{
		$page = 'config';
	}
$u=strtok($_SERVER['REQUEST_URI'], '?').'?m='.filemtime(__FILE__).'&sf=';

?><!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=yes">
<meta name="robots" content="noindex, nofollow">
<link rel="icon" type="image/svg+xml" href="<?php echo $u; ?>svg" />
<link rel="apple-touch-icon" sizes="180x180" href="<?php echo $u; ?>png">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<link rel="manifest" href="<?php echo $u; ?>mf" />
<link rel="stylesheet" href="pictap.css?<?php echo filemtime(dirnm(__FILE__).'/pictap.css'); ?>">
<title>Pictap</title>
</head>
<body class="<?php echo $page; ?>">

<header id="header" class="open">
	<div id="topbar">
		<div class="rbtn" id="lines" title="Menu" onclick="sidebar(2);"><i class="ico-menu"></i></div>

		<div id="title">Pictap Gallery</div>
		<form id="srchf" autocomplete="off"><input title="Search Text" type="search" id="srch"></form>
		<div class="rbtn" id="noselect" title="Selection" onclick="cMenu(event, this, 'sels')"><i class="ico-ring"></i></div>
		<div id="selected"></div>
		<div class="rbtn" id="deleteb" title="Delete" onclick="act_delete()"><i class="ico-delete"></i></div>
		<div class="rbtn" id="search" title="Search"><i class="ico-search"></i></div>
		<div class="rbtn" id="fscreen" title="Fullscreen"><i class="ico-max"></i></div>
		<div class="rbtn" id="dots" title="Menu expand" onclick="cMenu(event, this, 'dots')"><i class="ico-dots"></i></div>

		<div class="rbtn" id="tclose" title="Close"><i class="ico-x"></i></div>
		<div id="ctitle"></div>
		<div class="rbtn" id="tsave" title="Done"><i class="ico-check"></i></div>
	</div>
	<div id="selectbar"></div>
	<div id="breadcrumbs"></div>
</header>

<aside id="sidebar">
	<nav>
		<a id="accounts" title="Accounts" class="rbtn" href="?page=accounts"><i class="ico-user"></i></a>
		<a id="settings" title="Settings" class="rbtn" href="?page=settings"><i class="ico-set"></i></a>
		<div class="rbtn" title="Logout" onclick="cMenu(event, this,'exit')"><i class="ico-exit"></i></div>
		<div id="msortb" title="Menu sort" class="rbtn" onclick="cMenu(event, this,'msort')"><i class="ico-named" id="msorter"></i></div>
	</nav>
	<div id="menu"></div>
	<div class="version"><a href="https://github.com/junkfix/Pictap">Pictap Gallery v<?php echo PIC_VER[0]; ?></a></div>
</aside>

<main class="rows">
<div id="uploader"></div>
<div id="editor"></div>
<div id="subhead">
	<div id="sizes">0 Files</div>
	<div class="rbtn" title="Change View" onclick="cMenu(event, this,'view')"><i class="ico-rows" id="viewbt"></i></div>
	<div class="rbtn" title="Sort by"><i id="sorter" class="ico-namea" onclick="cMenu(event, this,'sort')"></i></div>
</div>

<div id="galleryc">
	<div class="gallery">
		<h1 style="flex: 0 0 100%;font-size:5em;height:2em;" class="loader"></h1>
	</div>
</div>
<?php echo $config; ?>
</main>
<ul id="cmenu"></ul>
<div id="popup"></div>
<div id="toast"></div>

<?php
	echo '<script src="pictap.js?'.filemtime(dirnm(__FILE__).'/pictap.js').'"></script>';
	$dirs = '{d:{},m:0,home:0,root:0}';

	$p = [
		'ext_images'=>[],
		'ext_videos'=>[],
		'ext_uploads'=>[],
		'auto_hide_slideshow_ui'=>0,
		'url_thumbs'=>'',
		'url_pictures'=>''
	];
	$role = 0;

	if(!$config){
		$rootdirs = menuList();
		if($rootdirs){
			$dirs = json_encode($rootdirs, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
			foreach ($p as $k => $v){
				$p[$k] = PICTAP->$k;
			};
			$role = USER->role;
		}else{
			$js.= ';popup("Error",'.json_encode(nl2br(PICTAP->err)).');';
		}
	}
	$ht = "<script>\nlet Dir = $dirs;\n";

	$p['can']=roleList($role);
	foreach(['share','info','download'] as $i){
		$p['can'][$i]=1;
	}
	$p['can']['map']=$p['can']['edit'];
	$p['can']['thumb']=$p['can']['edit'];
	$p['can']['eday']=$p['can']['search'];

	$ht .= "const _p = " . json_encode($p) . ";\n";
	$ht .= 'let startUp = ()=>{'.$lightbox.'};picT();'."\n";
	$ht .= '</script>';
	$ht .= '<script>{const n=navigator.serviceWorker;if(n){n.register("'.$u.'sw");	n.onmessage=(e)=>{act_upload(0,e.data.files);};}}'.$js.'</script>';
	$ht .= '</body></html>';
	echo $ht;
	exit;
}
