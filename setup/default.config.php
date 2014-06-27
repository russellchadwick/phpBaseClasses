<?php
// Globals
	$config['INCLUDE_URI'] = '/includes';
	$config['INCLUDE_PATH'] = $_SERVER['DOCUMENT_ROOT'] . $config['INCLUDE_URI'];
	$config['SKIN_URI'] = $config['INCLUDE_URI'] . '/skins/' . $_SERVER['SERVER_NAME'];
	$config['SKIN_PATH'] = $_SERVER['DOCUMENT_ROOT'] . $config['SKIN_URI'];
	$config['BACKUP_PATH'] = '';

// Debugging
	$config['ERROR_HANDLERS'] = array ('Display');				// Options are 'Display', 'Log'
	$config['CLI_ERROR_HANDLERS'] = array ('Log');				// Options are 'Display', 'Log'
	$config['ERRORLOG'] = $config['LOG_PATH'] . '/error.log';
	$config['FATAL_ERROR_HANDLERS'] = array ('Mail Admin');			// Options are 'Mail Admin', 'Show Backtrace'
	$config['ADMIN_EMAIL_ADDRESS'] = '';					// Will mail server admin listed in apache config if this is empty, no mailing will occur if both are empty

// Sessions
	$config['SESSIONS_ENABLED'] = false;
	$config['SESSION_VARS'] = array ('user_id', 'users_name', 
						'last_access');			// user_last_access needs to be last, and is for timeout feature
	$config['SESSION_TIMEOUT'] = 120;					// In minutes, requires user_last_access in line above
	$config['SESSION_REQUIRED_VAR'] = 'user_id';				// Only timeout if this is set
	$config['SESSION_LOGIN_URI'] = '/login.php';				// Where to send people if session expires
	$config['SESSION_SUCCESS_URI'] = '/main.php';				// Where to send people if they login successfully
	$config['SESSION_REQUIRED_URIS'] = array ('/protected', '/protected2');	// Which folders to timeout people in/check for login var
	$config['SESSION_REMEMBER_USER'] = false;				// Remember a users ID and display name when login is successful for autofil next session

// Caching
	$config['CACHING_ENABLED'] = false;
	$config['CACHE_TIMEOUT'] = 60;						// In minutes, how often to update cached pages.
	$config['CACHE_URIS'] = array ();					// Which folders to enable caching in
	$config['CACHE_DIRECTORY'] = $config['TEMP_PATH'] . '/cache';		// Where to place cached tree

// Database
	$config['DB'][1]['TYPE'] = 'PgSQL';					// Empty to not connect to database - Options are 'PgSQL', 'MySQL', 'Oracle', 'Informix'
	$config['DB'][1]['HOST'] = '';						// Empty to connect with domain socket
	$config['DB'][1]['USER'] = 'postgres';
	$config['DB'][1]['PASS'] = '';
	$config['DB'][1]['NAME'] = '';

//	$config['DB'][2]['TYPE'] = 'PgSQL';					// Empty to not connect to database
//	$config['DB'][2]['HOST'] = '';						// Empty to connect with domain socket
//	$config['DB'][2]['USER'] = 'pgsql';
//	$config['DB'][2]['PASS'] = '';
//	$config['DB'][2]['NAME'] = '';

	$config['QUERY_DIRECTORY'] = $config['INCLUDE_PATH'] . '/queries';	// Directory to load queries from
	$config['QUERY_FILES'] = array ();					// Filenames to load
	$config['DBERRORLOG'] = $config['LOG_PATH'] . '/db_error.log';		// Where to log database errors, empty will disable logging
	$config['DBUPDATELOG'] = 						// Where to log database updates, works like an archive log, everything run through doUpdate gets logged.
	$config['DBQUERYLOG'] = '';						// Where to log all queries, empty will disable logging
	$config['DBEXPLAINLOG'] = '';						// Where to log explanation of all queries, empty will disable logging
	$config['DBLOCKFILE'] = '';						// File to store status of database, if locked no updates will occur
	$config['DATALOCKLIFESPAN'] = '15';					// Time to manually expire lock on data in minutes.

// Date and Time
	$config['BEGINYEAR'] = 2000;						// Year that time starts on Jan 1st.  The later the date the faster library is, but library wont work on dates earlier than this
	$config['BEGINDAYOFWEEK'] = 6;						// Day of week for Jan 1st of above year.
	$config['HOLIDAYS']['FIXED'] = array (					// Holidays that are always on same month and day each year
						'01-01' => 'New Years Day', 
						'07-04' => 'Independence Day', 
						'11-11' => 'Veterans Day', 
						'12-25' => 'Christmas Day'
					);
	$config['HOLIDAYS']['VARIABLE'] = array (				// Holidays that are on a certain week of month and day of week.
						'05-L-Mon' => 'Memorial Day', 
						'09-1-Mon' => 'Labor Day', 
						'11-4-Thu' => 'Thanksgiving Day', 
						'11-4-Fri' => 'Thanksgiving Day'
					);

// Input
	$config['DEFAULT_DATE_INPUT'] = 'Calendar Popup';			// Options are 'Calendar Popup', 'Plain', 'Select Drop Boxes'

// HTML
	$config['CONTENT_TYPE'] = 'text/html; charset=iso-8859-1';
	$config['KEYWORDS'] = '';
	$config['BACKGROUND'] = '';
	$config['DESCRIPTION'] = '';
	$config['HOSTED_BY'] = '';
	$config['STYLESHEETS'] = array ('/includes/stylesheet.css');
	$config['JAVASCRIPTS'] = array ('/includes/javascripts/main.js');
	$config['TITLE_PREFIX'] = '';
	$config['HEADERS'] = array ();

	$config['EXTRA_INCLUDE_PATH'] = $config['INCLUDE_PATH'] . '/extra_include.php';
	$config['EXTRA_HEADER_PATH'] = $config['INCLUDE_PATH'] . '/header.php';
	$config['EXTRA_FOOTER_PATH'] = $config['INCLUDE_PATH'] . '/footer.php';
	$config['EXTRA_INCLUDE_PRE_PATH'] = array ();
	$config['EXTRA_INCLUDE_POST_PATH'] = array ();

// Mail
	$config['MAIL_FROM'] = 'donotreply@donotreply.com';			// Email address mailings will come from

// Extras
	$config['TREEMENU_ENABLED'] = false;
	$config['OVERLIB_ENABLED'] = false;
	$config['SMARTY_ENABLED'] = false;
?>
