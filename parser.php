#!/usr/bin/php
<?php

if(!@$argv[1])
{
echo "Error: Source directory not provided";
exit;
}

function addValue(&$res, $p)
{
	//get value
	$val=trim(strip_tags($p->asXML()));
	//trim value
	$rev=strrev($val);
	$val_=strrev(substr($rev, 0, strpos($rev," ")));
	
///	$res[]=$val;
	
	if(preg_match('/^http/',$val_))
	{
	$res['link']=$val_;
	$res['filename']=strrev(substr($rev, 0, strpos($rev,"/")));
	}

	if(preg_match('/MD5/',$val))
	{
	$res['MD5']=$val_;
	}

}

function scanFile(&$pkgs, $file)
{



$xml=simplexml_load_file($file,'SimpleXMLElement',LIBXML_DTDLOAD);
//var_dump($xml);
//$root=$xml->getElementsByTagName('package');
//echo "------";
$pkg=array();
$pkg['code']=$xml->code->__toString();
$pkg['files']=array();

$c=0;

foreach($xml->list as $xmllist)
{
if($c==0){
        $res=array();

    foreach($xmllist->item as $item){
	foreach($item->p as $p){
	addValue($res,$p);
//	$res[]=trim(strip_tags($p->asXML()));
	}
    }

    if (count($res)>1)
    {
        $pkg['files'][]=$res;
    }

$c++;

}else{

    foreach($xmllist->item as $item){
        $res=array();

	foreach($item->p as $p){
	addValue($res,$p);
	
	}

 if (count($res)>1)
    {

        $pkg['files'][]=$res;
}

    }


}
}

$pkgs[]=$pkg;

}

$pkgs=array();

$dir=$argv[1];

if (is_dir($dir)) {
    if ($dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false) {
        if(!in_array($file,array('.','..')) and !preg_match('/.cmd$/',$file))
        {
        scanFile($pkgs,$dir.'/'.$file);
        }
//            echo "filename: $file : filetype: " . filetype($dir . $file) . "\n";
        }
        closedir($dh);
    }
}

//scanFile($pkgs,'blfs-systemd-files/libreoffice.xml');
//var_dump($pkgs);


//export data to xml

$dom = new DOMDocument('1.0', 'utf-8');
$dom->formatOutput=true;
$root = $dom->createElement('packages');

foreach($pkgs as $pkg)
{

if(count($pkg['files'])){

$pkgnode=$dom->createElement('package');
$code = $dom->createElement('code',$pkg['code']);
$filesnode = $dom->createElement('files');

$pkgnode->appendChild($code);
$pkgnode->appendChild($filesnode);


foreach($pkg['files'] as $file)
{
$filenode=$dom->createElement('file');

if(@$file['link']){
$linknode=$dom->createElement('link', $file['link']);
$filenode->appendChild($linknode);
}

if(@$file['MD5']){
$md5node=$dom->createElement('md5', $file['MD5']);
$filenode->appendChild($md5node);
}

if(@$file['filename']){
$filenamenode=$dom->createElement('filename', $file['filename']);
$filenode->appendChild($filenamenode);
}





$filesnode->appendChild($filenode);
}
$cmdfile=$argv[1].'/'.$pkg['code'].'.cmd';
if(file_exists($cmdfile)){
$cmdsnode = $dom->createElement('commands');
$cmds=trim(file_get_contents($cmdfile));

$cmddatanode = $dom->createElement('data',base64_encode($cmds));
$cmdsnode->appendChild($cmddatanode);
$cmddatanode = $dom->createElement('md5',md5($cmds));
$cmdsnode->appendChild($cmddatanode);

$pkgnode->appendChild($cmdsnode);
}



$root->appendChild($pkgnode);
}
}


$dom->appendChild($root);
$xml=$dom->saveXML();

file_put_contents($argv[1].'.xml',$xml);

