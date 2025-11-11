<?php
/**
 * The base API client class.
 *
 * @link       https://gencloud.bg
 * @since      1.1.0
 *
 * @package    Bizzio_Sync_Gencloud
 * @subpackage Bizzio_Sync_Gencloud/includes/api
 */
class Bizzio_Sync_Gencloud_Api_Client {

	/**
	 * The API endpoint.
	 *
	 * @since    1.1.0
	 * @access   private
	 * @var      string    $endpoint    The API endpoint.
	 */
	private $endpoint = 'https://bizzio.gencloud.bg/Services/Extensions/RiznShopExtService.svc';

	/**
	 * The API Database.
	 *
	 * @since    1.1.0
	 * @access   protected
	 * @var      string    $api_database    The API Database.
	 */
	protected $api_database;

	/**
	 * The API Username.
	 *
	 * @since    1.1.0
	 * @access   protected
	 * @var      string    $api_username    The API Username.
	 */
	protected $api_username;

	/**
	 * The API Password.
	 *
	 * @since    1.1.0
	 * @access   protected
	 * @var      string    $api_password    The API Password.
	 */
	protected $api_password;

	/**
	 * The ID Site.
	 *
	 * @since    1.1.0
	 * @access   protected
	 * @var      string    $id_site    The ID Site.
	 */
	protected $id_site;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.1.0
	 */
	public function __construct() {
		$this->api_database = get_option( 'bizzio_api_database' );
		$this->api_username = get_option( 'bizzio_api_username' );
		$this->api_password = get_option( 'bizzio_api_password' );
		$this->id_site      = get_option( 'bizzio_id_site' );
	}

	/**
	 * Helper function to make SOAP requests
	 *
	 * @param string $soap_action The SOAPAction header.
	 * @param string $request_body The XML request body.
	 * @return array|WP_Error The response array or WP_Error on failure.
	 */
	protected function _make_soap_request( $soap_action, $request_body ) {
		if ( defined( 'BIZZIO_SYNC_GENCLOUD_DEBUG_LOG' ) && BIZZIO_SYNC_GENCLOUD_DEBUG_LOG ) {
			$sanitized_request_body = preg_replace( '/(<biz:Database>)(.*)(<\/biz:Database>)/i', '$1[REDACTED]$3', $request_body );
			$sanitized_request_body = preg_replace( '/(<biz:Username>)(.*)(<\/biz:Username>)/i', '$1[REDACTED]$3', $sanitized_request_body );
			$sanitized_request_body = preg_replace( '/(<biz:Password>)(.*)(<\/biz:Password>)/i', '$1[REDACTED]$3', $sanitized_request_body );
		}

		$args = array(
			'body'    => $request_body,
			'headers' => array(
				'Content-Type' => 'text/xml; charset=utf-8',
				'SOAPAction'   => $soap_action,
			),
			'method'  => 'POST',
			'timeout' => 60, // seconds
		);

		$response = wp_remote_post( $this->endpoint, $args );

		return $response;
	}

	/**
	 * Process Bizzio API response and handle common errors.
	 *
	 * @param SimpleXMLElement|WP_Error $xml The SimpleXMLElement object or WP_Error on failure.
	 * @param string                    $context  Context for error messages (e.g., 'categories', 'products').
	 * @return SimpleXMLElement|WP_Error SimpleXMLElement on success, WP_Error on failure.
	 */
	protected function _process_bizzio_response( $xml, $context ) {
		if ( is_wp_error( $xml ) ) {
			/* translators: 1: context, 2: error message */
			return new WP_Error( 'bizzio_api_error', sprintf( esc_html__( 'Error fetching %1$s: %2$s', 'bizzio-sync-for-woocommerce' ), $context, $xml->get_error_message() ) );
		}

		$namespaces         = $xml->getNamespaces( true );
		$body               = $xml->children( $namespaces['s'] )->Body;
		$response_container = $body->children( $namespaces[''] );

		// Determine the correct response element based on context
		$response_element_name = '';
		if ( 'categories' === $context || 'connection_test' === $context ) {
			$response_element_name = 'GetSiteGroupsResponse';
		} elseif ( 'products' === $context ) {
			$response_element_name = 'GetArticlesResponse';
		}

		$responseElement = $response_container->{$response_element_name};

		if ( ! $responseElement ) {
			/* translators: %s: context */
			return new WP_Error( 'bizzio_invalid_response', sprintf( esc_html__( 'Invalid response element for %s.', 'bizzio-sync-for-woocommerce' ), $context ) );
		}

		$error_code    = (string) $responseElement->ErrorCode;
		$error_message = (string) $responseElement->ErrorMessage;
		$error_type    = (string) $responseElement->ErrorType;

		if ( '0' !== $error_code || 'Success' !== $error_type ) {
			return new WP_Error(
				'bizzio_api_error',
				sprintf(
					/* translators: 1: context, 2: error message, 3: error code */
					esc_html__( 'API Error fetching %1$s: %2$s (Code: %3$s). <br> Please contact Bizzio administrator for assistance.', 'bizzio-sync-for-woocommerce' ),
					$context,
					$error_message,
					$error_code
				),
				array( 'response' => $xml->asXML() )
			);
		}

		return $responseElement;
	}
}
