<?php declare(strict_types=1);
/**
 * Hybula Looking Glass
 *
 * Provides UI and input for the looking glass backend.
 *
 * @copyright 2022 Hybula B.V.
 * @license Mozilla Public License 2.0
 * @version 1.1.0
 * @since File available since release 1.1.0
 * @link https://github.com/hybula/lookingglass
 */
use Hybula\LookingGlass;

if (!file_exists(__DIR__ . '/config.php')) {
    die('config.php is not found, but is required for application to work!');
}

require __DIR__ . '/LookingGlass.php';
require __DIR__ . '/config.php';

LookingGlass::validateConfig();
LookingGlass::startSession();

function exitErrorMessage(string $message): void
{
    unset($_SESSION[LookingGlass::SESSION_CALL_BACKEND]);
    $_SESSION[LookingGlass::SESSION_ERROR_MESSAGE] = $message;
    exitNormal();
}

function exitNormal(): void
{
    header('Location: /');
    exit;
}

$templateData           = [
    'title'                    => LG_TITLE,
    'custom_css'               => LG_CSS_OVERRIDES,
    'custom_head'              => LG_CUSTOM_HEAD,
    'logo_url'                 => LG_LOGO_URL,
    'logo_data'                => LG_LOGO,
    //
    'block_network'            => LG_BLOCK_NETWORK,
    'block_lookingglas'        => LG_BLOCK_LOOKINGGLAS,
    'block_speedtest'          => LG_BLOCK_SPEEDTEST,
    'block_custom'             => LG_BLOCK_CUSTOM,
    'custom_html'              => '',
    //
    'locations'                => LG_LOCATIONS,
    'current_location'         => LG_LOCATION,
    'maps_query'               => LG_MAPS_QUERY,
    'facility'                 => LG_FACILITY,
    'facility_url'             => LG_FACILITY_URL,
    'ipv4'                     => LG_IPV4,
    'ipv6'                     => LG_IPV6,
    'methods'                  => LG_METHODS,
    'user_ip'                  => LookingGlass::detectIpAddress(),
    //
    'speedtest_iperf'          => LG_SPEEDTEST_IPERF,
    'speedtest_incoming_label' => LG_SPEEDTEST_LABEL_INCOMING,
    'speedtest_incoming_cmd'   => LG_SPEEDTEST_CMD_INCOMING,
    'speedtest_outgoing_label' => LG_SPEEDTEST_LABEL_OUTGOING,
    'speedtest_outgoing_cmd'   => LG_SPEEDTEST_CMD_OUTGOING,
    'speedtest_files'          => LG_SPEEDTEST_FILES,
    //
    'tos'                      => LG_TERMS,
    'error_message'            => false,
];
