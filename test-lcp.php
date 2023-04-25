<?php
$conf = [ 
	//'url' => 'https://www.petaindia.com/issues/animals-used-for-clothing/leather/',
	'url'                          => 'https://www.animalrahat.com/',
	'file'                         => '',
	'test_original'                => 'f',
	'remove_js'                    => 't',
	'remove_css'                   => 't',
	'remove_css_file'              => 't',
	'remove_content_between'       => 'f',

	'remove_content_between_start' => '',
	'remove_content_between_end'   => '',
	'write_to_file'                => 't',
];
if ( ! empty( $_POST ) ) {
	$conf = $_POST['conf'];
	/* $conf_p = $_POST['conf'];
	$conf = json_decode( $conf_p, true ); */
}
$conf_json = json_encode( $conf, JSON_PRETTY_PRINT );
function _if( $case = '' ) {
	global $conf;
	if ( $conf['test_original'] == 't' ) {
		return false;
	}
	if ( isset( $conf[ $case ] ) ) {
		if ( $conf[ $case ] == 't' ) {
			return true;
		}
	}
	return false;
}
$url = $conf['url'];
if ( empty( $url ) ) {
	$url = $conf['file'];
}
$content = file_get_contents( $url );
$content_test = $content;
//print('<pre>DBG='.print_r($content_test,true).'</pre>'); //exit; 
if ( _if( 'remove_js' ) ) {
	$content_test = preg_replace( '#<script(.*?)>(.*?)</script>#is', '', $content_test );
}
if ( _if( 'remove_css' ) ) {
	$content_test = preg_replace( '#<style(.*?)>(.*?)</style>#is', '', $content_test );
}
if ( _if( 'remove_css_file' ) ) {
	$c = preg_match_all( '/<link\s+[^>]*rel="stylesheet"[^>]*>/i', $content_test, $tt );
	$info['css'] = $tt;
	$content_test = preg_replace( '/<link\s+[^>]*rel="stylesheet"[^>]*>/i', '', $content_test );
}

if ( _if( 'remove_content_between' ) ) {
	$start = $conf['remove_content_between_start'];
	$end = $conf['remove_content_between_end'];
	$start = preg_quote( $start );
	$end = preg_quote( $end );
	$content_test = preg_replace( '#' . $start . '(.*)' . $end . '#is', '', $content_test );
}
file_put_contents( 'test-lcp.html', $content_test );
/* if ( _if( 'write_to_file' ) ) {
	file_put_contents( 'test-lcp.html', $content_test );
}
 */
/* echo $content_test;
exit; */
?>
<style>
	* {
		font-family: system-ui, -apple-system;
	}

	input[type="text"] {
		width: 70%;
		padding: 4px;
	}
	input[value="t"] {
        background: #ffdede;
	}
	input[value="f"] {
        background: #c7ebb2;
	}

	.field {
		background: beige;
		padding: 4px;
		width: 70%;
		margin: 1px;
	}
</style>
<script>
function toggle(obj){
    if( obj.value == 't' ){
        obj.value = 'f'
        return;
    }
    if( obj.value == 'f' ){
        obj.value = 't'
        return;
    }

}

</script>
<div class='' id='' style="display: grid;grid-template-columns: 50% 50%;">
	<div class='' id=''>
		<form method="POST" name="test-lcp" action="" class="frm" id="test-lcp" enctype="multipart/form-data">
			<div class='fields'>
				<?php foreach ( $conf as $k => $v ) { ?>
					<div class='field'>
						<label>
							<?php echo $k ?>
						</label><br>
						<input type='text' name='conf[<?php echo $k ?>]' value='<?php echo $v ?>' class='' id='' onclick="toggle(this)"></input>
					</div>

				<?php } ?>
				<div class='field' style='display: none;'>
					<label>Conf</label><input type="submit" value="Submit" /><br>
					<textarea name="conf__"
						style="width: 80%;height: 270px;padding: 4px;font-size: 16px;"> <?php echo $conf_json; ?>  </textarea>
				</div>
			</div>
			<div class='fields'>
				<div class='field'>
					<input type='hidden' name='' value='' class='' id='' />
					<input type="submit" value="Submit" />
				</div>
			</div>

		</form>
	</div>
	<div class='' id=''>
		<?php echo sprintf( '<a href="%s" target="_balnk">%s</a>', 'test-lcp.html', 'test-lcp.html' );
		print( '<pre>Current Conf=' . print_r( $conf, true ) . '</pre>' ); //exit; ?>
	</div>
</div>