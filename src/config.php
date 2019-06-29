<?php

return [
	'links' => 4,
	'expire' => 120, // in seconds
	'noise' => true,
	'session_key' => 'antibotlink',
	'assets' => [
		'font_path' => public_path('abl') . DIRECTORY_SEPARATOR . 'fonts',
	],
	'captcha_instruction' => '
	<p class="alert alert-info">:instruction :image 
	<a href="#" id="antibotlink_reset" style="color: rgb(51, 51, 51); visibility: hidden;">( reset )</a>
	</p>',
	'words' => [
		[
			'one'=>'1', 'two'=>'2', 'three'=>'3', 'four'=>'4', 'five'=>'5', 'six'=>'6', 'seven'=>'7', 'eight'=>'8', 'nine'=>'9', 'ten'=>'10'
		], [
			'1'=>'one', '2'=>'two', '3'=>'three', '4'=>'four', '5'=>'five', '6'=>'six', '7'=>'seven', '8'=>'eight', '9'=>'nine', '10'=>'ten'
		], [
			'1'=>'I', '2'=>'II', '3'=>'III', '4'=>'IV', '5'=>'V', '6'=>'VI', '7'=>'VII', '8'=>'VIII', '9'=>'IX', '10'=>'X'
		], [
			'cat'=>'C@t', 'dog'=>'d0g', 'lion'=>'1!0n', 'tiger'=>'T!g3r', 'monkey'=>'m0nk3y', 'elephant'=>'31eph@nt', 'cow'=>'c0w', 'fox'=>'f0x', 'mouse'=>'m0us3', 'ant'=>'@nt'
		], [
			'2-1'=>'1', '1+1'=>'2', '1+2'=>'3', '2+2'=>'4', '3+2'=>'5', '2+4'=>'6', '3+4'=>'7', '4+4'=>'8', '1+8'=>'9', '5+6'=>'11'
		], [
			'1'=>'3-2', '2'=>'8-6', '3'=>'1+2', '4'=>'3+1', '5'=>'9-4', '6'=>'3+3', '7'=>'6+1', '8'=>'2*4', '9'=>'3+6', '10'=>'2+8'
		], [
			'--x'=>'OOX', '-x-'=>'OXO', 'x--'=>'XOO', 'xx-'=>'XXO', '-xx'=>'OXX', 'x-x'=>'XOX', '---'=>'OOO', 'xxx'=>'XXX', 'x-x-'=>'XOXO', '-x-x'=>'OXOX'
		], [
			'--x'=>'--+', '-x-'=>'-+-', 'x--'=>'+--', 'xx-'=>'++-', '-xx'=>'-++', 'x-x'=>'+-+', '---'=>'---', 'xxx'=>'+++', 'x-x-'=>'+-+-', '-x-x'=>'-+-+'
		], [
			'--x'=>'oo+', '-x-'=>'o+o', 'x--'=>'+oo', 'xx-'=>'++o', '-xx'=>'o++', 'x-x'=>'+o+', '---'=>'ooo', 'xxx'=>'+++', 'x-x-'=>'+o+o', '-x-x'=>'o+o+'
		], [
			'oox'=>'--+', 'oxo'=>'-+-', 'xoo'=>'+--', 'xxo'=>'++-', 'oxx'=>'-++', 'xox'=>'+-+', 'ooo'=>'---', 'xxx'=>'+++', 'xoxo'=>'+-+-', 'oxox'=>'-+-+'
		], [
			'2*A'=>'AA', '3*A'=>'AAA', '2*B'=>'BB', '3*B'=>'BBB', '1*A+1*B'=>'AB', '1*A+2*B'=>'ABB', '2*A+2*B'=>'AABB', '2*C'=>'CC', '3*C'=>'CCC', '1*C+1*A'=>'CA', '1*C+1*B'=>'CB', '1*C+2*A'=>'CAA', '1*C+2*B'=>'CBB', '2*C+1*A'=>'CCA'
		], [
			'AA'=>'2*A', 'AAA'=>'3*A', 'BB'=>'2*B', 'BBB'=>'3*B', 'AB'=>'1*A+1*B', 'ABB'=>'1*A+2*B', 'AABB'=>'2*A+2*B', 'CC'=>'2*C', 'CCC'=>'3*C', 'CA'=>'1*C+1*A', 'CB'=>'1*C+1*B', 'CAA'=>'1*C+2*A', 'CBB'=>'1*C+2*B', 'CCA'=>'2*C+1*A'
		], [
			'zoo'=>'200', 'ozo'=>'020', 'ooz'=>'002', 'soo'=>'500', 'oso'=>'050', 'oos'=>'005', 'lol'=>'101', 'sos'=>'505', 'zoz'=>'202', 'lll'=>'111'
		]
	]
];