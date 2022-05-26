<?php

function qtranxf_setLanguage($lang) {
	global $q_config;
	$q_config['language'] = $lang;
	qtranxf_set_language_cookie($lang);
}