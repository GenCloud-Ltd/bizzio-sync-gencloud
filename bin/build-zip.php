<?php
/**
 * Build the distributable plugin ZIP, honouring the repo-root .distignore (rsync-style patterns,
 * same file the 10up WordPress.org deploy action uses).
 *
 *   php bin/build-zip.php [plugin-slug] [out-dir]
 *
 * @package Bizzio_Sync_Gencloud
 */

$slug    = $argv[1] ?? 'bizzio-sync-for-woocommerce';
$root    = dirname( __DIR__ );
$src     = $root . '/' . $slug;
$out_dir = $argv[2] ?? $root . '/build';

if ( ! is_dir( $src ) ) {
	fwrite( STDERR, "Plugin dir not found: $src\n" );
	exit( 1 );
}

// .distignore lives in the repo root (as 10up/action-wordpress-plugin-deploy expects), so
// Plugin Check does not flag it as a hidden file inside the plugin.
$patterns   = array();
$distignore = is_file( $root . '/.distignore' ) ? $root . '/.distignore' : $src . '/.distignore';
if ( is_file( $distignore ) ) {
	foreach ( file( $distignore, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES ) as $line ) {
		$line = trim( $line );
		if ( '' !== $line && '#' !== $line[0] ) {
			$patterns[] = rtrim( $line, '/' );
		}
	}
}

/**
 * Whether a path relative to the plugin root matches a .distignore pattern.
 * "/x" is anchored to the plugin root; "x" matches that name at any depth.
 */
function bizzio_build_is_ignored( string $rel, array $patterns ): bool {
	$base = basename( $rel );
	foreach ( $patterns as $p ) {
		if ( '/' === $p[0] ) {
			if ( fnmatch( ltrim( $p, '/' ), $rel ) ) {
				return true;
			}
		} elseif ( fnmatch( $p, $base ) || fnmatch( $p, $rel ) ) {
			return true;
		}
	}
	return false;
}

if ( ! is_dir( $out_dir ) ) {
	mkdir( $out_dir, 0777, true );
}
$zip_path = $out_dir . '/' . $slug . '.zip';
if ( is_file( $zip_path ) ) {
	unlink( $zip_path );
}

$zip = new ZipArchive();
if ( true !== $zip->open( $zip_path, ZipArchive::CREATE ) ) {
	fwrite( STDERR, "Cannot create $zip_path\n" );
	exit( 1 );
}

$it = new RecursiveIteratorIterator(
	new RecursiveCallbackFilterIterator(
		new RecursiveDirectoryIterator( $src, FilesystemIterator::SKIP_DOTS ),
		static function ( SplFileInfo $file ) use ( $src, $patterns ) {
			$rel = ltrim( str_replace( '\\', '/', substr( $file->getPathname(), strlen( $src ) ) ), '/' );
			return ! bizzio_build_is_ignored( $rel, $patterns );
		}
	)
);

$count = 0;
foreach ( $it as $file ) {
	$rel = ltrim( str_replace( '\\', '/', substr( $file->getPathname(), strlen( $src ) ) ), '/' );
	$zip->addFile( $file->getPathname(), $slug . '/' . $rel );
	++$count;
}
$zip->close();

echo "Built $zip_path ($count files)\n";
