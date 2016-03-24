<?php

/**
 * Zine
 */

define( 'PATH',		\realpath( \dirname( __FILE__ ) ) . '/' );

define( 'TEMPLATES', 	PATH . 'templates/' );

# Ensure this folder only has read/write permissions for PHP
define( 'STORE',	PATH . 'data/' );

# If this is changed, remember to also rename the file
define( 'CONFIG',	'site.conf' );

# File name to store posts as (changing this after you've 
# added any posts will make them all unavailable !)
define( 'POST_FILE',	'blog.post' );

# Post draft file ( TODO )
define( 'DRAFT_FILE',	'draft.post' );

# Year limit to end when searching for posts
define( 'YEAR_END',	2000 );

# Default length of the auto-generated summary
define( 'SUMMARY_LEN',	200 );

# Session refresh timeout
define( 'SESSION_EXP',	300 );


/**
 * Common messages
 */
define( 'MSG_BODYM',	"<p>Post body is required. Please try <a href='/new'>again</a>.</p>");
define( 'MSG_LOGIN',	"<p>Please <a href='/login'>login</a> first.</p>" );
define( 'MSG_LOGOUT',	"<p>You have logged out successfully. Back to the <a href='/'>front page</a></p>" );
define( 'MSG_LOGININV',	"<p>Invalid login. Please try <a href='/login'>again</a>." );
define( 'MSG_LOGINGG',	"<p>Login successful. Back to the <a href='/'>front page</a>, create a <a href='/new'>new post</a> or enter the <a href='/manage'>site settings</a> area.</p>" );

define( 'MSG_PASSCH',	"<p>Password successfully changed. Back to the <a href='/'>front page</a>, create a <a href='/new'>new post</a> or return to the <a href='/manage'>site settings</a> area.</p>" );

define( 'MSG_NOPOSTS',	"<p>Couldn't find any more posts. Back to the <a href='/'>front page</a>.</p>" );
define( 'MSG_NOTFOUND', "<p>Couldn't find the post you're looking for. Back to the <a href='/'>front page</a>.</p>" );
define( 'MSG_POSTDEL',	"<p>Post successfully deleted. Back to the <a href='/'>front page</a>, create a <a href='/new'>new post</a> or enter the <a href='/manage'>site settings</a> area.</p>" );
define( 'MSG_POSTNDEL',	"<p>Couldn't delete post. It may have already been deleted or the delete path was invalid. Back to the <a href='/'>front page</a>." );
define( 'MSG_POSTDERR', "<p>Error loading post file. The formatting has been corrupted.</p>" );

define( 'MSG_FORMEXP',	"<p>The form you submitted has expired. <a href='/'>Go back</a>.</p>" );
define( 'MSG_INVALID',	"<p>Invalid data sent.</p>" );

define( 'MSG_SETSAVE',	"<p>Settings were successfully changed. Back to the <a href='/'>front page</a> or <a href='/manage'>settings</a>.</p>" );
define( 'MSG_NOSETTS',	"<p>No settings to change. Back to <a href='/manage'>settings</a>.</p>" );


/**
 * Stop errors
 */
define( 'MSG_SSDETECT',	'Server-side code detected in file. Exiting.' );
define( 'MSG_LOADERR',	'Error loading file. Exiting.' );
define( 'MSG_CONFERR',	'Error loading configuration');



/**
 * URL validation regular expressions
 */
define( 'RX_URL',	'~^(http|ftp)(s)?\:\/\/((([a-z|0-9|\-]{1,25})(\.)?){2,9})($|/.*$){4,255}$~i' );
define( 'RX_XSS2',	'/(<(s(?:cript|tyle)).*?)/ism' );
define( 'RX_XSS3',	'/(document\.|window\.|eval\(|\(\))/ism' );
define( 'RX_XSS4',	'/(\\~\/|\.\.|\\\\|\-\-)/sm' );




/**********************************
 *          END EDITING           *
 **********************************/

 

/* Post content formatting */

/**
 * Convert a string into a page slug
 */
function slugify( $title, $text ) {
	if ( empty( $text ) ) {
		$text = $title;
	}
	
	$text = preg_replace( '~[^\\pL\d]+~u', ' ', $text );
	$text = preg_replace( '/\s+/', '-', trim( $text ) );
	
	if ( empty( $text ) ) {
		return hash( 'md5', $title );
	}
	
	return strtolower( smartTrim( $text ) );
}

/**
 * Limit a string without cutting off words
 */
function smartTrim( $val, $max = 100 ) {
	$val	= trim( $val );
	$len	= mb_strlen( $val );
	
	if ( $len <= $max ) {
		return $val;
	}
	
	$out	= '';
	$words	= preg_split( '/([\.\s]+)/', $val, -1, 
			\PREG_SPLIT_OFFSET_CAPTURE | 
			\PREG_SPLIT_DELIM_CAPTURE );
		
	for ( $i = 0; $i < count( $words ); $i++ ) {
		$w	= $words[$i];
		# Add if this word's length is less than length
		if ( $w[1] <= $max ) {
			$out .= $w[0];
		}
	}
	
	$out	= preg_replace( "/\r?\n/", '', $out );
	
	# If there's too much overlap
	if ( mb_strlen( $out ) > $max + 10 ) {
		$out = mb_substr( $out, 0, $max );
	}
	
	return $out;
}

/**
 * Create a URL based on the date and title
 * @example /2015/02/26/an-example-post
 */
function datePath( $slug, $time = null ) {
	$now	= ( null == $time ) ? 
			date( 'Y/m/d/' ) : 
			date( 'Y/m/d/', $time );
	
	return $now . $slug;
}

/**
 * Extract the first line as a title from the body
 */
function fillTitle( $body ) {
	$title = strtok( strip_tags( $body ), "\n" );
	if ( false === $title ) {
		return null;
	}
	
	return smartTrim( $title[0] );
}

/**
 * Verify post editing profile
 */
function editTime( $edit ) {
	if ( mb_strlen( $edit, '8bit' ) > 1000 ) {
		message( MSG_INVALID, true );
	}
	
	$data	= base64_decode( $edit, true );
	
	if ( false === $data ) {
		message( MSG_INVALID, true );
	}
	if ( false === strpos( $data, '/' ) ) {
		message( MSG_INVALID, true );
	}
	
	return $data;
}

/**
 * Verify editing path contains all needed components
 */
function checkEdit( $data ) {
	$paths	= explode( '/', $data );
	if ( count( $paths ) != 4 ) {
		message( MSG_INVALID, true );
	}
	
	return $data;
}

/**
 * Parse and filter user submitted post data
 */
function getPost( $conf ) {
	$filter = array(
		'csrf'		=> \FILTER_SANITIZE_STRING,
		'edit'		=> \FILTER_SANITIZE_STRING,
		'title'		=> \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
		'pubdate'	=> \FILTER_SANITIZE_STRING,
		'slug'		=> \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
		'summary'	=> \FILTER_UNSAFE_RAW,
		'body'		=> \FILTER_UNSAFE_RAW,
		'draft'		=> \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
		'delpost'	=> \FILTER_SANITIZE_FULL_SPECIAL_CHARS
	);
	
	$data			= 
	\filter_input_array( \INPUT_POST, $filter );
	
	if ( !validateCsrf( 'post', $data['csrf'] ) ) {
		return null;
	}
	
	if ( empty( $data['body'] ) ) {
		message( MSG_BODYM );
	}
	
	if ( !empty( $data['delpost'] ) ) {
		if ( empty( $data['edit'] ) ) {
			return null;
		}
		
		$edit	= editTime( $data['edit'] );
		$path	= checkEdit( $edit );
		deletePost( $path );
	}
	
	# Post content exactly as entered by the user
	$data['raw']		= $data['body'];
	
	$data['body']		= clean( $data['body'], $conf['tags'] );
	if ( empty( $data['body'] ) ) {
		message( MSG_BODYM );
	}
	
	$data['title']		= 
		empty( $data['title'] ) ? 
			fillTitle( $data['body'] ) : $data['title'];
	
	$data['summary']	= 
		empty( $data['summary'] ) ? 
			smartTrim( strip_tags( $data['body'] ), SUMMARY_LEN ) : 
			strip_tags( clean( $data['summary'], $conf['tags'] ) );
	
	$data['slug']		= 
		slugify( $data['title'], $data['slug'] );
		
	$pub			= 
		empty( $data['pubdate'] ) ?
			time() : strtotime( $data['pubdate'] . ' UTC' );
	
	$params			= 
	array(
		'title'		=> $data['title'],
		'body'		=> $data['body'],
		'summary'	=> $data['summary'],
		'raw'		=> $data['raw'],
		'slug'		=> $data['slug'],
		'pubdate'	=> $pub
	);
	
	$draft			= isset( $data['draft'] ) ? 
					true : false;
	if ( empty( $data['edit'] ) ) {
		$path	= datePath( $data['slug'], $pub );
	} else {
		$edit	= editTime( $data['edit'] );
		$path	= checkEdit( $edit );
	}
	return array( $path, $params, $draft );
}


/* Post storage and pagination */

/**
 * Storage root path for all posts
 */
function postRoot() {
	return rtrim( STORE, \DIRECTORY_SEPARATOR ) . 
		\DIRECTORY_SEPARATOR . 'posts';
}

/**
 * Save a post in its specified path directory
 * Replaces an existing post in the same location
 */
function savePost( $path, $data, $draft = false ) {
	$paths	= explode( '/', $path );
	$root	= postRoot();
	
	foreach( $paths as $frag ) {
		$root .= \DIRECTORY_SEPARATOR . $frag;
		if ( is_dir( $root ) ) {
			continue;
		}
		
		mkdir( $root, 0600 );
	}
	$p	= $draft ? DRAFT_FILE : POST_FILE;
	$file	= $root . \DIRECTORY_SEPARATOR . $p;
	
	# Edit the post if it already exists
	if ( file_exists( $file ) ) {
		$edit	= loadPost( $file );
		$data	= array_merge( $edit, $data );
	}
		
	$post	= json_encode( $data, 
			\JSON_HEX_QUOT | \JSON_HEX_TAG | 
			\JSON_HEX_APOS | \JSON_PRETTY_PRINT );
	
	
	file_put_contents( $file, $post );
	return '/' . $path;
}

# https://secure.php.net/manual/en/features.file-upload.multiple.php
function fileSort() {
	$files	= array();
	
	foreach ( $_FILE as $k => $v ) {
		foreach( $v as $n => $f ) {
			$files[$n][$k] = $f;
		}
	}
	return $files;
}

# TODO
function saveUploads( $path ) {
	$s	= \DIRECTORY_SEPARATOR;
	$root	= postRoot();
	$files	= fileSort();
	$store	= $root . $s . $path . $s;
	
}

/**
 * Remove a post permanently
 */
function deletePost( $path, $draft ) {
	$s	= \DIRECTORY_SEPARATOR;
	$root	= postRoot();
	$dir	= $root . $s . $path;
	$file	= $dir . $s . POST_FILE;
	$draft	= $dir . $s . DRAFT_FILE;
	$del	= false;
	
	if ( file_exists( $file ) ) {
		if ( unlink( $file ) ) {
			$del = true;
		}
	}
	if ( file_exists( $draft ) ) {
		if ( unlink( $draft ) ) {
			$del = true;
		}
	}
	
	if ( $del ) {
		message( MSG_POSTDEL );
	}
	
	message( MSG_POSTNDEL );
}

/**
 * Sort returned file paths by last modified date
 */
function sortByModified( $post, $drafts = false ) {
	# Root path + the date - post slug
	if ( $drafts ) {
		$f = strlen( DRAFT_FILE ) + 1; 
	} else {
		$f = strlen( POST_FILE ) + 1; 
	}
	$i = strlen( postRoot() ) + $f;
	
	usort( $post, function( $a, $b ) use ( $i ) {
		$c = strncmp( $a, $b, $i );
		
		# If the posts were created on the same day, 
		# sort by created date (modified date on *nix)
		return ( 0 === $c ) ? 
			( filectime( $b ) - filectime( $a ) ) : 
			( $c < 0 );
	} );
	
	return $post;
}

/**
 * Search for posts in a day/month/year range
 */
function searchDays( $args ) {
	$s	= \DIRECTORY_SEPARATOR;
	$p	= fileByMode( $args );
	$params	= array();
	
	if ( isset( $args['day'] ) ) {
		$params['day']	= $args['day'];
		$f		= '*' . $s . $p;
	}
	if ( isset( $args['month'] ) ) {
		$params['month']	= $args['month'];
		$f		= '*' . $s . '*' . $s . $p;
	}
	if ( isset( $args['year'] ) ) {
		$params['year']	= $args['year'];
		$f		= 
			'*'. $s .'*'. $s .'*'. $s . $p;
	}
	$params	= array_reverse( $params );
	$search	= postRoot() . $s . implode( $s, $params ) . $s;
	$posts	= glob( $search . $f, \GLOB_NOSORT );
	$drafts	= ( $p == POST_FILE ) ? true : false;
	
	return sortByModified( $posts, $drafts );
}

/**
 * Parse archive search request
 */
function findArchive( $args ) {
	if ( isset( $args['day'] ) ) {
		$days = array(
			'year'	=> $args['year'],
			'month'	=> $args['month'],
			'day'	=> $args['day'],
		);
	}
	if ( isset( $args['month'] ) ) {
		$days = array(
			'year'	=> $args['year'],
			'month'	=> $args['month']
		);
	}
	if ( isset( $args['year'] ) ) {
		$days = array( 'year'=> $args['year'] );
	}
	$days['mode']	= isset( $args['mode'] ) ?
				$args['mode'] : null;
	return searchDays( $days );
}

/**
 * Get posts that are closest to the current date path
 */
function siblingPosts( $args ) {
	$mode		= isset( $args['mode'] ) ?
				$args['mode'] : null;
	$s1		=  
	searchDays( array( 
		'year'	=> $args['year'], 
		'month'	=> ( $args['month'] - 1 ),
		'mode'	=> $mode
	) );
	$s2		=  
	searchDays( array( 
		'year'	=> $args['year'], 
		'month'	=> $args['month'],
		'mode'	=> $mode
	) );
	$s3		=  
	searchDays( array( 
		'year'	=> $args['year'], 
		'month'	=> ( $args['month'] + 1 ),
		'mode'	=> $mode
	) );
	$siblings	= array_merge( $s1, $s2, $s3 );
	
	if ( empty( $siblings ) ) {
		$siblings = 
		searchDays( array( 
			'year'	=> $args['year'], 
			'mode'	=> $mode 
		) );
	}
	
	if ( empty( $siblings ) ) {
		if ( ( int ) $args['year'] < ( int ) date( 'Y' ) ) {
			$siblings = 
			searchDays( array( 
				'year'	=> ( $args['year'] + 1 ), 
				'mode'	=> $mode
			) );
		} else {
			$siblings = 
			searchDays( array( 
				'year' 	=> ( $args['year'] - 1 ), 
				'mode'	=> $mode
			) );
		}
	}
	
	$drafts	= ( fileByMode( $args ) == DRAFT_FILE ) ? 
			true : false;
	return sortByModified( $siblings, $drafts );
}

/**
 * Get posts that are closest neighbors to the current
 */
function nextPrev( $args ) {
	$siblings	= siblingPosts( $args );
	$current	= exactPost( $args );
	
	if ( !in_array( $current, $siblings ) ) {
		return array();
	}
	$np		= array();
	$k		= array_search( $current, $siblings );
	
	if ( $k > 0 ) {
		$np[] = $siblings[$k - 1];
	}
	
	if ( $k < count( $siblings ) - 1 ) {
		$np[] = $siblings[$k + 1];
	}
	
	return $np;
}

/**
 * Search all posts for a year starting with current
 * Keeps looking until posts are found or until the year 2000
 */
function searchFrom( $year ) {
	$paths		= array();
	while( empty( $paths ) && $year > YEAR_END ) {
		$paths	= searchDays( array( 'year' => $year ) );
		$year--;
	}
	
	return $paths;
}

/**
 * Paginate an archive index listing
 */
function archivePaginate( $args, $conf ) {
	$paths		= findArchive( $args );
	$page		= isset( $args['page'] ) ? $args['page'] : 1;
	$offset		= ( $page - 1 ) * $conf['post_limit'];
	
	return 
	array_slice( $paths, $offset, $conf['post_limit'] );
}

/**
 * Paginate the front page index listing
 */
function indexPaginate( $args, $conf ) {
	$year		= ( int ) date( 'Y' );
	$page		= isset( $args['page'] ) ? $args['page'] : 1;
	
	$offset		= ( $page - 1 ) * $conf['post_limit'];
	
	$paths		= searchFrom( $year );
	return 
	array_slice( $paths, $offset, $conf['post_limit'] );
}

/**
 * Select the file type post/draft etc... by mode
 */
function fileByMode( $args ) {
	if ( isset( $args['mode'] ) ) {
		switch( $args['mode'] ) {
			case 'drafts':
				return DRAFT_FILE;
				
			default: 
				return POST_FILE;
		}
	}
	return POST_FILE;
}

/**
 * Find the post data file of a specific post by date and slug
 */
function exactPost( $args ) {
	$s	= \DIRECTORY_SEPARATOR;
	$path	= array(
		$args['year'], $args['month'], 
		$args['day'], $args['slug']
	);
	$p	= fileByMode( $args );
	
	return postRoot() . $s . implode( $s, $path ) . 
		$s . $p;
}

/**
 * Load a content page and return decoded JSON
 */
function loadPost( $file ) {
	if ( !file_exists( $file ) ) {
		return null;
	}
	
	$data = file_get_contents( $file );
	if ( false !== strpos( $data, '<?' ) ) {
		endf( MSG_SSDETECT );
	}
	
	$params	= json_decode( utf8_encode( $data ), true );
	if ( empty( $params ) ) {
		message( MSG_POSTDERR, true );
	}
	
	return $params;
}


/**
 * Find a post if it exists. Returns JSON decoded post data
 */
function findPost( $args ) {
	$search = exactPost( $args );
	return loadPost( $search );
}

/**
 * Load list of post paths, JSON decoded and returns an array
 */
function loadPosts( $paths ) {
	$posts	= array();
	foreach ( $paths as $path ) {
		$post	= loadPost( $path );
		if ( !empty( $post ) ) {
			$posts[] = $post;
		}
	}
	
	return $posts;
}


/* User authentication */

/**
 * Parse and filter user submitted login data
 */
function getLogin() {
	$filter = array(
		'csrf'		=> \FILTER_SANITIZE_STRING,
		'password'	=> \FILTER_UNSAFE_RAW
	);
	
	$data			= 
	\filter_input_array( \INPUT_POST, $filter );
	
	if ( !validateCsrf( 'login', $data['csrf'] ) ) {
		return null;
	}
	if ( empty( $data['password'] ) ) {
		return null;
	}
	
	return $data['password'];
}

/**
 * Password change form data
 */
function getPass() {
	$filter = array(
		'csrf'		=> \FILTER_SANITIZE_STRING,
		'oldpassword'	=> \FILTER_UNSAFE_RAW,
		'newpassword'	=> \FILTER_UNSAFE_RAW
	);
	
	$data			= 
	\filter_input_array( \INPUT_POST, $filter );
	
	if ( !validateCsrf( 'changePass', $data['csrf'] ) ) {
		return null;
	}
	
	if ( 
		empty( $data['oldpassword'] ) || 
		empty( $data['newpassword'] ) 
	) {
		return null;
	}
	
	return array(
		'oldpassword'	=> $data['oldpassword'],
		'newpassword'	=> $data['newpassword']
	);
}

/**
 * Hash password securely and into a storage safe format
 * 
 * @link https://paragonie.com/blog/2015/04/secure-authentication-php-with-long-term-persistence
 */
function password( $password ) {
	return base64_encode(
	\password_hash(
		base64_encode(
			hash( 'sha384', $password, true )
		),
		\PASSWORD_DEFAULT
	) );
}

/**
 * Verify user provided password against stored one
 */
function verifyPassword( $password, $stored ) {
	$stored = base64_decode( $stored, true );
	if ( false === $stored ) {
		return false;
	}
	
	return 
	\password_verify(
		base64_encode( 
			hash( 'sha384', $password, true )
		),
		$stored
	);
}

/**
 * Checks if the current password needs to be rehashed
 */
function passNeedsRehash( $stored ) {
	$stored = base64_decode( $stored, true );
	if ( false === $stored ) {
		return false;
	}
	
	return 
	\password_needs_rehash( $stored, \PASSWORD_DEFAULT );
}

/**
 * Check authorization and refresh the session
 */
function authority() {
	if ( auth() ) {
		setAuth();
		return;
	}
	
	message( MSG_LOGIN );
}

/**
 * Check authorization token
 */
function auth() {
	sessionCheck();
	if ( empty( $_SESSION['auth'] ) ) {
		return false;
	}
	
	$sig			= signature();
	$visit			= $_SESSION['canary']['visit'];
	
	if ( verifyPbk( $sig . $visit, $_SESSION['auth'] ) ) {
		return true;
	}
	
	return false;
}

/**
 * Set the authorization token ( after login confirmation )
 */
function setAuth() {
	sessionCheck();
	$sig			= signature();
	$visit			= $_SESSION['canary']['visit'];
	$_SESSION['auth']	= pbk( $sig . $visit );
}

/**
 * Create current visitor's browser signature by sent headers
 */
function signature() {
	$headers	= httpHeaders();
	$skip		= 
	array(
		'Accept-Datetime',
		'Accept-Encoding',
		'Content-Length',
		'Cache-Control',
		'Content-Type',
		'Content-Md5',
		'Referer',
		'Cookie',
		'Expect',
		'Date',
		'TE'
	);
	
	$search		= 
	array_intersect_key( 
		array_keys( $headers ), 
		array_reverse( $skip ) 
	);
	
	$match		= '';
	foreach ( $headers as $k => $v ) {
		$match .= $v[0];
	}
}

/**
 * Process HTTP_* variables
 */
function httpHeaders() {
	$val = array();
	foreach ( $_SERVER as $k => $v ) {
		if ( 0 === strncasecmp( $k, 'HTTP_', 5 ) ) {
			$a = explode( '_' ,$k );
			array_shift( $a );
			array_walk( $a, function( &$r ) {
				$r = ucfirst( strtolower( $r ) );
			} );
			$val[ implode( '-', $a ) ] = $v;
		}
	}
	return $val;
}


/* Site configuration */

/**
 * Parse and filter sent site settings
 */
function getSettings( $conf ) {
	$filter = array(
		'csrf'		=> \FILTER_SANITIZE_STRING,
		'title'		=> \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
		'tagline'	=> \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
		'posts'		=>
		array(
			'filter'	=> \FILTER_VALIDATE_INT,
			'options'	=> 
			array(
				'default'	=> 5,
				'min_range'	=> 1,
				'max_range'	=> 100 
			)
		),
		'uploads'		=>
		array(
			'filter'	=> \FILTER_VALIDATE_INT,
			'options'	=> 
			array(
				'default'	=> 1,
				'min_range'	=> 0,
				'max_range'	=> 1 
			)
		),
		'datetime'	=> \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
		'timezone'	=> \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
		'copyright'	=> \FILTER_UNSAFE_RAW
	);
	
	$data			= 
	\filter_input_array( \INPUT_POST, $filter );
	
	if ( !validateCsrf( 'settings', $data['csrf'] ) ) {
		return null;
	}
	
	if ( !empty( $data['copyright'] ) ) {
		 $data['copyright']  = 
		 	clean( $data['copyright'], $conf['tags'] );
	}
	
	if ( !in_array(
		$data['timezone'],
		\DateTimeZone::listIdentifiers() 
	) ) {
		$data['timezone'] = 'America/New_York';
	}
	
	try {
		$test		= date( $data['datetime'] );
	} catch( \Exception $e ) {
		$data['datetime'] = 'l, M d, Y';
	}
	
	$params = array(
		'title'		=> 
			empty( $data['title'] ) ? 
				'No title' : $data['title'],
		
		'tagline'	=>
			empty( $data['tagline'] ) ? 
				'No tagline' : $data['tagline'],
		'allow_uploads'	=> $data['uploads'],
		'post_limit'	=> $data['posts'],
		'date_format'	=> $data['datetime'],
		'timezone'	=> $data['timezone'],
		'copyright'	=> $data['copyright']
	);
	
	return $params;
}



/* Templates, rendering, and configuration */


/**
 * Replace template placeholders with data
 */
function render( $content, $template ) {
	$v = array();
	foreach( array_keys( $content ) as $k ) {
		$v[] = '{' . $k . '}';
	}
	return str_replace( $v, array_values( $content ), $template );
}

/**
 * Load file contents and check for any server-side code
 */
function loadFile( $name ) {
	if ( file_exists( $name ) ) {
		$data = file_get_contents( $name );
		if ( false !== strpos( $data, '<?' ) ) {
			endf( MSG_SSDETCT );
		}
		return $data;
		
	} else {
		endf( MSG_LOADERR );
	}
}

/**
 * Load configuration file ( JSON formatted )
 */
function loadConf() {
	$data	= trim( loadFile( STORE . CONFIG ) );
	if ( empty( $data ) ) {
		endf( MSG_CONFERR );
	}
	
	$params	= json_decode( utf8_encode( $data ), true, 6 );
	if ( empty( $params ) ) {
		endf( MSG_CONFERR );
	}
	
	return $params;
}

/**
 * Save configuration file as JSON
 */
function saveConf( array $conf ) {
	$params	= array_merge( loadConf(), $conf );
	$data	= json_encode( $params, 
			\JSON_HEX_QUOT | \JSON_HEX_TAG | 
			\JSON_PRETTY_PRINT );
	
	file_put_contents( STORE . CONFIG, $data );
}

/**
 * Load a specific template file
 */
function loadTpl( $name ) {
	return loadFile( TEMPLATES . $name );
}


/* HTML Filtering */

/**
 * Convert an unformatted text block to paragraphs
 * 
 * @link http://stackoverflow.com/a/2959926
 * @param $val string Filter variable
 */
function makeParagraphs( $val ) {
	$out = nl2br( $val );
	
	/**
	 * Turn consecutive <br>s to paragraph breaks and wrap the 
	 * whole thing in a paragraph
	 */
	$out = '<p>' . preg_replace( 
			'#(?:<br\s*/?>\s*?){2,}#', '<p></p><p>', $out 
		) . '</p>';
	
	/**
	 * Remove <br> abnormalities
	 */
	$out = preg_replace( '#<p>(\s*<br\s*/?>)+#', '</p><p>', $out );
	return preg_replace( '#<br\s*/?>(\s*</p>)+#', '<p></p>', $out );
}

/**
 * HTML safe character entities in UTF-8
 * 
 * @return string
 */
function entities( $v, $quotes = true ) {
	if ( $quotes ) {
		return \htmlentities( 
			\iconv( 'UTF-8', 'UTF-8', $v ), 
			\ENT_QUOTES | \ENT_SUBSTITUTE, 
			'UTF-8'
		);
	}
	
	return \htmlentities( 
		\iconv( 'UTF-8', 'UTF-8', $v ), 
		\ENT_NOQUOTES | \ENT_SUBSTITUTE, 
		'UTF-8'
	);
}

/**
 * Scrub each node against white list
 */
function scrub(
	\DOMNode $node,
	&$flush = array(),
	$white
) {
	if ( isset( $white[$node->nodeName] ) ) {
		# Clean attributes first
		cleanAttributes( $node, $white );
		
		if ( $node->childNodes ) {
			# Continue to other tags
			foreach ( $node->childNodes as $child ) {
				scrub( $child, $flush, $white );
			}
		}
	} elseif ( $node->nodeType == \XML_ELEMENT_NODE ) {
		# This tag isn't on the whitelist
		$flush[] = $node;
	}
}
	
/**
 * Clean DOM node attribute against whitelist
 * 
 * @param $node object DOM Node
 */
function cleanAttributes(
	\DOMNode &$node,
	$white
) {
	if ( !$node->hasAttributes() ) {
		return;
	}
	
	foreach ( 
		\iterator_to_array( $node->attributes ) as $at
	) {
		$n = $at->nodeName;
		$v = $at->nodeValue;
		
		# Default action is to remove attribute
		# It will only get added if it's safe
		$node->removeAttributeNode( $at );
		if ( in_array( $n, $white[$node->nodeName] ) ) {
			switch( $n ) {
				case 'longdesc':
				case 'url':
				case 'src':
				case 'href':
					$v = cleanUrl( $v );
					break;
					
				default:
					$v = entities( $v );
			}
			
			$node->setAttribute( $n, $v );
		}
	}
}

/**
 * Filter URL 
 * 
 * @param string $txt Raw URL attribute value
 */
function cleanUrl( $txt, $xss = true ) {
	if ( empty( $txt ) ) {
		return '';
	}

	if ( filter_var( $txt, \FILTER_VALIDATE_URL ) ) {
		if ( $xss ) {
			if ( !preg_match( RX_URL, $txt ) ){
				return '';
			}	
		}
		if ( 
			preg_match( RX_XSS2, $txt ) || 
			preg_match( RX_XSS3, $txt ) || 
			preg_match( RX_XSS4, $txt ) 
		) {
			return '';
		}
		
		return $txt;
	}
	return '';
}

/**
 * Clean user provided HTML
 * 
 * @param string $html Raw HTML
 * @param array $white Whitelist of allowed tags and attributes
 * @param bool $parse Apply markdown syntax formatting (defaults to true)
 * 
 * @return string Cleaned and formatted HTML
 */
function clean( $html, $white, $parse = false ) {
	# TODO Markdown format
	# $parse
	$err		= \libxml_use_internal_errors( true );
	
	# Remove control chars except linebreaks/tabs etc...
	$html		= 
	preg_replace(
		'/[\x00-\x08\x0B\x0C\x0E-\x1F\x80-\x9F]/u', 
		'', 
		$html
	);
		
	# Unicode character support
	$html		= \mb_convert_encoding( 
				$html, 'HTML-ENTITIES', "UTF-8" 
			);
	
	# Wrap content in paragraphs
	$html		= makeParagraphs( $html );
	$html		= tidyup( $html );
	$dom		= new \DOMDocument();
	$dom->loadHTML( 
		$html, 
		\LIBXML_HTML_NOIMPLIED | \LIBXML_HTML_NODEFDTD | 
		\LIBXML_NOERROR | \LIBXML_NOWARNING | 
		\LIBXML_NOXMLDECL | \LIBXML_COMPACT | 
		\LIBXML_NOCDATA
	);
	
	$domBody	= 
	$dom->getElementsByTagName( 'body' )->item( 0 );
	
	# Iterate through every HTML element 
	foreach( $domBody->childNodes as $node ) {
		scrub( $node, $flush, $white );
	}
	
	# Remove any tags not found in the whitelist
	if ( !empty( $flush ) ) {
		foreach( $flush as $node ) {
			if ( $node->nodeName == '#text' ) {
				continue;
			}
			# Replace tag has harmless text
			$safe	= $dom->createTextNode( 
					$dom->saveHTML( $node )
				);
			$node->parentNode
				->replaceChild( $safe, $node );
		}
	}
	
	$clean		= '';
	foreach ( $domBody->childNodes as $node ) {
		$clean .= $dom->saveHTML( $node );
	}
	
	\libxml_clear_errors();
	\libxml_use_internal_errors( $err );
	$clean		= embeds( $clean );
	
	return trim( $clean );
}

/**
 * Tidy settings
 */
function tidyup( $text ) {
	if ( !exists( 'tidy_repair_string' ) ) {
		return $text;
	}
	
	$opt = array(
		'bare'				=> 1,
		'hide-comments' 		=> 1,
		'drop-proprietary-attributes'   => 1,
		'fix-uri'			=> 1,
		'join-styles'			=> 1,
		'output-xhtml'			=> 1,
		'merge-spans'			=> 1,
		'show-body-only'		=> 0,
		'wrap'				=> 0
	);
	
	return trim( \tidy_repair_string( $text, $opt ) );
}

/**
 * Embedded Big Brother silo media
 */
function embeds( $html ) {
	$filter		= 
	array(
		'/\[youtube http(s)?\:\/\/(www)?\.?youtube\.com\/watch\?v=([0-9a-z_]*)\]/is'
		=> 
		'<div class="media"><iframe width="560" height="315" src="https://www.youtube.com/embed/$3" frameborder="0" allowfullscreen></iframe></div>',
		
		'/\[youtube http(s)?\:\/\/(www)?\.?youtu\.be\/([0-9a-z_]*)\]/is'
		=> 
		'<div class="media"><iframe width="560" height="315" src="https://www.youtube.com/embed/$3" frameborder="0" allowfullscreen></iframe></div>',
		
		'/\[youtube ([0-9a-z_]*)\]/is'
		=> 
		'<div class="media"><iframe width="560" height="315" src="https://www.youtube.com/embed/$1" frameborder="0" allowfullscreen></iframe></div>',
		
		'/\[vimeo ([0-9]*)\]/is'
		=> 
		'<div class="media"><iframe src="https://player.vimeo.com/video/$1?portrait=0" width="500" height="281" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe></div>',
		
		'/\[vimeo http(s)?\:\/\/(www)?\.?vimeo\.com\/([0-9]*)\]/is'=> 
		'<div class="media"><iframe src="https://player.vimeo.com/video/$3?portrait=0" width="500" height="281" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe></div>'
	);
	
	return 
	preg_replace( 
		array_keys( $filter ), 
		array_values( $filter ), 
		$html 
	);
}

/**
 * Navigation page helper
 */
function pageLink( $text, $url, $tool = '' ) {
	if ( empty( $tool ) ) {
		return 
		"<li><a href='{$url}'>{$text}</a></li>";
	}
	return 
	"<li><a href='{$url}' title='{$tool}'>{$text}</a></li>";
}

/**
 * Format each post into the post template
 */
function parsePosts( $posts, $paths, $args, $conf ) {
	$ptpl	= loadTpl( 'tpl_postfrag.html' );
	$parsed	= '';
	$i	= 0;
	
	foreach( $posts as $post ) {
		$pdate	= dateWithoutSlug( dateAndSlug( $paths[$i], $args ) );
		$pdate	= date( $conf['date_format'], strtotime( $pdate ) );
		
		$ppath	= datePath( $post['slug'], strtotime( $pdate ) );
		
		$vars	= 
		array(
			'post_title'	=> $post['title'],
			'post_body'	=> $post['body'],
			'post_date'	=> $pdate,
			'post_path'	=> $ppath
		);
		
		$parsed .= render( $vars, $ptpl );
		$i++;
	}
	
	return $parsed;
}

/**
 * Next / Previous page links on the index and archive pages
 */
function indexPages( $args, $conf, $paths ) {
	$page	= isset( $args['page'] ) ? 
			( int ) $args['page'] : 1;
	$pre	= isset( $args['mode'] ) ? 
			$args['mode'] . '/' : '';
	
	$npa	= '';
	$pm1	= $page - 1;
	if ( $page > 1 ) {
		if ( 0 <= $pm1 ) {
			if ( count( $paths ) < $conf['post_limit'] ) {
				$npa .= 
				pageLink( 'Next', $pre . 'page'. $pm1 );
			}
			$npa .= 
			pageLink( 'Home', '/' );
		} else {
			$npa .= 
			pageLink( 'Next', $pre . 'page'. $pm1 );
		}
	} else {
		$npa .= '<li></li>';
	}
	
	if ( empty( $paths ) ) {
		return $npa;
	}
	
	if ( 
		count( $paths ) >= $conf['post_limit'] && 
		$page >= 1
	) {
		$npa .= 
		pageLink( 'Previous', '/' . $pre . 'page'. ( $page + 1 ) );
	}
	
	return $npa;
}

/**
 * Extract the date and slug from the full post path
 */
function dateAndSlug( $path, $args ) {
	$i = strlen( postRoot() ) + 1;
	$p = fileByMode( $args );
	# Remove the root and '/POST_FILE'
	$f = strlen( $p );
	return substr( substr( $path, 0, -$f ), $i );
}

/**
 * Extract the date and slug from the full post path
 */
function dateWithoutSlug( $path ) {
	$path	= rtrim( $path, '\\' );
	$i	= strrpos( $path, '\\' );
	$p	= substr( $path, 0, $i );
	
	return str_replace( '\\', '/', $p );
}

/**
 * Next and previous post links 
 */
function siblingPages( $pages, $args ) {
	$npa	= '';
	$sibs	= loadPosts( $pages );
	$i	= strlen( postRoot() ) + 1;
	$mode	= isset( $args['mode'] ) ? 
			$args['mode'] : 'read';
	
	foreach( $sibs as $k => $s ) {
		$p	= dateAndSlug( $pages[$k], $args );
		
		$path	= '/' . $mode . '/' . $p;
		$tool	= entities( $s['summary'], true );
		$npa	.= pageLink( $s['title'], $path, $tool );
	}
	
	return $npa;
}




/* Security */

/**
 * Secure comparison of two strings in constant time
 */
function equals( $str1, $str2 ) {
	if ( exists( 'hash_equals' ) ) {
		return \hash_equals( $str1, $str2 );
	}
	return 
	substr_count( $str1 ^ $str2, "\0" ) * 2 === 
		strlen( $str1 . $str2 );
}

/**
 * Key derivation function
 */
function pbk( 
	$txt, 
	$salt	= '', 
	$algo	= 'tiger160,4',
	$rounds	= 1000, 
	$kl	= 128
) {
	$salt	= empty( $salt ) ? bin2hex( bytes( 16 ) ) : $salt;
	$hash	= \hash_pbkdf2( $algo, $txt, $salt, $rounds, $kl );
	$out	= array(
			$algo, $salt, $rounds, $kl, $hash
		);
	return base64_encode( implode( '$', $out ) );
}

/**
 * Verify derived key against plain text
 */
function verifyPbk( $txt, $hash ) {
	if ( empty( $hash ) || mb_strlen( $hash, '8bit' ) > 600 ) {
		return false;
	}
	$key	= base64_decode( $hash, true );
	if ( false === $key ) {
		return false;
	}
	
	$k	= explode( '$', $key );
	if ( empty( $k ) || empty( $txt ) ) {
		return false;
	}
	if ( count( $k ) != 5 ) {
		return false;
	}
	if ( !in_array( $k[0], \hash_algos() , true ) ) {
		return false;
	}
	
	$pbk	= 
	\hash_pbkdf2( $k[0], $txt,$k[1], ( int ) $k[2], ( int ) $k[3] );
	
	return equals( cleanPbk( $k[4] ),  $pbk );
}

/**
 * Scrub the derived key of any invalid characters
 */
function cleanPbk( $hash ) {
	return preg_replace( '/[^a-f0-9\$]+$/i', '', $hash );
}

/**
 * Generate a form-specific anti-cross-site-request forgery token
 */
function getCsrf( $form ) {
	sessionCheck();
	$salt				= bin2hex( bytes( 4 ) );
	$_SESSION['form_' . $form]	= $salt;
	return pbk( $salt . $form . $_SESSION['canary']['visit'] );
}

/**
 * Validate anti-cross-site-request forgery token for this form
 */
function validateCsrf( $form, $hash ) {
	sessionCheck();
	if ( !isset( $_SESSION['form_' . $form] ) ) {
		return false;
	}
	$salt	= $_SESSION['form_' . $form];
	return 
	verifyPbk( 
		$salt . $form . $_SESSION['canary']['visit'], 
		$hash 
	);
}

/**
 * Generate cryptographically secure pseudorandom bytes
 */
function bytes( $len ) {
	if ( exists( 'random_bytes' ) ) {
		return \random_bytes( $len );
	}
	
	if ( exists( 'openssl_random_pseudo_bytes' ) ) {
		return \openssl_random_pseudo_bytes( $len );
	}
	
	if ( exists( 'mcrypt_create_iv' ) ) {
		return \mcrypt_create_iv( $len, \MCRYPT_DEV_URANDOM );
	}
}

/**
 * Session owner and staleness marker
 * 
 * @link https://paragonie.com/blog/2015/04/fast-track-safe-and-secure-php-sessions
 */
function sessionCanary( $visit = null ) {
	$_SESSION['canary'] = 
	array(
		'exp'	=> time() + SESSION_EXP,
		'visit'	=> empty( $visit ) ? 
				bin2hex( bytes( 12 ) ) : $visit
	);
}

/**
 * Check session staleness
 */
function sessionCheck( $reset = false ) {
	session( $reset );
	
	if ( empty( $_SESSION['canary'] ) ) {
		sessionCanary();
		return;
	}
	
	if ( time() > ( int ) $_SESSION['canary']['exp'] ) {
		$visit = $_SESSION['canary']['visit'];
		\session_regenerate_id( true );
		sessionCanary( $visit );
	}
}

/**
 * Scrub globals
 */
function cleanGlobals() {
	if ( !isset( $GLOBALS ) ) {
		return;
	}
	foreach ( $GLOBALS as $k => $v ) {
		if ( 0 != strcasecmp( $k, 'GLOBALS' ) ) {
			unset( $GLOBALS[$k] );
		}
	}
}

/**
 * End current session activity
 */
function cleanSession() {
	if ( \session_status() === \PHP_SESSION_ACTIVE ) {
		\session_unset();
		\session_destroy();
		\session_write_close();
	}
}

/**
 * Initiate a session if it doesn't already exist
 * Optionally reset and destroy session data
 */
function session( $reset = false ) {
	if ( 
		\session_status() === \PHP_SESSION_ACTIVE && 
		!$reset 
	) {
		return;
	}
	
	if ( \session_status() != \PHP_SESSION_ACTIVE ) {
		\session_name( 'is' );
		\session_start();
	}
	if ( $reset ) {
		\session_regenerate_id( true );
		foreach ( array_keys( $_SESSION ) as $k ) {
			unset( $_SESSION[$k] );
		}
	}
}


/**
 * Scrub all outputs and end the session
 */
function endf( $msg = '' ) {
	cleanGlobals();
	cleanSession();
		
	ob_start();
	ob_end_clean();
	die( $msg );
}

/**
 * Check if a function exists ( Suhosin compatible )
 * 
 * @param string $func Function name
 * @return boolean true If the function exists
 */
function exists( $func ) {
	if ( \extension_loaded( 'suhosin' ) ) {
		$exts = ini_get( 'suhosin.executor.func.blacklist' );
		if ( !empty( $exts ) ) {
			$blocked	= explode( ',', strtolower( $exts ) );
			$blocked	= array_map( 'trim', $blocked );
			$search		= strtolower( $func );
			
			return ( 
				true	== \function_exists( $func ) && 
				false	== array_search( $search, $blocked ) 
			);
		}
	}
	
	return \function_exists( $func );
}

/**
 * Paths are sent in bare. Make them suitable for matching.
 * 
 * @param string $route URL path in plain format
 * @return string Route in regex format
 */
function cleanRoute( $k, $v, $route ) {
	$route	= str_replace( $k, $v, $route );
	$regex	= str_replace( '.', '\.', $route );
	return '@^/' . $route . '/?$@i';
}

/**
 * Filter path parameters to get rid of numeric indexes
 */
function filter( $matches ) {
	return array_intersect_key(
		$matches, 
		array_flip( 
			array_filter(
				array_keys( $matches ), 
				'is_string' 
			)
		)
	);
}

/**
 * Route the current path according to the specified callback map
 */
function route( $routes ) {
	$verb		= strtolower( $_SERVER['REQUEST_METHOD'] );
	$path		= $_SERVER['REQUEST_URI'];
	$markers	= 
	array(
		'*'	=> '(?<all>.+?)',
		':page'	=> '(?<page>[1-9][0-9]*)',
		':year'	=> '(?<year>[2][0-9]{3})',
		':month'=> '(?<month>[0-3][0-9]{1})',
		':day'	=> '(?<day>[0-9][0-9]{1})',
		':slug'	=> '(?<slug>[\pL\-\d]{1,100})',
		':mode'	=> '(?<mode>edit|drafts)'
	);
	$k		= array_keys( $markers );
	$v		= array_values( $markers );
	$found		= false;
	
	foreach( $routes as $map ) {
		if ( $map[0] != $verb ) {
			continue;
		}
		
		$rx = cleanRoute( $k, $v, $map[1] );
		if ( preg_match( $rx, $path, $params ) ) {
			$found = true;
			if ( is_callable( $map[2] ) ) {
				$params = filter( $params );
				call_user_func( $map[2], $params );
			}
			break;
		}
	}
	
	if ( !$found ) {
		message( MSG_NOTFOUND );
	}
}

/**
 * Notification page used for logins, logouts, error pages etc...
 */
function message( $msg, $scrub = false ) {
	$conf	= loadConf();
	$vars	= 
	array(
		'page_title'	=> $conf['title'],
		'tagline'	=> $conf['tagline'],
		'theme'		=> $conf['theme_dir'],
		'page_body'	=> $msg
	);
	
	$tpl	= loadTpl( 'tpl_message.html' );
	$html	= render( $vars, $tpl );
	if ( $scrub ) {
		endf( $html );
	}
	
	echo $html;
	die();
}


/* Route functionality */


/**
 * Index/Homepage route
 */
$index		=  
function() {
	$args	= func_get_args()[0];
	$conf	= loadConf();
	
	$paths	= indexPaginate( $args, $conf );
	$posts	= loadPosts( $paths );
	if ( empty( $posts ) ) {
		message( MSG_NOPOSTS );
	}
	
	$parsed	= parsePosts( $posts, $paths, $args, $conf );
	$npa	= indexPages( $args, $conf, $paths );
	$vars	= 
	array(
		'page_title'	=> $conf['title'],
		'tagline'	=> $conf['tagline'],
		'page_body'	=> $parsed,
		'theme'		=> $conf['theme_dir'],
		'navpages'	=> $npa,
		'copyright'	=> $conf['copyright']
	);
	
	$tpl	= loadTpl( 'tpl_index.html' );
	echo render( $vars, $tpl );
	
	die();
};

/**
 * Year/Month/Day archive routes
 */
$archive	= 
function() {
	$args	= func_get_args()[0];
	$conf	= loadConf();
	\date_default_timezone_set( $conf['timezone'] );
	
	$paths	= archivePaginate( $args, $conf );
	if ( empty( $paths ) ) {
		message( MSG_NOPOSTS );
	}
	
	$posts	= loadPosts( $paths );
	if ( empty( $posts ) ) {
		message( MSG_NOPOSTS );
	}
	
	$npa	= indexPages( $args, $conf, $paths );
	$tpl	= loadTpl( 'tpl_index.html' );
	$parsed	= parsePosts( $posts, $paths, $args, $conf );
	
	$vars	= 
	array(
		'page_title'	=> $conf['title'],
		'tagline'	=> $conf['tagline'],
		'page_body'	=> $parsed,
		'theme'		=> $conf['theme_dir'],
		'navpages'	=> $npa,
		'copyright'	=> $conf['copyright']
	);
	
	echo render( $vars, $tpl );
	
	die();
};

/**
 * Reading a specific page
 */
$reading	= 
function() {
	$args	= func_get_args()[0];
	$conf	= loadConf();
	\date_default_timezone_set( $conf['timezone'] );
	
	$post	= findPost( $args );
	if ( empty( $post ) ) {
		message( MSG_NOTFOUND );
	}
	
	$pages	= nextPrev( $args );
	$npa	= '';
	if ( !empty( $pages ) ) {
		$npa = siblingPages( $pages, $args );
	}
	
	$path	= exactPost( $args );
	$pdate	= dateWithoutSlug( dateAndSlug( $path, $args ) );
	$pdate	= date( $conf['date_format'], strtotime( $pdate ) );
	
	$ppath	= datePath( $post['slug'], strtotime( $pdate ) );
	
	$vars	= 
	array(
		'page_title'	=> $conf['title'],
		'tagline'	=> $conf['tagline'],
		'theme'		=> $conf['theme_dir'],
		'post_title'	=> $post['title'],
		'post_date'	=> $pdate,
		'post_path'	=> $ppath,
			
		'post_body'	=> $post['body'],
		'navpages'	=> $npa,
		'copyright'	=> $conf['copyright']
	);
	$tpl	= loadTpl( 'tpl_post.html' );
	echo render( $vars, $tpl );
	
	die();
};

/**
 * Edit/Create file and redirect to read it
 */
$save		= 
function() {
	$conf	= loadConf();
	\date_default_timezone_set( $conf['timezone'] );
	
	authority();
	
	$data	= getPost( $conf );
	if ( empty( $data ) ) {
		message( MSG_FORMEXP, true );
	}
	
	$post	= savePost( $data[0], $data[1], $data[2] );
	if ( $conf['allow_uploads'] ) {
		saveUploads( $data[0] );
	}
	header( 'Location: /read' . $post );
	die();
};

/**
 * Creating a new post
 */
$creating	= 
function() {
	$conf	= loadConf();
	\date_default_timezone_set( $conf['timezone'] );
	
	authority();
	
	$vars	= 
	array(
		'page_title'	=> $conf['title'],
		'tagline'	=> $conf['tagline'],
		'theme'		=> $conf['theme_dir'],
		
		'csrf'		=> getCsrf( 'post' )
	);
	$tpl	= loadTpl( 'tpl_new.html' );
	echo render( $vars, $tpl );
	
	die();
};

/**
 * Editing an existing post
 */
$editing	= 
function() {
	$conf	= loadConf();
	\date_default_timezone_set( $conf['timezone'] );
	
	authority();
	
	$args	= func_get_args()[0];
	$post	= findPost( $args );
	if ( empty( $post ) ) {
		message( MSG_NOTFOUND );
	}
	
	$edit	= base64_encode( 
			$args['year'] . '/' . 
			$args['month'] . '/' .
			$args['day'] . '/' . 
			$args['slug'] 
		);
	$vars	= 
	array(
		'page_title'	=> $conf['title'],
		'tagline'	=> $conf['tagline'],
		'theme'		=> $conf['theme_dir'],
		
		'csrf'		=> getCsrf( 'post' ),
		'post_title'	=> $post['title'],
		'post_body'	=> $post['raw'],
		'post_summary'	=> $post['summary'],
		'post_slug'	=> $post['slug'],
		'edit'		=> $edit
	);
	$tpl	= loadTpl( 'tpl_edit.html' );
	echo render( $vars, $tpl );
	
	die();
};

/**
 * User login page
 */
$loggingIn	= 
function() {
	$conf	= loadConf();
	\date_default_timezone_set( $conf['timezone'] );
	
	$vars	= 
	array(
		'page_title'	=> $conf['title'],
		'csrf'		=> getCsrf( 'login' ),
		'theme'		=> $conf['theme_dir']
	);
	
	$tpl	= loadTpl( 'tpl_login.html' );
	echo render( $vars, $tpl );
	
	die();
};

/**
 * Do login. If login is verified, set the authorization token
 */
$login		= 
function() {
	$conf	= loadConf();
	\date_default_timezone_set( $conf['timezone'] );
	
	$data	= getLogin();
	if ( empty( $data ) ) {
		message( MSG_LOGININV );
	}
	
	$stored	= $conf['password'];
	if ( verifyPassword( $data, $stored ) ) {
		setAuth();
		
		if ( passNeedsRehash( $stored ) ) {
			$conf['password'] = password( $data );
			saveConf( $conf );
		}
		message( MSG_LOGINGG );
		
	} else {
		message( MSG_LOGININV, true );
	}
};

/**
 * Logout user by calling session clean up and reset
 */
$logout		= 
function() {
	$conf	= loadConf();
	\date_default_timezone_set( $conf['timezone'] );
	
	session( true );
	message( MSG_LOGOUT, true );
};

/**
 * Management/Site settings page
 */
$manage	= 
function() {
	$conf	= loadConf();
	\date_default_timezone_set( $conf['timezone'] );
	
	if ( !auth() ) {
		message( MSG_LOGIN );
	}
	$vars	= 
	array(
		'page_title'	=> $conf['title'],
		'theme'		=> $conf['theme_dir'],
		
		'csrf_pass'	=> getCsrf( 'changePass' ),
		'csrf_settings'	=> getCsrf( 'settings' ),
		'site_title'	=> $conf['title'],
		'site_tagline'	=> $conf['tagline'],
		'site_posts'	=> $conf['post_limit'],
		'site_date'	=> $conf['date_format'],
		'site_timezone'	=> $conf['timezone'],
		'site_copyright'=> $conf['copyright']
	);
	
	$tpl	= loadTpl( 'tpl_manage.html' );
	echo render( $vars, $tpl );
	
	die();
};

/**
 * Change site settings
 */
$settings	=
function() {
	$conf	= loadConf();
	\date_default_timezone_set( $conf['timezone'] );
	
	if ( !auth() ) {
		message( MSG_LOGIN );
	}
	$data	= getSettings( $conf );
	if ( empty( $data ) ) {
		message( MSG_NOSETTS, true );
	}
	$conf	= array_merge( $conf, $data );
	saveConf( $conf );
	message( MSG_SETSAVE );
};

/**
 * Change user password. Verify against old one first
 */
$passChanged	= 
function() {
	$conf	= loadConf();
	\date_default_timezone_set( $conf['timezone'] );
	
	if ( !auth() ) {
		message( MSG_LOGIN );
	}
	
	$data	= getPass();
	if ( empty( $data ) ) {
		message( MSG_LOGININV, true );
	}
	
	$stored	= $conf['password'];
	
	if ( verifyPassword( $data['oldpassword'], $stored ) ) {
		$conf['password'] = password( $data['newpassword'] );
		saveConf( $conf );
		message( MSG_PASSCH );
		
	} else {
		message( 'Passwords did not match', true );
	}
};


/* Site routes */

$routes = array(
	array( 'get', '', $index ), 
	array( 'get', 'page:page', $index ), 
	array( 'get', ':mode', $index ), 
	array( 'get', ':mode/page:page', $index ), 
	
	array( 'get', ':year', $archive ), 
	array( 'get', ':year/page:page', $archive ), 
	
	array( 'get', ':year/:month', $archive ), 
	array( 'get', ':year/:month/page:page', $archive ), 
	
	array( 'get', ':year/:month/:day', $archive ),
	array( 'get', ':year/:month/:day/page:page', $archive ),
	array( 'get', ':mode/:year/:month/:day/page:page', $archive ),
	
	array( 'get', 'read/:year/:month/:day/:slug', $reading ), 
	array( 'get', ':mode/:year/:month/:day/:slug', $editing ),
	array( 'post', 'edit', $save ),
	
	array( 'get', 'new', $creating ),
	array( 'post', 'new', $save ),
	
	array( 'get', 'login', $loggingIn ),
	array( 'post', 'login', $login ),
	
	array( 'get', 'logout', $logout ),
	
	array( 'get', 'manage', $manage ),
	array( 'post', 'settings', $settings ),
	array( 'post', 'changepass', $passChanged )
);

route( $routes );
