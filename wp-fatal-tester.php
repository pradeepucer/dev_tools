<?php
//fatal-tester.php?min_id=1&limit=0,5&post_type=page,post,test
include( 'wp-load.php' );
class SITE_TEST {

	public $source_urls = [];
	public $source_site = '';
	public $site = '';
	public $image_path = '';
	public $site_url = '';
	public $dest_url = [];

	public $path_wkhtml = 'wkhtmltopdf\bin\wkhtmltoimage.exe';

	public function url_to_images( $urls, $image_path = '' ) {
		foreach ( $urls as $k => $url ) {
			$u = pathinfo( $url );
			$file_name = $this->image_path . '/' . $this->file_name_from_url( $url ) . '.png';
			$this->run_command( $url, $file_name );
		}
	}

	public function run_command( $url, $image_path = '' ) {
		echo 'Converting ' . $url . ' File name : ' . $image_path;
		$cmd = sprintf( '%s  --username admin --password password --quality 2 "%s" %s', $this->path_wkhtml, $url, $image_path );
		$d = system( $cmd, $m );
	}

	public function comparison( $urls ) {
		$id = 0;
		foreach ( $urls as $source => $dest ) {
			$id++;
			$u = pathinfo( $source );
			$file_name = $this->source_path . '/' . $id . '_S_' . $this->file_name_from_url( $source ) . '.png';
			$this->run_command( $source, $file_name );

			$u = pathinfo( $dest );
			$file_name = $this->dest_path . '/' . $id . '_D_' . $this->file_name_from_url( $dest ) . '.png';
			$this->run_command( $dest, $file_name );
		}
	}

	public function show_comparison_result( $urls ) {
		$res = $this->get_comparison_result( $urls );
		$class = new compareImages();
		foreach ( $res as $k => $v ) {
			$diff = $class->compare( $v['source']['file'], $v['dest']['file'] );
			// echo '<pre>DBG==';print_r($diff);echo '</pre>'; exit;
			$single = "
            <div class='item_s'>
                <div class='url'>
                    <a href='%s' target='_blank'>%s</a> | Diff - %s
                </div>
                <div class='image'>
                    <img src='%s' />
                </div>
            </div>
            ";
			$s = sprintf( $single, $v['source']['org_url'], $v['source']['org_url'], $diff, $v['source']['image_url'] );

			$single = "
            <div class='item_d'>
                <div class='url'>
                    <a href='%s' target='_blank'>%s</a>
                </div>
                <div class='image'>
                    <img src='%s' />
                </div>
            </div>
            ";
			$d = sprintf( $single, $v['dest']['org_url'], $v['dest']['org_url'], $v['dest']['image_url'] );

			echo "<div class='item'>{$s} {$d}</div>";
		}
	}

	public function get_comparison_result( $urls ) {
		$id = 0;
		$s = $this->get_result( $this->source_path );
		$d = $this->get_result( $this->dest_path );
		$d_id = array_column( $d, 'id' );
		foreach ( $s as $k => $v ) {
			$key = array_search( $v['id'], $d_id );
			$m['source'] = $v;
			$m['dest'] = $d[ $key ];
			$res[] = $m;
		}
		return $res;
	}

	public function show_result( $site ) {
		$data = $this->get_result( $this->image_path );
		foreach ( $data as $k => $v ) {
			$single = "<div class='item'><div class='url'><a href='%s' target='_blank'>%s</a></div><div class='image'><img src='%s' /></div></div>";
			echo sprintf( $single, $v['org_url'], $v['org_url'], $v['image_url'] );
		}
	}

	public function get_result( $path ) {
		$files = glob( $path . '/*' );
		$data = [];
		foreach ( $files as $k => $file ) {
			$i = pathinfo( $file );

			$filename = $i['filename'];

			$d['id'] = 0;

			if ( strstr( $filename, '_S_' ) ) {
				list( $id, $filename ) = explode( '_S_', $filename );
				$d['id'] = $id;
			}
			if ( strstr( $filename, '_D_' ) ) {
				list( $id, $filename ) = explode( '_D_', $filename );
				$d['id'] = $id;
			}

			$url = $this->url_from_file_name( $filename );
			$d['basename'] = $i['basename'];
			$d['file'] = $file;
			$d['org_url'] = $url;
			$d['image_url'] = $this->site_url . '/' . $file;

			$data[] = $d;
		}
		return $data;
	}

	public function file_name_from_url( $url ) {
		return base64_encode( $url );
	}

	public function url_from_file_name( $f ) {
		return base64_decode( $f );
	}

	public function fatal_error_tester( $url, $searches = [] ) {
		$status = 'ALL OK';
		$status_code = 'ALL_OK';
		//return $status;
		$auth = base64_encode( "pass:pass" );
		$context = stream_context_create( [ 
			"http" => [ 
				"header" => "Authorization: Basic $auth"
			]
		] );
		$content = file_get_contents( $url, false, $context );
		//$result['content'] = $content; 
		$result['header'] = $http_response_header;
		if ( empty( $content ) ) {
			$status = 'BLANK :: Content Blank';
			$status_code = 'BLANK';
		} else if ( ! stristr( $content, 'footer' ) ) {
			$status = 'FATAL :: Footer not found in view source';
			$status_code = 'FATAL_FOOTER';
		} else if ( ! stristr( $content, '</body>' ) ) {
			$status = 'FATAL :: </body> not found in view source';
			$status_code = 'FATAL_BODY';
		} else if ( ! stristr( $content, '</html>' ) ) {
			$status = ' FATAL :: </html> not found in view source';
			$status_code = 'FATAL_HTML';
		} else {
			$result['status'] = $status;
			$result['status_code'] = $status_code;
		}
		if ( ! empty( $content ) && ! empty( $searches ) ) {
			foreach ( $searches as $k => $v ) {
				if ( ! stristr( $content, $v ) ) {
					$result['status_search'] = $v . ' not found in view source';
					$result['status_code_search'] = 'SEARCH_NOT_FOUND';
					break;
				}
			}
		}

		return $result;
	}

	public function show_in_iframe( $urls ) {
		foreach ( $urls as $k => $url ) {
			$this->show_ulr_iframe( $url );
		}
	}

	public function show_ulr_iframe( $url ) {
		$single = "<div class='item'><div class='url'><a href='%s' target='_blank'>%s</a>   |  <span class='max'>Max</span>|  <span class='min'>Min</span> | <span class='auto' onclick='autoScroll(this)'>Auto scroll</span></div><div class='image'><iframe src='%s' target='_blank'></iframe></div></div>";
		echo sprintf( $single, $url, $url, $url );
		sleep( 1 );
	}
}
//fatal-tester.php?min_id=1&limit=0,5&post_type=page,post,test
$SITE_TEST = new SITE_TEST();
$testing_done = get_option( 'testing_done', 1 );
$post_types = $_REQUEST['post_type'] ?? "page,post";
$limit = $_REQUEST['limit'] ?? "0,50";
$min_id = $_REQUEST['min_id'] ?? $testing_done;
$post_types = "'" . implode( "','", explode( ',', $post_types ) ) . "'";
if ( isset( $_REQUEST['show_post_types'] ) ) {
	$q = "SELECT post_type FROM {$wpdb->prefix}posts WHERE 1=1 group by post_type ";
	$res = $wpdb->get_results( $q );
	print( '<pre>DBG=' . print_r( $res, true ) . '</pre>' ); //exit; 
	print( '<pre>DBG=' . print_r( implode( ',', array_column( $res, 'post_type' ) ), true ) . '</pre>' );
	exit;

}


$q = "SELECT ID FROM {$wpdb->prefix}posts WHERE 1=1 and post_status='publish'  and post_type in ({$post_types}) AND ID>$min_id  ORDER BY ID ASC LIMIT {$limit} ";
$site_url = get_site_url();
print( '<pre>Query=' . print_r( $q, true ) . '</pre>' ); //exit; 
$res = $wpdb->get_results( $q );
foreach ( $res as $k => $v ) {
	$url = get_permalink( $v->ID );
	$post_link_m = sprintf( '<a href="%s" target="_blank">%s</a> | <a href="%s/wp-admin/post.php?post=%s&action=edit" target="_blank">%s</a> ', $url, $url, $site_url, $v->ID, $v->ID );

	$status = $SITE_TEST->fatal_error_tester( $url );
	$result[ $status['status_code'] ][] = $post_link_m;
	/* if ( 'ALL OK' != $status['status'] ) {
	print( '<pre>Testing=' . print_r( $post_link_m, true ) . '</pre>' ); //exit; 
	print( '<pre>$status=' . print_r( $status['status'], true ) . '</pre><hr>' ); //exit; 
	} */

	update_option( 'testing_done', $v->ID );
}
print( '<pre>Result = ' . print_r( $result, true ) . '</pre>' ); //exit; 