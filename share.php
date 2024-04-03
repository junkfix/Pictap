<?php
if(!isset($_GET['a'])){die;}
$a = $_GET['a'];
if(!$a || !is_string($a)){die;}
$f = dirname(__FILE__) .'/pictap_config.php';
$c = @file_get_contents($f);
if(!$c){die;}
$c = json_decode($c, true);
if(empty($c[1])){die;}
$c = $c[1];
try{
	$dbo = new SQLite3($c['db_file']);
	$dbo->busyTimeout(60000);
} catch (Exception $e) {
	die("Error");
}

$stmt = $dbo->prepare("SELECT f.fileid, a.name, f.file, f.w, f.h, f.ft, f.dur from albums a 
INNER JOIN albumfiles af USING (albumid) INNER JOIN files f USING (fileid)
WHERE share = :kwd ORDER BY f.tk ASC");
$stmt->bindValue(':kwd', $a, SQLITE3_TEXT);
$res = $stmt->execute();
$an = '';
$i = 0;
$q = [
	'url'=>$c['url_shared'].'/'.rawurlencode($a).'/',
	'items'=>[]
];
while ($r = $res->fetchArray(SQLITE3_ASSOC)) {
	$an = htmlspecialchars($r['name']);
	unset($r['name']);
	$q['items'][] = $r;
	$i++;
}
if(!$i){http_response_code(404);die;}
$dbo->close();$dbo = null;
$an = "$an ($i)";
?><!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 48 48'%3E%3Cpath fill='%23da5f0d' d='M10 1c-6 0-9 2-9 9v28c0 7 3 9 9 9h28c6 0 9-2 9-9V10c0-7-3-9-9-9H10'/%3E%3Cpath fill='%23cacaca' d='M17 20c-2 1-2 1-9 16-1 2 0 4 2 4h27c2 0 3-2 2-4l-6-8c-6-9-6 8-11-3-2-4-3-5-5-5'/%3E%3Cpath fill='%23fff' d='M28 7a5 5 90 1 0 1 10 5 5 90 0 0-1-10'/%3E%3C/svg%3E" type="image/svg+xml" />
<title><?php echo $an; ?></title>
<link rel="stylesheet" href="pictap.css?<?php echo filemtime(dirname(__FILE__).'/pictap.css'); ?>">
</head>
<body class="rows">
<h1><?php echo $an; ?></h1>
<div class="gallery"></div>
<script src="pictap.js?<?php echo filemtime(dirname(__FILE__).'/pictap.js'); ?>"></script>
<script type="text/javascript">
<?php echo "const f = " . json_encode($q); echo ";\n"; ?>
sharedView(f);
</script>
</body>
</html>