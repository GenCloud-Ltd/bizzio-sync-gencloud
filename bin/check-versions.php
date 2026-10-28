<?php
/**
 * Release consistency check: every place that carries the plugin version must agree.
 *
 *   php bin/check-versions.php [git-tag]
 *
 * Checks the main file "Version:" header, readme.txt "Stable tag:", the
 * BIZZIO_SYNC_GENCLOUD_VERSION constant, the fallback version in the core class, that
 * readme.txt has a changelog entry for the version and, when a tag is given (v1.2.3 or 1.2.3),
 * that it matches. Prints the version on success (for CI outputs), exits 1 on any mismatch.
 *
 * @package Bizzio_Sync_Gencloud
 */

$slug = 'bizzio-sync-for-woocommerce';
$dir  = dirname( __DIR__ ) . '/' . $slug;
$tag  = $argv[1] ?? '';

$main   = (string) file_get_contents( "$dir/$slug.php" );
$readme = (string) file_get_contents( "$dir/readme.txt" );
$core   = (string) file_get_contents( "$dir/includes/class-bizzio-sync-gencloud.php" );

$found = array(
	'plugin header Version'          => preg_match( '/^\s*\*\s*Version:\s*(\S+)/mi', $main, $m ) ? $m[1] : null,
	'readme.txt Stable tag'          => preg_match( '/^Stable tag:\s*(\S+)/mi', $readme, $m ) ? $m[1] : null,
	'BIZZIO_SYNC_GENCLOUD_VERSION'   => preg_match( "/define\(\s*'BIZZIO_SYNC_GENCLOUD_VERSION',\s*'([^']+)'/", $main, $m ) ? $m[1] : null,
	'core class fallback version'    => preg_match( "/\\\$this->version = '([^']+)'/", $core, $m ) ? $m[1] : null,
);
$version = $found['plugin header Version'];
$errors  = array();

foreach ( $found as $where => $value ) {
	if ( null === $value ) {
		$errors[] = "$where: not found";
	} elseif ( $value !== $version ) {
		$errors[] = "$where is $value, expected $version";
	}
}

if ( $version && ! preg_match( '/^= ' . preg_quote( $version, '/' ) . '\b/m', $readme ) ) {
	$errors[] = "readme.txt has no changelog entry \"= $version ... =\"";
}

if ( '' !== $tag && ltrim( $tag, 'v' ) !== $version ) {
	$errors[] = "git tag $tag does not match plugin version $version";
}

if ( $errors ) {
	fwrite( STDERR, "Version check failed:\n  - " . implode( "\n  - ", $errors ) . "\n" );
	exit( 1 );
}

echo $version . PHP_EOL;
