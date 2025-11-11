<?php
/**
 * The GetSiteGroups API class.
 *
 * @link       https://gencloud.bg
 * @since      1.1.0
 *
 * @package    Bizzio_Sync_Gencloud
 * @subpackage Bizzio_Sync_Gencloud/includes/api
 */
class Bizzio_Sync_Gencloud_Api_Get_Site_Groups extends Bizzio_Sync_Gencloud_Api_Client {

	/**
	 * Fetch site groups from the Bizzio API.
	 *
	 * @since    1.1.0
	 * @param    bool $with_files    Whether to include files in the response.
	 * @return   array|WP_Error Array of parsed categories or WP_Error on failure.
	 */
	public function fetch( $with_files = true ) {
		$soap_action  = 'http://tempuri.org/IRiznShopExtService/GetSiteGroups';
		$request_body = $this->build_get_site_groups_request_body( $with_files );

		$response = $this->_make_soap_request( $soap_action, $request_body );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );
		$xml  = simplexml_load_string( $body );

		if ( false === $xml ) {
			return new WP_Error( 'bizzio_xml_error', __( 'Failed to parse XML response for categories.', 'bizzio-sync-for-woocommerce' ) );
		}

		$response_element = $this->_process_bizzio_response( $xml, 'categories' );

		if ( is_wp_error( $response_element ) ) {
			return $response_element;
		}

		return $this->parse_site_groups_response( $xml, $response_element );
	}

	/**
	 * Test API connection using GetSiteGroups.
	 *
	 * @since    1.1.0
	 * @return   true|WP_Error True on success, WP_Error on failure.
	 */
	public function test_connection() {
		$soap_action  = 'http://tempuri.org/IRiznShopExtService/GetSiteGroups';
		$request_body = $this->build_get_site_groups_request_body( false );

		$response = $this->_make_soap_request( $soap_action, $request_body );

		if ( is_wp_error( $response ) ) {
			/* translators: %s: error message */
			return new WP_Error( 'connection_failed', sprintf( esc_html__( 'Connection test failed: %s', 'bizzio-sync-for-woocommerce' ), $response->get_error_message() ) );
		}

		$body = wp_remote_retrieve_body( $response );
		$xml  = simplexml_load_string( $body );

		if ( false === $xml ) {
			return new WP_Error( 'xml_parsing_failed', esc_html__( 'Connection test failed: Failed to parse XML response.', 'bizzio-sync-for-woocommerce' ) );
		}

		$result = $this->_process_bizzio_response( $xml, 'connection_test' );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return true;
	}

	/**
	 * Build the GetSiteGroupsRequest XML body.
	 *
	 * @since    1.2.0
	 * @param    bool $with_files Whether to include files.
	 * @return   string The XML request body.
	 */
	private function build_get_site_groups_request_body( $with_files ) {
		$is_files = $with_files ? 'true' : 'false';

		return '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:biz="http://schemas.datacontract.org/2004/07/Bizzio.Srv.Extensions.RiznShop" xmlns:arr="http://schemas.microsoft.com/2003/10/Serialization/Arrays">
			<soapenv:Header>
				<tem:Authentication xmlns:tem="http://tempuri.org/">
					<biz:Database xmlns:biz="http://schemas.datacontract.org/2004/07/Bizzio.Srv.Extensions.RiznShop">' . $this->api_database . '</biz:Database>
					<biz:Username xmlns:biz="http://schemas.datacontract.org/2004/07/Bizzio.Srv.Extensions.RiznShop">' . $this->api_username . '</biz:Username>
					<biz:Password xmlns:biz="http://schemas.datacontract.org/2004/07/Bizzio.Srv.Extensions.RiznShop">' . $this->api_password . '</biz:Password>
				</tem:Authentication>
			</soapenv:Header>
			<soapenv:Body>
				<tem:GetSiteGroupsRequest xmlns:tem="http://tempuri.org/">
					<tem:ID_Site>' . $this->id_site . '</tem:ID_Site>
					<tem:IsFiles>' . $is_files . '</tem:IsFiles>
				</tem:GetSiteGroupsRequest>
			</soapenv:Body>
		</soapenv:Envelope>';
	}

	/**
	 * Parse the GetSiteGroups XML response into a structured array.
	 *
	 * @since    1.2.0
	 * @param    SimpleXMLElement $xml              The full XML response.
	 * @param    SimpleXMLElement $response_element The GetSiteGroupsResponse element.
	 * @return   array Array of parsed categories.
	 */
	private function parse_site_groups_response( $xml, $response_element ) {
		$namespaces = $xml->getNamespaces( true );
		$categories = array();

		if ( ! isset( $response_element->SiteGroups ) || ! isset( $response_element->SiteGroups->children( $namespaces['a'] )->SG ) ) {
			return $categories;
		}

		foreach ( $response_element->SiteGroups->children( $namespaces['a'] )->SG as $category ) {
			$image_url = '';
			if ( isset( $category->Files ) && isset( $category->Files->children( $namespaces['a'] )->FI ) && isset( $category->Files->children( $namespaces['a'] )->FI->Uri ) ) {
				$image_url = (string) $category->Files->children( $namespaces['a'] )->FI->Uri;
			}

			$categories[] = array(
				'ID'        => (string) $category->ID,
				'ID_Parent' => (string) $category->ID_Parent,
				'Name'      => (string) $category->Name,
				'Note'      => (string) $category->Note,
				'Image'     => $image_url,
			);
		}

		return $categories;
	}
}
