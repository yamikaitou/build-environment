<?php

$file = file("{$_ENV['WORKSPACE']}/test.sma");
$newfile = array();
$version = 0;

foreach ($file as $line)
{
	if (strpos($line, "PLUGINVERSION[]") !== FALSE)
	{
		$line = str_replace("dev", "{$_ENV['MERCURIAL_REVISION_NUMBER']}", $line);
		$version = substr($line, strpos($line, "\"")+1, strpos($line, "\"")-strrpos($line, "\""));
		$archive .= $version.".zip";
	}
	
	$newfile[] = $line;
}
file_put_contents("{$_ENV['WORKSPACE']}/test.sma", $newfile);

touch("{$_ENV['WORKSPACE']}/test.txt");
exec("cd {$_ENV['AMXXRELEASE']}; ./amxxpc \"{$_ENV['WORKSPACE']}/test.sma\" -o\"{$_ENV['WORKSPACE']}/test.amxx\" -i\"{$_ENV['AMXXRELEASE']}/includes\" -i\"{$_ENV['AMXXINCLUDES']}\" -i\"{$_ENV['WORKSPACE']}/includes\">> {$_ENV['WORKSPACE']}/test.txt");

if (!file_exists("{$_ENV['WORKSPACE']}/test.amxx"))
{
	echo "Plugin(s) failed to compile\n";
	echo file_get_contents("{$_ENV['WORKSPACE']}/test.txt");
	exit(1);
}
echo file_get_contents("{$_ENV['WORKSPACE']}/test.txt");

exec("cd {$_ENV['WORKSPACE']}");
mkdir("package");
mkdir("package/addons");
mkdir("package/addons/amxmodx");
mkdir("package/addons/amxmodx/plugins");
mkdir("package/addons/amxmodx/scripting");

copy("{$_ENV['WORKSPACE']}/test.amxx", "{$_ENV['WORKSPACE']}/package/addons/amxmodx/plugins/test.amxx");
copy("{$_ENV['WORKSPACE']}/test.sma", "{$_ENV['WORKSPACE']}/package/addons/amxmodx/scripting/test.sma");
exec("cd package; zip -r $archive *; mv $archive ../$archive");

file_put_contents("latest.ver", "$version");

?>