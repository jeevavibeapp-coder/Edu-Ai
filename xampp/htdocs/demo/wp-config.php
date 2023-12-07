<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'NIC' );

/** Database username */
define( 'DB_USER', 'Ramya' );

/** Database password */
define( 'DB_PASSWORD', 'Ramya@0907' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '8=<D_V}R1*ARW3^An =Dc|cso<[wtwbO`$5!u5>TDBLK?S4*E,qbnwt=XM&:CT8h' );
define( 'SECURE_AUTH_KEY',  '6E^w3Nk9gQn/YzadJRCEa}+UIP<(aX@FQf4(7ht(O}E3WK?CLgv!*e,MxM7(g^7O' );
define( 'LOGGED_IN_KEY',    '&zss_V5 K0)>,,p:HSgRl%()LvyPn9Xm8htceynRH+,`<?YMZTr=)x4gFP%;<3V@' );
define( 'NONCE_KEY',        '2F%lyL4~M2Q=WWW0^JOI+]:rTY1%/V*D|5f~CFs=p.0E%KLGgKh3x0i`.(qfb+Zu' );
define( 'AUTH_SALT',        'uNA20~f*Y)kxKj|rE=n3p$.tb$emiAVT4y@[>zFvHp>1f3N=quc&e@)F<{1b85E,' );
define( 'SECURE_AUTH_SALT', '_y]tTb~T>H$n0u7COz~Sco;&wqS`9Si:_%+VZoQZQ:LO:ySn>H9c*zk?V1uern(4' );
define( 'LOGGED_IN_SALT',   '{@Ltcia`}{9BlZ9]i)[,C.Atde[}C+ESBs(E/qM)@2M?O=6-fi~H5`3y}&}vQ<lj' );
define( 'NONCE_SALT',       'G^y4Kpl+;[r 4_j5<I,#b|=r?e>0:mUPx&*Uz#eQQ$FH,erT=BH;KNopU?I8Oag7' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'cde_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
