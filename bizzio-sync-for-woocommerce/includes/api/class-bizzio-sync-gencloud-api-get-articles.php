<?php
/**
 * The GetArticles API class.
 *
 * @link       https://gencloud.bg
 * @since      1.1.0
 *
 * @package    Bizzio_Sync_Gencloud
 * @subpackage Bizzio_Sync_Gencloud/includes/api
 */
class Bizzio_Sync_Gencloud_Api_Get_Articles extends Bizzio_Sync_Gencloud_Api_Client {

	/**
	 * Fetch articles from the Bizzio API.
	 *
	 * @since    1.1.0
	 * @param    array $args Optional arguments for the request.
	 * @return   array|WP_Error Array of parsed articles or WP_Error on failure.
	 */
	public function fetch( $args = array() ) {
		$defaults = array(
			'available_only'       => false,
			'barcodes'             => null,
			'currency'             => null,
			'is_cars'              => false,
			'is_files'             => true,
			'is_qty_by_warehouses' => false,
		);

		$args = wp_parse_args( $args, $defaults );

		$soap_action  = 'http://tempuri.org/IRiznShopExtService/GetArticles';
		$request_body = $this->_build_get_articles_request_body( $args );

		$response = $this->_make_soap_request( $soap_action, $request_body );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );
		$xml  = simplexml_load_string( $body );

		if ( false === $xml ) {
			return new WP_Error( 'bizzio_xml_error', __( 'Failed to parse XML response for products.', 'bizzio-sync-for-woocommerce' ) );
		}

		$response_element = $this->_process_bizzio_response( $xml, 'products' );

		if ( is_wp_error( $response_element ) ) {
			return $response_element;
		}

		return $this->_parse_articles_response( $xml, $response_element );
	}

	/**
	 * Build the GetArticlesRequest XML body.
	 *
	 * @since    1.2.0
	 * @param    array $args Request arguments.
	 * @return   string The XML request body.
	 */
	private function _build_get_articles_request_body( $args ) {
		$available_only       = $args['available_only'] ? 'true' : 'false';
		$is_cars              = $args['is_cars'] ? 'true' : 'false';
		$is_files             = $args['is_files'] ? 'true' : 'false';
		$is_qty_by_warehouses = $args['is_qty_by_warehouses'] ? 'true' : 'false';

		return '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:biz="http://schemas.datacontract.org/2004/07/Bizzio.Srv.Extensions.RiznShop" xmlns:arr="http://schemas.microsoft.com/2003/10/Serialization/Arrays">
			<soapenv:Header>
				<tem:Authentication xmlns:tem="http://tempuri.org/">
					<biz:Database xmlns:biz="http://schemas.datacontract.org/2004/07/Bizzio.Srv.Extensions.RiznShop">' . $this->api_database . '</biz:Database>
					<biz:Username xmlns:biz="http://schemas.datacontract.org/2004/07/Bizzio.Srv.Extensions.RiznShop">' . $this->api_username . '</biz:Username>
					<biz:Password xmlns:biz="http://schemas.datacontract.org/2004/07/Bizzio.Srv.Extensions.RiznShop">' . $this->api_password . '</biz:Password>
				</tem:Authentication>
			</soapenv:Header>
			<soapenv:Body>
				<tem:GetArticlesRequest xmlns:tem="http://tempuri.org/">
					<tem:AvailableOnly>' . $available_only . '</tem:AvailableOnly>
					<tem:Barcodes xsi:nil="true" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"/>
					<tem:Currency xsi:nil="true" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"/>
					<tem:ID_Site>' . $this->id_site . '</tem:ID_Site>
					<tem:IsCars>' . $is_cars . '</tem:IsCars>
					<tem:IsFiles>' . $is_files . '</tem:IsFiles>
					<tem:IsQtyByWarehouses>' . $is_qty_by_warehouses . '</tem:IsQtyByWarehouses>
				</tem:GetArticlesRequest>
			</soapenv:Body>
		</soapenv:Envelope>';
	}

	/**
	 * Parse the GetArticles XML response into a structured array.
	 *
	 * @since    1.2.0
	 * @param    SimpleXMLElement $xml              The full XML response.
	 * @param    SimpleXMLElement $response_element The GetArticlesResponse element.
	 * @return   array Array of parsed articles.
	 */
	private function _parse_articles_response( $xml, $response_element ) {
		$namespaces = $xml->getNamespaces( true );
		$articles   = array();

		if ( ! isset( $response_element->Articles ) || ! isset( $response_element->Articles->children( $namespaces['a'] )->AI ) ) {
			return $articles;
		}

		foreach ( $response_element->Articles->children( $namespaces['a'] )->AI as $article ) {
			$article_data = array(
				'Name'         => (string) $article->Name,
				'Barcode'      => (string) $article->Barcode,
				'P_Sale'       => (float) $article->P_Sale,
				'Qty'          => (int) $article->Qty,
				'Description'  => '',
				'SiteArticles' => array(),
				'SiteProps'    => array(),
				'Files'        => array(),
			);

			// Parse Files (images).
			if ( isset( $article->Files ) ) {
				$files = $article->Files->children( $namespaces['a'] );
				foreach ( $files->FI as $file_info ) {
					$article_data['Files'][] = array(
						'ID'   => (string) $file_info->ID,
						'Name' => (string) $file_info->Name,
						'Uri'  => (string) $file_info->Uri,
					);
				}
			}

			// Parse Props (description with HTML).
			if ( isset( $article->Props ) ) {
				$props = $article->Props->children( $namespaces['b'] );
				foreach ( $props as $prop ) {
					if ( strpos( (string) $prop, '<p>' ) !== false ) {
						$article_data['Description'] = (string) $prop;
						break;
					}
				}
			}

			// Parse SiteArticles (category IDs).
			if ( isset( $article->SiteArticles ) ) {
				$site_articles = $article->SiteArticles->children( $namespaces['a'] );
				foreach ( $site_articles as $site_article ) {
					$article_data['SiteArticles'][] = (string) $site_article->ID_SiteGroup;
				}
			}

			// Parse SiteProps (additional properties like external URLs).
			if ( isset( $article->SiteProps ) ) {
				$site_props = $article->SiteProps->children( $namespaces['a'] );
				foreach ( $site_props as $site_prop ) {
					if ( isset( $site_prop->Val ) && strpos( (string) $site_prop->Val, 'http' ) === 0 ) {
						$article_data['SiteProps'][] = (string) $site_prop->Val;
					}
				}
			}

			$articles[] = $article_data;
		}

		return $articles;
	}
}
