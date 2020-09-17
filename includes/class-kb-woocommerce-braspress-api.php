<?php

class KB_WooCommerce_Braspress_API {

    use KB_WooCommerce_Braspress_Functions;

    /**
     * @var string
     */
    private $base_uri;

    /**
     * @var string
     */
    private $environment = KB_WOOCOMMERCE_BRASPRESS_ENVIRONMENT_STAGING;

    /**
     * @var string
     */
    private $user;

    /**
     * @var string
     */
    private $password;

    /**
     * @var int
     */
    private $instance_id;

    /**
     * @var int
     */
    private $method_id;

    /**
     * @var string
     */
    private $destination_postcode;

    /**
     * @var string
     */
    private $origin_postcode;

    /**
     * @var array
     */
    private $package;

    /**
     * @var int
     */
    private $type;

    /**
     * @var string
     */
    private $origin_identifier;

    /**
     * @var string
     */
    private $destination_identifier;

    /**
     * @var float
     */
    private $total_weight;

    /**
     * @var float
     */
    private $total_price;

    /**
     * @var int
     */
    private $total_package;

    /**
     * @var string
     */
    private $mode;

    /**
     * @var int
     */
    private $timeout;

    /**
     * KB_WooCommerce_Braspress_API constructor.
     * @param $method_id
     * @param int $instance_id
     * @param int $timeout
     */
    public function __construct($method_id, $instance_id = 0, $timeout = 30 )
    {
        $this->method_id = $method_id;
        $this->instance_id = $instance_id;
        $this->set_timeout($timeout);
    }

    /**
     * @param array $package
     * @return array
     */
    public function get_cubage(array $package): array
    {
        $data = [];
        foreach ( $package['contents'] as $item_id => $values ) {
            $product = $values['data'];
            $qty = $values['quantity'];

            if ($qty > 0 && $product->needs_shipping()) {
                $_height = wc_get_dimension($this->fix_format($product->height), 'm');
                $_width = wc_get_dimension($this->fix_format($product->width), 'm');
                $_length = wc_get_dimension($this->fix_format($product->length), 'm');

                $data[] = [
                    'comprimento' => $_length,
                    'largura' => $_width,
                    'altura' => $_height,
                    'volumes' => $qty,
                ];
            }
        }

        return $data;
    }

    /**
     * @param array $package
     * @return mixed|null
     */
    public function get_shipping(array $package)
    {
        $shipping = null;
		if (!$this->is_available()) {
			return $shipping;
		}

        $args = array(
            'cnpjRemetente' => $this->get_origin_identifier(),
            'cnpjDestinatario' => $this->remove_chars($this->get_destination_identifier()),
            'modal' => $this->get_mode(),
            'tipoFrete' => $this->type,
            'cepOrigem' => $this->remove_chars($this->get_origin_postcode()),
            'cepDestino' => $this->remove_chars($this->get_destination_postcode()),
            'vlrMercadoria' => $this->get_total_price(),
            'peso' => $this->get_total_weight(),
            'volumes' => $this->get_total_package(),
            'cubagem' => $this->get_cubage($package),
        );

        $params = array(
            'headers' => array(
                'Content-Type'  => 'application/json',
                'Authorization' => 'Basic ' . base64_encode("{$this->user}:{$this->password}"),
            ),
            'body' => wp_json_encode($args),
            'method' => 'POST',
            'data_format' => 'body',
        );

        $response = wp_remote_post($this->get_base_uri(), $params);

        if (is_wp_error($response)) {
            error_log(sprintf( "WP_ERROR: %s",  $response->get_error_message()));
        } elseif ( $response['response']['code'] >= 200  && $response['response']['code'] < 300 ) {
            try {
                $response = json_decode(wp_remote_retrieve_body($response), true);
                $shipping = [
                    'id' => $response['id'],
                    'deadline' => $response['prazo'],
                    'total_shipping' => $response['totalFrete'],
                ];
            } catch (Exception $exception) {
                error_log('KB_WooCommerce_Braspress_API invalid Reponse: ' . $exception->getMessage());
            }
        }

        return $shipping;
    }

    private function is_available()
    {
       // TODO: Validar todos os paramns
        return true;
    }

    /**
     * @return mixed
     */
    public function get_base_uri()
    {
        return $this->base_uri;
    }

    /**
     * @param mixed $base_uri
     */
    public function set_base_uri($base_uri): void
    {
        $this->base_uri = $base_uri;
    }

    /**
     * @return mixed
     */
    public function get_environment()
    {
        return $this->environment;
    }

    /**
     * @param mixed $environment
     */
    public function set_environment($environment): void
    {
        $environments = array(
            KB_WOOCOMMERCE_BRASPRESS_ENVIRONMENT_STAGING => 'https://hml-api.braspress.com/',
            KB_WOOCOMMERCE_BRASPRESS_ENVIRONMENT_PRODUCTION => 'https://api.braspress.com/',
        );

        if (!array_key_exists($environment, $environments)) {
            // TODO: tratar error
            die('error');
        }

        $uri = $environments[$this->environment] . 'v1/cotacao/calcular/json';
        $this->set_base_uri($uri);

        $this->environment = $environment;
    }

    /**
     * @return string
     */
    public function get_user(): string
    {
        return $this->user;
    }

    /**
     * @param string $user
     */
    public function set_user(string $user): void
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function get_password(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function set_password(string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function get_instance_id()
    {
        return $this->instance_id;
    }

    /**
     * @param mixed $instance_id
     */
    public function set_instance_id($instance_id): void
    {
        $this->instance_id = $instance_id;
    }

    /**
     * @return mixed
     */
    public function get_method_id()
    {
        return $this->method_id;
    }

    /**
     * @param mixed $method_id
     */
    public function set_method_id($method_id): void
    {
        $this->method_id = $method_id;
    }

    /**
     * @return mixed
     */
    public function get_destination_postcode()
    {
        return $this->destination_postcode;
    }

    /**
     * @param mixed $destination_postcode
     */
    public function set_destination_postcode($destination_postcode): void
    {
        $this->destination_postcode = $destination_postcode;
    }

    /**
     * @return mixed
     */
    public function get_origin_postcode()
    {
        return $this->remove_chars($this->origin_postcode);
    }

    /**
     * @param mixed $origin_postcode
     */
    public function set_origin_postcode($origin_postcode): void
    {
        $this->origin_postcode = $origin_postcode;
    }

    /**
     * @return mixed
     */
    public function get_package()
    {
        return $this->package;
    }

    /**
     * @param mixed $package
     */
    public function set_package($package): void
    {
        $this->package = $package;
    }

    /**
     * @return mixed
     */
    public function get_type()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function set_type($type): void
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function get_origin_identifier()
    {
        return $this->origin_identifier;
    }

    /**
     * @param mixed $origin_identifier
     */
    public function set_origin_identifier($origin_identifier): void
    {
        $this->origin_identifier = $origin_identifier;
    }

    /**
     * @return mixed
     */
    public function get_destination_identifier()
    {
        return $this->destination_identifier;
    }

    /**
     * @param mixed $destination_identifier
     */
    public function set_destination_identifier($destination_identifier): void
    {
        $this->destination_identifier = $destination_identifier;
    }

    /**
     * @return mixed
     */
    public function get_total_weight()
    {
        return $this->total_weight;
    }

    /**
     * @param mixed $total_weight
     */
    public function set_total_weight($total_weight): void
    {
        $this->total_weight = $total_weight;
    }

    /**
     * @return mixed
     */
    public function get_total_price()
    {
        return $this->total_price;
    }

    /**
     * @param mixed $total_price
     */
    public function set_total_price($total_price): void
    {
        $this->total_price = $total_price;
    }

    /**
     * @return mixed
     */
    public function get_total_package()
    {
        return $this->remove_chars($this->total_package);
    }

    /**
     * @param mixed $total_package
     */
    public function set_total_package($total_package): void
    {
        $this->total_package = $total_package;
    }

    /**
     * @return mixed
     */
    public function get_mode()
    {
        return $this->mode;
    }

    /**
     * @param mixed $mode
     */
    public function set_mode($mode): void
    {
        $this->mode = $mode;
    }

    /**
     * @return mixed
     */
    public function get_timeout()
    {
        return $this->timeout;
    }

    /**
     * @param mixed $timeout
     */
    public function set_timeout($timeout): void
    {
        $this->timeout = $timeout;
    }
}
