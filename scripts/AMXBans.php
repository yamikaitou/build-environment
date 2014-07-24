<?php

$files = array(
	"{$_ENV['WORKSPACE']}/Web/include/config.inc.php",
	"{$_ENV['WORKSPACE']}/Plugin/scripting/amxbans_core.sma",
	"{$_ENV['WORKSPACE']}/Plugin/scripting/amxbans_main.sma",
	"{$_ENV['WORKSPACE']}/Plugin/scripting/amxbans_flagged.sma",
	"{$_ENV['WORKSPACE']}/Plugin/scripting/amxbans_freeze.sma",
	"{$_ENV['WORKSPACE']}/Plugin/scripting/amxbans_assist.sma"
);
$newfile = array();
$version = 0;

foreach ($files as $filename)
{
	$file = file($filename);
	$newfile = array();
	
	foreach ($file as $line)
	{
		if (strpos($line, "PLUGINVERSION[]") !== FALSE)
		{
			$line = str_replace("dev", "{$_ENV['MERCURIAL_REVISION_NUMBER']}", $line);
		}
		else if (strpos($line, "@\$config") !== FALSE)
		{
			$line = str_replace("dev", "{$_ENV['MERCURIAL_REVISION_NUMBER']}", $line);
			$version = substr($line, strpos($line, "\"")+1, strrpos($line, "\"")-strpos($line, "\"")-1);
			$archive .= $version.".zip";
		}
		
		$newfile[] = $line;
	}
	file_put_contents($filename, $newfile);
}

$error = FALSE;
mkdir(pathinfo($filename, PATHINFO_DIRNAME)."/../plugins");
foreach ($files as $filename)
{
	if (pathinfo($filename, PATHINFO_EXTENSION) == "php")
		continue;
	
	touch(pathinfo($filename, PATHINFO_DIRNAME)."/".pathinfo($filename, PATHINFO_FILENAME).".txt");
	exec("cd {$_ENV['AMXXRELEASE']}; ./amxxpc \"$filename\" -o\"".substr(pathinfo($filename, PATHINFO_DIRNAME), 0, -9)."plugins/".pathinfo($filename, PATHINFO_FILENAME).".amxx\" -i\"{$_ENV['AMXXRELEASE']}/include\" -i\"{$_ENV['AMXXINCLUDES']}\" -i\"".pathinfo($filename, PATHINFO_DIRNAME)."/include\">> ".pathinfo($filename, PATHINFO_DIRNAME)."/".pathinfo($filename, PATHINFO_FILENAME).".txt");

	if (!file_exists(substr(pathinfo($filename, PATHINFO_DIRNAME), 0, -9)."plugins/".pathinfo($filename, PATHINFO_FILENAME).".amxx"))
	{
		$error = TRUE;
	}
	echo file_get_contents(pathinfo($filename, PATHINFO_DIRNAME)."/".pathinfo($filename, PATHINFO_FILENAME).".txt");
	unlink(pathinfo($filename, PATHINFO_DIRNAME)."/".pathinfo($filename, PATHINFO_FILENAME).".txt");
}
if ($error)
{
	echo "Plugin(s) failed to compile\n";
	exit(1);
}

exec("cd {$_ENV['WORKSPACE']}");
mkdir("package");
mkdir("package/Web");
mkdir("package/Plugin");
mkdir("package/Plugin/addons");
mkdir("package/Plugin/addons/amxmodx");

exec("cp -r Web/. package/Web/.");
exec("cp -r Plugin/. package/Plugin/addons/amxmodx/.");

exec("cd package; zip -r $archive *; mv $archive ../$archive");

file_put_contents("latest.ver", "$version");

?>