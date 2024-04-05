<?php
/* Pictap Gallery https://github.com/junkfix/Pictap */

const PIC_VER = ['1.0.1','1,1']; //[main, [config,db]]

if(get('sf')){sfile(get('sf'));}

$setup=loadConfig();

if(!is_array($setup)){
	$setup=['version'=>['0','0,0'], 'salt'=>bin2hex(random_bytes(8))];
}

$setup = (object) $setup;
define( 'PICTAP', $setup );


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

define( 'ROLES', (object)roleList());

function userCan($cando, $role=null) {
	$r = ($role === null)? USER->role : $role;
	$o = ((intval($r) & ROLES->$cando) === ROLES->$cando)? 1 : 0;
    return $o;
}


if($setup->version[1] !== PIC_VER[1]){
	if(property_exists($setup,'users')){
		userAuth(true);
	}
	pageConfig($setup);
}

@ini_set('memory_limit', '512M');
@ini_set('max_execution_time', '0');
@ini_set('max_input_time', '-1');


function loadConfig($save=0){
	$f = dirname(__FILE__) .'/pictap_config.php';
	if($save){
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


function logger($msg='',$level=0){
	$cli = (php_sapi_name() == 'cli');
	if ($cli && $msg){echo $msg."\n";}

	if($msg && PICTAP->debug && !empty(PICTAP->debug_file)){
		file_put_contents(PICTAP->debug_file, '['.date("Y-m-d H:i:s").'] '.$msg."\n",FILE_APPEND);
	}
	return $cli;
}

function globber($dir, $force){
	logger('Checking: '.$dir);
	scanFolder($dir, $force);
	$deep = glob(glob_nobr($dir) . '/*', GLOB_ONLYDIR | GLOB_NOSORT);
	foreach ($deep as $d) {
		globber($d, $force);
	}	
}

//command line
if (logger()) {
	set_time_limit(0);
	userAuth();
	
	//$p=getopt("s:c:f:d:"); //print_r($p); exit;
	
	$force = 0;
	
	globber(PICTAP->path_pictures, $force);
	
	$dbo=openDb();
	
	//$dbo->exec("UPDATE files SET th = 2 WHERE ft = 1 AND ori > 0");

	$stmt = $dbo->query("SELECT f.*, d.dir from files f INNER JOIN dirs d USING(dirid)");
	$rows = [];
	while ($row = $stmt->fetchArray(SQLITE3_ASSOC)){
		$rows[] = $row;
	}
	openDb(-1);
	echo "Scan begins\n";
	$forcethumb = 0;
	foreach ($rows as $r){
		$f = $r['dir'].'/'.$r['file'];
		if($r['th']===2){
			logger('Scan /'.$f.' ');
			getExif($r); 
		}
		if($r['th']===1 || $forcethumb){
			logger('Thum /'.$f);
			makeThumb($r,$forcethumb);
		}
	}

	echo "\nDatabase Optimize\n";
	$dbo=openDb();
	$dbo->exec('PRAGMA optimize;');
	$dbo->exec('VACUUM;');
	
	echo "\nChecking for orphan thumbs\n";
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
			logger('Removed Thumb Directory: '.$relth);
		}
		return;
	}
	$f = splitExt($file);
	if( $f[2] != 'webp' ){return;}
	$count = glob(glob_nobr(PICTAP->path_pictures . relativethumb($f[0])).'.*');
	if(!count($count)){ 
		unlink($file);
		logger('Removed Thumb: '.relativethumb($file));
	}
}



function openDb($db=0){
	static $dbo = null;
	if($db === -1){if($dbo!==null){$dbo->close();$dbo = null;}return;}
	if($dbo === null){
		try {
			if($db===0){$db = PICTAP->db_file;}
			$dbo = new SQLite3($db);
			$dbo->exec("PRAGMA foreign_keys = 1");
			$dbo->busyTimeout(60000);
		} catch (Exception $e) {
			die("Error: " . $e->getMessage());
		}
	}
	return $dbo;
}

function getDevID($dev,$insert=true){
	try {
		if(!$dev){return null;}
		$dbo=openDb();
		$stmt = $dbo->prepare("SELECT devid FROM devs WHERE dev = :dev COLLATE NOCASE");
		$stmt->bindValue(':dev', $dev, SQLITE3_TEXT);
		$res = $stmt->execute();
		$r = $res->fetchArray(SQLITE3_ASSOC);
		if ($r) {
			return $r['devid'];
		} else if($insert){
			$stmt = $dbo->prepare("INSERT OR IGNORE INTO devs (dev) VALUES (:dev)");
			$stmt->bindValue(':dev', $dev, SQLITE3_TEXT);
			$stmt->execute();
			//$dirID=$dbo->lastInsertRowID();
			return getDevID($dev, false);
		}else{
			return null;
		}
	} catch (Exception $e) {
		die("Error: " . $e->getMessage());
	}
}


function getDirID($n,$insert=true){
	try {
		$name = ltrim($n,'/');
		$dbo=openDb();
		$stmt = $dbo->prepare("SELECT * FROM dirs WHERE dir = :dir");
		$stmt->bindValue(':dir', $name, SQLITE3_TEXT);
		$res = $stmt->execute();
		$r = $res->fetchArray(SQLITE3_ASSOC);
		if ($r) {
			return $r;
		} else if($insert){
			$newid = $dbo->query("SELECT COALESCE(MIN(dirid + 1), 1) AS missing_id FROM dirs t1 WHERE NOT EXISTS ( SELECT 1 FROM dirs t2 WHERE t2.dirid = t1.dirid + 1 ) ORDER BY dirid LIMIT 1;")->fetchArray(SQLITE3_NUM)[0];
			
			$stmt = $dbo->prepare("INSERT OR IGNORE INTO dirs (dirid, dir) VALUES ($newid, :name)");
			$stmt->bindValue(':name', $name, SQLITE3_TEXT);
			$stmt->execute();
			//$dirID=$dbo->lastInsertRowID();
			return getDirID($n, false);
		}else{
			return false;
		}
	} catch (Exception $e) {
		die("Error: " . $e->getMessage());
	}
}



function familyQ(){
	$r='';
	if(!userCan('alldir')){
		$d = ["d.dir = '".USER->user."'","d.dir LIKE '".USER->user."/%'"];
		if(userCan('family') && PICTAP->users[0][0]){
			$d[] = "d.dir = '".PICTAP->users[0][0]."'";
			$d[] = "d.dir LIKE '".PICTAP->users[0][0]."/%'";
		}
		$r=" AND ( ".implode(' OR ',$d)." ) ";
	}
	return $r;
}

function getDirRow($id){
	try {
		if(!is_numeric($id)){return null;}
		$id = intval($id);
		
		$query = "SELECT * from dirs d WHERE d.dirid = $id" . familyQ();
		
		$dbo=openDb();
		$stmt = $dbo->query($query);
		$result = $stmt->fetchArray(SQLITE3_ASSOC);

		if ($result) {
			return $result;
		}
		return null;
	} catch (Exception $e) {
		die("Error: " . $e->getMessage());
	}
}


function updateDir($dirid, $c){
	$name=null;
	if(isset($c['dir'])){
		$name=$c['dir'];
		$c['dir'] = ':dir';
	}
	foreach(['parentid','thm'] as $k){
		if(array_key_exists($k,$c) && !$c[$k]){
			$c[$k] = 'NULL';
		}
	}
	foreach($c as $k=>$v){
		$c[$k] = $k.' = '.$v;
	}
	$r=null;
	$dbo=openDb();
	$q = "UPDATE dirs SET ".implode(', ',$c)." WHERE dirid = $dirid";
	try {
		$stmt = $dbo->prepare($q);
		if($name!==null){$stmt->bindValue(':dir', $name, SQLITE3_TEXT);}
		$stmt->execute();
		$r = $dbo->changes();
		if($name!==null){//dir name updated
			$f = getFileIds($dirid); $fid=[];
			foreach($f as $g) {
				$fid[]=$g['fileid'];
			}
			albumLinks(1,$fid);
		}
	} catch (Exception $e) {
		$l = "Error updateDir $dirid: $q " . $e->getMessage();
		logger($l,5);
		die($l);
	}
	return $r;
}

function getDirWild($path){
	$dbo=openDb();
	try {
		$stmt = $dbo->prepare("SELECT * from dirs WHERE dir = :dir OR dir LIKE :subd ORDER BY LENGTH(dir) - LENGTH(REPLACE(dir, '/', '')) DESC");
		$epath = addcslashes($path, '%_');
		$stmt->bindValue(':dir', $path, SQLITE3_TEXT);
		$stmt->bindValue(':subd', "$epath/%", SQLITE3_TEXT);
		$res = $stmt->execute();
		$rows = [];
		while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
			$rows[] = $row;
		}
	} catch (Exception $e) {
		$l = "Error getDirWild $path:" . $e->getMessage();
		logger($l,5);
		die($l);
	}
	return $rows;
}

function delDirRow($path){
	$res = getDirWild($path);
	
	foreach($res as $r) {
		$f = getFileIds($r['dirid']);
		foreach($f as $g) {
			delFileRow($g);
		}
	}
	$dbo=openDb();
	foreach($res as $r) {
		$q = "DELETE FROM dirs WHERE dirid = ".$r['dirid'];
		try {
			$dbo->exec($q);
		} catch (Exception $e) {
			$l = "Error delDirs $q " . $e->getMessage();
			logger($l,5);
			die($l);
		}
	}
}

function getFileIds($dirid){
	$dbo=openDb();
	$stmt = $dbo->query("SELECT f.fileid, f.file, d.dir FROM files f INNER JOIN dirs d USING(dirid) WHERE dirid = $dirid");
	$rows = [];
	while ($row = $stmt->fetchArray(SQLITE3_ASSOC)) {
		$rows[] = $row;
	}
	return $rows;
}

function delFileRow($r){

	$rp = joinp($r['dir'],$r['file']);
	$fullp = PICTAP->path_pictures . $rp;
	if(!file_exists($fullp) && $r['ft']){
		del_thumb($rp);
	}

	$dbo=openDb();
	albumLinks(0,$r['fileid']);
	$q = "DELETE FROM files WHERE fileid = ".$r['fileid'];
	$r=null;
	try {
		$r = $dbo->exec($q);
	} catch (Exception $e) {
		$l = "Error delFileRow $d: $q " . $e->getMessage();
		logger($l,5);
		die($l);
	}
	return $r;
	
}

function insertFile($c, $file){
	$dbo=openDb();
	logger("insertFile: ".$c['dirid'].' '.$file);

	$c['fileid'] = $dbo->query("SELECT COALESCE(MIN(fileid + 1), 1) AS missing_id FROM files t1 WHERE NOT EXISTS ( SELECT 1 FROM files t2 WHERE t2.fileid = t1.fileid + 1 ) ORDER BY fileid LIMIT 1;")->fetchArray(SQLITE3_NUM)[0];
	
	$q = "INSERT OR IGNORE INTO files (file, ".implode(', ',array_keys($c)).") VALUES (:file, ".implode(', ',$c).")";

	try {
		$stmt = $dbo->prepare($q);
		$stmt->bindValue(':file', $file, SQLITE3_TEXT);
		$stmt->execute();
		return $c['fileid'];
		//$fileid=$dbo->lastInsertRowID();
	} catch (Exception $e) {
		$l = "Error insert $file: $q " . $e->getMessage();
		logger($l,5);
		die($l);
	}
}

function updateFile($fileid, $c){
	if(!$fileid){
		die('no fileid updateFile');
	}
	foreach(['locationid','devid'] as $k){
		if(array_key_exists($k,$c) && !$c[$k]){
			$c[$k] = 'NULL';
		}
	}
	$name=null;
	if(isset($c['file'])){
		$name=$c['file'];
		$c['file'] = ':file';
	}
	
	foreach($c as $k=>$v){
		$c[$k] = $k.' = '.$v;
	}

	$r=null;
	$dbo=openDb();
	$q = "UPDATE files SET ".implode(', ',$c)." WHERE fileid = $fileid";
	try {
		$stmt = $dbo->prepare($q);
		if($name!==null){$stmt->bindValue(':file', $name, SQLITE3_TEXT);}
		$stmt->execute();
		$r = $dbo->changes();
	} catch (Exception $e) {
		$l = "Error updateFile $fileid: $name $q " . $e->getMessage();
		logger($l,5);
		die($l);
	}
	return $r;
	
}

function getFileRow($id,$name=''){
	try {
		if(!is_numeric($id)){return null;}
		$dbo=openDb();
		$id = intval($id);
		$f = "f.fileid = $id";
		if($name !== ''){
			$f = "d.dirid = $id AND f.file = :file";
		}
		$q = "SELECT f.*, d.dir, d.parentid FROM files f INNER JOIN dirs d USING(dirid) WHERE ". $f . familyQ();
		$stmt = $dbo->prepare($q);
		if($name !== ''){
			$stmt->bindValue(':file', $name, SQLITE3_TEXT);
		}
		$res = $stmt->execute();
		$r = $res->fetchArray(SQLITE3_ASSOC);

		if ($r) {
			return $r;
		}
		return null;
	} catch (Exception $e) {
		die("Error: " . $e->getMessage());
	}
}




function gpsCity($lat=0,$lon=0){
	$dbo=openDb();
	$query = "SELECT locationid	FROM locations ORDER BY ABS(lat - $lat) + ABS(lon - $lon) LIMIT 1";
	$stmt = $dbo->query($query);
	$r = $stmt->fetchArray(SQLITE3_ASSOC);
	if ($r) {
		return $r['locationid'];
	}
	return null;
}


function dirJSON($dir, $posttime=0){
	
	$dirID = getDirRow($dir);
	if(!$dirID){sendjs(0,"Invalid dir $dir");}
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
		
	$dbo=openDb();
	$stmt = $dbo->query(fileSql()."
	WHERE dirid = ".$dirID['dirid']." GROUP BY f.fileid ORDER BY file ASC;");
	loopFiles($stmt, $f);
		
	return $f;
}



function validGps($lat, $lon) {
	return ($lat >= -90 && $lat <= 90 && $lon >= -180 && $lon <= 180);
}


function getExif(&$r){
	$rp = joinp($r['dir'],$r['file']);
	$fullp = PICTAP->path_pictures . $rp;
	if(!file_exists($fullp)){
		delFileRow($r);
		return;
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
				$r['locationid']=gpsCity($lat, $lon);
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
		if($r['ft'] === 1 && !empty($exif['Orientation'])){
			$ori = intval($exif['Orientation']);
		}		
		if($r['ft'] === 2 && !empty($exif['Rotation'])){
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
	$r['devid'] = getDevID($model);

	if(!empty($exif['Duration'])){$r['dur'] = floor($exif['Duration']);}
	
	$ky = array_flip(explode(' ','tk lat lon locationid w h ori devid dur th'));
	if($r['th'] === 2){$r['th']=1;}
	if(!$r['ft']){$r['th']=0;}
	foreach($ky as $k=>$v){
		$ky[$k] = $r[$k];
	}
	updateFile($r['fileid'], $ky);
	
}


function scanFolder($dir,$force=0){
	$dir = rtrim($dir,'/');
	if(!$dir || $dir === '.' || $dir === '..') sendjs(0,'badpath');
	$rpath = ltrim(relative($dir), '\/');
	$dirs=getDirID($rpath);

	$parentid=0;
	if(strlen($rpath)){
		$parentid = getDirID(dirname('/'.$rpath),false)['dirid'];
	}
	
	
	$dirsize = 0;
	$totalfiles = 0;

	$did = $dirs['dirid'];
	
	$dthm = $dirs['thm'];
	
	$old=[];
	
	
	$dbo=openDb();
	
	$stmt = $dbo->query("SELECT * from dirs WHERE parentid = $did;");
    while ($r = $stmt->fetchArray(SQLITE3_ASSOC)) {
		$d = $r['dir'];
		if (str_starts_with($d, $dirs['dir'])) {
			$d = substr($d, strlen($dirs['dir']));
		}
		$old[ ltrim($d,'/') ] = $r;
	}
	
	
	$stmt = $dbo->query("SELECT * FROM files WHERE dirid = $did ORDER BY file ASC;");
    while ($r = $stmt->fetchArray(SQLITE3_ASSOC)) {
		$old[ $r['file'] ] = $r;
	}
	openDb(-1);
	
	$dupchecker=[];
	$filenames = (file_exists($dir) && filetype($dir) === 'dir') ? scandir($dir, SCANDIR_SORT_NONE) : [];
	


	foreach($filenames as $filename) {

		if($filename === '.' || $filename === '..') continue;
		
		$path = $dir . '/' . $filename;

		$is_dir = filetype($path) === 'dir' ? true : false;

		if(is_exclude($path, $is_dir)){
			logger("excluded dir $path");
			continue;
		}

		$filemtime = filemtime($path);
		$is_readable = is_readable($path);
		$filesize = $is_dir ? 0 : filesize($path);
		
		$ext = '';
		$ft = 0;
		if(!$is_dir){
			$ext = strtolower(splitExt($path)[2]);
			$ft = in_array($ext, PICTAP->ext_videos)? 2 : (in_array($ext, PICTAP->ext_images)?1:0);
			if(!$filesize){$ft=0;}
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
					$totalfiles++;
					$dirsize += $filesize;
					$nameonly = splitExt($path);
					if(isset($dupchecker[$nameonly[0]])){
						
						$q=1;
						while(file_exists($nameonly[0].'_'.$q.'.'.$nameonly[2])){
							$q++;
						}
						$npath=$nameonly[0].'_'.$q.'.'.$nameonly[2];
						$filename = basename($npath);
						
						logger('** rename '.$path." to ".$npath);
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
					if($old[$filename]['th']==0 && !file_exists(PICTAP->path_thumbs . $tn)){
						logger('Missing thumb '.$tn);
						updateFile($cfid, ['th'=>2]);
					}
					if($filename === PICTAP->folder_thumb && !$dthm){
						$dthm = $cfid;
					}
				}
				unset($old[$filename]);
				continue;
			}
		}
		
		
		
		if( $is_dir ){
			
			$currsubdir = getDirID(joinp($dirs['dir'], $filename, 0));

			if( !empty($old[$filename])){//update
				unset($old[$filename]);
			}
			
			if($did !== $currsubdir['parentid']){
				updateDir($currsubdir['dirid'], ['parentid'=>$did]);
			}
			
			
		}else{ 
		
			$c = [
				'dirid' => $did,
				'ft' => $ft,
				'sz' => $filesize,
				'mt' => $filemtime,
			];
			if( !empty($old[$filename])){//update
				$c['th']=2;
				$cfid = $old[$filename]['fileid'];
				updateFile($cfid, $c);
				unset($old[$filename]);
			}else{
				if(!$ft){$c['tk']=$filemtime;}
				$cfid = insertFile($c,$filename);
			}
			if($filename === PICTAP->folder_thumb && !$dthm){
				$dthm = $cfid;
			}
				
		}

	}
	if(!empty($old)){
		foreach($old as $k => $v){
			if(array_key_exists('parentid',$v)){//isdir
				if($v['dir']){
					delDirRow($v['dir']);
					logger("Removed: ".$v['dir']);
				}else{
					logger("Baddirval: $k : ".$v['dir']);
				}
			}else{
				$p = joinp($dirs['dir'], $k);
				logger("Removed: ".$p);
				delFileRow(getFileRow($v['fileid']));
			}
		}
	}
	$dtime = filemtime($dir);
	if($dtime){
		$u = ['mt'=>$dtime, 'qt'=>$totalfiles, 'sz'=>$dirsize, 'parentid'=>$parentid, 'thm'=>$dthm];
		foreach($u as $k=>$v){
			if($dirs[$k]!=$v){
				updateDir($did, $u);
				break;
			}
		}
	}else{
		delDirRow($dirs['dir']);
		$dirs = null;
	}
	return $dirs;
}


function renameSubDir($old,$newdir){
	$res = getDirWild($old);
	foreach($res as $r) {
		$upd=trim($newdir . substr($r['dir'], strlen($old)),'/');
		updateDir($r['dirid'],['dir'=>$upd]);
	}
}


function loopFiles(&$stmt, &$f){

	while ($r = $stmt->fetchArray(SQLITE3_ASSOC)) {
		$j=[
			'n' => $r['file'],
			'd' => $r['dirid'],
			's' => $r['sz'],
			'm' => $r['mt'],
			't' => $r['tk']
		];
		
		if(isset($r['rank'])){
			$j['z'] = $r['rank'];
		}
		$r['city'] = implode(', ', array_filter(array_unique([$r['location'], $r['state'], $r['country']])));
		foreach(['w','h','ori','dev','lat','lon','city','th','dur','k'] as $k => $v){
			if(!empty($r[$v])){$j[$v] = $r[$v];}
		}
		$f['ids'][] = $r['fileid'];
		$f['fds'][] = $j;
	}
}

function sanitise_name($name,$isdir=0){
	$name = preg_replace('/[<>:"\/\\\|?*]|\.\.|\.$/', '', $name);
	$name = trim($name);
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



function sendThumb(&$r){
	if($r['th']===2){ getExif($r); }
	if($r['th']===1){ makeThumb($r); }
	
	if($r['th']){
		http_response_code(500);
	}else{
		$f = implode('/', array_map('rawurlencode', explode('/', thumb_name(joinp($r['dir'],$r['file'])))));
		cacheHdr();
		header("Location: ".PICTAP->url_thumbs.$f, true, 301);
	}
	exit;	
}

function getPostFile(&$fr, &$path){
	$fr = getFileRow(post('fid'));
	$path = post('name');
	if($fr){
		$path = PICTAP->path_pictures . joinp($fr['dir'],$fr['file']);
		if(!file_exists($path)){$fr=0;}
	}		
	if(!$fr){
		sendjs(0,"Invalid path ".$path);
	}
}

function getPostDir(&$id, &$dirpath){
	$id = getDirRow(post('id'));
	if(!$id){
		sendjs(0,"Invalid id ".post('id'));
	}
	$dirpath = rtrim(PICTAP->path_pictures.'/'.$id['dir'],'/');
}

function menuList(){
	$dbo=openDb();
	$cond = familyQ();
	$stmt = $dbo->query("SELECT dirid, dir, mt, sz, qt, parentid FROM dirs d WHERE 1 = 1 $cond ORDER BY dir ASC;");
	$m = 0;
	$menu=[];
	$root = 0;
	$home=0;
	$user = (USER->id === 1)? '' : USER->user;
	while ($r = $stmt->fetchArray(SQLITE3_ASSOC)) {
		$m = max($m, $r['mt']);
		$p = intval($r['parentid']);
		$l = [ $r['dir'], $p, $r['mt'], $r['sz'], $r['qt'] ];
		$menu[$r['dirid']] = $l;
		if($r['dir'] === $user){
			$home = $r['dirid'];
			$root = $p;
		}
		if($r['dir'] === ''){$root = $r['dirid'];}
	}
	
	if($root && !isset($menu[ $root ])){
		$menu[ $root ] = ['',0,0,0,0];
	}
	$a = [];
	$stmt = $dbo->query("SELECT albumid, name, qt, mtime, userid, share, family FROM albums WHERE userid = ".USER->id." OR family > 0 ORDER BY 
  CASE WHEN userid = ".USER->id." THEN 0 ELSE 1 END, mtime DESC;");
	while ($r = $stmt->fetchArray(SQLITE3_ASSOC)) {
		if(!$r['share']){$r['share'] = 0;}
		$own = $r['userid'] == USER->id ? 1 : 0;
		$a[] = [$r['albumid'], $r['name'], $r['qt'], $r['mtime'], $own, $r['family'], $r['share']];
	}
	
	
	return ['d' => (object) $menu, 'm' => $m, 'a' => $a, 'home' => $home, 'root' => $root ];
}

function reltiveroot($p){
	$r = $_SERVER['DOCUMENT_ROOT'];	
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
	return "SELECT f.*, l.location, s.state, c.country, v.dev, GROUP_CONCAT(t.tag, ', ') AS k $pre
	FROM files f
	INNER JOIN dirs d USING(dirid)
	LEFT JOIN locations l USING(locationid)
	LEFT JOIN states s USING(stateid)
	LEFT JOIN countries c USING(countryid)
	LEFT JOIN devs v USING(devid)
	LEFT JOIN tagfiles tf USING(fileid)
	LEFT JOIN tags t USING(tagid) ";
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
		logger("Removed Thumb: $p");
		@unlink($t);
	}

}



function splitExt($path){
    $basename = basename($path);
	$n = strrpos($basename,".");
	if($n === false){
		return [ $path, $basename, '', dirname($path)];
	}
	return [
		substr($path, 0, strrpos($path, ".")),	//[0] full path no ext
		substr($basename,0,$n),					//[1] filename only
		substr($basename,$n+1),					//[2] ext only
		dirname($path)							//[3] dirname
	];
}


function is_exclude($path, $isdir){

	if(!$path || $path === PICTAP->path_pictures) return;

	if(PICTAP->exclude_dirs) {
		$dirname = $isdir ? $path : dirname($path);
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
		$id = getDirRow($id);
		if(!$id){
			return [[0,"Invalid id ".get('updir')]];
		}
		$dirpath = rtrim($dirpath.'/'.$id['dir'],'/');
	}else{
		if(file_exists($dirpath.'/'.USER->user)){
			$dirpath .= '/'.USER->user;
		}
	}
	$id = getDirID(relative($dirpath),0);
	
	
	$rt=[];
	
	$file = !empty($_FILES['media']) && is_array($_FILES['media']) ? $_FILES['media'] : false;
	
	if(empty($file) || !isset($file['error']) || !is_array($file['error']) || count($file['error'])<1){
		return [[0,"Upload invalid files"]];
	}
	$count = count($file['name']);

	
	for ($i = 0; $i < $count; $i++) {

		if($file['error'][$i] !== UPLOAD_ERR_OK) {
			$upload_errors = [
				UPLOAD_ERR_INI_SIZE   => 'UPLOAD_ERR_INI_SIZE',
				UPLOAD_ERR_FORM_SIZE  => 'UPLOAD_ERR_FORM_SIZE',
				UPLOAD_ERR_PARTIAL    => 'UPLOAD_ERR_PARTIAL',
				UPLOAD_ERR_NO_FILE    => 'UPLOAD_ERR_NO_FILE',
				UPLOAD_ERR_NO_TMP_DIR => 'UPLOAD_ERR_NO_TMP_DIR',
				UPLOAD_ERR_CANT_WRITE => 'UPLOAD_ERR_CANT_WRITE',
				UPLOAD_ERR_EXTENSION  => 'UPLOAD_ERR_EXTENSION'
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
		if(
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


function post($key,$strict=0){
	if(!isset($_POST[$key]) || !is_string($_POST[$key])){
		
		return ($strict? null : '');
	}
	return $_POST[$key];
}
function get($key){
	if(!isset($_GET[$key]) || !is_string($_GET[$key])){
		return '';
	}
	return $_GET[$key];
}

function sendJSON($json){
	$json = empty($json) ? '{}' : json_encode($json, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
	
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
	if(logger($msg)) {
		exit;
	}

	if($code){http_response_code($code);}
	header('content-type: text/html');
	header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
	header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0, s-maxage=0');
	header('Cache-Control: post-check=0, pre-check=0', false);
	header('Pragma: no-cache');
	exit('<h2>Error</h2>' . $msg);
}

function thumbOk(&$r,$thm=0,$mtime=0){
	updateFile($r['fileid'], ['th'=>0]);
	$r['th'] = 0;
	if($mtime && $thm){
		touch($thm,$mtime);
	}
	return $thm;
}
function makeThumb(&$r, $forcethumb = 0){
	if(!($r['ft'] === 1 || $r['ft'] === 2)){
		return thumbOk($r);
	}
	openDb(-1);
	$rp = joinp($r['dir'],$r['file']);
	
	$org = PICTAP->path_pictures . $rp;
	$size=0;
	$ts = PICTAP->thumb_size;
	if(!$size){
		$size = $ts;
	}
	$thm = PICTAP->path_thumbs . thumb_name($rp, $size);
	makeDir(dirname($thm));
	$mtime = filemtime($org);
	if(!$mtime){//no File
		delFileRow($r);
		return 0;
	}
	if(file_exists($thm)){
		if(filemtime($thm) === $mtime && ($forcethumb == 0 /*|| $r['th']===0 */ )){
			return thumbOk($r,$thm);
		}
		unlink($thm);
	}
	
	if(!$r['w']){
		$m='Error: '.$rp." is not a image/video";
		logger($m);
		return thumbOk($r,$thm,$mtime);
	}
	
	$ext=splitExt($org)[2]; if(!$ext){$ext='.';}
	if($r['ft']===2){//video
		$cmd = 'nice -n 19 '.escapeshellarg(PICTAP->bin_ffmpeg).' -y -hide_banner -ss 0 -t 7 -threads 1 -i ' . escapeshellarg($org) . ' -threads 1 -an -vf "fps=2,scale=iw*sar:ih,scale=w='.$ts.':h='.$ts.':force_original_aspect_ratio=decrease,setsar=1:1" -loop 0 -quality 40 ' . escapeshellarg($thm) . ' 2>&1';
		makeDir(dirname($thm));
		$res=1;
		$output='';
		if(logger() || locker(30)){
			ignore_user_abort(true);
			exec($cmd, $output, $res);
		}else{
			return 0;
		};
		locker();//unlocks
		if($res){
			$msx="makeThumb Error $res $cmd";
			logger($msx."\n".print_r($output, true));
		}

		return thumbOk($r,$thm,$mtime);
	}
	
	$imginfo = getimagesize($org);
	if(empty($imginfo) || !is_array($imginfo)){
		$m='Error: '.$rp." getimagesize()";
		logger($m);
		return thumbOk($r,$thm,$mtime);
	}	

	if(PICTAP->max_mp && $imginfo[0] * $imginfo[1] > PICTAP->max_mp){
		$h = round(($ts/4)*3);
		exec('nice -n 19 '.escapeshellarg(PICTAP->bin_ffmpeg).' -y -hide_banner -threads 1 -i ' . escapeshellarg($org) . ' -threads 1 -vf scale='.$ts.':'.$h.' ' . escapeshellarg($thm) . ' 2>&1');
		$m='Error: '.$rp." exceeds max_mp";
		logger($m);
		return thumbOk($r,$thm,$mtime);
	}
	
	$ratio = max($imginfo[0], $imginfo[1]) / $size;
	$width  = round($imginfo[0] / $ratio);
	$height = round($imginfo[1] / $ratio);

	$type = $imginfo[2];
	$image = null;
	if($type === IMAGETYPE_JPEG){
		$image =  imagecreatefromjpeg($org);
	} else if ($type === IMAGETYPE_PNG) {
		$image =  imagecreatefrompng($org);
	} else if ($type === IMAGETYPE_GIF) {
		$image =  imagecreatefromgif($org);
	} else if ($type === IMAGETYPE_WEBP) {
		$image =  imagecreatefromwebp($org);
	} else if ($type === IMAGETYPE_BMP) {
		$image =  imagecreatefrombmp($org);
	} else if ($type === IMAGETYPE_AVIF) {
		$image = imagecreatefromavif($org);
	}else{
		
	}
	if(!$image){
		$m='Error: '.$rp." image_create_from()";
		logger($m);
		return thumbOk($r,$thm,$mtime);		
	}
	$new_image = imagecreatetruecolor($width, $height);

	if(!imagecopyresampled($new_image, $image, 0, 0, 0, 0, $width, $height, $imginfo[0], $imginfo[1])){
		$m='Error: '.$rp." imagecopyresampled()";
		logger($m);
		return thumbOk($r,$thm,$mtime);
	}
	imagedestroy($image);
	
	if(!empty($r['ori'])){
		$mirror = [0,0,1,0,1,1,0,1,0];
		$rotate = [0,0,0,180,180,270,270,90,90];
		if (!empty($rotate[$r['ori']])) {
			$new_image = imagerotate($new_image, $rotate[$r['ori']], 0);
		}
		if (!empty($mirror[$r['ori']])) {
			imageflip($new_image, IMG_FLIP_HORIZONTAL);
		}
	}

	$matrix = [ [-1, -1, -1], [-1, 20, -1], [-1, -1, -1] ];
	$divisor = array_sum(array_map('array_sum', $matrix));
	imageconvolution($new_image, $matrix, $divisor, 0);

	if(!imagewebp($new_image, $thm, PICTAP->thumb_quality)){
		$m='Error: '.$rp." imagewebp()";
		logger($m);
	}
	@imagedestroy($new_image);
	return thumbOk($r,$thm,$mtime);
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

function remSymLinks($d,$s=0){
	if(file_exists($d) && filetype($d) === 'dir'){
		$fn = scandir($d, SCANDIR_SORT_NONE);
		foreach($fn as $f){
			if($f === '.' || $f === '..') continue;
			$fp = $d.'/'.$f;
			if(is_link($fp)){
				unlink($fp);
			}
		}
		if($s){
			rmdir($d);
		}
	}
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
				return (object)['id'=>intval($id),'user'=>$user[0],'pass'=>$user[1],'hash'=>$user[0].'-'.md5($user[1] . $user[2] ),'role'=>$user[3]];
			}
		}
	}
	return null;
}

function saveLogin($f){
	return (makeDir(dirname($f)) && file_put_contents($f,''));
}


function userAuth($html=0){
	
	if(logger()) {
		$user = getUser(PICTAP->users[1][0]);
		define('USER', $user);
		return;
	}
	
	if($html && !file_exists(PICTAP->path_pictures)) {
		error('path_pictures missing.');
	}
	
	$cook = 'pictap';
	
	$c=isset($_COOKIE[$cook])? $_COOKIE[$cook] : '';
	$c=is_string($c)? $c : '';
	$carr=explode('-',$c.'');
	
	$user = getUser($carr[0]);
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
					//admin password set, change it in config.php
					$f = clone PICTAP;
					$z = $f->users[$user->id];
					$k=usergen($z[0],$p,$z[2],$z[3],$f);
					$f->users[$user->id]=$k;
					loadConfig($f);
					error('Password updated.', 401);
				}
				$q = md5($p . PICTAP->salt );
				
				$ip = PICTAP->path_data . '/badip/'.str_replace(':','.',$_SERVER['REMOTE_ADDR']);
				$bip = $ip.'.txt';
				$iplist=file_exists($bip);
				$valid = $user && $q === $user->pass;
				if(!$iplist && $valid){
					$authfile = PICTAP->path_data .'/auth/'.$user->hash.'.txt';
					setcookie($cook, $user->hash, time()+60*60*24*PICTAP->login_remember, "/");//90days
					saveLogin($authfile);
				} else {
					if($valid){$u=$p='*';}
					$t='['.date("Y-m-d H:i:s").'] ['.$_SERVER['REMOTE_ADDR'].'] '.$u.' - '.$p.' '.htmlspecialchars($_SERVER['HTTP_USER_AGENT'])."\n";
					
					@file_put_contents(PICTAP->path_data.'/failed_logins.log', $t, FILE_APPEND);
					
					
					if(!$iplist){
						@file_put_contents($ip, ' ', FILE_APPEND);
						if(intval(@filesize($ip))>PICTAP->login_attempts){
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
		if($html && $user && userCan('login',$user->role) &&  !file_exists($authfile)){
			saveLogin($authfile);
		}
	}
	
	if($user && !userCan('login',$user->role)){
		if(file_exists($authfile)){@unlink($authfile);}
		error('Account disabled', 401);
	}

	if(post('logout') && $user){
		setcookie($cook, '', time() - 3600, '/');
		@unlink($authfile);
		if(post('logout')!=='1'){
			$setup = clone PICTAP;
			$setup->users[$user->id][2] = bin2hex(random_bytes(4));
			loadConfig($setup);
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
	$out = '<details class="accounts"><summary><b>'.$nu.'</b></summary><div>'.itext('<i>Username:</i>','user'.$id, $user[0], 'Username, Clear to Delete a user', ' maxlength="16" pattern="^[A-Za-z0-9]*$"');
	
	$out .= itext('<i>Password:</i>', 'pass'.$id, $user[1], 'Password', $req.' maxlength="32"');
		
	if($id != '1'){
		$perm = (userCan('alldir',$user[3]) << 1) | userCan('family',$user[3]);
		$u = '/'.$ul;
		$out .= '<label><i>Permission:</i><br/><select name="perm'.$id.'">';
		foreach([$u.' Folder',$u.' + /Family Folders','All Folders'] as $i=>$t){
			$chk = ($perm == $i)? ' selected' : '';
			if($i === 1 && PICTAP->users[0][0] == ''){$chk = ' disabled';}
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

function itext($label, $name, $val, $ph='', $attr='', $type='text'){
	$o = '<p><label>'.$label;
	if($label !== ''){$o .= '<br/>';}
	$o .= '<input type="'.$type.'" name="'.$name.'" value="'.$val.'" placeholder="'.$ph.'"'.$attr.'></label></p>';
	return $o;
}

function usergen($u,$p,$t,$r,$s){
	$u = preg_replace("/[^A-Za-z0-9]/", '', $u);
	if(strlen($p) < 32){
		$p = md5($p.$s->salt);
	}
	return [$u,$p,$t,intval($r)];
}


function pageAccounts(){
	$setup = clone PICTAP;
	$url = strtok($_SERVER['REQUEST_URI'], '?');
	
	$ispost = post('submit');
	$newuser = [];
	$out='';
	$maxid = 0;
	
	if($ispost){
		$allu=[];
		foreach($setup->users as $id=>$u){
			$allu[$u[0]]=$id;
		}
		$idlist = explode(',', post('idlist'));
		$dbo=openDb();
		foreach($idlist as $id){
			$role = 0;
			$af = intval(post('perm'.$id));
			$_POST['alldir'.$id]=''.(($af >> 1) & 0x1);
			$_POST['family'.$id]=''.(($af & 0x1));
			foreach(ROLES as $r=>$n){
				if(post($r.$id)
					|| ($id == '1' && in_array($r,['admin','alldir','login']) )
				){
					$role |= $n;
				}
			}
			
			$tok = empty($setup->users[$id][2])? bin2hex(random_bytes(4)) : $setup->users[$id][2];
			$user = usergen(post('user'.$id), post('pass'.$id), $tok, $role, $setup);
			$ret = 0;
			
			if($user[0] && isset($allu[$user[0]]) && $allu[$user[0]] != $id){
				error('Duplicate user: '.$user[0]);
			}
			
			if(isset($setup->users[$id])){//existing user
				if($user[0] ==''){//delete
					if($id>1){//not admin
						//$dbo->exec("DELETE FROM users WHERE userid = ".$id);
						unset($setup->users[$id]);
					}
				}else{//update
					$setup->users[$id] = $user;
					//$dbo->exec("UPDATE users SET username = '".$user[0]."', password = '".$user[1]."', token = '".$user[2]."', role = ".intval($user[3])." WHERE userid = ".$id);
				}
			}else if($user[0] !==''){//create
				$setup->users[$id] = $user;
				$newp = PICTAP->path_pictures .'/'.$user[0];
				makeDir($newp);
				scanFolder($newp);
				//$dbo->exec("REPLACE INTO users (userid,username,password,token,role) VALUES (".$id.", '".$user[0]."', '".$user[1]."', '".$user[2]."', ".intval($user[3]).")");
			}

		}
		$saved = loadConfig($setup);
		$msg = '<div style="text-align:center">';
		if(post('ipclear')){
			flushIP(0);
			$msg .= '<h3>IP Cleared</h3>';
		}
		if(!$saved){
			$msg .= '<h3>Error Saving</h3>';
		}else{
			$msg .= '<h3>Saved sucessfully</h2><h3><a href="'.$url.'">View Gallery</a></h3>';
		}
		$out = $msg.'</div>'.$out;		
	}
	

	$idlist=[];
	foreach($setup->users as $id=>$user){
		if($id<1){continue;}
		$maxid = max(intval($id),$maxid);
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
	
	$out='<form style="padding:1em" method="post" action="'.$url.'?page=settings" name="login" autocomplete="off">';
	
	$input=['users'=>['0'=>['','','',0],'1'=>['Admin','','',0xfff]]]; //id => [user, pass, token, role]
	$inituser=1;
	
	$setup = clone $oldsetup;
	
	if(property_exists($setup,'users')){
		$input['users'] = $setup->users;
		$inituser=0;
	}
	
	$rp = dirname(__FILE__).'/';
	$pp = $rp.'pictures';
	$tp = $rp.'thumbs';
	$db = $rp.'data';
	$sp = $rp.'shared';
	
	$defs = [ //[type, default, required, title, extra]
		'admin_user'=> ['text', $input['users'][1][0], 1, 'Main user','pattern="^[A-Za-z0-9]*$"'],
		'admin_pass'=> ['text', $input['users'][1][1], 1, 'Less then 32 chars'],
		'path_pictures' => ['text', $pp, 1, 'Main folder path eg. '.$pp],
		'path_thumbs' 	=> ['text', $tp, 1, 'Thumb folder path eg. '.$tp],
		'path_shared' 	=> ['text', $sp, 1, 'Shared Albums folder path eg. '.$sp],
		'path_recycle' 	=> ['text', '', 0, 'Trash path (leave blank to disable) eg. '.$rp.'trash'],
		'path_data' 	=> ['text', $db, 1, 'folder path to store login tokens, can be anywhere outside public folder eg. '.$db],
		'db_file' 		=> ['text', $db .'/pictap.db', 1, 'sqlite database file eg. '.$db .'/pictap.db'],
		'family_dir' 	=> ['text', 'Family', 0, 'Name of shared folder eg. Family, leave blank to disable','pattern="^[A-Za-z0-9]*$"'],
		'url_pictures' 	=> ['text', reltiveroot($rp.'pictures'), 1, 'Full or relative url of main pictures folder'],
		'url_thumbs' 	=> ['text', reltiveroot($rp.'thumbs'), 1, 'Full or relative url of thumbs folder'],
		'url_shared' 	=> ['text', reltiveroot($rp.'shared'), 1, 'Full or relative url of shared folder'],
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
		'exclude_dirs' 	=> ['text', '', 0,'Regex for preg_match eg. <code>/\/bank|\/house(\/|$)/i</code> to exclude bank* or /house/* dirs'],
		'exclude_files' => ['text', '', 0,'Regex for preg_match eg. <code>/\.(gif|png)$/i</code> to exclude .gif/.png files'],
		'folder_thumb' 	=> ['text', '', 0,'Default Folder Thumbnail (optional) eg. folder.jpg'],
		'search_max_results' 	=> ['number', 1000, 1, ''],
		'auto_hide_slideshow_ui' => ['number', 0, 1,'0 = disable, 4 = after 4 sec ...'],
		'max_mp' 		=> ['number', 6000 * 5000, 1,'Width x Height, Larger images may not get thumbnails'],
		'auto_rename' 	=> ['tick', 0, 1, 'Auto Rename IMG_/VID_date_time.* to date_time.*'],
		'debug' 		=> ['tick', 0, 1,'Debug'],
		'debug_file' 	=> ['text', '', 0,'Debug file (optional) eg. '. $db.'/debug.log'],
	];

	if(post('submit')){
		foreach ($defs as $k=>$v){
			if(!$inituser && in_array($k,['admin_user','admin_pass'])){continue;}
			$i = trim(post($k));
			if($v[0] === 'number'){$i = intval($i);}
			if($v[0] === 'tick'){$i = $i? 1 : 0;}
			$input[$k]=$i;
		}
		
		$input['salt'] = $setup->salt;
		foreach(['pictures','thumbs','recycle','data','shared'] as $s){
			$i = rtrim(str_replace('\\','/',$input['path_'.$s]),'/');
			$input['path_'.$s] = $i;
			if($i){
				if(!(is_dir(dirname($i)) && makeDir($i))){
					error('Failed to create '.$i);
				}
			}
		}
		foreach(['images','videos','uploads'] as $s){
			$input['ext_'.$s] = explode(',',$input['ext_'.$s]);
		}
		
		$input['users'][0] = usergen($input['family_dir'], 0, 0, 0, $setup);
		unset($input['family_dir']);
		$family = $input['users'][0][0];
		if($family){
			$np = $input['path_pictures'] .'/'.$family;
			makeDir($np);
		}
		
		if($inituser){
			$input['users'][1] = usergen($input['admin_user'], $input['admin_pass'], bin2hex(random_bytes(4)), 0xfff, $setup);
			if(!$input['users'][1][0] || !$input['admin_pass']){die('invalid user/pass');}

			unset($input['admin_user']);
			unset($input['admin_pass']);

		}
		
		$input['version'] = PIC_VER;
		//for future db upgrades
		$newver = explode(',', PIC_VER[1]); //[config,db]
		$oldver = explode(',', $setup->version[1]);

		$msg = '';
		$setup = loadConfig($input);
		if($setup){
			$setup = (object) $setup;
			$msg .= '<h3>Saved sucessfully</h2><h3><a href="'.$url.'">View Gallery</a></h3>';
			
			
			if(!file_exists($setup->db_file)){
				$tf = $setup->path_data .'/tables.sql';
				$t = @file_get_contents($tf);
				$gf = $setup->path_data .'/gps.sql';
				$g = @file_get_contents($gf);
				if(!$t){error('missing '.$tf);}
				if(!$g){error('missing '.$gf);}
				$dbo=openDb($setup->db_file);
				$dbo->exec($t);
				$dbo->exec($g);
				$msg .= '<div>'.$setup->db_file.' created</div>';
			}
			$dbo=openDb($setup->db_file);
			if($inituser){
				//$dbo->exec("REPLACE INTO users (userid,username,password,token,role) VALUES (1, '".$setup->users[1][0]."', '".$setup->users[1][1]."', '".$setup->users[1][2]."', ".intval($setup->users[1][3]).")");
				$inituser=0;
			}
			foreach ($setup as $k=>$v){
				if($k=='users'){continue;}
				$stmt = $dbo->prepare("REPLACE INTO config (key, value) VALUES ( '$k', :val )");
				if(is_array($v)){$v = json_encode($v,JSON_UNESCAPED_SLASHES);}
				$stmt->bindValue(':val', $v, SQLITE3_TEXT);
				$stmt->execute();
			}
			

		}else{
			$msg .= '<h3>Error Saving</h2>';
		}
		$out = $msg.$out;		
	}
	if(!$inituser){
		$setup->family_dir = $setup->users[0][0];
	}
	foreach ($defs as $k=>$v){
		if(!$inituser && in_array($k,['admin_user','admin_pass'])){continue;}

		$sv = property_exists($setup,$k) ? $setup->$k : $v[1]; 
		if(is_array($sv)){
			$sv = implode(',',$sv);
		}
		if($v[0] === 'tick'){
			$out .= '<p>'. itick($k, $sv, 0, $v[3]) .'</p>';
		}else{
			$req = $v[2] ? ' required':'';
			if(isset($v[4])){$req .= ' '.$v[4];}
			
			$out .= itext('<i>'.$k.':</i> '. $v[3], $k, $sv, $v[1], $req, $v[0]);
		}
	}
	
	if(!$inituser){
		$out .='<a class="btn" style="max-width:170px;font-size:14px" href="'.$url.'">Close</a>';
	}
	

	$out .='<input style="max-width:170px;font-size:14px" class="btn default" type="submit" name="submit" value="Save">';

	$out .= '</form>';
	
	htmldoc('<h1 style="text-align:center">Settings</h1>'.$out);
}




function albumLinks($act,$fid,$alb=0){
	$fid=is_array($fid)? implode(',',$fid) : $fid;
	if(!$fid){return;}
	$cond = "fileid IN ($fid)";
	if($alb){
		$cond = "albumid = $fid";
	}
	$dbo=openDb();
	$q="SELECT fileid, ft, th, share, dir, file from albums a 
	INNER JOIN albumfiles af USING (albumid) 
	INNER JOIN files f USING (fileid)
	INNER JOIN dirs d USING(dirid)
	WHERE $cond AND share IS NOT NULL";
	try {
		$stmt = $dbo->query($q);
	} catch (Exception $e) {
		die("Error: ".$q . $e->getMessage());
	}
	while ($r = $stmt->fetchArray(SQLITE3_ASSOC)) {
		$sp = PICTAP->path_shared .'/'.$r['share'];
		$t = PICTAP->path_thumbs . thumb_name(joinp($r['dir'],$r['file']));
		$tl = $sp.thumb_name($r['fileid'].'t-'.$r['file']);
		
		$p = PICTAP->path_pictures . joinp($r['dir'],$r['file']);
		$pl = $sp.'/'.$r['fileid'].'f-'.$r['file'];
		
		if($act!==2){//del if not insert
			if($r['ft']){@unlink($tl);}
			@unlink($pl);
		}
		if($act===1){//update
			makeDir($sp);
			if($r['ft'] && !$r['th']){symlink($t,$tl);}
			symlink($p,$pl);
		}
	}
}




if(post('task')){

	userAuth();
	
	$task = post('task');
	$name = trim(preg_replace('/\s+/', ' ', str_replace(['\\','/'],' ',post('name'))));
	
	
	if($task === 'album' && userCan('album')){
		$act=post('act');
		$dbo=openDb();
		$aid = intval(post('aid'));
		$sp = PICTAP->path_shared.'/';
		$uf = "AND (userid = ".USER->id." OR family > 0)";
		$ua=0;
		if($aid){
			$aq="SELECT * from albums WHERE albumid = $aid $uf";
			$stmt = $dbo->query($aq);
			if($r = $stmt->fetchArray(SQLITE3_ASSOC)) {
				$ua = $r;
			}			
		}
		if($act == 'edit'){
			$fam = intval(post('fam'));
			$shr = post('shr');
			if(!$name){sendjs(0,'Invalid name');}
			$shn = 0;
			if($shr=='0'){
				$shr = 'NULL';
			}else{
				$shn = str_replace(' ','',sanitise_name($shr,1));
				$shr = ':shr';
			}
			$r=0;
			if(!$aid){//add
				$query = "INSERT INTO albums (userid, name, mtime, share, family) VALUES (".USER->id.", :kwd, ".time().", $shr, $fam);";
				
			}else{//edit
				if($ua) {
					if(USER->id != $ua['userid']){
						$fam = $ua['family'];
					}
					$query = "UPDATE albums SET name = :kwd, mtime = ".time().", share = $shr, family = $fam WHERE albumid = $aid;";
				}else{
					sendjs(0,'Invalid id');
				}	
			}
			$stmt = $dbo->prepare($query);
			$stmt->bindValue(':kwd', $name, SQLITE3_TEXT);
			if($shn){$stmt->bindValue(':shr', $shn, SQLITE3_TEXT);}
			$t = $stmt->execute();
			if ($t === false) {
				sendjs(0, "Error: " . $dbo->lastErrorMsg());
			}
			if($ua){
				if($shn && $ua['share'] && $shn !== $ua['share']){//rename dir
					@rename($sp.$ua['share'], $sp.$shn);
				}else if($ua['share'] && !$shn){//del dir
					albumLinks(0,$aid,1);
					remSymLinks($sp.$ua['share'],1);
				}else{//update
					albumLinks(1,$aid,1);
				}
				
			}
			$msg = 'Album Updated: '.$name;
			if(!$aid){$msg = $dbo->lastInsertRowID();}
			sendjs(1, $msg, ['Dir' => menuList()]);
			
		}else if($act == 'rema' && $aid){
			if(!$ua){sendjs(0,'Invalid request');}
			albumLinks(0,$aid,1);
			$r = $dbo->exec("DELETE FROM albumfiles LEFT JOIN albums a USING(albumid) WHERE albumid = $aid $uf");
			$fr = $dbo->changes();
			if($ua['share']) {
				@unlink($sp.$ua['share']);
			}
			$r = $dbo->exec("DELETE FROM albums WHERE albumid = $aid $uf");
			if ($r === false) {
				sendjs(0, "Error: " . $dbo->lastErrorMsg());
			}
			
			sendjs(1, 'Album Removed: '.$name.' ('.$fr.')', ['Dir' => menuList()]);
			
		}else if($act == 'add' || $act == 'rem'){
			$fids = array_filter(explode(',',preg_replace("/[^0-9,]/", '', post('fids'))));
			if(!$ua || !$aid || !count($fids)){sendjs(0,'Invalid request');}
			$lid=[];
			if($act == 'add'){
				for($i=0;$i<count($fids);$i++){
					$lid[]=$fids[$i];
					$fids[$i] = '('.$aid.','.$fids[$i].')';
				}
				$fids = implode(',',$fids);
				$query = "INSERT OR IGNORE INTO albumfiles (albumid, fileid) VALUES $fids;";
				
			}else{
				for($i=0;$i<count($fids);$i++){
					$lid[]=$fids[$i];
					$fids[$i] = '(albumid = '.$aid.' AND fileid = '.$fids[$i].')';
				}
				$fids = implode(' OR ',$fids);
				$query = "DELETE FROM albumfiles WHERE $fids;";
				albumLinks(0,$lid);
			}
			
			$r = $dbo->exec($query);
			if ($r === false) {
				sendjs(0, "Error: " . $dbo->lastErrorMsg());
			}
			if($act == 'add'){
				albumLinks(1,$lid);
			}
			$tot = $dbo->changes();
			$dbo->exec("UPDATE albums SET mtime = ".time().", qt = (SELECT COUNT(*) FROM albumfiles af WHERE af.albumid = $aid) WHERE albums.albumid = $aid");
			sendjs(1, $tot.' Files '.($act == 'add' ? 'Added': 'Removed'), ['Dir' => menuList()]);
		}

	}else if($task === 'city' && userCan('edit')){
		$name = addcslashes($name, '%_');
		$dbo=openDb();
		$query = "SELECT l.location, s.state, c.country, l.lat, l.lon
		FROM locations l
		INNER JOIN states s ON l.stateid = s.stateid
		INNER JOIN countries c ON l.countryid = c.countryid
		where l.location LIKE :kwd COLLATE NOCASE 
		OR s.state LIKE :kwd COLLATE NOCASE
		OR c.country LIKE :kwd COLLATE NOCASE
		LIMIT 10";
		$stmt = $dbo->prepare($query);
		$stmt->bindValue(':kwd', "%$name%", SQLITE3_TEXT);
		$res = $stmt->execute();
		$rv = [];
		while ($r = $res->fetchArray(SQLITE3_ASSOC)) {
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
			$dbo=openDb();
			$family = familyQ();
			$kwds = [];
			$joins = '';
			$rank = $limit = '';
			if($mode === 'd'){
				$out = dirJSON(intval($name), intval(post('mt')));
				sendJSON($out);
				
			}else if($mode === 't'){
				if($name==='Timeline'){
					$f['i']=[];
					$query = "SELECT COUNT(*) as n, strftime('%Y-%m', datetime(f.tk, 'unixepoch')) as m FROM files f
					INNER JOIN dirs d USING(dirid)
					WHERE 1 = 1 $family GROUP BY m ORDER BY m DESC;";
					$stmt = $dbo->query($query);
					
					while ($r = $stmt->fetchArray(SQLITE3_ASSOC)) {
						$f['i'][$r['m']] = $r['n'];
					}					
				}else{
					$name = preg_replace("/[^0-9-]/", '', $name);
					$s = '%Y';
					$d = explode('-',$name);
					if(count($d)>1){$s.='-%m';}
					if(count($d)>2){$s.='-%d';}
					$cond = "strftime('$s', datetime(f.tk, 'unixepoch')) = '".$name."'";
				}
			}else if($mode === 'a'){
				if(!userCan('album')){die;}
				if($name==='Albums'){
					$query = "SELECT t.cat, t.tag, COUNT(tf.tagid) AS n
					FROM tags t
					INNER JOIN tagfiles tf USING(tagid)
					INNER JOIN files f USING(fileid)
					INNER JOIN dirs d USING(dirid)
					WHERE 1 = 1 $family GROUP BY t.tag;";
					$stmt = $dbo->query($query);
					$f['i']=[];
					while ($r = $stmt->fetchArray(SQLITE3_ASSOC)) {
						if(!isset($f['i'][ $r['cat'] ])){$f['i'][ $r['cat'] ] = [];}
						$f['i'][ $r['cat'] ][ $r['tag'] ] = $r['n'];
					}
				}else{
					$name = preg_replace("/[^0-9]/", '', $name);
					if(!$name){sendJSON($f);}
					$joins = 'LEFT JOIN albumfiles af USING(fileid) LEFT JOIN albums a USING(albumid)';
					$cond = "albumid = $name AND (userid = ".USER->id." OR family > 0)";
					
				}
			}else if($mode === 'k'){
				if($name==='Tags'){
					$query = "SELECT t.cat, t.tag, COUNT(tf.tagid) AS n
					FROM tags t
					INNER JOIN tagfiles tf USING(tagid)
					INNER JOIN files f USING(fileid)
					INNER JOIN dirs d USING(dirid)
					WHERE 1 = 1 $family GROUP BY t.tag;";
					$stmt = $dbo->query($query);
					$f['i']=[];
					while ($r = $stmt->fetchArray(SQLITE3_ASSOC)) {
						if(!isset($f['i'][ $r['cat'] ])){$f['i'][ $r['cat'] ] = [];}
						$f['i'][ $r['cat'] ][ $r['tag'] ] = $r['n'];
					}
				}else{
					$cond = "1 = 1";
					$limit = 'HAVING SUM(CASE WHEN t.tag = :kwd THEN 1 ELSE 0 END)> 0';
				}
			}else{// s
				if(!userCan('search')){sendjs(0,"Search Disabled");}
				$kwds = array_unique(explode(' ', $name));
				
				$limit = 'ORDER BY rank DESC LIMIT '.PICTAP->search_max_results.';';
				$cond = [];
				$rank = [];
				$scol = ['file', 'l.location', 's.state', 'c.country','v.dev','t.tag'];
				foreach ($kwds as $i => $kwd) {
					foreach($scol as $c){
						$a = $c.' LIKE :kwd'.$i.' COLLATE NOCASE';
						$cond[] = $a;
						$rank[] = 'CASE WHEN '.$a.' THEN 1 ELSE 0 END';
					}
					$ft = 0;
					if(stripos($kwd,'photo') !== false){
						$ft = 1;
					}
					if(stripos($kwd,'video') !== false){
						$ft = 2;
					}
					if($ft){
						$a = 'ft = '.$ft;
						$cond[] = $a;
						$rank[] = 'CASE WHEN '.$a.' THEN 1 ELSE 0 END';
					}
				}

				$cond = '(' . implode(' OR ', $cond) .') AND rank > 0';
				$rank = ', (' . implode(' + ', $rank) .') AS rank';
				

			}
			
			
			if(!$query){
				$cond .= $family;
				$query = fileSql($rank)." $joins
				WHERE $cond GROUP BY f.fileid ".$limit;
				//die($query);
				$stmt = $dbo->prepare($query);
				if($mode === 'k'){
					$stmt->bindValue(':kwd', $name, SQLITE3_TEXT);
				}
				foreach ($kwds as $i => $kwd) {
					$stmt->bindValue(':kwd'.$i, "%$kwd%", SQLITE3_TEXT);
				}
				$res = $stmt->execute();
				loopFiles($res, $f);
			}
			if(empty($f['mt'])){
				$f['mt'] = $dbo->query("SELECT MAX(mt) FROM dirs d WHERE 1 = 1 $family LIMIT 1;")->fetchArray(SQLITE3_NUM)[0];
			}
			
			
		}
			
		sendJSON($f);
		
	}else if($task === 'gps-tag' && userCan('edit')){
		getPostFile($fr, $path);
		if(!$fr['ft']){sendjs(0,"Unsupported: ".basename($path));}
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
		updateFile($fr['fileid'],['lat'=>round($lat*10000),'lon'=>round($lon*10000),'locationid'=>gpsCity($lat, $lon)]);
		
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
			$i = '-Orientation='.$p['r'];
			if(post('vrot')){$i = '-rotation<${rotation;$_ += '.$p['r'].'}';}
			
			$cmd = escapeshellarg(PICTAP->bin_exiftool).' -n \''.$i.'\' -m -api largefilesupport=1 -overwrite_original '.$ef;
			
			if(locker(15)){
				ignore_user_abort(true);
				exec($cmd, $output,	$result);
				locker();//unlocks
			}else{
				sendjs(0,"System busy, please try again");
			}
			
			if ( 0 !== $result) {
				sendjs(0,"exiftool failed ".implode('',$output)."<br>".$cmd);
			}
		}
		if($p['w'] && $p['h']){
			exec(escapeshellarg(PICTAP->bin_jpegtran).' -optimize -copy all -crop '.$p['w'].'x'.$p['h'].'+'.$p['x'].'+'.$p['y'].' -outfile '.$ef.' '.$ef, $output, $res);
			if ( 0 !== $res) {
				sendjs(0,"jpegtran failed ".implode('',$output));
			}
		}
		scanFolder(dirname($path));
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
				scanFolder(dirname($dirpath));
				sendjs(1,"Deleted /".$id['dir']);
			}
		} else {// file
			getPostFile($fr, $path);
			$rp = relative($path);
			if($fr['ft']){del_thumb($rp);}
			if(PICTAP->path_recycle){
				$binpath=PICTAP->path_recycle.$rp;
				
				makeDir(dirname($binpath));
				$res=safe_rename($path,$binpath)[0];
			}else{
				$res=@unlink($path);
			}
			if($res){
				scanFolder(dirname($path));
				sendjs(1,"Deleted ".$rp);
			}else{
				sendjs(0,"Failed to delete ".$rp);
			}
		}

	} else if($task === 'move' && userCan('move')){
		getPostDir($id,$dirpath);

		$newid = getDirRow(post('new'));
		
		if(empty($newid)){
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
					makeDir(dirname($thmto));
					@rename($thmfrom, $thmto);
					locker();//unlocks
					$newparent = $newid['dirid'];
					updateDir($id['dirid'],['dir'=>ltrim($newdir, '\/'),'parentid'=>$newparent]);
					
					renameSubDir($id['dir'], ltrim($newdir, '\/'));
					scanFolder(dirname($dirpath));
					scanFolder(dirname($new_path));

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
					makeDir(dirname(PICTAP->path_thumbs.$thmto));
					@rename(PICTAP->path_thumbs.$thmfrom, PICTAP->path_thumbs.$thmto);
					locker();//unlocks
					updateFile($fr['fileid'], ['dirid'=>$newid['dirid'] ]);
					albumLinks(1,$fr['fileid']);
					scanFolder(dirname($new_path));
					sendjs(1,"Moved ".$relpath);
				}
				locker();//unlocks
			}
			sendjs(0,"Failed to move ".$relpath);
			
			
		}
		
	} else if($task === 'thumb' && userCan('edit')){
		getPostFile($fr, $path);
		if(!$fr['ft']){sendjs(0,"Unsupported: ".basename($path));}
		$ni = getDirRow(post('new'));
		
		if(empty($ni)){
			sendjs(0,"Invalid id ".post('new'));
		}
		$m = time();
		touch(rtrim(PICTAP->path_pictures.'/'.$ni['dir'],'/'),$m);
		updateDir($ni['dirid'],['thm'=>$fr['fileid'],'mt'=>$m]);
		sendjs(1, "Updated Thumbnail: ".$ni['dir'], ['Dir' => menuList()]);
		
	} else if($task === 'rename' && userCan('rename')){
		getPostDir($id,$dirpath);

		if(post('type')==='dir'){
			
			$newname = sanitise_name(post('new'),1);
			$new_path = dirname($dirpath) . '/' . $newname;
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
					
					makeDir(dirname($thmto));
					@rename($thmfrom, $thmto);
					locker();//unlocks
					updateDir($id['dirid'],['dir'=>ltrim($newdir, '\/')]);
					
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
			$new_path = dirname($path) . '/' . $newname;
			$ext = splitExt($newname)[2];
			
			if($newname === '' || (
				!in_array($ext, PICTAP->ext_images) &&
				!in_array($ext, PICTAP->ext_videos)
			)){
				sendjs(0,"Invalid name ".post('new'));
			}
			if(file_exists($new_path)){
				sendjs(0,"Already exists ".post('new'));
			}
			if(locker(10)){
				$res = rename($path, $new_path);
				if($res){

					$thmfrom = thumb_name(relative($path));
					$thmto = thumb_name(relative($new_path));
					@rename(PICTAP->path_thumbs.$thmfrom, PICTAP->path_thumbs.$thmto);
					locker();//unlocks

					updateFile($fr['fileid'],['file'=>$newname]);
					albumLinks(1,$fr['fileid']);
					scanFolder(dirname($new_path));
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
	
	if(get('ithumb')){//make thumb
		userAuth();
		$fid = intval(get('ithumb'));
		if($fid){
			$fid = getFileRow($fid);
		}
		if($fid ){
			if($fid['th']===2){
				getExif($fid);
			}
			if($fid['th']===1){
				if(makeThumb($fid)){$fid['th']=0;}
			}
			if(!$fid['th']){
				sendjs(1, $fid);
			}
				
		}
		sendjs(0, "thumb error ".get('n'));
		
	}else if(get('fthmb')){//folder thumb
		userAuth();
		$id = intval(get('fthmb'));
		if(!$id){http_response_code(400);exit;}
		$dbo=openDb();
		$c = "d.dirid = $id";
		$d = getDirRow($id);
		if($d['thm']){$c = "f.fileid = ".$d['thm'];}
		$stmt = $dbo->query("SELECT f.*, d.dir from files f INNER JOIN dirs d USING(dirid) WHERE f.ft <> 0 AND $c ".familyQ()." ORDER BY tk DESC Limit 1");

		if($r = $stmt->fetchArray(SQLITE3_ASSOC)){
			sendThumb($r);
		}else{//fallback thumbnail
			$stmt = $dbo->prepare("SELECT f.*, d.dir from files f INNER JOIN dirs d USING(dirid) WHERE f.ft <> 0 AND dir LIKE :subd ORDER BY tk DESC Limit 1");
			$epath = addcslashes($d['dir'], '%_');
			$stmt->bindValue(':subd', "$epath/%", SQLITE3_TEXT);
			$res = $stmt->execute();
			if($r = $res->fetchArray(SQLITE3_ASSOC)){
				sendThumb($r);
			}
		}
		cacheHdr();
		http_response_code(204);
		exit;
		
	}else if(get('tthmb')){//timeline thumb
		userAuth();
		
		$dt = preg_replace("/[^0-9-]/", '', trim(get('tthmb')));
		if(!$dt){http_response_code(400);exit;}
		
		$s = '%Y';
		$d = explode('-',$dt);
		if(count($d)>1){$s.='-%m';}
		if(count($d)>2){$s.='-%d';}
		
		$cond = "strftime('$s', datetime(f.tk, 'unixepoch')) = '".$dt."'".familyQ();
		
		$q = "SELECT f.*, d.dir from files f INNER JOIN dirs d USING(dirid) WHERE f.ft <> 0 AND $cond ORDER BY f.tk DESC LIMIT 1";
		$dbo=openDb();
		$stmt = $dbo->query($q);

		if($r = $stmt->fetchArray(SQLITE3_ASSOC)){
			sendThumb($r);
		}
		cacheHdr();
		http_response_code(204);
		exit;
		
	}else if(get('kthmb')){//tags thumb
		userAuth();
		
		$kw = trim(get('kthmb'));
		if(!$kw){http_response_code(400);exit;}
		
		$q = "SELECT f.*, d.dir FROM tags t
		INNER JOIN tagfiles tf USING(tagid)
		INNER JOIN files f USING(fileid)
		INNER JOIN dirs d USING(dirid)
		WHERE f.ft <> 0 AND t.tag = :kw ".familyQ()." ORDER BY f.tk DESC LIMIT 1";
		$dbo=openDb();
		$stmt = $dbo->prepare($q);
		$stmt->bindValue(':kw', $kw, SQLITE3_TEXT);
		$res = $stmt->execute();
		if($r = $res->fetchArray(SQLITE3_ASSOC)){
			sendThumb($r);
		}
		cacheHdr();
		http_response_code(204);
		exit;
		
	}else if(get('athmb')){//albums thumb
		userAuth();
		
		$aid = intval(get('athmb'));
		if(!$aid){http_response_code(400);exit;}
		
		$q = "SELECT f.*, d.dir FROM files f
		INNER JOIN dirs d USING(dirid)
		INNER JOIN albumfiles af USING(fileid)
		WHERE f.ft <> 0 AND af.albumid = $aid ".familyQ()."
		ORDER BY f.tk DESC LIMIT 1;";
		$dbo=openDb();
		$res = $dbo->query($q);
		if($r = $res->fetchArray(SQLITE3_ASSOC)){
			sendThumb($r);
		}
		cacheHdr();
		http_response_code(204);
		exit;
		
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
		$p=base64_decode('iVBORw0KGgoAAAANSUhEUgAAALQAAAC0AgMAAABAo+6hAAAACVBMVEX////MycfaZRicHJzVAAABk0lEQVR42u3XsW3DMBAFUNqFC4/gETKFl/gsOMJN4RHUsE9hAqGnTByFOMShiH8yHajg7wg8CMJRn4TczRI39NBDDz0nikFPzp1ond1XaD3d9ZHV7p4dqa/uO8LpadYnTp9nfeC0m7On9If7CaWvRQuj34t+Y/RU9InRl6KP/fW56INJ7xnthiYm2G93Luv00fbF2tvAN613i3V7doTWoRysJ1v/UzPra3c97fXhnW8pDcJW7uKhN6eTWDRg0BkIvE6A53UEILQGgMDqbNIJALxJg9VxhRZSw6LzrINJe06nNRqcjqu0UBqrdGB0XqF1hMm3dHrQgDAa2uhlHVVrowktZdnQUF2WwuigjV7SWbXXRls06loHWFDktWhHF3SEJmhHCe1vyaBRVn5JoxqblrrOPXSo61TX3qRR17GLlqrGQoJJ+5rOfTRqOi1qMelQ0bGT9iaNisZy5EmdGzqYtP+jExp5UseWFpMOjxqt+BdqPOjc1vJCHX7rNLRZv34v+a+Kb4O3dB6y/b/RoYceeuj/0Z9NtUMI+7S5oQAAAABJRU5ErkJggg==');
	}else if($f=='svg'){
		header('Content-Type: image/svg+xml');
		$p = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48"><path fill="#da5f0d" d="M10 1c-6 0-9 2-9 9v28c0 7 3 9 9 9h28c6 0 9-2 9-9V10c0-7-3-9-9-9H10"/><path fill="#cacaca" d="M17 20c-2 1-2 1-9 16-1 2 0 4 2 4h27c2 0 3-2 2-4l-6-8c-6-9-6 8-11-3-2-4-3-5-5-5"/><path fill="#fff" d="M28 7a5 5 90 1 0 1 10 5 5 90 0 0-1-10"/></svg>';
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
	}
	header('Content-Length: '.strlen($p));
	die($p);
}

function login_page($attempt){
	flushIP();
	$per=$attempt ? '<p style="color:red">Error! Please try again</p>' : '';

	$url=strtok($_SERVER['REQUEST_URI'], '?');
	http_response_code(401);
	header('Cache-Control: no-cache, must-revalidate');
	echo '<!DOCTYPE html><html><head><title>Login</title><link rel="icon" type="image/svg+xml" href="'.$url.'?file=svg" /><meta name="viewport" content="width=device-width, initial-scale=1" /><style>body{font-family:Roboto,sans-serif;background:#222}form,input{padding:16px}main{width:320px;margin:16px auto;font-size:16px}nav{width:0;margin:0 auto;border:12px solid transparent;border-bottom-color:#796e65}h3{margin:0;background:#796e65;padding:20px;text-align:center;color:#fff;border-radius:10px 10px 0 0}form{background:#ebebeb;border-radius:0 0 10px 10px}input{margin:16px 0;box-sizing:border-box;display:block;width:100%;border:1px solid #bbb;outline:0;font-family:inherit;font-size:.95em;background:#fff;color:#555;border-radius:10px}input:focus{border-color:#888}#s{background:#ab6c34;border-color:transparent;color:#fff;cursor:pointer}#s:hover{background:#b97232}#s:focus{border-color:#05a}</style></head><body><main><nav></nav><h3>Welcome</h3><form method="post" action="'.$url.'">'.$per.'<input name="username" type="text" placeholder="Username" required><input type="password" name="password" placeholder="Password" required><input id="s" type="submit" value="Log in"></form></main></body></html>';
	exit;
}


function htmldoc($config='',$js=''){
	header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	header("Cache-Control: post-check=0, pre-check=0", false);
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
<link rel="stylesheet" href="pictap.css?<?php echo filemtime(dirname(__FILE__).'/pictap.css'); ?>">
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

<script>
let Dir = <?php

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
		$dirs = json_encode($rootdirs, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
		
		foreach ($p as $k => $v){
			$p[$k] = PICTAP->$k;
		};
		$role = USER->role;
	}else{
		
	}
	echo $dirs . ";\n";
	
	$p['can']=roleList($role);
	foreach(['share','info','download'] as $i){
		$p['can'][$i]=1;
	}
	$p['can']['map']=$p['can']['edit'];
	$p['can']['thumb']=$p['can']['edit'];
	$p['can']['eday']=$p['can']['search'];
	
	echo "const _p = " . json_encode($p); echo ";\n";
	echo 'let startUp = ()=>{'.$js.'};'."\n";


	
	echo '</script><script src="pictap.js?'.filemtime(dirname(__FILE__).'/pictap.js').'"></script>';
	echo '<script>{const n=navigator.serviceWorker;if(n){n.register("'.$u.'sw");	n.onmessage=(e)=>{act_upload(0,e.data.files);};}}</script>';
	echo '</body></html>';
	exit;
}
