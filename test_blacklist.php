<?php
//
// This script is used to test the blacklist expressions identified
// within a newznab database are valid.
//
// by l2g
//
define('FS_ROOT', realpath(dirname(__FILE__)));
require_once(FS_ROOT."/../../www/config.php");
require_once(FS_ROOT."/../../www/lib/framework/db.php");
require_once(FS_ROOT."/../../www/lib/binaries.php");
require_once(FS_ROOT."/../../www/lib/releases.php");

function group($name)
{
   # Quick lookup function
   static $hash = array();
   $db = new DB();
   if(!array_key_exists($name, $hash)){
      $hash[$name]=$db->query(sprintf("select ID from groups where name = '%s'", $name));
   }
   return $hash[$name];
}

$blacklist = array(
   # random string of crap
   array("groupname"=>"alt.binaries.*", "regex"=>'^[a-z0-9]{1,80}([0-9-]+$|$)'),
   # Missing blacklist catching of foreign content
   array("groupname"=>"alt.binaries.*", "regex"=>'[-.](FR|DE|ITA)[-.]'),
   # Common German Keywords
   array("groupname"=>"alt.binaries.*", "regex"=>'(^|[.\/ \-]+)(sie|seit|ihn|ihm|haben|besitzen|sein|kriegen|nehmen|welche|jenes|von|auf|gegen|nach|das|sein|der|und|fuer|ersten|leicht|meinem|zum|aus|dem|blitzlicht|alle|grosse|zed|ich|sed|blitzen)([.\/ \-]+|$)'),
   # to many vowels strung together... either not ligit, or german ((don't use, it catches to many other things)
   #array("groupname"=>"alt.binaries.multimedia", "regex"=>'[aoeiuy]{4,80}'),
   #array("groupname"=>"alt.binaries.multimedia", "regex"=>'^{4,80}$'),
);

# Test DB Data
$res = array(
   # Set id's to 0 so id's are purged unessisarily when testing
   array('ID'=>0, 'groupname'=>"alt.binaries.multimedia", 'name'=>"1204odayrtgh6j7app"),
   array('ID'=>0, 'groupname'=>"alt.binaries.multimedia", 'name'=>"BB555"),
   array('ID'=>0, 'groupname'=>"alt.binaries.multimedia", 'name'=>"bnedhe8utrh5tnbg9"),
   array('ID'=>0, 'groupname'=>"alt.binaries.multimedia", 'name'=>"Uoaaqunio-396653289-201212201118"),
   array('ID'=>0, 'groupname'=>"alt.binaries.multimedia", 'name'=>"ich26389"),
   array('ID'=>0, 'groupname'=>"alt.binaries.multimedia", 'name'=>"23YKC 20121212 013"),
);

$catsql = "SELECT releases.*,groups.name as groupname FROM releases left join groups on releases.groupID = groups.ID";
$db = new DB();

# If satisfied with what is matched, set this to true and have
# matched content removed
$purgeMatched=false;

$errcnt=0;
$total=count($res);
$batch=100;
$offset=0;
while(1){
   $res = $db->query($catsql.sprintf(' LIMIT %d,%d', $offset, $batch));
   $subtotal=count($res);
   if ($subtotal <=0) break;
   $total+=$subtotal;
   $offset+=$batch;

   $binaries = new Binaries();
   $release = new Releases();

   foreach ($res as $header)
   {
      //print_r($header);
      //foreach ($binaries->getBlacklist(true) as $bl)
      foreach ($blacklist as $bl)
      {
         if (preg_match('/^'.$bl['groupname'].'$/i', $header['groupname']))
         {
            //print_r($bl);
            if (preg_match('/'.$bl['regex'].'/i', $header['name'])) {
               $errcnt++;
               echo '/'.$bl['regex'].'/i matched '.$header['ID'].'/'.$header['name'].
                  " (".$header['groupname'].")\n";
               if ($purgeMatched && $header['ID'] > 0){
                  $release->delete($header['ID']);
               }
               break;
            }
         }
      }
   }
}
echo "Scanned $total record(s), $errcnt match(es) found.\n";
exit(($errcnt>0)?1:0);
