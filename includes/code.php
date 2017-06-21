<?php

//code
function code($string){

	$code = array(

		'Invalid authorization'	=> '无效的授权',
	);

	if(isset($code[$string]) && $code[$string]!=''){
		return $code[$string];
	};
	return $string;
}