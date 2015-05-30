#!/usr/bin/php
<?php

require_once('./core/core.php');
$bot = new bot();
if (file_exists('./core/status/restart.bot')) unlink('./core/status/restart.bot');
if (file_exists('./core/status/close.bot')) unlink('./core/status/close.bot');
if (!file_exists('./data/config/login.ini') || in_array("--config", $argv) || in_array("-c", $argv)) $bot->config();
$bot->readConfig();
if (in_array("--input", $argv) || in_array("-i", $argv)) { $bot->input = true; }
if (in_array("--noinput", $argv) || in_array("-n", $argv)) { $bot->input = false; }
$bot->Modules->load('./modules/');
if (!isset($bot->pk) && ($bot->oldpk == '' || $bot->oldcookie == ''))
{
	$array = $bot->dAmn->getAuthtoken($bot->username, $bot->password);
	if (isset($array['token']) && isset($array['cookie']))
	{
		$bot->pk = $array['token'];
		$bot->cookie = $array['cookie'];
		if (in_array("--input", $argv))
		{
			$bot->input = false;
			$bot->saveConfig();
			$bot->input = true;
		}
		elseif (in_array("--noinput", $argv))
		{
			$bot->input = true;
			$bot->saveConfig();
			$bot->input = false;
		}
		else
		{
			$bot->saveConfig();
		} 
		
	}
}
else
{
	$bot->pk = $bot->oldpk;
	$bot->Console->msg(GREEN."Attemping to use saved authtoken...".NORM, NULL);
}

if ($bot->pk == NULL)
{
	$bot->Console->msg(RED."Failed to retrieve authtoken. Check your username and password to make sure your login is correct.".NORM, NULL);
	$bot->quit = true;
}

function handle_login_error(&$bot, $error, $skip_retry=false)
{
	$retry_error = -1;
	switch ($error)
	{
		case 0:
			$bot->Console->msg(GREEN . "Logged in to dAmn successfully!".NORM, NULL);
			foreach($bot->join as $j)
			{
				$bot->dAmn->join($j);
				$bot->dAmn->packet_loop();
			}
			if ($bot->pk != $bot->oldpk)
			{
				$bot->saveConfig();
			}
		break;
		case 1:
			$bot->Console->msg(GREEN . ($skip_retry ? "Failed to log in with old authtoken." : "Failed to log in with old authtoken, retrieving new authtoken...") . NORM, NULL);
			if (!$skip_retry)
			{
				$array = $bot->dAmn->getAuthtoken($bot->username, $bot->password);
				if (isset($array['token']) && isset($array['cookie']))
				{
					$bot->pk = $array['token'];
					$bot->cookie = $array['cookie'];
				}
				$retry_error = $bot->dAmn->login($bot->username, $bot->pk);
			}
		break;
		case 2:
			$bot->Console->msg(GREEN . "Uh oh, looks like you're banned from dAmn!".NORM, NULL);
		break;
		case 3:
			$bot->Console->msg(GREEN . "Failed to connect to dAmn on retry... let's try again!".NORM, NULL);
			handle_login_error($bot, $error);
		break;
		default:
			$bot->Console->msg(GREEN . "I'm not sure what's going on here!".NORM, NULL);
			exit();
		break;
	}
	
	if ($retry_error != -1)
	{
		handle_login_error($bot, $retry_error, true);
		$bot->saveConfig();
	}
}
function run(&$bot) // the run process
{
	if (!$bot->quit)
	{
		$error = $bot->dAmn->login($bot->username, $bot->pk);
		handle_login_error($bot, $error);
	}
	while ($bot->quit==false)
	{
		usleep(5000);
		if ($bot->input==false)
		{
			if ($bot->disconnected == true)
			{	
				$bot->restart = true;
				$bot->Console->msg(RED."The bot has disconnected from the server.".NORM, NULL);
				$bot->disconnected = false;
				run($bot);
			}

			$done = false;
			$bot->dAmn->packet_loop();
		}
		elseif ($bot->input==true)
		{
			$bot->dAmn->say("Input is now on.", $bot->dAmn->ns);
			$bot->dAmn->input();
			$bot->dAmn->say("Input is now off.", $bot->dAmn->ns);
		}
	}
}

function fake_run(&$bot) { for(;;) $bot->dAmn->process(trim(str_replace("\\n", "\n", $bot->Console->get("Enter packet: ", true)))); }

function eval_loop(&$bot) { for(;;) { $code = eval(trim($bot->Console->get("Eval code: "))); if (isset($code)) print_r($code); } }

//fake_run($bot);
//eval_loop($bot);
run($bot);
?>
