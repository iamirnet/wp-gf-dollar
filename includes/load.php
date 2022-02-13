<?php

function i_amir_gf_dollar_front_style () { ?>
    <style>
        .inumber input {
            direction: ltr;
        }
    </style>
<?php }
add_action( 'wp_head', 'i_amir_gf_dollar_front_style' );

add_action('admin_enqueue_scripts', 'i_amir_gf_dollar_admin_style');
function i_amir_gf_dollar_admin_style() {
    global $parent_file;
    if ($parent_file == 'i_amir_gf_dollar') {
        $main_dir = plugin_dir_url(__DIR__);
        wp_enqueue_style('i_amir_gf_dollar_admin_style_main', $main_dir . 'assets/css/styles.css', [], filemtime($main_dir . 'assets/css/styles.css'), 'all');
        wp_enqueue_style('i_amir_gf_dollar_admin_style_bootstrap', $main_dir . 'assets/css/bootstrap.rtl.min.css', [], filemtime($main_dir . 'assets/css/bootstrap.rtl.min.css'), 'all');
        wp_enqueue_script('i_amir_gf_dollar_admin_scripts_jquery', $main_dir . 'assets/js/jquery-1.11.1.js', [], filemtime($main_dir . 'assets/js/jquery-1.11.1.js'));
        wp_enqueue_script('i_amir_gf_dollar_admin_scripts_repeater', $main_dir . 'assets/js/jquery.repeater.min.js', [], filemtime($main_dir . 'assets/js/jquery.repeater.min.js'));
    }
}
function getIAmirGetDollar($count = 1) {
    $defaultValue = intval(get_option("i_amir_gf_dollar_dollar_default"), 30000);
    $response = wp_remote_get('https://dapi.p3p.repl.co/api/?currency=usd');
    if (isset($response['body'])) {
        $body = (array) json_decode($response['body']);
        return getIAmirGFDollarCalcFee((isset($body['Price']) && $body['Price'] ? intval($body['Price']) : $defaultValue) / 10, $count);
    }

    return getIAmirGFDollarCalcFee($defaultValue / 10, $count);
}

function getIAmirGFDollarCalcFee($dollar, $count = 1) {
    $fee_list = get_option("i_amir_gf_dollar_fee_list", [["type"=>"percent","value"=>"","rule"=>"=","count"=>""]]);
    $fee_list = array_filter($fee_list, function ($item) use ($count) {
        if (!isset($item['value']) || !strlen($item['value'])) return false;
        switch ((string) $item['rule']) {
            case "=":
                return $count == $item['count'];
                break;
            case "!=":
                return $count != $item['count'];
                break;
            case ">":
                return $count >= $item['count'];
                break;
            case "<":
                return $count < $item['count'];
                break;
        }
        return false;
    });
    if (count($fee_list)) {
        $fee_value = $fee_list[0]['value'];
        $fee_type = $fee_list[0]['type'];
        $fee = $fee_type == 'percent' ? ($dollar * (intval($fee_value) / 100)) : intval($fee_value);
        $dollar += $fee;
    }
    return ceil($dollar);
}

add_filter( 'gform_product_info', 'add_fee', 10, 3 );
function add_fee( $product_info, $form, $lead ) {
    $dollars = 0;
    foreach ($form['fields'] as $index => $field) {
        if (isset($field['type']) && isset($field['id']) && $field['type'] == 'calc_dollar' && isset($lead[(string) $field['id']]) && strlen($lead[(string) $field['id']])) {
            $dollars += $lead[(string) $field['id']];
        }
    }
    if (isset($product_info['products']) && count($product_info['products'])) {
        $product_info['products'] = array_filter($product_info['products'], function ($value) {
            return $value['price'] != GFCommon::to_money(0, GFCommon::get_currency());
        });
    }
    if ($dollars) {
        $dollar = getIAmirGetDollar($dollars);
        $product_info['products'] = [
            'dollars' => [
                'name' => 'دلار',
                'price' => $dollar,
                'quantity' => $dollars
            ]
        ];
    }
    return $product_info;
}

add_action( 'rest_api_init', function () {
    register_rest_route( 'iamir/api/v1', '/dollar', array(
        'methods' => 'POST',
        'callback' => function () {
            $value = isset($_POST['value']) && $_POST['value'] ? floatval($_POST['value']) : 1;
            $dollar = getIAmirGetDollar($value);
            return [
                'data' => [
                    'status' => true,
                    'price' => $dollar,
                    'amount' => $dollar * $value,
                ],
            ];
        },
    ));
} );