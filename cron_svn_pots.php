<?php
require_once 'makepot.php';

$options = getopt("c:p:m:n:");

$application_svn_checkout = realpath($options['c']);
$pot_svn_checkout = realpath($options['p']);
$makepot_project = $options['m'];
$pot_name = $options['n'];

$makepot = new MakePOT(false);	

$versions = array();

chdir($application_svn_checkout);
system("svn up");
if (is_dir("trunk")) $versions[] = 'trunk';
$branches = glob('branches/*');
if (false !== $branches) $versions = array_merge($versions, $branches);
$tags = glob('tags/*');
if (false !== $tags) $versions = array_merge($versions, $tags);

chdir($pot_svn_checkout);
system("svn up");
foreach($versions as $version) {
	$pot = "$version/$pot_name";
	$exists = is_file($pot);
	// do not update old tag pots
	if ('tags/' == substr($version, 0, 5) && $exists) continue;
	if (!is_dir($version)) system("svn mkdir $version");
	call_user_func(array(&$makepot, $makepot_project), realpath("$application_svn_checkout/$version"), "$pot_svn_checkout/$pot");
	if (!$exists) system("svn add $pot");
	// do not commit if the difference is only in the header
	// always commit a new file
	if (!$exists || `svn diff $pot | wc -l` > 13) {
		system("svn ci $version --message='Automatic POT update'");
	}
}

?>
