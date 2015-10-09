<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: index.php 34524 2014-05-15 04:42:23Z nemohou $
 */

fwrite(fopen( "F:\\tmp\\log.txt" , "a" ), "[" .date('Y-m-d H:i:s',time()+60*60*6). "]". "index.php start" . "\n" );

fwrite(fopen( "F:\\tmp\\log.txt" , "a" ), "[" .date('Y-m-d H:i:s',time()+60*60*6). "]". "SERVER['QUERY_STRING'] = ".$_SERVER['QUERY_STRING'] . "\n" );

if(!empty($_SERVER['QUERY_STRING']) && is_numeric($_SERVER['QUERY_STRING'])) {
	$_ENV['curapp'] = 'home';
	$_GET = array('mod'=>'space', 'uid'=>$_SERVER['QUERY_STRING']);
} else {

	fwrite(fopen( "F:\\tmp\\log.txt" , "a" ), "[" .date('Y-m-d H:i:s',time()+60*60*6). "]". "SERVER['QUERY_STRING'] null" . "\n" );

	$url = '';
	$domain = $_ENV = array();
	$jump = false;

	//MEMO 此时DOMAIN=NULL
// 	if($domain){
// 		fwrite(fopen( "F:\\tmp\\log.txt" , "a" ), "[" .date('Y-m-d H:i:s',time()+60*60*6). "]". "domain true" . "\n" );
// 	}else{
// 		fwrite(fopen( "F:\\tmp\\log.txt" , "a" ), "[" .date('Y-m-d H:i:s',time()+60*60*6). "]". "domain false" . "\n" );
// 	}
// 	foreach ($domain as $key => $value) { fwrite(fopen( "F:\\tmp\\log.txt" , "a" ), "[" .date('Y-m-d H:i:s' ,time()+60*60*6). "]". "domain:" . $key . ": " .$value."\n" ); }

	@include_once './data/sysdata/cache_domain.php';
	$_ENV['domain'] = $domain;

	fwrite(fopen( "F:\\tmp\\log.txt" , "a" ), "[" .date('Y-m-d H:i:s',time()+60*60*6). "]". "_ENV['domain'] = ".$_ENV['domain'] . "\n" );
	foreach ($domain as $key => $value) { fwrite(fopen( "F:\\tmp\\log.txt" , "a" ), "[" .date('Y-m-d H:i:s' ,time()+60*60*6). "]". "domain:" . $key . ": " .$value."\n" ); }
	
	if(empty($_ENV['domain'])) {
		$_ENV['curapp'] = 'forum';
		fwrite(fopen( "F:\\tmp\\log.txt" , "a" ), "[" .date('Y-m-d H:i:s',time()+60*60*6). "]". "curapp = FORUM" . "\n" );
	} else {
		$_ENV['defaultapp'] = array('portal.php' => 'portal', 'forum.php' => 'forum', 'group.php' => 'group', 'home.php' => 'home');
		$_ENV['hostarr'] = explode('.', $_SERVER['HTTP_HOST']);
		$_ENV['domainroot'] = substr($_SERVER['HTTP_HOST'], strpos($_SERVER['HTTP_HOST'], '.')+1);
		if(!empty($_ENV['domain']['app']) && is_array($_ENV['domain']['app']) && in_array($_SERVER['HTTP_HOST'], $_ENV['domain']['app'])) {
			fwrite(fopen( "F:\\tmp\\log.txt" , "a" ), "[" .date('Y-m-d H:i:s',time()+60*60*6). "]". "20151008113155" . "\n" );
			$_ENV['curapp'] = array_search($_SERVER['HTTP_HOST'], $_ENV['domain']['app']);
			if($_ENV['curapp'] == 'mobile') {
				$_ENV['curapp'] = 'forum';
				if(!isset($_GET['mobile'])) {
					@$_GET['mobile'] = '2';
				}
			}
			if($_ENV['curapp'] == 'default' || !isset($_ENV['defaultapp'][$_ENV['curapp'].'.php'])) {
				$_ENV['curapp'] = '';
			}
		} elseif(!empty($_ENV['domain']['root']) && is_array($_ENV['domain']['root']) && in_array($_ENV['domainroot'], $_ENV['domain']['root'])) {
			fwrite(fopen( "F:\\tmp\\log.txt" , "a" ), "[" .date('Y-m-d H:i:s',time()+60*60*6). "]". "20151008113211" . "\n" );

			$_G['setting']['holddomain'] = $_ENV['domain']['holddomain'] ? $_ENV['domain']['holddomain'] : array('www');
			$list = $_ENV['domain']['list'];
			if(isset($list[$_SERVER['HTTP_HOST']])) {
				$domain = $list[$_SERVER['HTTP_HOST']];
				switch($domain['idtype']) {
					case 'subarea':
						$_ENV['curapp'] = 'forum';
						$_GET['gid'] = intval($domain['id']);
						break;
					case 'forum':
						$_ENV['curapp'] = 'forum';
						$_GET['mod'] = 'forumdisplay';
						$_GET['fid'] = intval($domain['id']);
						break;
					case 'topic':
						$_ENV['curapp'] = 'portal';
						$_GET['mod'] = 'topic';
						$_GET['topicid'] = intval($domain['id']);
						break;
					case 'channel':
						$_ENV['curapp'] = 'portal';
						$_GET['mod'] = 'list';
						$_GET['catid'] = intval($domain['id']);
						break;
					case 'plugin':
						$_ENV['curapp'] = 'plugin';
						$_GET['id'] = $domain['id'];
						$_GET['fromapp'] = 'index';
						break;
				}
			} elseif(count($_ENV['hostarr']) > 2 && $_ENV['hostarr'][0] != 'www' && !checkholddomain($_ENV['hostarr'][0])) {
				$_ENV['prefixdomain'] = addslashes($_ENV['hostarr'][0]);
				$_ENV['domainroot'] = addslashes($_ENV['domainroot']);
				require_once './source/class/class_core.php';
				C::app()->init_setting = true;
				C::app()->init_user = false;
				C::app()->init_session = false;
				C::app()->init_cron = false;
				C::app()->init_misc = false;
				C::app()->init();
				$jump = true;
				$domain = C::t('common_domain')->fetch_by_domain_domainroot($_ENV['prefixdomain'], $_ENV['domainroot']);
				$apphost = $_ENV['domain']['app'][$domain['idtype']] ? $_ENV['domain']['app'][$domain['idtype']] : $_ENV['domain']['app']['default'];
				$apphost = $apphost ? 'http://'.$apphost.'/' : '';
				switch($domain['idtype']) {
					case 'home':
						if($_G['setting']['rewritestatus'] && in_array('home_space', $_G['setting']['rewritestatus'])) {
							$url = rewriteoutput('home_space', 1, $apphost, $domain['id']);
						} else {
							$url = $apphost.'home.php?mod=space&uid='.$domain['id'];
						}
						break;
					case 'group':
						if($_G['setting']['rewritestatus'] && in_array('group_group', $_G['setting']['rewritestatus'])) {
							$url = rewriteoutput('group_group', 1, $apphost, $domain['id']);
						} else {
							$url = $apphost.'forum.php?mod=group&fid='.$domain['id'].'&page=1';
						}
						break;
				}
			}
		} else {
			fwrite(fopen( "F:\\tmp\\log.txt" , "a" ), "[" .date('Y-m-d H:i:s',time()+60*60*6). "]". "20151008113236" . "\n" );
			$jump = true;
		}
		if(empty($url) && empty($_ENV['curapp'])) {
			fwrite(fopen( "F:\\tmp\\log.txt" , "a" ), "[" .date('Y-m-d H:i:s',time()+60*60*6). "]". "20151008113351" . "\n" );
			if(!empty($_ENV['domain']['defaultindex']) && !$jump) {
				fwrite(fopen( "F:\\tmp\\log.txt" , "a" ), "[" .date('Y-m-d H:i:s',time()+60*60*6). "]". "20151008113436" . "\n" );
				if($_ENV['defaultapp'][$_ENV['domain']['defaultindex']]) {
					$_ENV['curapp'] = $_ENV['defaultapp'][$_ENV['domain']['defaultindex']];
				} else {
					$url = $_ENV['domain']['defaultindex'];
				}
			} else {
				fwrite(fopen( "F:\\tmp\\log.txt" , "a" ), "[" .date('Y-m-d H:i:s',time()+60*60*6). "]". "20151008113445" . "\n" );
				if($jump) {
					fwrite(fopen( "F:\\tmp\\log.txt" , "a" ), "[" .date('Y-m-d H:i:s',time()+60*60*6). "]". "20151008113601" . "\n" );
					$url = empty($_ENV['domain']['app']['default']) ? (!empty($_ENV['domain']['defaultindex']) ? $_ENV['domain']['defaultindex'] : 'forum.php') : 'http://'.$_ENV['domain']['app']['default'];
					if(empty($_ENV['domain']['app']['default'])){
						fwrite(fopen( "F:\\tmp\\log.txt" , "a" ), "[" .date('Y-m-d H:i:s',time()+60*60*6). "]". "20151008113645" . "\n" );
					}else{
						fwrite(fopen( "F:\\tmp\\log.txt" , "a" ), "[" .date('Y-m-d H:i:s',time()+60*60*6). "]". "20151008113651" . "\n" );
					}
					if(!empty($_ENV['domain']['defaultindex'])){
						fwrite(fopen( "F:\\tmp\\log.txt" , "a" ), "[" .date('Y-m-d H:i:s',time()+60*60*6). "]". "20151008113928" . "\n" );
					}else{
						fwrite(fopen( "F:\\tmp\\log.txt" , "a" ), "[" .date('Y-m-d H:i:s',time()+60*60*6). "]". "20151008113931" . "\n" );
					}
					fwrite(fopen( "F:\\tmp\\log.txt" , "a" ), "[" .date('Y-m-d H:i:s',time()+60*60*6). "]". "url = ".$url . "\n" );
					
				} else {
					fwrite(fopen( "F:\\tmp\\log.txt" , "a" ), "[" .date('Y-m-d H:i:s',time()+60*60*6). "]". "20151008113604" . "\n" );
					$_ENV['curapp'] = 'forum';
				}
			}
		}
	}
}
if(!empty($url)) {
	fwrite(fopen( "F:\\tmp\\log.txt" , "a" ), "[" .date('Y-m-d H:i:s',time()+60*60*6). "]". "20151008174403" . "\n" );
	$delimiter = strrpos($url, '?') ? '&' : '?';
	if(isset($_GET['fromuid']) && $_GET['fromuid']) {
		$url .= sprintf('%sfromuid=%d', $delimiter, $_GET['fromuid']);
	} elseif(isset($_GET['fromuser']) && $_GET['fromuser']) {
		$url .= sprintf('%sfromuser=%s', $delimiter, rawurlencode($_GET['fromuser']));
	}
	
	fwrite(fopen( "F:\\tmp\\log.txt" , "a" ), "[" .date('Y-m-d H:i:s',time()+60*60*6). "]". "url = ".$url . "\n" );
	header("HTTP/1.1 301 Moved Permanently");
	header("location: $url");
} else {
	require './'.$_ENV['curapp'].'.php';
}

function checkholddomain($domain) {
	global $_G;

	$domain = strtolower($domain);
	if(preg_match("/^[^a-z]/i", $domain)) return true;
	$holdmainarr = empty($_G['setting']['holddomain']) ? array('www') : explode('|', $_G['setting']['holddomain']);
	$ishold = false;
	foreach ($holdmainarr as $value) {
		if(strpos($value, '*') === false) {
			if(strtolower($value) == $domain) {
				$ishold = true;
				break;
			}
		} else {
			$value = str_replace('*', '.*?', $value);
			if(@preg_match("/$value/i", $domain)) {
				$ishold = true;
				break;
			}
		}
	}
	return $ishold;
}
?>