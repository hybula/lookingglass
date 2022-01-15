<?php
/**
 * Hybula Looking Glass
 *
 * Provides UI and input for the looking glass backend.
 *
 * @copyright 2022 Hybula B.V.
 * @license Mozilla Public License 2.0
 * @version 0.1
 * @since File available since release 0.1
 * @link https://github.com/hybula/lookingglass
 */

declare(strict_types=1);

require __DIR__.'/config.php';
require __DIR__.'/LookingGlass.php';

use Hybula\LookingGlass;

LookingGlass::validateConfig();
LookingGlass::startSession();
$detectIpAddress = LookingGlass::detectIpAddress();

if (!empty($_POST)) {
    do {
        if (!isset($_POST['csrfToken']) || ($_POST['csrfToken'] != $_SESSION['CSRF'])) {
            $errorMessage = 'Missing or incorrect CSRF token.';
        break;
        }
        if (isset($_POST['submitForm'])) {
            if (!in_array($_POST['backendMethod'], LG_METHODS)) {
                $errorMessage = 'Unsupported backend method.';
                break;
            }
            $_SESSION['METHOD'] = $_POST['backendMethod'];
            $_SESSION['TARGET'] = $_POST['targetHost'];
            if (!isset($_POST['checkTerms']) && LG_TERMS) {
                $errorMessage = 'You must agree with the Terms of Service.';
                break;
            }

            if (in_array($_POST['backendMethod'], ['ping', 'mtr', 'traceroute'])) {
                if (!LookingGlass::isValidIpv4($_POST['targetHost'])) {
                    $targetHost = LookingGlass::isValidHost($_POST['targetHost'], 'ipv4');
                    if (!$targetHost) {
                        $errorMessage = 'No valid IPv4 provided.';
                        break;
                    }
                    $_SESSION['TARGET'] = $targetHost;
                }
            }
            if (in_array($_POST['backendMethod'], ['ping6', 'mtr6', 'traceroute6'])) {
                if (!LookingGlass::isValidIpv6($_POST['targetHost'])) {
                    $targetHost = LookingGlass::isValidHost($_POST['targetHost'], 'ipv6');
                    if (!$targetHost) {
                        $errorMessage = 'No valid IPv6 provided.';
                        break;
                    }
                    $_SESSION['TARGET'] = $targetHost;
                }
            }
            $_SESSION['TERMS'] = true;
            $callBackend = true;
            break;
        }
        $errorMessage = 'Unsupported POST received.';
        break;
    } while (true);
}

$_SESSION['CSRF'] = bin2hex(random_bytes(12));

if (LG_BLOCK_CUSTOM) {
    include LG_CUSTOM_PHP;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1" name="viewport">
    <meta content="" name="description">
    <meta content="Hybula" name="author">
    <title><?php echo LG_TITLE; ?></title>
    <link crossorigin="anonymous" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" rel="stylesheet">
    <?php if (LG_CSS_OVERRIDES) { echo '<link href="'.LG_CSS_OVERRIDES.'" rel="stylesheet">'; } ?>
</head>
<body>

<div class="col-lg-6 mx-auto p-3 py-md-5">

    <header class="d-flex align-items-center pb-3 mb-5 border-bottom">
            <div class="col-8">
                <a class="d-flex align-items-center text-dark text-decoration-none" href="<?php echo LG_LOGO_URL; ?>" target="_blank">
                    <?php echo LG_LOGO; ?>
                </a>
            </div>
            <div class="col-4 float-end">
                <select class="form-select" onchange="window.location = this.options[this.selectedIndex].value">
                    <option selected><?php echo LG_LOCATION; ?></option>
                    <?php foreach (LG_LOCATIONS as $location => $link) { ?>
                        <option value="<?php echo $link; ?>"><?php echo $location; ?></option>
                    <?php } ?>
                </select>
            </div>
    </header>

    <main>

        <?php if (LG_BLOCK_NETWORK) { ?>
        <div class="row mb-5">
            <div class="card shadow-lg">
                <div class="card-body p-3">
                    <h1 class="fs-4 card-title mb-4">Network</h1>

                    <div class="row mb-3">
                        <div class="col-md-7">
                            <label class="mb-2 text-muted">Location</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" value="<?php echo LG_LOCATION; ?>" onfocus="this.select()" readonly="">
                                <a class="btn btn-outline-secondary" href="https://www.openstreetmap.org/search?query=<?php echo urlencode(LG_LOCATION); ?>" target="_blank">Map</a>
                                <?php if (!empty(LG_LOCATIONS)) { ?>
                                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Locations</button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <?php foreach (LG_LOCATIONS as $location => $link) { ?>
                                    <li><a class="dropdown-item" href="<?php echo $link; ?>"><?php echo $location; ?></a></li>
                                    <?php } ?>
                                </ul>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <label class="mb-2 text-muted">Facility</label>
                            <div class="input-group">
                                <input type="text" class="form-control" value="<?php echo LG_FACILITY; ?>" onfocus="this.select()" readonly="">
                                <a href="<?php echo LG_FACILITY_URL; ?>" class="btn btn-outline-secondary" target="_blank">PeeringDB</a>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="mb-2 text-muted">Test IPv4</label>
                            <div class="input-group">
                                <input type="text" class="form-control" value="<?php echo LG_IPV4; ?>" onfocus="this.select()" readonly="">
                                <button class="btn btn-outline-secondary" onclick="copyToClipboard('<?php echo LG_IPV4; ?>', this)">Copy</button>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <label class="mb-2 text-muted">Test IPv6</label>
                            <div class="input-group">
                                <input type="text" class="form-control" value="<?php echo LG_IPV6; ?>" onfocus="this.select()" readonly="">
                                <button class="btn btn-outline-secondary" onclick="copyToClipboard('<?php echo LG_IPV6; ?>', this)">Copy</button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="mb-2 text-muted">Your IP</label>
                            <div class="input-group">
                                <input type="text" class="form-control" value="<?php echo $detectIpAddress; ?>" onfocus="this.select()" readonly="">
                                <button class="btn btn-outline-secondary" onclick="copyToClipboard('<?php echo $detectIpAddress; ?>', this)">Copy</button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <?php } ?>

        <?php if (LG_BLOCK_LOOKINGGLAS) { ?>
        <div class="row pb-5">
            <div class="card shadow-lg">
                <div class="card-body p-3">
                    <h1 class="fs-4 card-title mb-4">Looking Glass</h1>
                    <form method="POST" action="/" autocomplete="off">
                        <input type="hidden" name="csrfToken" value="<?php echo $_SESSION['CSRF']; ?>">

                        <div class="row">
                            <div class="col-md-7 mb-3">
                                <div class="input-group">
                                    <span class="input-group-text" id="basic-addon1">Target</span>
                                    <input type="text" class="form-control" placeholder="IP address or host..." name="targetHost" value="<?php if (isset($_SESSION['TARGET'])) { echo $_SESSION['TARGET']; } ?>" required="">
                                </div>
                            </div>
                            <div class="col-md-5 mb-3">
                                <div class="input-group">
                                    <label class="input-group-text">Method</label>
                                    <select class="form-select" name="backendMethod" id="backendMethod">
                                        <?php foreach (LG_METHODS as $method) { ?>
                                            <option value="<?php echo $method; ?>"<?php if (isset($_SESSION['METHOD']) && $_SESSION['METHOD'] == $method) { echo 'selected'; } ?>><?php echo $method; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center">
                            <?php if (LG_TERMS) { ?>
                            <div class="form-check">
                                <input type="checkbox" id="checkTerms" name="checkTerms" class="form-check-input"<?php if (isset($_SESSION['TERMS'])) { echo 'checked'; } ?>>
                                <label for="checkTerms" class="form-check-label">I agree with the <a href="<?php echo LG_TERMS; ?>" target="_blank">Terms of Use</a></label>
                            </div>
                            <?php } ?>
                            <button type="submit" class="btn btn-primary ms-auto" id="executeButton" name="submitForm">
                                Execute
                            </button>
                        </div>

                        <?php if (isset($errorMessage)) echo '<div class="alert alert-danger mt-3" role="alert">'.$errorMessage.'</div>'; ?>

                        <div class="card card-body bg-light mt-4" style="display: none;" id="outputCard">
                            <pre id="outputContent" style="overflow: hidden; white-space: pre; word-wrap: normal;"></pre>
                        </div>
                    </form>

                </div>
            </div>
        </div>
        <?php } ?>

        <?php if (LG_BLOCK_SPEEDTEST) { ?>
        <div class="row pb-5">
            <div class="card shadow-lg">
                <div class="card-body p-3">
                    <h1 class="fs-4 card-title mb-4">Speedtest</h1>

                    <?php if (LG_SPEEDTEST_IPERF) { ?>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="mb-2 text-muted"><?php echo LG_SPEEDTEST_LABEL_INCOMING; ?></label>
                            <p><code><?php echo LG_SPEEDTEST_CMD_INCOMING; ?></code></p>
                            <button class="btn btn-sm btn-outline-secondary" onclick="copyToClipboard('<?php echo LG_SPEEDTEST_CMD_INCOMING; ?>', this)">Copy</button>
                        </div>
                        <div class="col-md-6">
                            <label class="mb-2 text-muted"><?php echo LG_SPEEDTEST_LABEL_OUTGOING; ?></label>
                            <p><code><?php echo LG_SPEEDTEST_CMD_OUTGOING; ?></code></p>
                            <button class="btn btn-sm btn-outline-secondary" onclick="copyToClipboard('<?php echo LG_SPEEDTEST_CMD_OUTGOING; ?>', this)">Copy</button>
                        </div>
                    </div>
                    <?php } ?>

                    <div class="row">
                        <label class="mb-2 text-muted">Test Files</label>
                        <div class="btn-group input-group mb-3">
                            <?php foreach (LG_SPEEDTEST_FILES as $file => $link) { ?>
                                <a href="<?php echo $link; ?>" class="btn btn-outline-secondary"><?php echo $file; ?></a>
                            <?php } ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <?php } ?>

        <?php if (LG_BLOCK_CUSTOM) { include LG_CUSTOM_HTML; } ?>


    </main>
    <footer class="pt-3 mt-5 my-5 text-muted border-top">
        Powered by <a href="https://github.com/hybula/lookingglass" target="_blank">Hybula Looking Glass</a>
        <a href="https://github.com/hybula/lookingglass" target="_blank" class="float-end"><img src="https://img.shields.io/github/watchers/hybula/lookingglass?style=social" alt="GitHub Watchers"></a>
    </footer>
</div>

<script type="text/javascript">
    <?php if (isset($callBackend)) { echo 'callBackend();'; } ?>
    function callBackend() {
        const executeButton = document.getElementById('executeButton');
        executeButton.innerText = 'Executing...';
        executeButton.disabled = true;
        document.getElementById('outputCard').style.display = 'inherit';
        const xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
            document.getElementById('outputContent').innerHTML = this.responseText.replace(/<br \/> +/g, '<br />');
            if (this.readyState === XMLHttpRequest.DONE) {
                executeButton.innerText = 'Execute';
                executeButton.disabled = false;
                console.log('Backend ready!');
            }
        };
        xhr.open('GET', 'backend.php', true);
        xhr.send();
    }
</script>

<script type="text/javascript">
    async function copyToClipboard(text, button) {
        button.innerHTML = 'Copied!';
        const textAreaObject = document.createElement('textarea');
        textAreaObject.value = text;
        document.body.appendChild(textAreaObject);
        textAreaObject.select();
        document.execCommand('copy');
        document.body.removeChild(textAreaObject);
        await new Promise(r => setTimeout(r, 2000));
        button.innerHTML = 'Copy';
    }
</script>

<script crossorigin="anonymous" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
