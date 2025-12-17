<?php
require_once __DIR__ . '/../config/db.php';
include 'partials/header.php';
include 'partials/nav.php';

if (empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
?>
<div class="container">
    <div class="card">
        <h2 class="card-title">Converter</h2>
        <div class="tabbar">
            <button id="tabCurrency" class="active">Currency</button>
            <button id="tabWeight">Weight</button>
            <button id="tabLength">Length</button>
            <button id="tabNumber">Number Sys</button>
        </div>

        <div id="convCurrency" class="grid grid-2">
            <div><label>Amount</label><input id="curAmount" type="number" value="1"></div>
            <div class="row grid-2">
                <div><label>From</label><select id="curFrom">
                        <option value="USD">USD</option>
                        <option value="EUR">EUR</option>
                        <option value="INR">INR</option>
                    </select></div>
                <div><label>To</label><select id="curTo">
                        <option value="USD">USD</option>
                        <option value="EUR">EUR</option>
                        <option value="INR" selected>INR</option>
                    </select></div>
            </div>
            <div class="actions"><button class="btn" id="curConvert">Convert</button><span id="curResult"
                    style="margin-left:auto; font-weight:bold;"></span></div>
        </div>

        <div id="convWeight" class="grid grid-2" style="display:none">
            <div><label>Value</label><input id="wVal" type="number" value="1"></div>
            <div class="row grid-2">
                <div><label>From</label><select id="wFrom">
                        <option value="kg">kg</option>
                        <option value="g">g</option>
                        <option value="lb">lb</option>
                    </select></div>
                <div><label>To</label><select id="wTo">
                        <option value="kg">kg</option>
                        <option value="g">g</option>
                        <option value="lb" selected>lb</option>
                    </select></div>
            </div>
            <div class="actions"><button class="btn" id="wConvert">Convert</button><span id="wResult"
                    style="margin-left:auto; font-weight:bold;"></span></div>
        </div>

        <div id="convLength" class="grid grid-2" style="display:none">
            <div><label>Value</label><input id="lVal" type="number" value="1"></div>
            <div class="row grid-2">
                <div><label>From</label><select id="lFrom">
                        <option value="m">m</option>
                        <option value="km">km</option>
                        <option value="ft">ft</option>
                    </select></div>
                <div><label>To</label><select id="lTo">
                        <option value="m">m</option>
                        <option value="km">km</option>
                        <option value="ft" selected>ft</option>
                    </select></div>
            </div>
            <div class="actions"><button class="btn" id="lConvert">Convert</button><span id="lResult"
                    style="margin-left:auto; font-weight:bold;"></span></div>
        </div>

        <div id="convNumber" class="grid grid-2" style="display:none">
            <div><label>Input</label><input id="nInput" value="10"></div>
            <div class="row grid-2">
                <div><label>From</label><select id="nFrom">
                        <option value="10">Decimal</option>
                        <option value="2">Binary</option>
                        <option value="16">Hex</option>
                    </select></div>
                <div><label>To</label><select id="nTo">
                        <option value="2">Binary</option>
                        <option value="10">Decimal</option>
                        <option value="16" selected>Hex</option>
                    </select></div>
            </div>
            <div class="actions"><button class="btn" id="nConvert">Convert</button><span id="nResult"
                    style="margin-left:auto; font-weight:bold;"></span></div>
        </div>
    </div>
</div>
<script src="assets/js/converter.js"></script>
<?php include 'partials/footer.php'; ?>