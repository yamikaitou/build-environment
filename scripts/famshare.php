<?php

$files = "{$_ENV['WORKSPACE']}/family-sharing.sma";
$newfile = array();
$version = 0;

$file = file($files);
$newfile = array();

foreach ($file as $line)
{
	if (strpos($line, "PLUGINVERSION[]") !== FALSE)
	{
		$line = str_replace("dev", "{$_ENV['MERCURIAL_REVISION_NUMBER']}", $line);
		$version = substr($line, strpos($line, "\"")+1, strrpos($line, "\"")-strpos($line, "\"")-1);
		$archive .= $version.".zip";
	}
	
	$newfile[] = $line;
}
file_put_contents($files, $newfile);

$error = FALSE;
if (pathinfo($files, PATHINFO_EXTENSION) == "php")
	continue;

touch(pathinfo($files, PATHINFO_DIRNAME)."/".pathinfo($files, PATHINFO_FILENAME).".txt");
exec("cd $compiler; ./amxxpc \"$files\" -o\"".substr($files, 0, -4).".amxx\" -i\"$compiler/include\" -i\"{$_ENV['AMXXINCLUDES']}\" -i\"".pathinfo($files, PATHINFO_DIRNAME)."/include\" >> ".pathinfo($files, PATHINFO_DIRNAME)."/".pathinfo($files, PATHINFO_FILENAME).".txt");

if (!file_exists(substr($files, 0, -4).".amxx"))
{
	$error = TRUE;
}
echo file_get_contents(pathinfo($files, PATHINFO_DIRNAME)."/".pathinfo($files, PATHINFO_FILENAME).".txt");
unlink(pathinfo($files, PATHINFO_DIRNAME)."/".pathinfo($files, PATHINFO_FILENAME).".txt");
if ($error)
{
	echo "Plugin(s) failed to compile\n";
	exit(1);
}

exec("cd {$_ENV['WORKSPACE']}");
mkdir("package");
mkdir("package/addons");
mkdir("package/addons/amxmodx");
mkdir("package/addons/amxmodx/scripting");
mkdir("package/addons/amxmodx/plugins");

exec("cp -r include package/addons/amxmodx/scripting/include");
exec("cp family-sharing.sma package/addons/amxmodx/scripting/family-sharing.sma");
exec("cp family-sharing.amxx package/addons/amxmodx/plugins/family-sharing.amxx");

exec("cd package; zip -r $archive *; mv $archive ../$archive");

file_put_contents("latest.ver", "$version");

?>