<?php

declare(strict_types=1);
/**
 * Hybula Looking Glass
 *
 * The LookingGlass class provides all functionality.
 *
 * @copyright 2022 Hybula B.V.
 * @license Mozilla Public License 2.0
 * @version 0.1
 * @since File available since release 0.1
 * @link https://github.com/hybula/lookingglass
 */

namespace Hybula;

class LookingGlass
{
    public const IPV4 = 'ipv4';
    public const IPV6 = 'ipv6';

    public const SESSION_TARGET_HOST = 'target_host';
    public const SESSION_TARGET_METHOD = 'target_method';
    public const SESSION_TOS_CHECKED = 'tos_checked';
    public const SESSION_CALL_BACKEND = 'call_backend';
    public const SESSION_ERROR_MESSAGE = 'error_message';
    public const SESSION_CSRF = 'CSRF';

    public const METHOD_PING = 'ping';
    public const METHOD_PING6 = 'ping6';
    public const METHOD_MTR = 'mtr';
    public const METHOD_MTR6 = 'mtr6';
    public const METHOD_TRACEROUTE = 'traceroute';
    public const METHOD_TRACEROUTE6 = 'traceroute6';

    private const MTR_COUNT = 10;

    /**
     * Validates the config.php file for required constants.
     *
     * @return void
     */
    public static function validateConfig(): void
    {
        //@formatter:off
        if (!defined('LG_TITLE')) {
            die('LG_TITLE not found in config.php');
        }
        if (!defined('LG_LOGO')) {
            die('LG_LOGO not found in config.php');
        }
        if (!defined('LG_LOGO_DARK')) {
            die('LG_LOGO_DARK not found in config.php');
        }
        if (!defined('LG_LOGO_URL')) {
            die('LG_LOGO_URL not found in config.php');
        }
        if (!defined('LG_CSS_OVERRIDES')) {
            die('LG_CSS_OVERRIDES not found in config.php');
        }
        if (!defined('LG_BLOCK_NETWORK')) {
            die('LG_BLOCK_NETWORK not found in config.php');
        }
        if (!defined('LG_BLOCK_LOOKINGGLASS')) {
            die('LG_BLOCK_LOOKINGGLASS not found in config.php');
        }
        if (!defined('LG_BLOCK_SPEEDTEST')) {
            die('LG_BLOCK_SPEEDTEST not found in config.php');
        }
        if (!defined('LG_BLOCK_CUSTOM')) {
            die('LG_BLOCK_CUSTOM not found in config.php');
        }
        if (!defined('LG_CUSTOM_HTML')) {
            die('LG_CUSTOM_HTML not found in config.php');
        }
        if (!defined('LG_CUSTOM_PHP')) {
            die('LG_CUSTOM_PHP not found in config.php');
        }
        if (!defined('LG_LOCATION')) {
            die('LG_LOCATION not found in config.php');
        }
        if (!defined('LG_MAPS_QUERY')) {
            die('LG_MAPS_QUERY not found in config.php');
        }
        if (!defined('LG_FACILITY')) {
            die('LG_FACILITY not found in config.php');
        }
        if (!defined('LG_FACILITY_URL')) {
            die('LG_FACILITY_URL not found in config.php');
        }
        if (!defined('LG_IPV4')) {
            die('LG_IPV4 not found in config.php');
        }
        if (!defined('LG_IPV6')) {
            die('LG_IPV6 not found in config.php');
        }
        if (!defined('LG_METHODS')) {
            die('LG_METHODS not found in config.php');
        }
        if (!defined('LG_LOCATIONS')) {
            die('LG_LOCATIONSnot found in config.php');
        }
        if (!defined('LG_SPEEDTEST_IPERF')) {
            die('LG_SPEEDTEST_IPERF not found in config.php');
        }
        if (!defined('LG_SPEEDTEST_LABEL_INCOMING')) {
            die('LG_SPEEDTEST_LABEL_INCOMING not found in config.php');
        }
        if (!defined('LG_SPEEDTEST_CMD_INCOMING')) {
            die('LG_SPEEDTEST_CMD_INCOMING not found in config.php');
        }
        if (!defined('LG_SPEEDTEST_LABEL_OUTGOING')) {
            die('LG_SPEEDTEST_LABEL_OUTGOING not found in config.php');
        }
        if (!defined('LG_SPEEDTEST_CMD_OUTGOING')) {
            die('LG_SPEEDTEST_CMD_OUTGOING not found in config.php');
        }
        if (!defined('LG_SPEEDTEST_FILES')) {
            die('LG_SPEEDTEST_FILES not found in config.php');
        }
        if (!defined('LG_TERMS')) {
            die('LG_TERMS not found in config.php');
        }
        if (!defined('LG_CHECK_LATENCY')) {
            die('LG_CHECK_LATENCY not found in config.php');
        }
        if (!defined('LG_THEME')) {
            die('LG_THEME not found in config.php');
        }
        //@formatter:on
    }

    /**
     * Starts a PHP session and sets security tokens.
     *
     * @return void
     */
    public static function startSession(): void
    {
        session_name('HYLOOKINGLASS');
        @session_start() or die('Could not start session!');
    }

    /**
     * Validates and checks an IPv4 address.
     *
     * @param  string  $ip  The IPv4 address to validate.
     * @return bool True or false depending on validation.
     */
    public static function isValidIpv4(string $ip): bool
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return true;
        }
        return false;
    }

    /**
     * Validates and checks an IPv6 address.
     *
     * @param  string  $ip  The IPv6 address to validate.
     * @return bool True or false depending on validation.
     */
    public static function isValidIpv6(string $ip): bool
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return true;
        }
        return false;
    }

    /**
     * Validates and checks a host address.
     * Differs from isValidIpvX because it also extracts the host.
     *
     * @param  string  $host  The host to validate.
     * @return string Actual hostname or empty if none found.
     */
    public static function isValidHost(string $host, string $type): string
    {
        $host = str_replace(['http://', 'https://'], '', $host);
        if (!substr_count($host, '.')) {
            return '';
        }

        if (filter_var('https://'.$host, FILTER_VALIDATE_URL)) {
            if ($host = parse_url('https://'.$host, PHP_URL_HOST)) {
                if ($type === self::IPV4 && isset(dns_get_record($host, DNS_A)[0]['ip'])) {
                    return $host;
                }
                if ($type === self::IPV6 && isset(dns_get_record($host, DNS_AAAA)[0]['ipv6'])) {
                    return $host;
                }

                return '';
            }
        }

        return '';
    }

    /**
     * Determine the IP address of the client.
     * Also supports clients behind a proxy, however we need to validate this as this header can be spoofed.
     * The REMOTE_ADDR header is secure because it's populated by the webserver (extracted from TCP packets).
     *
     * @return string The IP address of the client.
     */
    public static function detectIpAddress(): string
    {
        if (php_sapi_name() === 'cli') {
            return '127.0.0.1';
        }

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && filter_var($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP)) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }

    /**
     * Executes a ping command.
     *
     * @param  string  $host  The target host.
     * @param  int  $count  Number of requests.
     * @return bool True on success.
     */
    public static function ping(string $host, int $count = 4): bool
    {
        return self::procExecute('ping -4 -c'.$count.' -w15', $host);
    }

    /**
     * Executes a ping6 command.
     *
     * @param  string  $host  The target host.
     * @param  int  $count  Number of requests.
     * @return bool True on success.
     */
    public static function ping6(string $host, int $count = 4): bool
    {
        return self::procExecute('ping -6 -c'.$count.' -w15', $host);
    }

    /**
     * Executes a mtr command.
     *
     * @param  string  $host  The target host.
     * @return bool True on success.
     */
    public static function mtr(string $host): bool
    {
        return self::procExecute('mtr --raw -n -4 -c '.self::MTR_COUNT, $host);
    }

    /**
     * Executes a mtr6 command.
     *
     * @param  string  $host  The target host.
     * @return bool True on success.
     */
    public static function mtr6(string $host): bool
    {
        return self::procExecute('mtr --raw -n -6 -c '.self::MTR_COUNT, $host);
    }

    /**
     * Executes a traceroute command.
     *
     * @param  string  $host  The target host.
     * @param  int  $failCount  Number of failed hops.
     * @return bool True on success.
     */
    public static function traceroute(string $host, int $failCount = 4): bool
    {
        return self::procExecute('traceroute -4 -w2', $host, $failCount);
    }

    /**
     * Executes a traceroute6 command.
     *
     * @param  string  $host  The target host.
     * @param  int  $failCount  Number of failed hops.
     * @return bool True on success.
     */
    public static function traceroute6(string $host, int $failCount = 4): bool
    {
        return self::procExecute('traceroute -6 -w2', $host, $failCount);
    }

    /**
     * Executes a command and opens pipe for input/output.
     * Directly taken from telephone/LookingGlass (MIT License)
     *
     * @param  string  $cmd  The command to execute.
     * @param  string  $host  The host that is used as param.
     * @param  int  $failCount  Number of consecutive failed hops.
     * @return boolean True on success.
     * @link https://github.com/telephone/LookingGlass/blob/master/LookingGlass/LookingGlass.php#L172
     * @license https://github.com/telephone/LookingGlass/blob/master/LICENCE.txt
     */
    private static function procExecute(string $cmd, string $host, int $failCount = 2): bool
    {
        // define output pipes
        $spec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ];

        // sanitize + remove single quotes
        $host = str_replace('\'', '', filter_var($host, FILTER_SANITIZE_URL));
        // execute command
        $process = proc_open("{$cmd} '{$host}'", $spec, $pipes, null);

        // check pipe exists
        if (!is_resource($process)) {
            return false;
        }

        // check for mtr/traceroute
        if (strpos($cmd, 'mtr') !== false) {
            $type = 'mtr';
            $parser = new Parser();
        } elseif (strpos($cmd, 'traceroute') !== false) {
            $type = 'traceroute';
        } else {
            $type = '';
        }

        $fail = 0;
        $match = 0;
        $traceCount = 0;
        $lastFail = 'start';
        // iterate stdout
        while (($str = fgets($pipes[1], 4096)) != null) {
            // check for output buffer
            if (ob_get_level() == 0) {
                ob_start();
            }

            // fix RDNS XSS (outputs non-breakble space correctly)
            $str = htmlspecialchars(trim($str));

            // correct output for mtr
            if ($type === 'mtr') {
                // correct output for mtr
                $parser->update($str);
                echo '@@@'.PHP_EOL.$parser->__toString().PHP_EOL.str_pad('', 4096).PHP_EOL;

                // flush output buffering
                @ob_flush();
                flush();
                continue;
            } // correct output for traceroute
            elseif ($type === 'traceroute') {
                if ($match < 10 && preg_match('/^[0-9] /', $str, $string)) {
                    $str = preg_replace('/^[0-9] /', '&nbsp;'.$string[0], $str);
                    $match++;
                }
                // check for consecutive failed hops
                if (strpos($str, '* * *') !== false) {
                    $fail++;
                    if ($lastFail !== 'start'
                        && ($traceCount - 1) === $lastFail
                        && $fail >= $failCount
                    ) {
                        echo str_pad($str.'<br />-- Traceroute timed out --<br />', 4096, ' ', STR_PAD_RIGHT);
                        break;
                    }
                    $lastFail = $traceCount;
                }
                $traceCount++;
            }

            // pad string for live output
            echo str_pad($str.'<br />', 4096, ' ', STR_PAD_RIGHT);

            // flush output buffering
            @ob_flush();
            flush();
        }

        // iterate stderr
        while (($err = fgets($pipes[2], 4096)) != null) {
            // check for IPv6 hostname passed to IPv4 command, and vice versa
            if (strpos($err, 'Name or service not known') !== false || strpos($err, 'unknown host') !== false) {
                echo 'Unauthorized request';
                break;
            }
        }

        $status = proc_get_status($process);
        if ($status['running']) {
            // close pipes that are still open
            foreach ($pipes as $pipe) {
                fclose($pipe);
            }
            if ($status['pid']) {
                // retrieve parent pid
                //$ppid = $status['pid'];
                // use ps to get all the children of this process
                $pids = preg_split('/\s+/', 'ps -o pid --no-heading --ppid '.$status['pid']);
                // kill remaining processes
                foreach ($pids as $pid) {
                    if (is_numeric($pid)) {
                        posix_kill((int)$pid, 9);
                    }
                }
            }
            proc_close($process);
        }
        return true;
    }

    public static function getLatency(): float
    {
        $getLatency = self::getLatencyFromSs(self::detectIpAddress());
        if (isset($getLatency[0])) {
            return round((float)$getLatency[0]['latency']);
        } else {
            return 0.00;
        }
    }

    /**
     * This uses the command 'ss' in order to find out latency.
     * A clever way coded by @ayyylias, so please keep credits and do not just steal.
     *
     * @param  string  $ip  The command to execute.
     * @return array  Returns an array with results.
     */
    private static function getLatencyFromSs(string $ip): array
    {
        $ssPath = exec('which ss 2>/dev/null');
        if (empty($ssPath)) {
            // RHEL based systems;
            $ssPath = '/usr/sbin/ss';
        }
        $lines = shell_exec("$ssPath -Hintp state established");
        $ss = [];
        $i = 0;
        $j = 0;
        foreach (explode(PHP_EOL, $lines) as $line) {
            if ($i > 1) {
                $i = 0;
                $j++;
            }
            if ($line !== '') {
                @$ss[$j] .= $line;
                $i++;
            }
        }
        $output = [];
        foreach ($ss as $socket) {
            $socket = preg_replace('!\s+!', ' ', $socket);
            $explodedsocket = explode(' ', $socket);
            preg_match('/\d+\.\d+\.\d+\.\d+|\[[:a-fA-F0-9]+\]/', $explodedsocket[2], $temp);
            if (!isset($temp[0])) {
                continue;
            }
            $sock['local'] = $temp[0];
            preg_match('/\d+\.\d+\.\d+\.\d+|\[[:a-fA-F0-9]+\]/', $explodedsocket[3], $temp);
            if (preg_match('/^\[(.*)\]$/', $temp[0], $matches)) { $temp[0] = $matches[1]; }
            $sock['remote'] = $temp[0];
            preg_match('/segs_out:(\d+)/', $socket, $temp);
            $sock['segs_out'] = $temp[1];
            preg_match('/segs_in:(\d+)/', $socket, $temp);
            $sock['segs_in'] = $temp[1];
            preg_match_all('/rtt:(\d+\.\d+)\/(\d+\.\d+)/', $socket, $temp);
            $sock['latency'] = $temp[1][0];
            $sock['jitter'] = $temp[2][0];
            preg_match_all('/retrans:\d+\/(\d+)/', $socket, $temp);
            $sock['retransmissions'] = (isset($temp[1][0]) ? $temp[1][0] : 0);
            if ($sock['remote'] == $ip) {
                $output[] = $sock;
            }
        }
        return $output;
    }
}

class Hop
{
    /** @var int */
    public $idx;
    /** @var string */
    public $asn = '';
    /** @var float */
    public $avg = 0.0;
    /** @var int */
    public $loss = 0;
    /** @var float */
    public $stdev = 0.0;
    /** @var int */
    public $sent = 0;
    /** @var int */
    public $recieved = 0;
    /** @var float */
    public $last = 0.0;
    /** @var float */
    public $best = 0.0;
    /** @var float */
    public $worst = 0.0;

    /** @var string[] */
    public $ips = [];
    /** @var string[] */
    public $hosts = [];
    /** @var float[] */
    public $timings = [];

}

class RawHop
{
    /** @var string */
    public $dataType;
    /** @var int */
    public $idx;
    /** @var string */
    public $value;
}

class Parser
{
    /** @var Hop[] */
    protected $hopsCollection = [];
    /** @var int */
    private $hopCount = 0;
    /** @var int */
    private $outputWidth = 38;

    public function __construct()
    {
        putenv('RES_OPTIONS=retrans:1 retry:1 timeout:1 attempts:1');
    }

    public function __toString(): string
    {
        $str = '';
        foreach ($this->hopsCollection as $index => $hop) {
            $host = $hop->hosts[0] ?? $hop->ips[0] ?? '???';

            if (strlen($host) > $this->outputWidth) {
                $this->outputWidth = strlen($host);
            }

            $hop->recieved = count($hop->timings);
            if (count($hop->timings)) {
                $hop->last = $hop->timings[count($hop->timings) - 1];
                $hop->best = $hop->timings[0];
                $hop->worst = $hop->timings[0];
                $hop->avg = array_sum($hop->timings) / count($hop->timings);
            }

            if (count($hop->timings) > 1) {
                $hop->stdev = $this->stDev($hop->timings);
            }

            foreach ($hop->timings as $time) {
                if ($hop->best > $time) {
                    $hop->best = $time;
                }

                if ($hop->worst < $time) {
                    $hop->worst = $time;
                }
            }

            $hop->loss = $hop->sent ? (100 * ($hop->sent - $hop->recieved)) / $hop->sent : 100;

            $str = sprintf(
                "%s%2d.|-- %s%3d.0%%   %3d  %5.1f %5.1f %5.1f %5.1f %5.1f\n",
                $str,
                $index,
                str_pad($host, $this->outputWidth + 3, ' ', STR_PAD_RIGHT),
                $hop->loss,
                $hop->sent,
                $hop->last,
                $hop->avg,
                $hop->best,
                $hop->worst,
                $hop->stdev
            );
        }

        return sprintf("       Host%sLoss%%   Snt   Last   Avg  Best  Wrst StDev\n%s", str_pad('', $this->outputWidth + 7, ' ', STR_PAD_RIGHT), $str);
    }

    private function stDev(array $array): float
    {
        $sdSquare = function ($x, $mean) {
            return pow($x - $mean, 2);
        };

        // square root of sum of squares devided by N-1
        return sqrt(array_sum(array_map($sdSquare, $array, array_fill(0, count($array), (array_sum($array) / count($array))))) / (count($array) - 1));
    }

    public function update($rawMtrInput)
    {
        //Store each line of output in rawhop structure
        $things = explode(' ', $rawMtrInput);

        if (count($things) !== 3 && (count($things) !== 4 && $things[0] === 'p')) {
            return;
        }

        $rawHop = new RawHop();
        $rawHop->dataType = $things[0];
        $rawHop->idx = (int)$things[1];
        $rawHop->value = $things[2];

        if ($this->hopCount < $rawHop->idx + 1) {
            $this->hopCount = $rawHop->idx + 1;
        }

        if (!isset($this->hopsCollection[$rawHop->idx])) {
            $this->hopsCollection[$rawHop->idx] = new Hop();
        }

        $hop = $this->hopsCollection[$rawHop->idx];
        $hop->idx = $rawHop->idx;
        switch ($rawHop->dataType) {
            case 'h':
                $hop->ips[] = $rawHop->value;
                $hop->hosts[] = gethostbyaddr($rawHop->value) ?: null;
                break;
            case 'd':
                //Not entirely sure if multiple IPs. Better use -n in mtr and resolve later in summarize.
                //out.Hops[data.idx].Host = append(out.Hops[data.idx].Host, data.value)
                break;
            case 'p':
                $hop->sent++;
                $hop->timings[] = (float)$rawHop->value / 1000;
                break;
        }

        $this->hopsCollection[$rawHop->idx] = $hop;

        $this->filterLastDupeHop();
    }

    // Function to calculate standard deviation (uses sd_square)

    private function filterLastDupeHop()
    {
        // filter dupe last hop
        $finalIdx = 0;
        $previousIp = '';

        foreach ($this->hopsCollection as $key => $hop) {
            if (count($hop->ips) && $hop->ips[0] !== $previousIp) {
                $previousIp = $hop->ips[0];
                $finalIdx = $key + 1;
            }
        }

        unset($this->hopsCollection[$finalIdx]);

        usort($this->hopsCollection, function ($a, $b) {
            return $a->idx - $b->idx;
        });
    }
}
