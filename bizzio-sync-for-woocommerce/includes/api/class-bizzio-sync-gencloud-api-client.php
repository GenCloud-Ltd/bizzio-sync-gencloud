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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The base API client class.
 *
 * Holds the credentials and the SOAP transport shared by all Bizzio API methods.
 *
 * @since      1.1.0
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
		$this->api_database = (string) get_option( 'bizzio_api_database', '' );
		$this->api_username = (string) get_option( 'bizzio_api_username', '' );
		$this->api_password = (string) get_option( 'bizzio_api_password', '' );
		$this->id_site      = (string) get_option( 'bizzio_id_site', '' );
	}

	/**
	 * Build the SOAP envelope with the authentication header around a request body.
	 *
	 * All values are XML-escaped so credentials containing &, < or > cannot break the request.
	 *
	 * @since    1.1.1
	 * @param    string $body_xml The inner <soapenv:Body> content.
	 * @return   string The full SOAP envelope.
	 */
	protected function build_envelope( $body_xml ) {
		return '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:biz="http://schemas.datacontract.org/2004/07/Bizzio.Srv.Extensions.RiznShop" xmlns:arr="http://schemas.microsoft.com/2003/10/Serialization/Arrays">
			<soapenv:Header>
				<tem:Authentication xmlns:tem="http://tempuri.org/">
					<biz:Database xmlns:biz="http://schemas.datacontract.org/2004/07/Bizzio.Srv.Extensions.RiznShop">' . esc_xml( $this->api_database ) . '</biz:Database>
					<biz:Username xmlns:biz="http://schemas.datacontract.org/2004/07/Bizzio.Srv.Extensions.RiznShop">' . esc_xml( $this->api_username ) . '</biz:Username>
					<biz:Password xmlns:biz="http://schemas.datacontract.org/2004/07/Bizzio.Srv.Extensions.RiznShop">' . esc_xml( $this->api_password ) . '</biz:Password>
				</tem:Authentication>
			</soapenv:Header>
			<soapenv:Body>
				' . $body_xml . '
			</soapenv:Body>
		</soapenv:Envelope>';
	}

	/**
	 * Helper function to make SOAP requests.
	 *
	 * @since    1.1.0
	 * @param    string $soap_action  The SOAPAction header.
	 * @param    string $request_body The XML request body.
	 * @return   array|WP_Error The response array or WP_Error on failure.
	 */
	protected function make_soap_request( $soap_action, $request_body ) {
		$args = array(
			'body'    => $request_body,
			'headers' => array(
				'Content-Type' => 'text/xml; charset=utf-8',
				'SOAPAction'   => $soap_action,
			),
			'method'  => 'POST',
			'timeout' => 60,
		);

		return wp_remote_post( $this->endpoint, $args );
	}

	/**
	 * Process Bizzio API response and handle common errors.
	 *
	 * @since    1.1.0
	 * @param    SimpleXMLElement|WP_Error $xml     The SimpleXMLElement object or WP_Error on failure.
	 * @param    string                    $context Context for error messages (e.g., 'categories', 'products').
	 * @return   SimpleXMLElement|WP_Error SimpleXMLElement on success, WP_Error on failure.
	 */
	protected function process_bizzio_response( $xml, $context ) {
		if ( is_wp_error( $xml ) ) {
			return new WP_Error(
				'bizzio_api_error',
				sprintf(
					/* translators: 1: context, 2: error message */
					esc_html__( 'Error fetching %1$s: %2$s', 'bizzio-sync-for-woocommerce' ),
					esc_html( $context ),
					esc_html( $xml->get_error_message() )
				)
			);
		}

		$namespaces         = $xml->getNamespaces( true );
		$body               = $xml->children( $namespaces['s'] )->Body; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- SOAP element name.
		$response_container = $body->children( $namespaces[''] );

		// Determine the correct response element based on context.
		$response_element_name = '';
		if ( 'categories' === $context || 'connection_test' === $context ) {
			$response_element_name = 'GetSiteGroupsResponse';
		} elseif ( 'products' === $context ) {
			$response_element_name = 'GetArticlesResponse';
		}

		$response_element = $response_container->{$response_element_name};

		if ( ! $response_element ) {
			return new WP_Error(
				'bizzio_invalid_response',
				sprintf(
					/* translators: %s: context */
					esc_html__( 'Invalid response element for %s.', 'bizzio-sync-for-woocommerce' ),
					esc_html( $context )
				)
			);
		}

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Property names mirror the Bizzio SOAP schema.
		$error_code    = (string) $response_element->ErrorCode;
		$error_message = (string) $response_element->ErrorMessage;
		$error_type    = (string) $response_element->ErrorType;
		// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

		if ( '0' !== $error_code || 'Success' !== $error_type ) {
			return new WP_Error(
				'bizzio_api_error',
				sprintf(
					/* translators: 1: context, 2: error message, 3: error code */
					esc_html__( 'API Error fetching %1$s: %2$s (Code: %3$s). Please contact Bizzio administrator for assistance.', 'bizzio-sync-for-woocommerce' ),
					esc_html( $context ),
					esc_html( $error_message ),
					esc_html( $error_code )
				),
				array( 'response' => $xml->asXML() )
			);
		}

		return $response_element;
	}
}
