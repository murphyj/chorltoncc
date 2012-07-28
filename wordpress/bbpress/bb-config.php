<?php
/** 
 * The base configurations of bbPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys and bbPress Language. You can get the MySQL settings from your
 * web host.
 *
 * This file is used by the installer during installation.
 *
 * @package bbPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for bbPress */
define( 'BBDB_NAME', 'jmblogge_wpblog' );

/** MySQL database username */
define( 'BBDB_USER', 'jmblogge_wpadmin' );

/** MySQL database password */
define( 'BBDB_PASSWORD', 'jabWO2woPKr52xndaexp' );

/** MySQL hostname */
define( 'BBDB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'BBDB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'BBDB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/bbpress/ WordPress.org secret-key service}
 *
 * @since 1.0
 */
define( 'BB_AUTH_KEY', 'E*R;iC7&zYozMah5{;JuIqYpjDM9p,@-/S=D2X)PtR-+XgmV;d*IoQK3`AayQ0-[' );
define( 'BB_SECURE_AUTH_KEY', 'tz+~7-co>H(6P];-|x{UW50<$A62|L+-G)N{GWl0]teR5|Gl6ti.z,SN6(6T4>l/' );
define( 'BB_LOGGED_IN_KEY', 'qJ1cMcH-^R%Kow.@Y &R*k0KiS=8t1w9U-ckd_]VQ?r/ug[v-z>G<|.vbX-{5;9>' );
define( 'BB_NONCE_KEY', 'put your unique phrase here' );
/**#@-*/

/**
 * bbPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$bb_table_prefix = 'bb_';

/**
 * bbPress Localized Language, defaults to English.
 *
 * Change this to localize bbPress. A corresponding MO file for the chosen
 * language must be installed to a directory called "my-languages" in the root
 * directory of bbPress. For example, install de.mo to "my-languages" and set
 * BB_LANG to 'de' to enable German language support.
 */
define( 'BB_LANG', '' );
?>